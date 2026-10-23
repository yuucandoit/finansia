import sys
import json
import pymupdf
from parsers import resolve_parser

if len(sys.argv) < 2:
    print("Penggunaan: python pdf_extractor.py <path_pdf> [bank_code] [password]")
    sys.exit(1)

path = sys.argv[1]
doc = pymupdf.open(path)

KNOWN_BANKS = {"jago", "bca", "mandiri", "bri", "bni", "cimb", "seabank", "auto", "014", "008", "213", "002", "009"}

bank_code = "auto"
password = ""

extra_args = sys.argv[2:]

if doc.needs_pass:
    authenticated = False
    # Cek setiap argumen yang diinput, apakah ada yang valid sebagai password
    for idx, arg in enumerate(extra_args):
        if doc.authenticate(arg):
            password = arg
            authenticated = True
            # Argumen sisanya dianggap sebagai bank_code
            remaining = [a for i, a in enumerate(extra_args) if i != idx]
            if remaining:
                bank_code = remaining[0]
            break

    if not authenticated:
        if sys.stdin.isatty():
            password = input("Password PDF (kosongkan jika tidak ada): ")
            if not doc.authenticate(password):
                sys.stderr.write("Password salah atau dokumen terproteksi password\n")
                sys.exit(1)
            authenticated = True
            if extra_args:
                bank_code = extra_args[0]
        else:
            sys.stderr.write("Password salah atau dokumen terproteksi password\n")
            sys.exit(1)
else:
    # Dokumen tidak terenkripsi
    for arg in extra_args:
        if arg.lower() in KNOWN_BANKS:
            bank_code = arg.lower()
            break
    if bank_code == "auto" and extra_args:
        bank_code = extra_args[0]

# Ambil teks halaman pertama untuk auto-detection jika bank_code == 'auto'
first_page_text = doc[0].get_text() if len(doc) > 0 else ""
parser = resolve_parser(bank_code, first_page_text)

sys.stderr.write(f"Bank terdeteksi / dipilih: {parser.name} (kode: {parser.bank_code})\n\n")

all_rows = []
try:
    all_rows = parser.parse(doc)
except Exception as e:
    sys.stderr.write(f"Parser {parser.name} gagal mengekstrak baris tabel: {e}\n")
    all_rows = []

# Ekstrak seluruh teks mentah dari PDF untuk fallback AI jika parsing lokal kosong/tidak sesuai
full_text = "\n\n--- HALAMAN BARU ---\n\n".join(page.get_text() for page in doc)

output_payload = {
    "status": "success" if len(all_rows) > 0 else "need_ai",
    "parser": parser.name,
    "bank_code": parser.bank_code,
    "transactions": all_rows,
    "raw_text": full_text
}

print("\nSelesai mengekstrak data.\n", file=sys.stderr)
print("===JSON_START===")
print(json.dumps(output_payload, indent=2, ensure_ascii=False))
print("===JSON_END===")