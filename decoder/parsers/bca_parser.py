import re
import sys
from .base_parser import BaseBankParser, clean_text, clean_amount

class BcaParser(BaseBankParser):
    name = "Bank BCA"
    bank_code = "bca"

    def can_handle(self, text: str) -> bool:
        t = text.lower()
        return "bca" in t or "bank central asia" in t or "rekening tahapan" in t

    def parse(self, doc) -> list[dict]:
        total = len(doc)
        print(f"Mengekstrak {total} halaman Bank BCA...\n", file=sys.stderr)

        # 1. Cari tahun periode dari teks halaman pertama
        full_first_page = doc[0].get_text()
        year = self._extract_period_year(full_first_page)

        all_transactions = []
        date_pattern = re.compile(r"^(\d{2}/\d{2})\s+")
        amount_pattern = re.compile(r"([\d.,]+)\s+(DB|CR)\s+([\d.,]+)?$")

        current_tx = None

        for i, page in enumerate(doc, start=1):
            text = page.get_text("text")
            lines = [ln.strip() for ln in text.splitlines() if ln.strip()]

            for line in lines:
                # Lewati header dan footer halaman
                if any(k in line.upper() for k in ["SALDO AWAL", "SALDO AKHIR", "MUTASI CR", "MUTASI DB", "HALAMAN", "BERSAMBUNG KE"]):
                    continue

                # Cek apakah baris dimulai dengan tanggal DD/MM
                date_match = date_pattern.match(line)
                if date_match:
                    # Simpan transaksi sebelumnya jika ada
                    if current_tx and current_tx["amount"] > 0:
                        all_transactions.append(current_tx)

                    date_part = date_match.group(1) # Cth: "02/10"
                    rest = line[date_match.end():].strip()

                    # Cek apakah baris ini memuat nominal DB/CR di ujung
                    amt_match = amount_pattern.search(rest)
                    if amt_match:
                        raw_amount = amt_match.group(1)
                        tx_type = "Cash In" if amt_match.group(2) == "CR" else "Cash Out"
                        raw_balance = amt_match.group(3)
                        desc = rest[:amt_match.start()].strip()

                        # Susun tanggal YYYY-MM-DD
                        day, month = date_part.split("/")
                        formatted_date = f"{year}-{month}-{day}"

                        current_tx = {
                            "date": formatted_date,
                            "raw_date": f"{date_part}/{year}",
                            "description": clean_text(desc),
                            "type": tx_type,
                            "amount": clean_amount(raw_amount),
                            "balance": clean_amount(raw_balance) if raw_balance else None,
                        }
                    else:
                        day, month = date_part.split("/")
                        formatted_date = f"{year}-{month}-{day}"
                        current_tx = {
                            "date": formatted_date,
                            "raw_date": f"{date_part}/{year}",
                            "description": clean_text(rest),
                            "type": "Cash Out",
                            "amount": 0.0,
                            "balance": None,
                        }
                elif current_tx:
                    # Baris lanjutan (kelanjutan deskripsi atau baris nominal)
                    amt_match = amount_pattern.search(line)
                    if amt_match and current_tx["amount"] == 0:
                        raw_amount = amt_match.group(1)
                        current_tx["type"] = "Cash In" if amt_match.group(2) == "CR" else "Cash Out"
                        current_tx["amount"] = clean_amount(raw_amount)
                        if amt_match.group(3):
                            current_tx["balance"] = clean_amount(amt_match.group(3))
                        desc_extra = line[:amt_match.start()].strip()
                        if desc_extra:
                            current_tx["description"] += " " + clean_text(desc_extra)
                    else:
                        # Baris tambahan deskripsi
                        current_tx["description"] += " " + clean_text(line)

        if current_tx and current_tx["amount"] > 0:
            all_transactions.append(current_tx)

        return all_transactions

    def _extract_period_year(self, text: str) -> str:
        # Coba cocokkan "PERIODE : ... 202X"
        match = re.search(r"PERIODE\s*:\s*.*?(20\d{2})", text, re.IGNORECASE)
        if match:
            return match.group(1)
        # Coba cocokkan tahun apapun 202X
        match2 = re.search(r"\b(20\d{2})\b", text)
        if match2:
            return match2.group(1)
        import datetime
        return str(datetime.date.today().year)
