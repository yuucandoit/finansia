import sys
from .base_parser import BaseBankParser

REGISTRY: dict[str, BaseBankParser] = {}

try:
    from .jago_parser import JagoParser
    REGISTRY["jago"] = JagoParser()
except Exception as e:
    sys.stderr.write(f"Peringatan: JagoParser gagal dimuat: {e}\n")

try:
    from .bca_parser import BcaParser
    REGISTRY["bca"] = BcaParser()
except Exception as e:
    sys.stderr.write(f"Peringatan: BcaParser gagal dimuat: {e}\n")

try:
    from .mandiri_parser import MandiriParser
    REGISTRY["mandiri"] = MandiriParser()
except Exception as e:
    sys.stderr.write(f"Peringatan: MandiriParser gagal dimuat: {e}\n")

try:
    from .bri_parser import BriParser
    REGISTRY["bri"] = BriParser()
except Exception as e:
    sys.stderr.write(f"Peringatan: BriParser gagal dimuat: {e}\n")

def resolve_parser(bank_code: str = "auto", first_page_text: str = "") -> BaseBankParser:
    """
    Pilih parser yang tepat berdasarkan kode bank atau auto-detect dari teks dokumen.
    """
    code = (bank_code or "auto").lower().strip()

    # 1. Cek kecocokan alias kode bank
    alias_map = {
        "jago": "jago",
        "213": "jago",
        "472": "jago",
        "bca": "bca",
        "014": "bca",
        "mandiri": "mandiri",
        "008": "mandiri",
        "bri": "bri",
        "002": "bri",
        "brimo": "bri",
    }

    resolved_code = alias_map.get(code)
    if resolved_code and resolved_code in REGISTRY:
        return REGISTRY[resolved_code]

    # 2. Jika auto-detect, periksa kecocokan can_handle()
    if first_page_text:
        for parser in REGISTRY.values():
            if parser.can_handle(first_page_text):
                return parser

    # 3. Default fallback ke JagoParser atau parser pertama yang tersedia
    if "jago" in REGISTRY:
        return REGISTRY["jago"]
    if REGISTRY:
        return next(iter(REGISTRY.values()))
    
    return BaseBankParser()
