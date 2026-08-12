import re
import pandas as pd
import numpy as np

# Regency Code Map from 2022.csv to Standard Names
REGENCY_MAP = {
    "1": "Pacitan Kabupaten",
    "2": "Ponorogo Kabupaten",
    "3": "Trenggalek Kabupaten",
    "4": "Tulungagung Kabupaten",
    "5": "Blitar Kabupaten",
    "6": "Kediri Kabupaten",
    "7": "Malang Kabupaten",
    "8": "Lumajang Kabupaten",
    "9": "Jember Kabupaten",
    "10": "Banyuwangi Kabupaten",
    "11": "Bondowoso Kabupaten",
    "12": "Situbondo Kabupaten",
    "13": "Probolinggo Kabupaten",
    "14": "Pasuruan Kabupaten",
    "15": "Sidoarjo Kabupaten",
    "16": "Mojokerto Kabupaten",
    "17": "Jombang Kabupaten",
    "18": "Nganjuk Kabupaten",
    "19": "Madiun Kabupaten",
    "20": "Magetan Kabupaten",
    "21": "Ngawi Kabupaten",
    "22": "Bojonegoro Kabupaten",
    "23": "Tuban Kabupaten",
    "24": "Lamongan Kabupaten",
    "25": "Gresik Kabupaten",
    "26": "Bangkalan Kabupaten",
    "27": "Sampang Kabupaten",
    "28": "Pamekasan Kabupaten",
    "29": "Sumenep Kabupaten",
    "30": "Kediri Kota",
    "31": "Blitar Kota",
    "32": "Malang Kota",
    "33": "Probolinggo Kota",
    "34": "Pasuruan Kota",
    "35": "Mojokerto Kota",
    "36": "Madiun Kota",
    "37": "Surabaya Kota",
    "38": "Batu Kota"
}

# Disaster Type Map from 2022.csv to Standard Names
DISASTER_TYPE_MAP = {
    '2': 'Gempa Bumi',
    '5': 'Tanah Longsor',
    '6': 'Banjir',
    '7': 'Banjir Bandang',
    '9': 'Angin Puting Beliung',
    '10': 'Kebakaran',
    '17': 'Kejadian Lain',
    '18': 'Kejadian Lain',
    '19': 'Angin Kencang',
    '21': 'Banjir Rob',
    '22': 'Gerakan Tanah',
    '23': 'Pohon Tumbang',
    '25': 'Kebakaran Lahan'
}

def is_float(val):
    try:
        float(val)
        return True
    except ValueError:
        return False

def clean_coord(val):
    val_str = str(val).strip()
    if val_str in ('', '-', 'nan', 'NaN', 'None'):
        return np.nan
    try:
        return float(val_str)
    except ValueError:
        return np.nan

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
    
    # 1. Parse simple columns
    id_logs = parts[0]
    id_disastertype = parts[1]
    eventdate = parts[2]
    regency = parts[3]
    
    # 2. Find Longitude (which is always in range 110.0 to 117.0 for East Java)
    lon_idx = -1
    for i in range(4, len(parts)):
        if is_float(parts[i]):
            val = float(parts[i])
            if 110.0 <= val <= 117.0:
                lon_idx = i
                break
                
    if lon_idx == -1:
        # Fallback if no longitude matches the range
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

print("=== Processing 2022.csv ===")
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

parsed_2022 = []
for idx, r in enumerate(rows):
    try:
        p = parse_robust_row(r, idx)
        parsed_2022.append(p)
    except Exception as e:
        print(f"Error parsing row {idx}: {e}")

df_2022 = pd.DataFrame(parsed_2022)
print(f"2022 dataset cleaned. Shape: {df_2022.shape}")

print("\n=== Processing bencana 2024.xlsx ===")
df_2024_raw = pd.read_excel("bencana 2024.xlsx")

# Standardize columns to match 2022 structure
df_2024 = pd.DataFrame()
df_2024["id"] = df_2024_raw["ID"].apply(lambda x: f"2024_{x}")
df_2024["tahun"] = 2024
df_2024["waktu"] = df_2024_raw["Waktu"].astype(str)
df_2024["type_bencana"] = df_2024_raw["Tipe Kejadian"]
df_2024["kab_ko"] = df_2024_raw["Kab. / Kota"].astype(str).str.strip()
df_2024["area"] = df_2024_raw["Area"].astype(str).str.strip()
df_2024["latitude"] = df_2024_raw["Lintang"].apply(clean_coord)
df_2024["longitude"] = df_2024_raw["Bujur"].apply(clean_coord)
df_2024["photos"] = df_2024_raw["Foto"].astype(str).str.strip()
df_2024["source"] = df_2024_raw["Sumber"].astype(str).str.strip()
df_2024["level"] = df_2024_raw["Tingkatan"].astype(str).str.strip()

print(f"2024 dataset cleaned. Shape: {df_2024.shape}")

print("\n=== Merging datasets ===")
df_merged = pd.concat([df_2022, df_2024], ignore_index=True)

# Replace standard nan representations
df_merged["photos"] = df_merged["photos"].replace("nan", "")
df_merged["source"] = df_merged["source"].replace("nan", "")
df_merged["level"] = df_merged["level"].replace("nan", "")

print(f"Merged dataset shape: {df_merged.shape}")
print("\nUnique disaster types in merged data:")
print(df_merged["type_bencana"].value_counts())

# Save to CSV
df_merged.to_csv("bencana_jatim_2022_2024.csv", index=False, encoding="utf-8")
print("\nSaved merged data to bencana_jatim_2022_2024.csv")
