import sys, json, re
import pymupdf
import pymupdf4llm

path = sys.argv[1]
doc = pymupdf.open(path)
if doc.needs_pass:
    password = sys.argv[2] if len(sys.argv) > 2 else ""
    if not password and sys.stdin.isatty():
        password = input("Password PDF (kosongkan jika tidak ada): ")
    if not doc.authenticate(password):
        sys.stderr.write("Password salah atau dokumen terproteksi password\n")
        sys.exit(1)

total = len(doc)
DATE_RE = re.compile(r"^\d{1,2}\s+\w+\s+\d{4}(\s*<br>\s*\d{1,2}:\d{2})?$")
MONTH_RE = re.compile(r"^\*\*\w+\s+\d{4}\*\*$")

def split_row(line):
    return [c.strip() for c in line.strip("|").split("|")]

def clean(cell):
    return cell.replace("<br>", " ").strip()

def is_date_cell(cell):
    return bool(DATE_RE.match(cell.strip()))

def is_month_row(row):
    return len(row) == 1 and bool(MONTH_RE.match(row[0].strip()))

def progress(current, total, width=30):
    pct = current / total if total else 1
    filled = int(width * pct)
    bar = "█" * filled + "░" * (width - filled)
    print(f"\r  [{bar}] {current}/{total} ({pct*100:5.1f}%)", end="", flush=True, file=sys.stderr)

all_rows = []

print(f"Membaca {total} halaman...\n", file=sys.stderr)

for i, page in enumerate(doc, start=1):
    # Proses per halaman: konversi halaman ini saja ke markdown
    md = pymupdf4llm.to_markdown(doc, pages=[page.number])

    table_blocks = re.findall(r"((?:^\|.*\|\s*$\n?)+)", md, re.MULTILINE)
    for block in table_blocks:
        lines = [ln.strip() for ln in block.strip().splitlines() if ln.strip()]
        if len(lines) < 2:
            continue
        if not re.match(r"^\|[\s\-:|]+\|$", lines[1]):
            continue

        header = [clean(h) for h in split_row(lines[0])]
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
            item = {k: clean(v) for k, v in zip(header, row)}
            all_rows.append(item)

    progress(i, total)

print("\n\nSelesai. Menulis JSON...\n", file=sys.stderr)
print("===JSON_START===")
print(json.dumps(all_rows, indent=2, ensure_ascii=False))
print("===JSON_END===")