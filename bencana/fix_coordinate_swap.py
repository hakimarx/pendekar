import os
import json
import re
import pandas as pd
import numpy as np

# Files
PESANTREN_FILE = "data pesantren jatim.xlsx"
BENCANA_2022_CSV = "2022.csv"
BENCANA_2024_XLSX = "bencana 2024.xlsx"
MERGED_BENCANA_OUT = "bencana_jatim_2022_2024.csv"
DERIVED_OUT = "derived_coordinates.json"
CACHE_FILE = "kecamatan_coordinates.json"
FINAL_RISK_OUT = "pesantren_jatim_risk_mapped.csv"

# Regency Code Map from 2022.csv to Standard Names
REGENCY_MAP = {
    "1": "Pacitan Kabupaten", "2": "Ponorogo Kabupaten", "3": "Trenggalek Kabupaten",
    "4": "Tulungagung Kabupaten", "5": "Blitar Kabupaten", "6": "Kediri Kabupaten",
    "7": "Malang Kabupaten", "8": "Lumajang Kabupaten", "9": "Jember Kabupaten",
    "10": "Banyuwangi Kabupaten", "11": "Bondowoso Kabupaten", "12": "Situbondo Kabupaten",
    "13": "Probolinggo Kabupaten", "14": "Pasuruan Kabupaten", "15": "Sidoarjo Kabupaten",
    "16": "Mojokerto Kabupaten", "17": "Jombang Kabupaten", "18": "Nganjuk Kabupaten",
    "19": "Madiun Kabupaten", "20": "Magetan Kabupaten", "21": "Ngawi Kabupaten",
    "22": "Bojonegoro Kabupaten", "23": "Tuban Kabupaten", "24": "Lamongan Kabupaten",
    "25": "Gresik Kabupaten", "26": "Bangkalan Kabupaten", "27": "Sampang Kabupaten",
    "28": "Pamekasan Kabupaten", "29": "Sumenep Kabupaten", "30": "Kediri Kota",
    "31": "Blitar Kota", "32": "Malang Kota", "33": "Probolinggo Kota",
    "34": "Pasuruan Kota", "35": "Mojokerto Kota", "36": "Madiun Kota",
    "37": "Surabaya Kota", "38": "Batu Kota"
}

# Disaster Type Map from 2022.csv to Standard Names
DISASTER_TYPE_MAP = {
    '2': 'Gempa Bumi', '5': 'Tanah Longsor', '6': 'Banjir', '7': 'Banjir Bandang',
    '9': 'Angin Puting Beliung', '10': 'Kebakaran', '17': 'Kejadian Lain',
    '18': 'Kejadian Lain', '19': 'Angin Kencang', '21': 'Banjir Rob',
    '22': 'Gerakan Tanah', '23': 'Pohon Tumbang', '25': 'Kebakaran Lahan'
}

def clean_coord(val):
    val_str = str(val).strip()
    if val_str in ('', '-', 'nan', 'NaN', 'None'):
        return np.nan
    try:
        return float(val_str)
    except ValueError:
        return np.nan

def is_float(val):
    try:
        float(val)
        return True
    except ValueError:
        return False

def parse_robust_row(r, idx):
    if r.startswith('"') and r.endswith('"'):
        r_strip = r[1:-1]
    elif r.startswith('"'):
        r_strip = r[1:]
    elif r.endswith('"'):
        r_strip = r[:-1]
    else:
        r_strip = r
        
    parts = r_strip.split('""')
    
    id_logs = parts[0]
    id_disastertype = parts[1]
    eventdate = parts[2]
    regency = parts[3]
    
    lon_idx = -1
    for i in range(4, len(parts)):
        if is_float(parts[i]):
            val = float(parts[i])
            if 110.0 <= val <= 117.0:
                lon_idx = i
                break
                
    if lon_idx == -1:
        for i in range(4, len(parts)):
            if is_float(parts[i]):
                lon_idx = i
                break
                
    if lon_idx == -1 or lon_idx == 4:
        raise ValueError(f"Could not find coordinates in row {idx}: {parts[:10]}")
        
    lat_idx = lon_idx - 1
    
    area = '""'.join(parts[4:lat_idx]).strip()
    latitude = parts[lat_idx]
    longitude = parts[lon_idx]
    weather = parts[lon_idx + 1]
    
    source = parts[-3].strip()
    status = parts[-2].strip()
    level = parts[-1].strip()
    
    return {
        "id": f"2022_{id_logs}",
        "tahun": 2022,
        "waktu": eventdate,
        "type_bencana": DISASTER_TYPE_MAP.get(id_disastertype, f"Kejadian Lain ({id_disastertype})"),
        "kab_ko": REGENCY_MAP.get(regency, f"Unknown ({regency})"),
        "area": area,
        "latitude": clean_coord(latitude),
        "longitude": clean_coord(longitude),
        "photos": '""'.join(parts[16:-3]).strip(),
        "source": source,
        "level": level
    }

