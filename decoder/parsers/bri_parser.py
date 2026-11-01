import re
import sys
from datetime import date
from .base_parser import BaseBankParser, clean_text, clean_amount

def to_markdown_compat(doc, page_number: int) -> str:
    """
    Ekstrak halaman PDF ke format Markdown dengan kompatibilitas lintas versi:
    1. PyMuPDF 2.0+ native (doc.to_markdown)
    2. pymupdf4llm standalone (< 2.0.0)
    3. pymupdf.to_markdown
    4. Fallback plain text jika tidak tersedia
    """
    if hasattr(doc, "to_markdown"):
        try:
            return doc.to_markdown(pages=[page_number])
        except Exception:
            pass

    try:
        import pymupdf4llm
        if hasattr(pymupdf4llm, "to_markdown"):
            return pymupdf4llm.to_markdown(doc, pages=[page_number])
    except ImportError:
        pass

    try:
        import pymupdf
        if hasattr(pymupdf, "to_markdown"):
            return pymupdf.to_markdown(doc, pages=[page_number])
    except ImportError:
        pass

    try:
        page = doc[page_number] if isinstance(page_number, int) and page_number < len(doc) else doc[0]
        return page.get_text("text")
    except Exception as e:
        print(f"Peringatan: Gagal mengekstrak teks halaman {page_number}: {e}", file=sys.stderr)
        return ""

def split_row(line: str) -> list[str]:
    return [c.strip() for c in line.strip("|").split("|")]

MONTH_MAP = {
    "jan": "01", "feb": "02", "mar": "03", "apr": "04",
    "mei": "05", "may": "05", "jun": "06", "jul": "07",
    "agu": "08", "agt": "08", "aug": "08", "sep": "09",
    "okt": "10", "oct": "10", "nov": "11", "des": "12", "dec": "12",
}

def format_bri_date(raw_date_str: str, default_year: str = "") -> str:
    """
    Konversi berbagai format tanggal BRI/BRImo menjadi YYYY-MM-DD.
    Contoh:
    - 25/09/2026 atau 25/09/26 -> 2026-09-25
    - 25-09-2026 -> 2026-09-25
    - 25 Sep 2026 -> 2026-09-25
    - 2026-09-25 14:30:00 -> 2026-09-25
    """
    if not raw_date_str:
        return ""

    s = clean_text(raw_date_str).strip()

    # 1. YYYY-MM-DD
    m1 = re.match(r"^(\d{4})-(\d{2})-(\d{2})", s)
    if m1:
        return f"{m1.group(1)}-{m1.group(2)}-{m1.group(3)}"

    # 2. DD/MM/YYYY atau DD-MM-YYYY
    m2 = re.match(r"^(\d{1,2})[/-](\d{1,2})[/-](\d{4})", s)
    if m2:
        day = m2.group(1).zfill(2)
        month = m2.group(2).zfill(2)
        year = m2.group(3)
        return f"{year}-{month}-{day}"

    # 3. DD/MM/YY (dua digit tahun)
    m3 = re.match(r"^(\d{1,2})[/-](\d{1,2})[/-](\d{2})\b", s)
    if m3:
        day = m3.group(1).zfill(2)
        month = m3.group(2).zfill(2)
        yr_short = int(m3.group(3))
        year = f"20{yr_short:02d}"
        return f"{year}-{month}-{day}"

    # 4. DD Mon YYYY (contoh: 25 Sep 2026)
    m4 = re.match(r"^(\d{1,2})\s+([A-Za-z]{3,})\s+(\d{2,4})", s)
    if m4:
        day = m4.group(1).zfill(2)
        mon_str = m4.group(2)[:3].lower()
        month = MONTH_MAP.get(mon_str, "01")
        yr = m4.group(3)
        year = f"20{yr}" if len(yr) == 2 else yr
        return f"{year}-{month}-{day}"

    # 5. DD/MM tanpa tahun (ambil dari default_year)
    m5 = re.match(r"^(\d{1,2})[/-](\d{1,2})\b", s)
    if m5:
        day = m5.group(1).zfill(2)
        month = m5.group(2).zfill(2)
        year = default_year or str(date.today().year)
        return f"{year}-{month}-{day}"

    return s

