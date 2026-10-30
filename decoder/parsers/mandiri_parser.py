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

def split_row(line):
    return [c.strip() for c in line.strip("|").split("|")]

class MandiriParser(BaseBankParser):
    name = "Bank Mandiri"
    bank_code = "mandiri"

    def can_handle(self, text: str) -> bool:
        t = text.lower()
        return "mandiri" in t or "livin" in t or "bank mandiri" in t

    def parse(self, doc) -> list[dict]:
        total = len(doc)
        print(f"Mengekstrak {total} halaman Bank Mandiri...\n", file=sys.stderr)

        all_transactions = []

        # 1. Coba ekstraksi tabel markdown
        for i, page in enumerate(doc, start=1):
            md = to_markdown_compat(doc, page.number)
            table_blocks = re.findall(r"((?:^\|.*\|\s*$\n?)+)", md, re.MULTILINE)

            for block in table_blocks:
                lines = [ln.strip() for ln in block.strip().splitlines() if ln.strip()]
                if len(lines) < 2:
                    continue
                if not re.match(r"^\|[\s\-:|]+\|$", lines[1]):
                    continue

                header = [clean_text(h).lower() for h in split_row(lines[0])]
                raw_rows = [split_row(ln) for ln in lines[2:]]

                date_idx = self._find_col(header, ["tanggal", "tgl", "date"])
                desc_idx = self._find_col(header, ["keterangan", "uraian", "deskripsi", "description"])
                debet_idx = self._find_col(header, ["debet", "debit", "dana keluar", "keluar", "mutasi d"])
                kredit_idx = self._find_col(header, ["kredit", "credit", "dana masuk", "masuk", "mutasi c", "mutasi k"])
                saldo_idx = self._find_col(header, ["saldo", "balance"])

                for row in raw_rows:
                    if len(row) < len(header):
                        row += [""] * (len(header) - len(row))

                    raw_date = clean_text(row[date_idx]) if date_idx != -1 else ""
                    if not raw_date or not re.search(r"\d", raw_date):
                        continue

                    desc = clean_text(row[desc_idx]) if desc_idx != -1 else "Transaksi Mandiri"
                    debet_val = clean_amount(row[debet_idx]) if debet_idx != -1 else 0.0
                    kredit_val = clean_amount(row[kredit_idx]) if kredit_idx != -1 else 0.0
                    saldo_val = clean_amount(row[saldo_idx]) if saldo_idx != -1 and row[saldo_idx] else None

                    if kredit_val > 0:
                        all_transactions.append({
                            "date": raw_date,
                            "raw_date": raw_date,
                            "description": desc,
                            "type": "Cash In",
                            "amount": kredit_val,
                            "balance": saldo_val,
                        })
                    elif debet_val > 0:
                        all_transactions.append({
                            "date": raw_date,
                            "raw_date": raw_date,
                            "description": desc,
                            "type": "Cash Out",
                            "amount": debet_val,
                            "balance": saldo_val,
                        })

        # 2. Jika tabel markdown kosong, gunakan text-layout parser (Livin' e-statement)
        if not all_transactions:
            all_transactions = self._parse_text_layout(doc)

        return all_transactions

    def _parse_text_layout(self, doc) -> list[dict]:
        full_text = "\n".join(p.get_text() for p in doc)
        lines = [ln.strip() for ln in full_text.splitlines() if ln.strip()]

        txs = []
        i = 0
        while i < len(lines):
            line = lines[i]
            # Pola: baris nominal e-statement Mandiri: +11.758.422,00 atau -2.500,00
            m_amt = re.match(r"^([+-])\s*([\d.,]+)$", line)
            if m_amt:
                sign = m_amt.group(1)
                raw_amt = m_amt.group(2)
                tx_type = "Cash In" if sign == "+" else "Cash Out"
                amount = clean_amount(raw_amt)

                date_str = ""
                desc_lines = []
                balance = None

                # Cari tanggal di baris setelah nominal (biasanya jam lalu tanggal)
                for j in range(i + 1, min(i + 6, len(lines))):
                    m_d = re.search(r"\b\d{1,2}\s+[A-Za-z]{3}\s+\d{4}\b", lines[j])
                    if m_d:
                        date_str = m_d.group(0)
                        break

                # Cari saldo dan deskripsi di baris sebelum nominal
                if i >= 2 and re.match(r"^\d+$", lines[i - 1]):
                    # lines[i-1] adalah nomor urut
                    if re.match(r"^[\d.,]+$", lines[i - 2]):
                        balance = clean_amount(lines[i - 2])
                        k = i - 3
                        while (
                            k >= 0
                            and not re.match(r"^[+-]\s*[\d.,]+$", lines[k])
                            and not re.search(r"\b\d{1,2}\s+[A-Za-z]{3}\s+\d{4}\b", lines[k])
                            and "Saldo (IDR)" not in lines[k]
                            and "Remarks" not in lines[k]
                        ):
                            desc_lines.insert(0, lines[k])
                            k -= 1

                desc = " ".join(desc_lines) if desc_lines else "Transaksi Mandiri"
                if date_str and "Dana Masuk" not in desc and "Dana Keluar" not in desc:
                    txs.append({
                        "date": date_str,
                        "raw_date": date_str,
                        "description": clean_text(desc),
                        "type": tx_type,
                        "amount": amount,
                        "balance": balance,
                    })
            i += 1

        return txs

    def _find_col(self, header: list[str], keywords: list[str]) -> int:
        for idx, col in enumerate(header):
            for kw in keywords:
                if kw in col:
                    return idx
        return -1
