#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Scrape & Paraphrase Event Lari → hasil.json + download cover image (tanpa open new tab)

Apa yang dilakukan skrip ini:
- Meminta URL di terminal
- Mengambil HTML & mengekstrak teks utama
- Meminta OpenAI untuk parafrase + struktur JSON (judul, isi_informasi, benefit_peserta, lokasi, kota, tanggal_event (ISO), kontak_event, biaya_registrasi, kategori, jenis_event)
- Normalisasi tanggal ke ISO (YYYY-MM-DD), tentukan jenis_event & kota (AI + heuristik), bersihkan Email placeholder/"protected"
- Menyimpan hasil ke hasil.json (overwrite)
- Mengunduh gambar LANGSUNG dari selector yang diberikan, dengan fallback cerdas jika selector berubah:
  1) Selector presisi (PRIMARY)
  2) Selector dilonggarkan (RELAXED)
  3) meta og:image
  4) Heuristik gambar terbesar/terbaik
- Menyimpan gambar ke ./images dengan nama file: "<judul>_<tanggal_event>.<ext>"
- **Update hasil.json** menambahkan field **image** di posisi pertama. Jika gagal unduh, `image: "-"`.

Prasyarat:
  pip install requests beautifulsoup4 lxml python-dotenv openai
.env:
  OPENAI_API_KEY=sk-...
"""

import os
import sys
import json
import re
import html
import mimetypes
import traceback
from urllib.parse import urlparse, urljoin

import requests
from bs4 import BeautifulSoup
from dotenv import load_dotenv

# ===================== Konstanta =====================

PRIMARY_IMG_SELECTOR = (
    "#tve_editor > div:nth-child(6) > div.tve-page-section-in.tve_empty_dropzone > "
    "div > div > div:nth-child(1) > div > div > div > span > img"
)
RELAXED_IMG_SELECTORS = [
    "#tve_editor div.tve-page-section-in img",
    "#tve_editor .tve-page-section-in img",
    "#tve_editor img",
]
IMAGES_DIR = "images"

# ===================== Utilitas umum =====================

def slugify(s: str) -> str:
    s = (s or "").strip()
    s = re.sub(r"\s+", "_", s)
    s = re.sub(r"[^0-9A-Za-z_\-\.]", "", s)
    return s[:150] if len(s) > 150 else s

def ensure_dir(path: str):
    if not os.path.isdir(path):
        os.makedirs(path, exist_ok=True)

def guess_ext_from_content_type(ct: str) -> str:
    if not ct:
        return ".jpg"
    ct = ct.split(";")[0].strip().lower()
    ext = mimetypes.guess_extension(ct) or ""
    if ext:
        return ext
    if "jpeg" in ct or "jpg" in ct:
        return ".jpg"
    if "png" in ct:
        return ".png"
    if "webp" in ct:
        return ".webp"
    if "gif" in ct:
        return ".gif"
    return ".jpg"

def _absolute_url(base_url: str, src: str) -> str:
    if not src:
        return ""
    if src.startswith("//"):
        parsed = urlparse(base_url)
        return f"{parsed.scheme}:{src}"
    if src.startswith("http://") or src.startswith("https://"):
        return src
    return urljoin(base_url, src)

def _pick_from_srcset(srcset: str) -> str:
    try:
        items = [i.strip() for i in srcset.split(",") if i.strip()]
        best = None
        best_w = -1
        for it in items:
            parts = it.split()
            if not parts:
                continue
            url = parts[0]
            w = 0
            if len(parts) > 1 and parts[1].endswith("w"):
                try:
                    w = int(parts[1][:-1])
                except:
                    w = 0
            elif len(parts) > 1 and parts[1].endswith("x"):
                try:
                    w = int(float(parts[1][:-1]) * 1000)
                except:
                    w = 0
            if w > best_w:
                best_w = w
                best = url
        return best or (items[-1].split()[0] if items else "")
    except Exception:
        return ""

# ===================== HTTP & Ekstraksi HTML =====================

def fetch_html(url: str, timeout: int = 30) -> str:
    headers = {
        "User-Agent": (
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/120.0 Safari/537.36"
        ),
        "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
        "Accept-Language": "id,en;q=0.9",
    }
    resp = requests.get(url, headers=headers, timeout=timeout)
    resp.raise_for_status()
    return resp.text

def extract_main_text_and_title(html_text: str, url: str) -> tuple[str, str, BeautifulSoup]:
    soup = BeautifulSoup(html_text, "lxml")

    # Judul
    title = None
    og = soup.find("meta", property="og:title")
    if og and og.get("content"):
        title = og["content"].strip()
    if not title and soup.title and soup.title.string:
        title = soup.title.string.strip()
    if not title:
        h1 = soup.find("h1")
        if h1:
            title = h1.get_text(" ", strip=True)
    if not title:
        parsed = urlparse(url)
        title = f"{parsed.netloc}{parsed.path}"

    # Isi utama
    candidates = []
    candidates += soup.find_all("article")
    candidates += soup.select(
        ".entry-content, .post-content, .single-content, "
        ".content, .article-content, .post-body, .page-content"
    )
    if not candidates:
        main = soup.find("main")
        if main:
            candidates = [main]
    if not candidates:
        content_div = soup.find("div", id="content")
        if content_div:
            candidates = [content_div]
    if not candidates:
        candidates = [soup.body] if soup.body else [soup]

    def clean_node(node):
        for tag in node.find_all(["script", "style", "noscript", "iframe", "svg"]):
            tag.decompose()
        for tag in node.find_all(["nav", "aside", "footer", "form"]):
            tag.decompose()
        return node

    texts = []
    for c in candidates:
        if not c:
            continue
        c = clean_node(c)
        parts = []
        for el in c.find_all(["p", "li", "h2", "h3", "h4"]):
            t = el.get_text(" ", strip=True)
            if t:
                parts.append(t)
        blob = "\n".join(parts)
        blob = html.unescape(blob)
        blob = re.sub(r"[ \t]+", " ", blob)
        blob = re.sub(r"\n{3,}", "\n\n", blob).strip()
        texts.append((len(blob), blob))

    texts.sort(reverse=True, key=lambda x: x[0])
    main_text = texts[0][1] if texts else ""

    if len(main_text) < 100:
        ps = [p.get_text(" ", strip=True) for p in soup.find_all("p")]
        fallback = "\n".join([t for t in ps if t])
        if len(fallback) > len(main_text):
            main_text = fallback

    return title, main_text, soup

# ===================== OpenAI: Prompt, Skema, Panggilan =====================

PROMPT_INSTRUCTIONS = """
Anda adalah asisten yang menulis ulang (parafrase) informasi event lari tanpa mengubah inti atau makna.