class BriParser(BaseBankParser):
    name = "Bank BRI"
    bank_code = "bri"

    def can_handle(self, text: str) -> bool:
        t = text.lower()
        return (
            "bank rakyat indonesia" in t
            or "bank bri" in t
            or "brimo" in t
            or "britama" in t
            or "simpedes" in t
            or "rekening koran bri" in t
            or "pt. bank bri" in t
            or ("bri" in t and ("mutasi" in t or "statement" in t or "tabungan" in t or "transaksi" in t))
        )

    def parse(self, doc) -> list[dict]:
        total = len(doc)
        print(f"Mengekstrak {total} halaman Bank BRI...\n", file=sys.stderr)

        all_transactions = []
        full_first_page = doc[0].get_text() if len(doc) > 0 else ""
        period_year = self._extract_period_year(full_first_page)

        # -------------------------------------------------------------
        # METODE 1: Ekstraksi Tabel Berstruktur (Markdown Tables)
        # -------------------------------------------------------------
        for page_idx, page in enumerate(doc, start=1):
            md = to_markdown_compat(doc, page.number)
            table_blocks = re.findall(r"((?:^\|.*\|\s*$\n?)+)", md, re.MULTILINE)

            for block in table_blocks:
                lines = [ln.strip() for ln in block.strip().splitlines() if ln.strip()]
                if len(lines) < 2:
                    continue
                # Lewati baris separator markdown |---|---|
                if not re.match(r"^\|[\s\-:|]+\|$", lines[1]):
                    continue

                header = [clean_text(h).lower() for h in split_row(lines[0])]
                raw_rows = [split_row(ln) for ln in lines[2:]]

                date_idx = self._find_col(header, ["tanggal", "tgl", "tgl_tran", "date", "waktu", "tgl transaksi"])
                desc_idx = self._find_col(header, ["uraian", "keterangan", "deskripsi", "description", "remark", "transaksi", "uraian transaksi", "desk_tran", "remark_custom"])
                debet_idx = self._find_col(header, ["debet", "debit", "mutasi debet", "mutasi_debet", "dr", "dana keluar", "keluar", "mutasi d"])
                kredit_idx = self._find_col(header, ["kredit", "credit", "mutasi kredit", "mutasi_kredit", "cr", "dana masuk", "masuk", "mutasi c"])
                saldo_idx = self._find_col(header, ["saldo", "balance", "saldo akhir", "saldo_akhir_mutasi"])

                for row in raw_rows:
                    if len(row) < len(header):
                        row += [""] * (len(header) - len(row))

                    raw_date = clean_text(row[date_idx]) if date_idx != -1 else ""
                    if not raw_date or not re.search(r"\d", raw_date):
                        continue

                    # Filter jika baris adalah summary/header
                    if any(k in raw_date.upper() for k in ["SALDO AWAL", "SALDO AKHIR", "TOTAL", "HALAMAN"]):
                        continue

                    desc = clean_text(row[desc_idx]) if desc_idx != -1 else "Transaksi BRI"
                    debet_val = clean_amount(row[debet_idx]) if debet_idx != -1 else 0.0
                    kredit_val = clean_amount(row[kredit_idx]) if kredit_idx != -1 else 0.0
                    saldo_val = clean_amount(row[saldo_idx]) if saldo_idx != -1 and row[saldo_idx] else None

                    # Format tanggal YYYY-MM-DD
                    std_date = format_bri_date(raw_date, period_year)

                    if kredit_val > 0:
                        all_transactions.append({
                            "date": std_date,
                            "raw_date": raw_date,
                            "description": desc,
                            "type": "Cash In",
                            "amount": kredit_val,
                            "balance": saldo_val,
                        })
                    elif debet_val > 0:
                        all_transactions.append({
                            "date": std_date,
                            "raw_date": raw_date,
                            "description": desc,
                            "type": "Cash Out",
                            "amount": debet_val,
                            "balance": saldo_val,
                        })

        # Jika metode tabel berhasil mendapatkan transaksi, kembalikan hasil
        if all_transactions:
            return all_transactions

        # -------------------------------------------------------------
        # METODE 2: Fallback Ekstraksi Text Line-by-Line Regex
        # -------------------------------------------------------------
        print("Tabel markdown kosong, mencoba parser baris teks BRI...\n", file=sys.stderr)

        date_pattern = re.compile(r"^(\d{2}[/-]\d{2}(?:[/-]\d{2,4})?|\d{2}\s+[A-Za-z]{3,}\s+\d{2,4})\s+")
        amount_db_cr_pattern = re.compile(r"([\d.,]+)\s+(DB|CR|D|C)\s+([\d.,]+)?$", re.IGNORECASE)

        current_tx = None

        for page_idx, page in enumerate(doc, start=1):
            text = page.get_text("text")
            lines = [ln.strip() for ln in text.splitlines() if ln.strip()]

            for line in lines:
                # Lewati baris saldo awal/akhir, header, footer
                if any(k in line.upper() for k in ["SALDO AWAL", "SALDO AKHIR", "MUTASI CR", "MUTASI DB", "HALAMAN", "BERSAMBUNG"]):
                    continue

                date_match = date_pattern.match(line)
                if date_match:
                    if current_tx and current_tx["amount"] > 0:
                        all_transactions.append(current_tx)

                    raw_date_part = date_match.group(1)
                    rest = line[date_match.end():].strip()

                    amt_match = amount_db_cr_pattern.search(rest)
                    if amt_match:
                        raw_amount = amt_match.group(1)
                        db_cr = amt_match.group(2).upper()
                        tx_type = "Cash In" if db_cr in ["CR", "C"] else "Cash Out"
                        raw_balance = amt_match.group(3)
                        desc = rest[:amt_match.start()].strip()

                        current_tx = {
                            "date": format_bri_date(raw_date_part, period_year),
                            "raw_date": raw_date_part,
                            "description": clean_text(desc),
                            "type": tx_type,
                            "amount": clean_amount(raw_amount),
                            "balance": clean_amount(raw_balance) if raw_balance else None,
                        }
                    else:
                        current_tx = {
                            "date": format_bri_date(raw_date_part, period_year),
                            "raw_date": raw_date_part,
                            "description": clean_text(rest),
                            "type": "Cash Out",
                            "amount": 0.0,
                            "balance": None,
                        }
                elif current_tx:
                    amt_match = amount_db_cr_pattern.search(line)
                    if amt_match and current_tx["amount"] == 0:
                        raw_amount = amt_match.group(1)
                        db_cr = amt_match.group(2).upper()
                        current_tx["type"] = "Cash In" if db_cr in ["CR", "C"] else "Cash Out"
                        current_tx["amount"] = clean_amount(raw_amount)
                        if amt_match.group(3):
                            current_tx["balance"] = clean_amount(amt_match.group(3))
                        desc_extra = line[:amt_match.start()].strip()
                        if desc_extra:
                            current_tx["description"] += " " + clean_text(desc_extra)
                    else:
                        current_tx["description"] += " " + clean_text(line)

        if current_tx and current_tx["amount"] > 0:
            all_transactions.append(current_tx)

        return all_transactions

    def _find_col(self, header: list[str], candidates: list[str]) -> int:
        for c in candidates:
            for idx, h in enumerate(header):
                if c in h:
                    return idx
        return -1

    def _extract_period_year(self, text: str) -> str:
        # Coba cocokkan "PERIODE : ... 202X"
        match = re.search(r"PERIODE\s*:\s*.*?(20\d{2})", text, re.IGNORECASE)
        if match:
            return match.group(1)
        match2 = re.search(r"\b(20\d{2})\b", text)
        if match2:
            return match2.group(1)
        return str(date.today().year)

