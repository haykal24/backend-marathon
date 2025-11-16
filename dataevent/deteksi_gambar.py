import os
import sys
import csv
import math
import threading
import traceback
from concurrent.futures import ThreadPoolExecutor, as_completed
import time
import shutil

import tkinter as tk
from tkinter import ttk, filedialog, messagebox

from PIL import Image
import imagehash
import cv2
import numpy as np

# --------- Konfigurasi ---------
IMAGE_EXTS = ('.jpg', '.jpeg', '.png', '.bmp', '.webp', '.tiff', '.tif')
DEFAULT_MAX_WORKERS = max(4, os.cpu_count() or 4)

# --------- Util ---------
def list_images(root_folder: str):
    for dirpath, _, filenames in os.walk(root_folder):
        for f in filenames:
            if f.lower().endswith(IMAGE_EXTS):
                yield os.path.join(dirpath, f)

def safe_open_image(path):
    try:
        img = Image.open(path)
        img = img.convert('RGB')
        return img
    except Exception:
        return None

def compute_phash(img_pil):
    # pHash 64-bit (8x8) default imagehash.phash
    return imagehash.phash(img_pil)

def hamming_distance(hash1, hash2):
    # imagehash object supports - operator
    return (hash1 - hash2)

def load_cv_gray(p):
    try:
        im = cv2.imdecode(np.fromfile(p, dtype=np.uint8), cv2.IMREAD_GRAYSCALE)  # Windows-safe untuk unicode path
        if im is None:
            # fallback
            im = cv2.imread(p, cv2.IMREAD_GRAYSCALE)
        return im
    except Exception:
        return None

def orb_match_score(img1_gray, img2_gray, nfeatures=1500):
    """
    Kembalikan tuple (good_matches, total_matches, ratio),
    di mana ratio = good/total (0..1). Jika gagal, kembalikan (0,0,0).
    """
    try:
        orb = cv2.ORB_create(nfeatures=nfeatures)
        kp1, des1 = orb.detectAndCompute(img1_gray, None)
        kp2, des2 = orb.detectAndCompute(img2_gray, None)
        if des1 is None or des2 is None:
            return 0, 0, 0.0

        # BFMatcher Hamming (untuk ORB)
        bf = cv2.BFMatcher(cv2.NORM_HAMMING, crossCheck=True)
        matches = bf.match(des1, des2)
        if not matches:
            return 0, 0, 0.0

        # Semakin kecil distance semakin mirip; ambil "good" menggunakan ambang adaptif
        distances = [m.distance for m in matches]
        if not distances:
            return 0, 0, 0.0
        # Threshold: mean + 0.5*std dev (semakin ketat, semakin sedikit "good")
        mean_d = float(np.mean(distances))
        std_d = float(np.std(distances)) if len(distances) > 1 else 0.0
        thr = mean_d + 0.5 * std_d

        good = [m for m in matches if m.distance <= thr]
        good_cnt = len(good)
        total_cnt = len(matches)
        ratio = good_cnt / total_cnt if total_cnt else 0.0
        return good_cnt, total_cnt, ratio
    except Exception:
        return 0, 0, 0.0

def combined_score(phash_dist, orb_ratio, max_phash_bits=64):
    """
    Skor gabungan untuk pengurutan (lebih kecil = lebih mirip).
    - Normalisasi hamming (0..1) lalu gabungkan dengan (1 - orb_ratio).
    """
    phash_norm = phash_dist / max_phash_bits
    return 0.6 * phash_norm + 0.4 * (1.0 - orb_ratio)

def unique_name_in_folder(folder, filename):
    """
    Menghasilkan nama unik jika sudah ada file dengan nama sama di folder.
    """
    base = os.path.splitext(os.path.basename(filename))[0]
    ext = os.path.splitext(filename)[1]
    candidate = os.path.join(folder, base + ext)
    i = 1
    while os.path.exists(candidate):
        candidate = os.path.join(folder, f"{base}__{i}{ext}")
        i += 1
    return candidate