TUGAS:
1) Parafrase isi utama agar ringkas, jelas, dan setia pada fakta.
2) Susun JSON dengan kunci PERSIS berikut:
   - judul (string)
   - isi_informasi (string)
   - benefit_peserta (array string)
   - lokasi (string) → alamat/detail venue lengkap sebagaimana sumber
   - kota (string) → hanya nama kota/kabupaten tempat event diselenggarakan (tanpa kecamatan/venue)
   - tanggal_event (string) → standar ISO YYYY-MM-DD (contoh: 2025-01-19). Jika rentang, tulis tanggal awal.
   - kontak_event (object) → bisa berisi: Instagram (array string), WhatsApp (string), Email (string)
   - biaya_registrasi (object ATAU string; jika per kategori, jadikan object)
   - kategori (array string: 5K, 10K, 21K, 42K, dst.)
   - jenis_event (string SATU dari: "road run", "trail run", "virtual run", "fun run")

ATURAN:
- Jangan menambah info yang tidak ada di sumber. Jika tidak tersedia tulis "Tidak tercantum".
- "kota" hanya nama kota/kabupaten (hindari provinsi).
- "tanggal_event" HARUS ISO (YYYY-MM-DD). Jika "19–20 Januari 2025" → "2025-01-19".
- "jenis_event":
    * trail (pegunungan/hutan/elevasi/single track) → "trail run"
    * virtual/online → "virtual run"
    * fun run/walk → "fun run"
    * lainnya → "road run"
