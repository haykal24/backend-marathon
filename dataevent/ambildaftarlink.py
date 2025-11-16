import json
import re
import time
from pathlib import Path

import requests
from bs4 import BeautifulSoup

URL = "https://jadwallari.id/event-lari-2024/"
TABLE_ID = "tablepress-19"
OUTPUT_FILE = Path("daftarlink.json")

HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/120.0.0.0 Safari/537.36"
    )
}

def fetch_html(url: str, max_retries: int = 3, timeout: int = 30) -> str:
    for attempt in range(1, max_retries + 1):
        try:
            resp = requests.get(url, headers=HEADERS, timeout=timeout)
            resp.raise_for_status()
            return resp.text
        except Exception:
            if attempt == max_retries:
                raise
            time.sleep(1.2 * attempt)
    return ""

def main():
    # 1) Ambil HTML sekali (tanpa refresh)
    html = fetch_html(URL)
    soup = BeautifulSoup(html, "html.parser")

    # 2) Temukan tbody tabel dan semua <tr> dengan class "row-<angka>"
    tbody = soup.select_one(f"#{TABLE_ID} > tbody")
    if not tbody:
        print("Tabel tidak ditemukan.")
        return

    row_nums = []
    for tr in tbody.select("tr"):
        # gabungkan semua class pada tr lalu cari pola row-<angka>
        classes = tr.get("class", [])
        for c in classes:
            m = re.fullmatch(r"row-(\d+)", c)
            if m:
                row_nums.append(int(m.group(1)))
                break

    if not row_nums:
        print("Tidak ada baris dengan pola class 'row-<angka>'.")
        return

    row_min, row_max = min(row_nums), max(row_nums)
    print(f"Memindai baris dari row-{row_min} sampai row-{row_max} ...")

    # 3) Loop satu per satu dari row_min..row_max dan ambil link kolom ke-2 jika ada
    links = []
    seen = set()  # untuk jaga-jaga jika ada duplikasi
    for i in range(row_min, row_max + 1):
        a = soup.select_one(f"#{TABLE_ID} > tbody > tr.row-{i} > td.column-2 > a")
        if not a or not a.has_attr("href"):
            continue
        href = (a["href"] or "").strip()
        if not href:
            continue
        if href in seen:
            continue
        seen.add(href)
        links.append(href)

    # 4) Simpan hanya link ke daftarlink.json
    OUTPUT_FILE.write_text(json.dumps(links, ensure_ascii=False, indent=2), encoding="utf-8")

    print(f"Selesai. Row terpindai: {row_min}-{row_max}. Total link valid unik: {len(links)}")
    print(f"Disimpan ke: {OUTPUT_FILE.resolve()}")

if __name__ == "__main__":
    main()
