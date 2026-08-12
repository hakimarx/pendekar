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

regency_sources = defaultdict(list)
regency_areas = defaultdict(list)

for idx, r in enumerate(rows):
    try:
        p = parse_robust_row(r, idx)
        reg = p["regency"]
        source = p["source"]
        area = p["area"]
        regency_sources[reg].append(source)
        regency_areas[reg].append(area)
    except:
        pass

for reg in sorted(regency_sources.keys(), key=lambda x: int(x) if x.isdigit() else 999):
    sources_counter = Counter(regency_sources[reg])
    areas_counter = Counter(regency_areas[reg])
    print(f"Regency Code: {reg} (Total cases: {len(regency_sources[reg])})")
    print("  Most Common Sources:")
    for src, cnt in sources_counter.most_common(3):
        print(f"    - {src}: {cnt}")
    print("  Sample Areas:")
    for area, cnt in areas_counter.most_common(3):
        print(f"    - {area}: {cnt}")
    print("-" * 50)