- Bahasa Indonesia, informatif & netral.
"""

def build_schema():
    return {
        "name": "EventInfo",
        "schema": {
            "type": "object",
            "additionalProperties": False,
            "properties": {
                "judul": {"type": "string"},
                "isi_informasi": {"type": "string"},
                "benefit_peserta": {"type": "array", "items": {"type": "string"}},
                "lokasi": {"type": "string"},
                "kota": {"type": "string"},
                "biaya_registrasi": {"oneOf": [{"type": "object"}, {"type": "string"}]},
                "tanggal_event": {"type": "string"},
                "kontak_event": {
                    "type": "object",
                    "additionalProperties": False,
                    "properties": {
                        "Instagram": {"type": "array", "items": {"type": "string"}},
                        "WhatsApp": {"type": "string"},
                        "Email": {"type": "string"}
                    }
                },
                "kategori": {"type": "array", "items": {"type": "string"}},
                "jenis_event": {"type": "string"},
            },
            "required": [
                "judul","isi_informasi","benefit_peserta","lokasi","kota",
                "tanggal_event","kontak_event","biaya_registrasi","kategori","jenis_event",
            ],
        },
        "strict": True,
        "description": "Informasi event lari terstruktur untuk listing & detail."
    }

def openai_paraphrase_to_json(source_url: str, page_title: str, page_text: str) -> dict:
    from openai import OpenAI
    client = OpenAI(api_key=os.environ["OPENAI_API_KEY"])

    models_try = ["gpt-4o-mini", "gpt-4o", "gpt-4.1-mini", "gpt-4.1"]
    schema = build_schema()
    input_text = (
        f"{PROMPT_INSTRUCTIONS}\n\n"
        f"Sumber URL: {source_url}\n\n"
        f"Judul halaman (mentah): {page_title}\n\n"
        f"Teks utama (untuk diparafrase & disusun):\n{page_text}\n"
    )

    last_err = None
    for m in models_try:
        try:
            resp = client.responses.create(
                model=m,
                input=input_text,
                response_format={"type": "json_schema", "json_schema": schema},
            )
            if hasattr(resp, "output_text") and resp.output_text:
                return json.loads(resp.output_text)

            data = None
            if hasattr(resp, "output"):
                try:
                    for chunk in resp.output:
                        for c in getattr(chunk, "content", []) or []:
                            t = getattr(c, "text", None) or getattr(c, "value", None)
                            if t:
                                data = json.loads(t)
                                break
                        if data:
                            break
                except Exception:
                    pass

            if data is not None:
                return data

            raw = getattr(resp, "content", None) or getattr(resp, "message", None)
            if isinstance(raw, str):
                return json.loads(raw)
            if isinstance(raw, dict) and isinstance(raw.get("content"), str):
                return json.loads(raw["content"])

            raise RuntimeError("Responses API tidak mengembalikan JSON yang dapat diparse.")
        except Exception as e:
            last_err = e
            continue

    # Fallback Chat Completions (opsional)
    for m in models_try:
        try:
            comp = client.chat.completions.create(
                model=m,
                messages=[
                    {"role": "system", "content": "Anda asisten ekstraksi informasi yang teliti."},
                    {"role": "user", "content": input_text}
                ],
                temperature=0.2,
                response_format={"type": "json_object"},
            )
            return json.loads(comp.choices[0].message.content)
        except Exception as e:
            last_err = e
            continue

    raise RuntimeError(f"Gagal memproses di semua model. Error terakhir: {last_err}")

# ===================== Post-processing (Email, Tanggal, Kota, Jenis) =====================

def _normalize_email_str(s: str) -> str:
    s = s.strip().replace("mailto:", "").strip("<>").strip()
    s = re.sub(r"\s+", "", s)
    s = re.sub(r"[^a-zA-Z0-9@._+-]", "", s)
    return s.lower()

def _is_placeholder_email(value) -> bool:
    placeholders_words = {"", "-", "tidaktercantum", "tidakdicantumkan"}
    placeholders_exact = {"[email protected]", "email@example.com"}
    if value is None:
        return True
    if isinstance(value, list):
        return all(_is_placeholder_email(v) for v in value)
    if isinstance(value, str):
        s = _normalize_email_str(value)
        if s in placeholders_words or s in placeholders_exact:
            return True
        if s.endswith("@example.com"):
            return True
        return False
    return False

def _contains_protected_marker(value) -> bool:
    if value is None:
        return False
    if isinstance(value, list):
        return any(_contains_protected_marker(v) for v in value)
    if not isinstance(value, str):
        return False
    raw = value.lower()
    compact = re.sub(r"[^a-z0-9]", "", raw)
    keywords = [
        "protected", "protected from spambots", "cloudflare",
        "email protected", "emailprotected", "protection"
    ]
    return any(k in raw for k in keywords) or any(k.replace(" ", "") in compact for k in keywords)

def clean_contacts_email(data: dict) -> dict:
    try:
        kontak = data.get("kontak_event")
        if isinstance(kontak, dict):
            email_keys = [k for k in list(kontak.keys()) if k.lower() == "email"]
            for ek in email_keys:
                val = kontak.get(ek)
                if _is_placeholder_email(val) or _contains_protected_marker(val):
                    del kontak[ek]
    except Exception:
        pass
    return data

_ID_MONTHS = {
    "januari": "01","jan": "01",
    "februari": "02","feb": "02",
    "maret": "03","mar": "03",
    "april": "04","apr": "04",
    "mei": "05",
    "juni": "06","jun": "06",
    "juli": "07","jul": "07",
    "agustus": "08","agu": "08","ags": "08",
    "september": "09","sep": "09",
    "oktober": "10","okt": "10",
    "november": "11","nov": "11",
    "desember": "12","des": "12",
}

def _to_iso_date(date_str: str) -> str:
    if not isinstance(date_str, str):
        return date_str
    s = date_str.strip().lower()
    s = re.sub(r"\b(senin|selasa|rabu|kamis|jumat|jum'at|sabtu|minggu),?\s*", "", s)
    s = s.replace("–", "-").replace("—", "-").replace(" s/d ", "-").replace(" s.d ", "-")
    part = s.split("-")[0].strip()

    m = re.search(r"(\d{1,2})\s+([a-zA-Z\.]+)\s+(\d{4})", part)
    if m:
        dd = int(m.group(1))
        mon_raw = m.group(2).replace(".", "")
        yy = int(m.group(3))
        mon = _ID_MONTHS.get(mon_raw, None)
        if mon:
            return f"{yy:04d}-{mon}-{dd:02d}"

    m = re.search(r"(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})", part)
    if m:
        dd = int(m.group(1)); mm = int(m.group(2)); yy = int(m.group(3))
        if mm > 12 and dd <= 12:
            dd, mm = mm, dd
        return f"{yy:04d}-{mm:02d}-{dd:02d}"

    m = re.search(r"(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})", part)
    if m:
        yy = int(m.group(1)); mm = int(m.group(2)); dd = int(m.group(3))
        return f"{yy:04d}-{mm:02d}-{dd:02d}"

    return date_str

def _extract_city_from_location(lokasi: str) -> str:
    if not isinstance(lokasi, str) or not lokasi.strip():
        return "Tidak tercantum"
    txt = lokasi.strip()
    parts = [p.strip() for p in txt.split(",") if p.strip()]
    candidate = parts[-1] if parts else txt
    candidate = re.sub(r"^(kota adm\.\s*)", "", candidate, flags=re.I)
    candidate = re.sub(r"^(kota\s+)", "", candidate, flags=re.I)
    candidate = re.sub(r"^(kab\.?\s*|kabupaten\s+)", "", candidate, flags=re.I)
    if re.fullmatch(r"dki\s*jakarta", candidate, flags=re.I):
        return "Jakarta"
    prov_indicators = {
        "aceh","sumatera utara","sumatera barat","riau","jambi","sumatera selatan","bengkulu",
        "lampung","kepulauan bangka belitung","kepulauan riau","dki jakarta","jawa barat","jawa tengah",
        "diy","daerah istimewa yogyakarta","yogyakarta","jawa timur","banten","bali","ntb","nusa tenggara barat",
        "ntt","nusa tenggara timur","kalimantan barat","kalimantan tengah","kalimantan selatan","kalimantan timur",
        "kalimantan utara","sulawesi utara","sulawesi tengah","sulawesi selatan","sulawesi tenggara","gorontalo",
        "sulawesi barat","maluku","maluku utara","papua","papua barat","papua selatan","papua pegunungan",
        "papua tengah","papua barat daya"
    }
    if candidate.lower() in prov_indicators and len(parts) >= 2:
        candidate = parts[-2]
        candidate = re.sub(r"^(kota adm\.\s*|kota\s+|kab\.?\s*|kabupaten\s+)", "", candidate, flags=re.I)
    candidate = candidate.strip()
    return candidate if candidate else "Tidak tercantum"

def _infer_jenis_event_from_text(text: str) -> str:
    if not isinstance(text, str):
        return "road run"
    t = text.lower()
    if any(k in t for k in ["trail", "gunung", "pegunungan", "elevasi", "elevation", "single track", "hutan", "mount"]):
        return "trail run"
    if any(k in t for k in ["virtual", "online"]):
        return "virtual run"
    if any(k in t for k in ["fun run", "funrun", "fun walk", "funwalk"]):
        return "fun run"
    return "road run"

def finalize_fields(data: dict) -> dict:
    data = clean_contacts_email(data)
    tgl = data.get("tanggal_event")
    if isinstance(tgl, str):
        data["tanggal_event"] = _to_iso_date(tgl)
    kota = data.get("kota", "")
    if not isinstance(kota, str) or not kota.strip() or kota.strip().lower() in {"tidak tercantum", "-"}:
        lokasi = data.get("lokasi", "")
        data["kota"] = _extract_city_from_location(lokasi)
    je = data.get("jenis_event", "")
    if not isinstance(je, str) or je.strip().lower() not in {"road run","trail run","virtual run","fun run"}:
        bundle = " ".join([
            str(data.get("isi_informasi", "")),
            str(data.get("lokasi", "")),
            " ".join(data.get("kategori", []) if isinstance(data.get("kategori"), list) else [])
        ])
        data["jenis_event"] = _infer_jenis_event_from_text(bundle)
    return data

# ===================== Ekstraksi URL gambar & download (tanpa new tab) =====================

def _collect_img_url_from_element(img_el, base_url: str) -> str:
    srcset = img_el.get("srcset") or ""
    if srcset.strip():
        best = _pick_from_srcset(srcset)
        if best:
            return _absolute_url(base_url, best)
    for attr in ("src", "data-src", "data-lazy-src", "data-original"):
        val = img_el.get(attr)
        if val:
            return _absolute_url(base_url, val)
    return ""

def _find_image_candidates(soup: BeautifulSoup, page_url: str) -> list[str]:
    urls = []
    img = soup.select_one(PRIMARY_IMG_SELECTOR)
    if img:
        u = _collect_img_url_from_element(img, page_url)
        if u:
            urls.append(u)
    for sel in RELAXED_IMG_SELECTORS:
        for el in soup.select(sel):
            u = _collect_img_url_from_element(el, page_url)
            if u:
                urls.append(u)
    og_img = soup.find("meta", property="og:image") or soup.find("meta", attrs={"name": "og:image"})
    if og_img and og_img.get("content"):
        urls.append(_absolute_url(page_url, og_img["content"]))

    def _score_img(el) -> tuple:
        score = 0
        try:
            w = int(el.get("width") or 0)
            h = int(el.get("height") or 0)
            score += w * h
        except:
            pass
        src = el.get("src") or el.get("data-src") or el.get("data-lazy-src") or el.get("srcset") or ""
        s = src.lower()
        if any(k in s for k in ["hero", "banner", "header", "cover"]):
            score += 2_000_000
        m = re.search(r"(\d{3,4})x(\d{3,4})", s)
        if m:
            try:
                score += int(m.group(1)) * int(m.group(2))
            except:
                pass
        return (score,)

    all_imgs = soup.find_all("img")
    all_imgs_sorted = sorted(all_imgs, key=_score_img, reverse=True)
    for el in all_imgs_sorted[:10]:
        u = _collect_img_url_from_element(el, page_url)
        if u:
            urls.append(u)

    cleaned, seen = [], set()
    for u in urls:
        if not u or u.startswith("data:") or u.lower().endswith(".svg"):
            continue
        if u not in seen:
            seen.add(u)
            cleaned.append(u)
    return cleaned

def _choose_best_image(urls: list[str]) -> str:
    if not urls:
        return ""
    def rank(u: str) -> tuple:
        bonus = 0
        L = u.lower()
        if L.startswith("https://"):
            bonus += 1000
        if any(k in L for k in ["hero", "banner", "header", "cover"]):
            bonus += 500
        bonus += min(len(u), 2000)
        return (bonus,)
    return sorted(urls, key=rank, reverse=True)[0]

def download_image_direct(page_url: str, soup: BeautifulSoup, dest_path_wo_ext: str) -> str:
    candidates = _find_image_candidates(soup, page_url)
    img_url = _choose_best_image(candidates)
    if not img_url:
        return ""

    headers = {
        "User-Agent": (
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/120.0 Safari/537.36"
        ),
        "Referer": page_url,
    }
    r = requests.get(img_url, headers=headers, timeout=30, stream=True)
    r.raise_for_status()

    ct = r.headers.get("Content-Type", "")
    ext = guess_ext_from_content_type(ct)

    parsed = urlparse(img_url)
    url_ext = os.path.splitext(parsed.path)[1].lower()
    if url_ext in {".jpg", ".jpeg", ".png", ".webp", ".gif"}:
        ext = url_ext

    final_path = dest_path_wo_ext + ext
    with open(final_path, "wb") as f:
        for chunk in r.iter_content(chunk_size=8192):
            if chunk:
                f.write(chunk)
    return final_path

# ===================== Ordering & Inject image field =====================

def order_output_with_image(data: dict, image_filename: str) -> dict:
    """
    Sisipkan kolom 'image' di posisi pertama, lalu kunci lainnya dalam urutan yang konsisten
    agar cocok untuk listing dan detail.
    """
    desired_order = [
        "image", "judul", "isi_informasi", "benefit_peserta",
        "lokasi", "kota", "tanggal_event", "kontak_event",
        "biaya_registrasi", "kategori", "jenis_event"
    ]
    out = {}
    out["image"] = image_filename  # first
    for key in desired_order[1:]:
        if key in data:
            out[key] = data[key]
    # Tambahkan kunci sisa jika ada (untuk jaga-jaga)
    for k, v in data.items():
        if k not in out:
            out[k] = v
    return out

# ===================== Main =====================

def main():
    load_dotenv()
    if not os.getenv("OPENAI_API_KEY"):
        print("ERROR: OPENAI_API_KEY belum ada. Tambahkan ke file .env atau environment.", file=sys.stderr)
        sys.exit(1)

    try:
        print("Masukkan URL halaman event (mis. https://jadwallari.id/events/manyogot-aceh-run/):")
        url = input("> ").strip().strip('"').strip("'")
        if not url or not url.startswith(("http://", "https://")):
            print("URL tidak valid. Contoh: https://jadwallari.id/events/manyogot-aceh-run/", file=sys.stderr)
            sys.exit(1)

        print("Mengambil halaman...")
        html_text = fetch_html(url)

        print("Mengekstrak konten...")
        title, main_text, soup = extract_main_text_and_title(html_text, url)
        if len(main_text) < 50:
            print("Peringatan: konten tampak terlalu pendek — hasil mungkin kurang lengkap.", file=sys.stderr)

        print("Meminta OpenAI untuk parafrase & struktur JSON...")
        data = openai_paraphrase_to_json(url, title, main_text)

        print("Finalisasi field (tanggal ISO, kota, jenis_event, email)...")
        data = finalize_fields(data)

        # Simpan hasil.json (sementara, tanpa kolom image)
        out_json = "hasil.json"
        with open(out_json, "w", encoding="utf-8") as f:
            json.dump(data, f, ensure_ascii=False, indent=2)
        print(f"Hasil AI tersimpan di: {out_json}")

        # Download gambar
        judul = data.get("judul", "").strip() or "event"
        tanggal_iso = data.get("tanggal_event", "").strip() or "tanggal"
        filename_base = f"{slugify(judul)}_{slugify(tanggal_iso)}"
        ensure_dir(IMAGES_DIR)
        dest_wo_ext = os.path.join(IMAGES_DIR, filename_base)

        print("Mencari & mengunduh gambar (tanpa open new tab)...")
        saved_path = download_image_direct(url, soup, dest_wo_ext)
        if saved_path:
            image_filename = os.path.basename(saved_path)
            print(f"Gambar tersimpan: {saved_path}")
        else:
            image_filename = "-"
            print("Tidak berhasil menemukan/mengunduh gambar. (image diset '-')")

        # Sisipkan kolom image di posisi pertama & tulis ulang hasil.json
        final_data = order_output_with_image(data, image_filename)
        with open(out_json, "w", encoding="utf-8") as f:
            json.dump(final_data, f, ensure_ascii=False, indent=2)
        print(f"Selesai. hasil.json telah diupdate dengan kolom 'image'.")

    except requests.HTTPError as e:
        print(f"HTTPError: {e} — periksa URL atau koneksi.", file=sys.stderr)
        sys.exit(2)
    except Exception as e:
        print("Terjadi error tak terduga:", file=sys.stderr)
        print(str(e), file=sys.stderr)
        traceback.print_exc()
        sys.exit(3)

if __name__ == "__main__":
    main()