# --------- App ---------
class ImageMatchFinder(tk.Tk):
    def __init__(self):
        super().__init__()
        self.title("Image Match Finder (Offline)")
        self.geometry("1060x680")
        self.minsize(980, 560)

        # State
        self.query_path = tk.StringVar()
        self.folder_path = tk.StringVar()
        self.phash_threshold = tk.IntVar(value=12)      # 0..64 (0 identik)
        self.min_orb_ratio = tk.DoubleVar(value=0.10)   # 0..1
        self.max_workers = tk.IntVar(value=DEFAULT_MAX_WORKERS)

        self._query_pil = None
        self._query_phash = None
        self._query_gray = None

        self._results = []  # list of dict
        self._scan_thread = None
        self._stop_flag = threading.Event()

        # Untuk checklist/seleksi baris
        self._item_selected = {}   # iid -> bool
        self._iid_to_result = {}   # iid -> dict result
        self._current_target_folder = None  # simpan folder target terakhir untuk fitur hapus

        self._build_ui()

    def _build_ui(self):
        # --- Top Controls ---
        top = ttk.Frame(self, padding=10)
        top.pack(side=tk.TOP, fill=tk.X)

        # Query image
        ttk.Label(top, text="Gambar Query:").grid(row=0, column=0, sticky="w")
        ttk.Entry(top, textvariable=self.query_path, width=60).grid(row=0, column=1, sticky="we", padx=6)
        ttk.Button(top, text="Pilih Gambar", command=self.pick_query_image).grid(row=0, column=2, padx=4)

        # Folder target
        ttk.Label(top, text="Folder Target:").grid(row=1, column=0, sticky="w")
        ttk.Entry(top, textvariable=self.folder_path, width=60).grid(row=1, column=1, sticky="we", padx=6)
        ttk.Button(top, text="Pilih Folder", command=self.pick_target_folder).grid(row=1, column=2, padx=4)

        # Params
        param = ttk.Frame(top)
        param.grid(row=2, column=0, columnspan=3, sticky="we", pady=(8,0))
        for i in range(6):
            param.grid_columnconfigure(i, weight=1)

        ttk.Label(param, text="Ambang pHash (0-64, lebih kecil=lebih mirip):").grid(row=0, column=0, sticky="w")
        phash_entry = ttk.Entry(param, textvariable=self.phash_threshold, width=6)
        phash_entry.grid(row=0, column=1, sticky="w", padx=(6,18))

        ttk.Label(param, text="Min ORB Ratio (0.00-1.00):").grid(row=0, column=2, sticky="w")
        orb_entry = ttk.Entry(param, textvariable=self.min_orb_ratio, width=6)
        orb_entry.grid(row=0, column=3, sticky="w", padx=(6,18))

        ttk.Label(param, text="Worker (threads):").grid(row=0, column=4, sticky="w")
        worker_entry = ttk.Entry(param, textvariable=self.max_workers, width=6)
        worker_entry.grid(row=0, column=5, sticky="w", padx=(6,0))

        # Action buttons
        actions = ttk.Frame(self, padding=(10, 6))
        actions.pack(side=tk.TOP, fill=tk.X)

        self.scan_btn = ttk.Button(actions, text="Scan", command=self.start_scan)
        self.scan_btn.pack(side=tk.LEFT)

        self.stop_btn = ttk.Button(actions, text="Stop", command=self.stop_scan, state=tk.DISABLED)
        self.stop_btn.pack(side=tk.LEFT, padx=(8,0))

        self.export_btn = ttk.Button(actions, text="Export CSV", command=self.export_csv, state=tk.DISABLED)
        self.export_btn.pack(side=tk.LEFT, padx=(8,0))

        # --- New bulk actions (checklist & delete) ---
        bulk = ttk.Frame(self, padding=(10, 0))
        bulk.pack(side=tk.TOP, fill=tk.X)

        self.check_all_btn = ttk.Button(bulk, text="Pilih Semua", command=self.select_all, state=tk.DISABLED)
        self.check_all_btn.pack(side=tk.LEFT)

        self.uncheck_all_btn = ttk.Button(bulk, text="Kosongkan Pilihan", command=self.unselect_all, state=tk.DISABLED)
        self.uncheck_all_btn.pack(side=tk.LEFT, padx=(8,0))

        self.delete_sel_btn = ttk.Button(bulk, text="Hapus Terpilih (Pindah ke _IMF_trash)", command=self.delete_selected, state=tk.DISABLED)
        self.delete_sel_btn.pack(side=tk.LEFT, padx=(8,0))

        # --- Results table ---
        table_frame = ttk.Frame(self, padding=(10, 4))
        table_frame.pack(side=tk.TOP, fill=tk.BOTH, expand=True)

        columns = ("sel","#","filename","path","phash","ham_dist","orb_good","orb_total","orb_ratio","score")
        self.tree = ttk.Treeview(table_frame, columns=columns, show="headings", height=16)

        # kolom checklist
        self.tree.heading("sel", text="✔")
        self.tree.column("sel", width=36, anchor="center")

        self.tree.heading("#", text="#")
        self.tree.heading("filename", text="Filename")
        self.tree.heading("path", text="Path")
        self.tree.heading("phash", text="pHash")
        self.tree.heading("ham_dist", text="Hamming")
        self.tree.heading("orb_good", text="ORB Good")
        self.tree.heading("orb_total", text="ORB Total")
        self.tree.heading("orb_ratio", text="ORB Ratio")
        self.tree.heading("score", text="Score")

        self.tree.column("#", width=40, anchor="center")
        self.tree.column("filename", width=200)
        self.tree.column("path", width=420)
        self.tree.column("phash", width=120, anchor="center")
        self.tree.column("ham_dist", width=80, anchor="e")
        self.tree.column("orb_good", width=80, anchor="e")
        self.tree.column("orb_total", width=80, anchor="e")
        self.tree.column("orb_ratio", width=80, anchor="e")
        self.tree.column("score", width=80, anchor="e")

        vsb = ttk.Scrollbar(table_frame, orient="vertical", command=self.tree.yview)
        hsb = ttk.Scrollbar(table_frame, orient="horizontal", command=self.tree.xview)
        self.tree.configure(yscroll=vsb.set, xscroll=hsb.set)

        self.tree.grid(row=0, column=0, sticky="nsew")
        vsb.grid(row=0, column=1, sticky="ns")
        hsb.grid(row=1, column=0, sticky="we")
        table_frame.grid_rowconfigure(0, weight=1)
        table_frame.grid_columnconfigure(0, weight=1)

        self.status = tk.StringVar(value="Siap.")
        ttk.Label(self, textvariable=self.status, padding=10).pack(side=tk.BOTTOM, fill=tk.X)

        # Double-click open in file explorer
        self.tree.bind("<Double-1>", self.open_in_explorer)

        # Klik untuk toggle checkbox pada kolom "sel"
        self.tree.bind("<Button-1>", self._on_tree_click)

        # Context menu untuk hapus terpilih
        self._menu = tk.Menu(self, tearoff=0)
        self._menu.add_command(label="Hapus Terpilih (pindah ke _IMF_trash)", command=self.delete_selected)
        self.tree.bind("<Button-3>", self._on_right_click)

    # --- Actions ---
    def pick_query_image(self):
        p = filedialog.askopenfilename(
            title="Pilih Gambar Query",
            filetypes=[("Images", "*.jpg *.jpeg *.png *.bmp *.webp *.tif *.tiff"), ("All files","*.*")]
        )
        if p:
            self.query_path.set(p)

    def pick_target_folder(self):
        d = filedialog.askdirectory(title="Pilih Folder Target")
        if d:
            self.folder_path.set(d)

    def prepare_query(self):
        qpath = self.query_path.get().strip()
        if not qpath:
            messagebox.showwarning("Perhatian", "Silakan pilih Gambar Query terlebih dahulu.")
            return False
        if not os.path.isfile(qpath):
            messagebox.showerror("Error", "Path gambar query tidak valid.")
            return False

        img = safe_open_image(qpath)
        if img is None:
            messagebox.showerror("Error", "Gagal membuka gambar query.")
            return False

        self._query_pil = img
        self._query_phash = compute_phash(img)
        # Simpan versi grayscale untuk ORB
        self._query_gray = cv2.cvtColor(np.array(img), cv2.COLOR_RGB2GRAY)
        return True

    def start_scan(self):
        if self._scan_thread and self._scan_thread.is_alive():
            return

        folder = self.folder_path.get().strip()
        if not folder or not os.path.isdir(folder):
            messagebox.showwarning("Perhatian", "Silakan pilih Folder Target yang valid.")
            return

        if not self.prepare_query():
            return

        # Ambang
        try:
            pthr = int(self.phash_threshold.get())
            if pthr < 0 or pthr > 64:
                raise ValueError
        except Exception:
            messagebox.showwarning("Perhatian", "Ambang pHash harus bilangan 0..64.")
            return

        try:
            orbr = float(self.min_orb_ratio.get())
            if orbr < 0.0 or orbr > 1.0:
                raise ValueError
        except Exception:
            messagebox.showwarning("Perhatian", "Min ORB Ratio harus 0.00..1.00.")
            return

        try:
            workers = int(self.max_workers.get())
            if workers <= 0 or workers > 64:
                raise ValueError
        except Exception:
            messagebox.showwarning("Perhatian", "Jumlah worker 1..64.")
            return

        # Reset
        self._results.clear()
        self._item_selected.clear()
        self._iid_to_result.clear()
        for item in self.tree.get_children():
            self.tree.delete(item)
        self.export_btn.config(state=tk.DISABLED)
        self.check_all_btn.config(state=tk.DISABLED)
        self.uncheck_all_btn.config(state=tk.DISABLED)
        self.delete_sel_btn.config(state=tk.DISABLED)

        self._stop_flag.clear()
        self.status.set("Memindai...")
        self._current_target_folder = folder

        # Tombol
        self.scan_btn.config(state=tk.DISABLED)
        self.stop_btn.config(state=tk.NORMAL)

        # Jalankan di thread terpisah
        self._scan_thread = threading.Thread(target=self._do_scan, args=(folder, pthr, orbr, workers), daemon=True)
        self._scan_thread.start()

        # Polling untuk update UI
        self.after(300, self._poll_scan_done)

    def stop_scan(self):
        self._stop_flag.set()
        self.status.set("Menghentikan pemindaian...")

    def _poll_scan_done(self):
        if self._scan_thread and self._scan_thread.is_alive():
            self.after(300, self._poll_scan_done)
            return
        # Selesai
        self.scan_btn.config(state=tk.NORMAL)
        self.stop_btn.config(state=tk.DISABLED)
        if self._results:
            self.export_btn.config(state=tk.NORMAL)
            self.check_all_btn.config(state=tk.NORMAL)
            self.uncheck_all_btn.config(state=tk.NORMAL)
            self.delete_sel_btn.config(state=tk.NORMAL)
            self.status.set(f"Selesai. Ditemukan {len(self._results)} kandidat mirip.")
        else:
            self.status.set("Selesai. Tidak ada kandidat sesuai ambang.")

    def _do_scan(self, folder, phash_thr, min_orb_ratio, workers):
        files = list(list_images(folder))
        total = len(files)
        if total == 0:
            self.status.set("Folder tidak berisi gambar.")
            return

        # Siapkan data query
        q_phash = self._query_phash
        q_gray = self._query_gray

        progress_count = 0
        progress_lock = threading.Lock()

        def process_one(p):
            if self._stop_flag.is_set():
                return None
            try:
                ipil = safe_open_image(p)
                if ipil is None:
                    return None
                ph = compute_phash(ipil)
                ham = hamming_distance(q_phash, ph)

                if ham > phash_thr:
                    # terlalu jauh, skip
                    return ("skip", p, ham)

                # Lolos pHash -> verifikasi ORB
                igray = load_cv_gray(p)
                if igray is None:
                    return None
                good, totalm, ratio = orb_match_score(q_gray, igray)

                # ratio < min_orb_ratio tetap kita simpan, namun skornya akan lebih besar
                score = combined_score(ham, ratio)
                return {
                    "path": p,
                    "filename": os.path.basename(p),
                    "phash": str(ph),
                    "ham": int(ham),
                    "orb_good": int(good),
                    "orb_total": int(totalm),
                    "orb_ratio": float(ratio),
                    "score": float(score),
                }
            except Exception:
                return None
            finally:
                nonlocal progress_count
                with progress_lock:
                    progress_count += 1
                    self.status.set(f"Memindai {progress_count}/{total}...")

        results = []
        skips = 0

        with ThreadPoolExecutor(max_workers=workers) as ex:
            futs = [ex.submit(process_one, p) for p in files]
            for fut in as_completed(futs):
                if self._stop_flag.is_set():
                    break
                r = fut.result()
                if r is None:
                    continue
                if isinstance(r, tuple) and r[0] == "skip":
                    skips += 1
                    continue
                results.append(r)

        if self._stop_flag.is_set():
            self.status.set("Dihentikan oleh pengguna.")
            return

        # Urutkan: skor gabungan terkecil → paling mirip
        results.sort(key=lambda x: (x["score"], x["ham"], -x["orb_good"], -x["orb_ratio"]))
        self._results = results

        # Tampilkan ke tabel
        self._populate_table(results)

    def _populate_table(self, rows):
        # Bersihkan
        for item in self.tree.get_children():
            self.tree.delete(item)
        self._item_selected.clear()
        self._iid_to_result.clear()

        # Isi
        for i, r in enumerate(rows, start=1):
            # default tidak terpilih
            sel_mark = "☐"
            iid = self.tree.insert("", tk.END, values=(
                sel_mark,
                i,
                r["filename"],
                r["path"],
                r["phash"],
                r["ham"],
                r["orb_good"],
                r["orb_total"],
                f"{r['orb_ratio']:.2f}",
                f"{r['score']:.3f}",
            ))
            self._item_selected[iid] = False
            self._iid_to_result[iid] = r

    def export_csv(self):
        if not self._results:
            messagebox.showinfo("Info", "Tidak ada data untuk diekspor.")
            return
        path = filedialog.asksaveasfilename(
            title="Simpan CSV",
            defaultextension=".csv",
            filetypes=[("CSV","*.csv")]
        )
        if not path:
            return
        try:
            with open(path, "w", newline="", encoding="utf-8") as f:
                w = csv.writer(f)
                w.writerow(["rank","filename","path","phash","hamming","orb_good","orb_total","orb_ratio","score"])
                for i, r in enumerate(self._results, start=1):
                    w.writerow([i, r["filename"], r["path"], r["phash"], r["ham"], r["orb_good"], r["orb_total"], f"{r['orb_ratio']:.4f}", f"{r['score']:.6f}"])
            messagebox.showinfo("Sukses", f"Berhasil simpan: {path}")
        except Exception as e:
            messagebox.showerror("Error", f"Gagal simpan CSV:\n{e}")

    def open_in_explorer(self, event):
        item = self.tree.identify_row(event.y)
        if not item:
            return
        vals = self.tree.item(item, "values")
        if not vals or len(vals) < 4:
            return
        p = vals[3]  # kolom "path"
        if not os.path.exists(p):
            messagebox.showwarning("Perhatian", "Path tidak ditemukan.")
            return
        folder = os.path.dirname(p)
        try:
            if sys.platform.startswith("win"):
                # Buka Explorer dan seleksi file
                os.system(f'explorer /select,"{p}"')
            elif sys.platform == "darwin":
                os.system(f'open -R "{p}"')
            else:
                # Linux: buka folder
                os.system(f'xdg-open "{folder}"')
        except Exception:
            pass

    # --------- Checklist / Bulk ops ----------
    def _on_tree_click(self, event):
        """
        Toggle checkbox jika klik terjadi di kolom 'sel'.
        """
        region = self.tree.identify("region", event.x, event.y)
        if region != "cell":
            return  # biarkan default behavior (mis. resize header)
        col = self.tree.identify_column(event.x)  # e.g. "#1" untuk kolom pertama
        if col != "#1":  # "#1" adalah kolom 'sel'
            return
        row_iid = self.tree.identify_row(event.y)
        if not row_iid:
            return
        self._toggle_item(row_iid)

    def _toggle_item(self, iid):
        cur = self._item_selected.get(iid, False)
        new = not cur
        self._item_selected[iid] = new
        vals = list(self.tree.item(iid, "values"))
        vals[0] = "☑" if new else "☐"
        self.tree.item(iid, values=vals)

    def select_all(self):
        for iid in self.tree.get_children(""):
            self._item_selected[iid] = True
            vals = list(self.tree.item(iid, "values"))
            vals[0] = "☑"
            self.tree.item(iid, values=vals)

    def unselect_all(self):
        for iid in self.tree.get_children(""):
            self._item_selected[iid] = False
            vals = list(self.tree.item(iid, "values"))
            vals[0] = "☐"
            self.tree.item(iid, values=vals)

    def _on_right_click(self, event):
        try:
            row_iid = self.tree.identify_row(event.y)
            if row_iid:
                # jika klik kanan pada row, fokuskan dulu
                self.tree.selection_set(row_iid)
            self._menu.tk_popup(event.x_root, event.y_root)
        finally:
            self._menu.grab_release()

    def delete_selected(self):
        """
        Pindahkan semua file terpilih ke folder _IMF_trash di dalam folder target.
        """
        # Kumpulkan yang terpilih
        selected_iids = [iid for iid, chosen in self._item_selected.items() if chosen]
        if not selected_iids:
            messagebox.showinfo("Info", "Tidak ada baris terpilih.")
            return

        if not self._current_target_folder or not os.path.isdir(self._current_target_folder):
            messagebox.showerror("Error", "Folder target tidak valid.")
            return

        trash_dir = os.path.join(self._current_target_folder, "_IMF_trash")
        os.makedirs(trash_dir, exist_ok=True)

        confirm = messagebox.askyesno(
            "Konfirmasi",
            f"Akan memindahkan {len(selected_iids)} file ke folder:\n{trash_dir}\nLanjutkan?"
        )
        if not confirm:
            return

        errors = []
        moved_count = 0

        for iid in selected_iids:
            r = self._iid_to_result.get(iid)
            if not r:
                continue
            src = r["path"]
            if not os.path.exists(src):
                errors.append(f"File tidak ditemukan: {src}")
                continue
            try:
                dst = unique_name_in_folder(trash_dir, os.path.basename(src))
                # Gunakan os.replace agar bisa memindahkan antar folder (drive yang sama)
                os.replace(src, dst)
                moved_count += 1
                # Hapus dari UI dan state
                self.tree.delete(iid)
                self._item_selected.pop(iid, None)
                self._iid_to_result.pop(iid, None)
                # Juga keluarkan dari _results (optional)
                try:
                    self._results = [x for x in self._results if x["path"] != src]
                except Exception:
                    pass
            except Exception as e:
                errors.append(f"Gagal pindahkan: {src} -> {e}")

        msg = f"Berhasil memindahkan {moved_count} file ke _IMF_trash."
        if errors:
            msg += "\n\nBeberapa error:\n- " + "\n- ".join(errors[:10])
            if len(errors) > 10:
                msg += f"\n... dan {len(errors)-10} error lainnya."

        messagebox.showinfo("Selesai", msg)

        # Disable tombol jika sudah tidak ada data
        if not self.tree.get_children(""):
            self.export_btn.config(state=tk.DISABLED)
            self.check_all_btn.config(state=tk.DISABLED)
            self.uncheck_all_btn.config(state=tk.DISABLED)
            self.delete_sel_btn.config(state=tk.DISABLED)

    # -----------------------------------------

if __name__ == "__main__":
    app = ImageMatchFinder()
    app.mainloop()