# 1. Process 2022.csv
print("=== Processing 2022.csv ===")
with open(BENCANA_2022_CSV, "r", encoding="utf-8", errors="ignore") as f:
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

parsed_2022 = []
for idx, r in enumerate(rows):
    try:
        p = parse_robust_row(r, idx)
        parsed_2022.append(p)
    except:
        pass

df_2022 = pd.DataFrame(parsed_2022)

# 2. Process bencana 2024.xlsx (Fixing swapped columns!)
print("=== Processing bencana 2024.xlsx (fixing swapped Bujur/Lintang) ===")
df_2024_raw = pd.read_excel(BENCANA_2024_XLSX)

df_2024 = pd.DataFrame()
df_2024["id"] = df_2024_raw["ID"].apply(lambda x: f"2024_{x}")
df_2024["tahun"] = 2024
df_2024["waktu"] = df_2024_raw["Waktu"].astype(str)
df_2024["type_bencana"] = df_2024_raw["Tipe Kejadian"]
df_2024["kab_ko"] = df_2024_raw["Kab. / Kota"].astype(str).str.strip()
df_2024["area"] = df_2024_raw["Area"].astype(str).str.strip()

# SWAP FIXED:
# Bujur column actually holds Lintang values (Latitude, around -7)
# Lintang column actually holds Bujur values (Longitude, around 112)
df_2024["latitude"] = df_2024_raw["Bujur"].apply(clean_coord)
df_2024["longitude"] = df_2024_raw["Lintang"].apply(clean_coord)

df_2024["photos"] = df_2024_raw["Foto"].astype(str).str.strip()
df_2024["source"] = df_2024_raw["Sumber"].astype(str).str.strip()
df_2024["level"] = df_2024_raw["Tingkatan"].astype(str).str.strip()

# 3. Merge and Save Disasters
df_merged = pd.concat([df_2022, df_2024], ignore_index=True)
df_merged["photos"] = df_merged["photos"].replace("nan", "")
df_merged["source"] = df_merged["source"].replace("nan", "")
df_merged["level"] = df_merged["level"].replace("nan", "")
df_merged.to_csv(MERGED_BENCANA_OUT, index=False, encoding="utf-8")
print(f"Saved merged disasters to {MERGED_BENCANA_OUT}. Shape: {df_merged.shape}")

# 4. Extract Corrected derived coordinates from corrected disasters
print("\n=== Re-extracting derived coordinates from corrected disasters ===")
df_pes = pd.read_excel(PESANTREN_FILE)
df_pes['Kabupaten'] = df_pes['Kabupaten'].astype(str).str.strip().str.upper()
df_pes['Kecamatan'] = df_pes['Kecamatan'].astype(str).str.strip().str.upper()
pesantren_kec = set(df_pes[['Kabupaten', 'Kecamatan']].drop_duplicates().apply(lambda x: (x['Kabupaten'], x['Kecamatan']), axis=1).tolist())

kec_pattern = re.compile(r'(?:kec\.|kecamatan)\s*([a-zA-Z\s]+)', re.IGNORECASE)
district_coords = {}

for idx, row in df_merged.iterrows():
    kab = str(row['kab_ko']).strip().upper()
    area = str(row['area'])
    lat = row['latitude']
    lon = row['longitude']
    
    if pd.isna(lat) or pd.isna(lon):
        continue
        
    match = kec_pattern.search(area)
    if match:
        kec = match.group(1).strip().upper()
        kec_clean = re.split(r'[,.\n\-\(\)]', kec)[0].strip()
        kec_clean = re.sub(r'\s+(KAB\.|KABUPATEN|KOTA|PROV).*$', '', kec_clean).strip()
        
        if kec_clean:
            kab_clean = kab.replace(" KABUPATEN", "").replace(" KOTA", "").strip()
            key = (kab_clean, kec_clean)
            if key not in district_coords:
                district_coords[key] = []
            district_coords[key].append((lat, lon))

