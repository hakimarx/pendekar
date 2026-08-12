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
        regency_data[reg].append((p["source"], p["area"]))
    except:
        pass

for tr in ["16", "19", "21"]:
    print(f"=== Regency {tr} (Count: {len(regency_data[tr])}) ===")
    counter_src = Counter([x[0] for x in regency_data[tr]])
    counter_area = Counter([x[1] for x in regency_data[tr]])
    print("  Sources:")
    for k, v in counter_src.most_common(3):
        print(f"    - {k}: {v}")
    print("  Areas:")
    for k, v in counter_area.most_common(3):
        print(f"    - {k}: {v}")
    print("-" * 40)
