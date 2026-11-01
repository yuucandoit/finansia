import re
import sys
from .base_parser import BaseBankParser, clean_text, clean_amount

def to_markdown_compat(doc, page_number: int) -> str:
    """
    Ekstrak halaman PDF ke format Markdown dengan kompatibilitas:
    1. PyMuPDF 2.0+ native (doc.to_markdown)
    2. pymupdf4llm standalone (< 2.0.0)
    3. pymupdf.to_markdown
    4. Fallback plain text jika tidak tersedia
    """
    # 1. Coba method bawaan doc.to_markdown (PyMuPDF 2.0+)
    if hasattr(doc, "to_markdown"):
        try:
            return doc.to_markdown(pages=[page_number])
        except Exception:
            pass

    # 2. Coba library standalone pymupdf4llm (versi < 2.0.0)
    try:
        import pymupdf4llm
        if hasattr(pymupdf4llm, "to_markdown"):
            return pymupdf4llm.to_markdown(doc, pages=[page_number])
    except ImportError:
        pass

    # 3. Coba modul pymupdf.to_markdown
    try:
        import pymupdf
        if hasattr(pymupdf, "to_markdown"):
            return pymupdf.to_markdown(doc, pages=[page_number])
    except ImportError:
        pass

    # 4. Fallback jika tidak ada to_markdown
    try:
        page = doc[page_number] if isinstance(page_number, int) and page_number < len(doc) else doc[0]
        return page.get_text("text")
    except Exception as e:
        print(f"Peringatan: Gagal mengekstrak teks halaman {page_number}: {e}", file=sys.stderr)
        return ""

DATE_RE = re.compile(r"^\d{1,2}\s+\w+\s+\d{4}(\s*<br>\s*\d{1,2}:\d{2})?$")
MONTH_RE = re.compile(r"^\*\*\w+\s+\d{4}\*\*$")

def split_row(line):
    return [c.strip() for c in line.strip("|").split("|")]

def is_date_cell(cell):
    return bool(DATE_RE.match(cell.strip()))

def is_month_row(row):
    return len(row) == 1 and bool(MONTH_RE.match(row[0].strip()))

def progress_bar(current, total, width=30):
    pct = current / total if total else 1
    filled = int(width * pct)
    bar = "█" * filled + "░" * (width - filled)
    print(f"\r  [Bank Jago] [{bar}] {current}/{total} ({pct*100:5.1f}%)", end="", flush=True, file=sys.stderr)

class JagoParser(BaseBankParser):
    name = "Bank Jago"
    bank_code = "jago"

    def can_handle(self, text: str) -> bool:
        t = text.lower()
        return "bank jago" in t or "kantong utama" in t or "pocket" in t or "jago" in t

    def parse(self, doc) -> list[dict]:
        total = len(doc)
        print(f"Mengekstrak {total} halaman Bank Jago...\n", file=sys.stderr)

        all_rows = []
        for i, page in enumerate(doc, start=1):
            md = to_markdown_compat(doc, page.number)

            table_blocks = re.findall(r"((?:^\|.*\|\s*$\n?)+)", md, re.MULTILINE)
            for block in table_blocks:
                lines = [ln.strip() for ln in block.strip().splitlines() if ln.strip()]
                if len(lines) < 2:
                    continue
                if not re.match(r"^\|[\s\-:|]+\|$", lines[1]):
                    continue

                header = [clean_text(h) for h in split_row(lines[0])]
                raw_rows = [split_row(ln) for ln in lines[2:]]

                merged = []
                for row in raw_rows:
                    if is_month_row(row):
                        continue
                    if merged and not is_date_cell(row[0]) and len(row) < len(header):
                        prev = merged[-1]
                        for j, cell in enumerate(row):
                            if j < len(prev):
                                prev[j] = (prev[j] + " " + cell).strip() if cell else prev[j]
                    else:
                        merged.append(list(row))

                for row in merged:
                    if len(row) < len(header):
                        row += [""] * (len(header) - len(row))
                    item = {k: clean_text(v) for k, v in zip(header, row)}

                    # Normalisasi ke Standar Kontrak Finansia
                    norm = self._normalize_row(item)
                    if norm and norm["amount"] > 0:
                        all_rows.append(norm)

            progress_bar(i, total)

        print("\n", file=sys.stderr)
        return all_rows

    def _normalize_row(self, item: dict) -> dict:
        raw_date = item.get("Date & Time") or item.get("Date") or ""
        
        # Ekstrak deskripsi gabungan
        desc_parts = []
        if item.get("Source/Destination"):
            desc_parts.append(item["Source/Destination"])
        if item.get("Transaction Details"):
            desc_parts.append(item["Transaction Details"])
        if item.get("Notes"):
            desc_parts.append(item["Notes"])
        description = " - ".join(desc_parts) if desc_parts else "Transaksi Bank Jago"

        # Tipe & Nominal
        amount_raw = item.get("Amount", "")
        tx_type = "Cash Out"
        if amount_raw.startswith("+"):
            tx_type = "Cash In"
        elif amount_raw.startswith("-"):
            tx_type = "Cash Out"

        amount = clean_amount(amount_raw)
        balance = clean_amount(item.get("Balance")) if item.get("Balance") else None

        return {
            "date": raw_date,
            "raw_date": raw_date,
            "description": description,
            "type": tx_type,
            "amount": amount,
            "balance": balance,
            "raw_data": item,
        }