corrected_derived = {}
for (kab, kec), coords in district_coords.items():
    lats = [c[0] for c in coords]
    lons = [c[1] for c in coords]
    avg_lat = sum(lats) / len(lats)
    avg_lon = sum(lons) / len(lons)
    
    for p_kab, p_kec in pesantren_kec:
        p_kab_clean = p_kab.replace(" KABUPATEN", "").replace(" KOTA", "").strip()
        if p_kab_clean == kab and p_kec == kec:
            # ONLY use if they are in standard range of East Java
            if -10.0 <= avg_lat <= -5.0 and 110.0 <= avg_lon <= 117.0:
                key = f"{p_kec}, {p_kab}"
                corrected_derived[key] = {"latitude": avg_lat, "longitude": avg_lon}
                break

with open(DERIVED_OUT, "w") as f:
    json.dump(corrected_derived, f, indent=2)
print(f"Saved {len(corrected_derived)} corrected derived coordinates.")

# 5. Fix CACHE_FILE
print("\n=== Fixing cached coordinates (preserving Nominatim, fixing swapped ones) ===")
cache = {}
if os.path.exists(CACHE_FILE):
    with open(CACHE_FILE, "r") as f:
        cache = json.load(f)

fixed_cache = {}
preserved_nominatim = 0
fixed_derived = 0

for key, val in cache.items():
    lat = val.get("latitude")
    lon = val.get("longitude")
    
    if lat is None or pd.isna(lat):
        fixed_cache[key] = {"latitude": None, "longitude": None}
        continue
        
    # Check if coordinate is swapped (latitude around 112, longitude around -7)
    if lat > 0:
        # This was a swapped derived coordinate!
        # Replace it with corrected derived coordinate if available
        if key in corrected_derived:
            fixed_cache[key] = corrected_derived[key]
            fixed_derived += 1
        else:
            fixed_cache[key] = {"latitude": None, "longitude": None}
    else:
        # Already correct (Latitude is negative, e.g. -7; Longitude is positive, e.g. 112)
        # This was from Nominatim! Keep it!
        fixed_cache[key] = {"latitude": lat, "longitude": lon}
        preserved_nominatim += 1

# Add any new corrected derived coordinates that were missing
for key, val in corrected_derived.items():
    if key not in fixed_cache or fixed_cache[key].get("latitude") is None:
        fixed_cache[key] = val

with open(CACHE_FILE, "w") as f:
    json.dump(fixed_cache, f, indent=2)

print(f"Coordinates Cache fixed. Preserved Nominatim: {preserved_nominatim}, Fixed Derived: {fixed_derived}, Total in Cache: {len(fixed_cache)}")

# 6. Re-map Pesantren to corrected coordinates
print("\n=== Re-mapping Pesantren to corrected coordinates ===")
df_pes_clean = pd.DataFrame()
df_pes_clean['NSPP'] = df_pes['NSPP'].astype(str).str.strip()
df_pes_clean['Nama Lembaga'] = df_pes['Nama Lembaga'].astype(str).str.strip()
df_pes_clean['Jenjang'] = df_pes['Jenjang'].astype(str).str.strip()
df_pes_clean['Kabupaten'] = df_pes['Kabupaten'].astype(str).str.strip().str.upper()
df_pes_clean['Kecamatan'] = df_pes['Kecamatan'].astype(str).str.strip().str.upper()
df_pes_clean['Alamat'] = df_pes['Alamat'].astype(str).str.strip()
df_pes_clean['Afiliasi Organisasi'] = df_pes['Afiliasi Organisasi'].astype(str).str.strip()

# Standardize names
df_pes_clean['Kabupaten'] = df_pes_clean['Kabupaten'].replace(r'\s+', ' ', regex=True)
df_pes_clean['Kecamatan'] = df_pes_clean['Kecamatan'].replace(r'\s+', ' ', regex=True)

def get_lat(row):
    key = f"{row['Kecamatan']}, {row['Kabupaten']}"
    return fixed_cache.get(key, {}).get("latitude", np.nan)

def get_lon(row):
    key = f"{row['Kecamatan']}, {row['Kabupaten']}"
    return fixed_cache.get(key, {}).get("longitude", np.nan)

