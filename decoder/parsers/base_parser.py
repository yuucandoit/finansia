import re
from abc import ABC, abstractmethod

def clean_text(cell: str) -> str:
    """Bersihkan teks dari HTML break dan spasi berlebih."""
    if not cell:
        return ""
    text = cell.replace("<br>", " ").replace("\n", " ")
    return re.sub(r"\s+", " ", text).strip()

def clean_amount(val) -> float:
    """Bersihkan string format nominal Indonesia menjadi float murni."""
    if val is None:
        return 0.0
    if isinstance(val, (int, float)):
        return abs(float(val))

    s = str(val).strip()
    s = re.sub(r"[^\d.,]", "", s)
    if not s:
        return 0.0

    if "." in s and "," in s:
        last_dot = s.rfind(".")
        last_comma = s.rfind(",")
        if last_comma > last_dot:
            # Standar Indonesia: 1.234.567,89 -> titik ribuan, koma desimal
            s = s.replace(".", "").replace(",", ".")
        else:
            s = s.replace(",", "")
    elif "." in s:
        parts = s.split(".")
        if len(parts[-1]) == 3:
            s = s.replace(".", "")
    elif "," in s:
        s = s.replace(",", ".")

    try:
        return abs(float(s))
    except ValueError:
        return 0.0

class BaseBankParser(ABC):
    name: str = "BaseParser"
    bank_code: str = "base"

    @abstractmethod
    def can_handle(self, text: str) -> bool:
        """Deteksi apakah parser ini cocok untuk file PDF berdasarkan teks halaman pertama."""
        pass

    @abstractmethod
    def parse(self, doc) -> list[dict]:
        """
        Ekstrak transaksi dari pymupdf Document.
        Wajib mengembalikan list of dict dengan standar kontrak:
        [
            {
                "date": "YYYY-MM-DD",
                "raw_date": "...",
                "description": "...",
                "type": "Cash In" | "Cash Out",
                "amount": float,
                "balance": float | None
            }
        ]
        """
        pass
