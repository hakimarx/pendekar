import re
from parse_robust import parse_robust_row
from collections import defaultdict, Counter

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

regency_data = defaultdict(list)
for idx, r in enumerate(rows):
    try:
        p = parse_robust_row(r, idx)
        reg = p["regency"]
        regency_data[reg].append(p["source"] + " | " + p["area"])
    except:
        pass

for reg in sorted(regency_data.keys(), key=lambda x: int(x) if x.isdigit() else 999):
    print(f"Code {reg}:")
    for sample in regency_data[reg][:3]:
        print(f"  - {sample[:150]}")
