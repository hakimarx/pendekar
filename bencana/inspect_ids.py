import re
from parse_robust import parse_robust_row
from collections import defaultdict

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

type_map = defaultdict(list)
for idx, r in enumerate(rows):
    try:
        p = parse_robust_row(r, idx)
        type_map[p["id_disastertype"]].append(p)
    except:
        pass

# Let's inspect the target IDs: 7, 9, 17, 18, 19, 21, 22, 23, 25
target_ids = ['7', '9', '17', '18', '19', '21', '22', '23', '25']
for tid in target_ids:
    p_list = type_map[tid]
    print(f"=== ID {tid} (Total {len(p_list)} cases) ===")
    for p in p_list[:5]:
        print(f"  Area: {p['area']}")
        print(f"  Chron: {p['chronology'][:200]}")
        print(f"  Source: {p['source']}")
        print(f"  Level: {p['level']}")
        print("-" * 15)
    print("\n" + "="*50 + "\n")