df_pes_clean['latitude'] = df_pes_clean.apply(get_lat, axis=1)
df_pes_clean['longitude'] = df_pes_clean.apply(get_lon, axis=1)

mapped_count = df_pes_clean['latitude'].notna().sum()
print(f"Mapped {mapped_count} out of {len(df_pes_clean)} pesantren ({mapped_count/len(df_pes_clean)*100:.2f}%)")

# Save temporary cleaned pesantren
df_pes_clean.to_csv("pesantren_jatim_clean.csv", index=False, encoding="utf-8")

# 7. Re-calculate Proximity Risk
print("\n=== Re-calculating Proximity Risks with correct coordinates ===")
df_pes_coords = df_pes_clean[df_pes_clean['latitude'].notna() & df_pes_clean['longitude'].notna()].copy()
df_dis_coords = df_merged[df_merged['latitude'].notna() & df_merged['longitude'].notna()].copy()

lat_dis_rad = np.radians(df_dis_coords['latitude'].values)
lon_dis_rad = np.radians(df_dis_coords['longitude'].values)
dis_types = df_dis_coords['type_bencana'].values
dis_years = df_dis_coords['tahun'].values

count_5km = []
count_10km = []
count_20km = []
closest_type = []
closest_dist = []
closest_yr = []

total_pes = len(df_pes_coords)
for idx, row in enumerate(df_pes_coords.itertuples()):
    lat_pes = np.radians(row.latitude)
    lon_pes = np.radians(row.longitude)
    
    dlat = lat_dis_rad - lat_pes
    dlon = lon_dis_rad - lon_pes
    
    a = np.sin(dlat / 2.0)**2 + np.cos(lat_pes) * np.cos(lat_dis_rad) * np.sin(dlon / 2.0)**2
    c = 2.0 * np.arcsin(np.sqrt(a))
    distances = 6371.0 * c
    
    count_5 = np.sum(distances <= 5.0)
    count_10 = np.sum(distances <= 10.0)
    count_20 = np.sum(distances <= 20.0)
    
    min_idx = np.argmin(distances)
    min_dist = distances[min_idx]
    
    count_5km.append(count_5)
    count_10km.append(count_10)
    count_20km.append(count_20)
    closest_type.append(dis_types[min_idx])
    closest_dist.append(round(min_dist, 2))
    closest_yr.append(int(dis_years[min_idx]))

df_pes_coords['disasters_within_5km'] = count_5km
df_pes_coords['disasters_within_10km'] = count_10km
df_pes_coords['disasters_within_20km'] = count_20km
df_pes_coords['closest_disaster_type'] = closest_type
df_pes_coords['closest_disaster_distance_km'] = closest_dist
df_pes_coords['closest_disaster_year'] = closest_yr

def determine_risk(row):
    if row['disasters_within_5km'] >= 3:
        return 'SANGAT TINGGI'
    elif row['disasters_within_5km'] >= 1 or row['disasters_within_10km'] >= 5:
        return 'TINGGI'
    elif row['disasters_within_10km'] >= 1:
        return 'SEDANG'
    elif row['disasters_within_20km'] >= 1:
        return 'RENDAH'
    else:
        return 'TIDAK ADA RIWAYAT BENCANA'

df_pes_coords['level_kerawanan'] = df_pes_coords.apply(determine_risk, axis=1)

df_final = df_pes_clean.merge(
    df_pes_coords[['NSPP', 'disasters_within_5km', 'disasters_within_10km', 'disasters_within_20km', 
                   'closest_disaster_type', 'closest_disaster_distance_km', 'closest_disaster_year', 'level_kerawanan']],
    on='NSPP',
    how='left'
)

df_final['level_kerawanan'] = df_final['level_kerawanan'].fillna('TIDAK ADA DATA KOORDINAT')
df_final['disasters_within_5km'] = df_final['disasters_within_5km'].fillna(0).astype(int)
df_final['disasters_within_10km'] = df_final['disasters_within_10km'].fillna(0).astype(int)
df_final['disasters_within_20km'] = df_final['disasters_within_20km'].fillna(0).astype(int)

print("\nFinal Risk Level Distribution:")
print(df_final['level_kerawanan'].value_counts())

df_final.to_csv(FINAL_RISK_OUT, index=False, encoding="utf-8")
print(f"Saved final output to {FINAL_RISK_OUT}")
