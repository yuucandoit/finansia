from .base_parser import BaseBankParser
from .jago_parser import JagoParser
from .bca_parser import BcaParser
from .mandiri_parser import MandiriParser

REGISTRY: dict[str, BaseBankParser] = {
    "jago": JagoParser(),
    "bca": BcaParser(),
    "mandiri": MandiriParser(),
}

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
    }

    resolved_code = alias_map.get(code)
    if resolved_code and resolved_code in REGISTRY:
        return REGISTRY[resolved_code]

    # 2. Jika auto-detect, periksa kecocokan can_handle()
    if first_page_text:
        for parser in REGISTRY.values():
            if parser.can_handle(first_page_text):
                return parser

    # 3. Default fallback ke JagoParser
    return REGISTRY["jago"]
