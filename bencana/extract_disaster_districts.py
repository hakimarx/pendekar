import pandas as pd
import re
import json
import os
import numpy as np

# Load clean merged disasters
df_dis = pd.read_csv("bencana_jatim_2022_2024.csv")
# Load pesantren data to see what unique Kecamatan we need
df_pes = pd.read_excel("data pesantren jatim.xlsx")

df_pes['Kabupaten'] = df_pes['Kabupaten'].astype(str).str.strip().str.upper()
df_pes['Kecamatan'] = df_pes['Kecamatan'].astype(str).str.strip().str.upper()

pesantren_kec = set(df_pes[['Kabupaten', 'Kecamatan']].drop_duplicates().apply(lambda x: (x['Kabupaten'], x['Kecamatan']), axis=1).tolist())
print(f"Total unique Kecamatan in Pesantren: {len(pesantren_kec)}")

# Try to extract Kecamatan from disaster areas
# Regex to match Kec. <Name> or Kecamatan <Name>
kec_pattern = re.compile(r'(?:kec\.|kecamatan)\s*([a-zA-Z\s]+)', re.IGNORECASE)

extracted_coords = {}

# We will collect coordinates for each (Kabupaten, Kecamatan)
district_coords = {}

for idx, row in df_dis.iterrows():
    kab = str(row['kab_ko']).strip().upper()
    area = str(row['area'])
    lat = row['latitude']
    lon = row['longitude']
    
    if pd.isna(lat) or pd.isna(lon):
        continue
        
    # Search in area
    match = kec_pattern.search(area)
    if match:
        kec = match.group(1).strip().upper()
        # Clean up: take only the first word or words until comma/dot/newline
        kec_clean = re.split(r'[,.\n\-\(\)]', kec)[0].strip()
        # Remove "KABUPATEN" or similar if they slipped in
        kec_clean = re.sub(r'\s+(KAB\.|KABUPATEN|KOTA|PROV).*$', '', kec_clean).strip()
        
        if kec_clean:
            # Let's map clean kab to match pesantren format
            # In pesantren format, it's just "BANGKALAN", but in disaster it's "Bangkalan Kabupaten"
            kab_clean = kab.replace(" KABUPATEN", "").replace(" KOTA", "").strip()
            
            key = (kab_clean, kec_clean)
            if key not in district_coords:
                district_coords[key] = []
            district_coords[key].append((lat, lon))

# Average coordinates for each extracted district
derived_cache = {}
matched_districts = 0

for (kab, kec), coords in district_coords.items():
    lats = [c[0] for c in coords]
    lons = [c[1] for c in coords]
    avg_lat = sum(lats) / len(lats)
    avg_lon = sum(lons) / len(lons)
    
    # We need to match with the exact spelling in the pesantren list
    # Let's find matches in pesantren_kec
    # Note that there might be slight spelling differences, but let's check exact match first
    for p_kab, p_kec in pesantren_kec:
        p_kab_clean = p_kab.replace(" KABUPATEN", "").replace(" KOTA", "").strip()
        if p_kab_clean == kab and p_kec == kec:
            key = f"{p_kec}, {p_kab}"
            derived_cache[key] = {"latitude": avg_lat, "longitude": avg_lon}
            matched_districts += 1
            break

print(f"Successfully matched and derived coordinates for {matched_districts} Kecamatan from disaster data!")

# Let's save this to a json file to inspect
with open("derived_coordinates.json", "w") as f:
    json.dump(derived_cache, f, indent=2)
