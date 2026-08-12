import re
from parse_robust import parse_robust_row

with open("2022.csv", "r", encoding="utf-8", errors="ignore") as f:
    content = f.read()

if content.startswith('\ufeff'):
    content = content[1:]

pattern = r'\n"(\d+)""(\d+)""'
matches = list(re.finditer(pattern, content))

rows = []
header_end = content.find('\n')
prev_idx = header_end + 1 if header_end != -1 else 0

for m in matches:
    start = m.start()
    row_text = content[prev_idx:start].strip()
    if row_text:
        rows.append(row_text)
    prev_idx = start + 1

last_row = content[prev_idx:].strip()
if last_row:
    rows.append(last_row)

for idx, r in enumerate(rows):
    if "17990" in r:
        print(f"Row {idx} text:")
        print(r)
        if r.startswith('"') and r.endswith('"'):
            r_strip = r[1:-1]
        else:
            r_strip = r
        parts = r_strip.split('""')
        print("\nParts:")
        for p_idx, p in enumerate(parts):
            print(f"  {p_idx}: {repr(p)}")
