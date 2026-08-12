import re
import json
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
for idx, r in enumerate(rows):
    try:
        p = parse_robust_row(r, idx)
        reg = p["regency"]
        source = p["source"]
        regency_sources[reg].append(source)
    except:
        pass

# Let's map each code to the most likely name
mapping = {}
for reg, sources in regency_sources.items():
    # Find most common words in sources to deduce regency name
    src_text = " ".join(sources).upper()
    
    # We want to match Kabupaten/Kota names in East Java
    # Let's look for keywords in the sources
    candidates = [
        "TRENGGALEK", "LUMAJANG", "JEMBER", "SUMENEP", "PASURUAN", "KEDIRI",
        "BANYUWANGI", "PROBOLINGGO", "MOJOKERTO", "BLITAR", "BONDOWOSO", 
        "SITUBONDO", "MALANG", "NGAWI", "PACITAN", "PAMEKASAN", "BONDOWOSO",
        "JOMBANG", "TULUNGAGUNG", "PONOROGO", "MAGETAN", "SAMPANG",
        "BOJONEGORO", "TUBAN", "LAMONGAN", "GRESIK", "BANGKALAN", "MADIUN",
        "SURABAYA", "BATU", "Nganjuk"
    ]
    # Check candidates
    matched_name = None
    for cand in candidates:
        if cand.upper() in src_text:
            matched_name = cand
            break
            
    # Let's check if the word "KOTA" is in the source to differentiate Kota vs Kabupaten
    is_kota = "KOTA" in src_text or "SURABAYA" in src_text or "BATU" in src_text
    
    if matched_name:
        if matched_name == "SURABAYA":
            full_name = "Surabaya Kota"
        elif matched_name == "BATU":
            full_name = "Batu Kota"
        elif is_kota:
            full_name = f"{matched_name.title()} Kota"
        else:
            full_name = f"{matched_name.title()} Kabupaten"
    else:
        full_name = f"Unknown_{reg}"
        
    mapping[reg] = full_name

# Sort by key
mapping = {k: mapping[k] for k in sorted(mapping.keys(), key=lambda x: int(x) if x.isdigit() else 999)}

print("Regency Code Mapping:")
print(json.dumps(mapping, indent=2))

with open("regency_mapping_derived.json", "w") as jf:
    json.dump(mapping, jf, indent=2)
