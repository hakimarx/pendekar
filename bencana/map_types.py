import re

# We will reuse the parse_robust_row function
# and print unique id_disastertype values with some sample chronologies.

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

from collections import defaultdict
type_map = defaultdict(list)

for idx, r in enumerate(rows):
    try:
        p = parse_robust_row(r, idx)
        dtype = p["id_disastertype"]
        chron = p["chronology"]
        type_map[dtype].append(chron)
    except:
        pass

for dtype, chrons in sorted(type_map.items(), key=lambda x: int(x[0]) if x[0].isdigit() else 999):
    print(f"ID: {dtype} (Count: {len(chrons)})")
    # Find common terms
    sample_text = " | ".join(chrons[:3])
    # Let's clean up sample text to avoid printing too much
    print(f"  Samples: {sample_text[:300]}...")
    print("-" * 50)
