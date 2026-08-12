import json
import os
import pandas as pd
import numpy as np

# Files
BENCANA_IN = "bencana_jatim_2022_2024.csv"
PESANTREN_IN = "data pesantren jatim.xlsx"
CACHE_IN = "kecamatan_coordinates.json"
CACHE_OUT = "kecamatan_coordinates.json"
BENCANA_OUT = "bencana_jatim_2022_2024.csv"
PESANTREN_OUT = "pesantren_jatim_risk_mapped.csv"

# 1. Clean and normalize coordinates for disasters
print("=== Polishing Disaster Coordinates ===")
df_dis = pd.read_csv(BENCANA_IN)

def clean_coord(val):
    val_str = str(val).strip()
    if val_str in ('', '-', 'nan', 'NaN', 'None'):
        return np.nan
    try:
        return float(val_str)
    except ValueError:
        return np.nan

def normalize_disaster_coords(row):
    lat = clean_coord(row['latitude'])
    lon = clean_coord(row['longitude'])
    
    if pd.isna(lat) or pd.isna(lon):
        return pd.Series([np.nan, np.nan])
        
    # Fix swapped coords (lat is 112, lon is -7)
    if 110.0 <= lat <= 117.0 and -10.0 <= lon <= -5.0:
        lat, lon = lon, lat
        
    # Fix positive latitudes in Southern Hemisphere (e.g., 7.8 -> -7.8)
    if 5.0 <= lat <= 11.0:
        lat = -lat
        
    # Check if they fall within East Java bounding box (extended slightly for offshore events)
    if -11.5 <= lat <= -5.0 and 110.0 <= lon <= 117.5:
        return pd.Series([round(lat, 6), round(lon, 6)])
    else:
        return pd.Series([np.nan, np.nan])

df_dis[['latitude', 'longitude']] = df_dis.apply(normalize_disaster_coords, axis=1)
df_dis.to_csv(BENCANA_OUT, index=False, encoding="utf-8")
print(f"Polished disasters. Saved to {BENCANA_OUT}. Rows with coordinates: {df_dis['latitude'].notna().sum()}")

# 2. Clean geocoding cache
print("\n=== Polishing Geocoding Cache ===")
if os.path.exists(CACHE_IN):
    with open(CACHE_IN, "r") as f:
        cache = json.load(f)
else:
    cache = {}

polished_cache = {}
removed_keys = 0

for key, val in cache.items():
    # Split key to check Kecamatan/Kabupaten
    parts = key.split(",")
    kec = parts[0].strip().upper()
    kab = parts[1].strip().upper() if len(parts) > 1 else ""
    
    # If key is nan, None, or empty, remove it
    if kec in ("NAN", "NONE", "") or kab in ("NAN", "NONE", ""):
        removed_keys += 1
        continue
        
    lat = clean_coord(val.get("latitude"))
    lon = clean_coord(val.get("longitude"))
    
    if pd.isna(lat) or pd.isna(lon):
        polished_cache[key] = {"latitude": None, "longitude": None}
        continue
        
    # Fix swapped coords
    if 110.0 <= lat <= 117.0 and -10.0 <= lon <= -5.0:
        lat, lon = lon, lat
        
    # Fix positive latitudes
    if 5.0 <= lat <= 10.0:
        lat = -lat
        
    # Bounding box check for land subdistricts in East Java
    # East Java Land Lat: -9.0 to -6.0; Lon: 110.0 to 116.0
    if -9.5 <= lat <= -5.5 and 110.0 <= lon <= 116.5:
        polished_cache[key] = {"latitude": round(lat, 6), "longitude": round(lon, 6)}
    else:
        polished_cache[key] = {"latitude": None, "longitude": None}
        removed_keys += 1

with open(CACHE_OUT, "w") as f:
    json.dump(polished_cache, f, indent=2)
print(f"Polished cache. Saved to {CACHE_OUT}. Total active keys: {len(polished_cache)}, Removed: {removed_keys}")

# 3. Clean Pesantren and Map to polished coordinates
print("\n=== Mapping Pesantren to Polished Coordinates ===")
df_pes = pd.read_excel(PESANTREN_IN)

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
    kec = str(row['Kecamatan']).strip().upper()
    kab = str(row['Kabupaten']).strip().upper()
    if kec in ("NAN", "NONE", "") or kab in ("NAN", "NONE", ""):
        return np.nan
    key = f"{kec}, {kab}"
    return polished_cache.get(key, {}).get("latitude", np.nan)

def get_lon(row):
    kec = str(row['Kecamatan']).strip().upper()
    kab = str(row['Kabupaten']).strip().upper()
    if kec in ("NAN", "NONE", "") or kab in ("NAN", "NONE", ""):
        return np.nan
    key = f"{kec}, {kab}"
    return polished_cache.get(key, {}).get("longitude", np.nan)

df_pes_clean['latitude'] = df_pes_clean.apply(get_lat, axis=1)
df_pes_clean['longitude'] = df_pes_clean.apply(get_lon, axis=1)

mapped_count = df_pes_clean['latitude'].notna().sum()
print(f"Mapped {mapped_count} out of {len(df_pes_clean)} pesantren ({mapped_count/len(df_pes_clean)*100:.2f}%)")

# 4. Calculate Proximity Risk with polished coordinates
print("\n=== Re-calculating Proximity Risks ===")
df_pes_coords = df_pes_clean[df_pes_clean['latitude'].notna() & df_pes_clean['longitude'].notna()].copy()
df_dis_coords = df_dis[df_dis['latitude'].notna() & df_dis['longitude'].notna()].copy()

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
    # Proximity risk logic
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

# Fill NaN values for pesantren without coordinates
df_final['level_kerawanan'] = df_final['level_kerawanan'].fillna('TIDAK ADA DATA KOORDINAT')
df_final['disasters_within_5km'] = df_final['disasters_within_5km'].fillna(0).astype(int)
df_final['disasters_within_10km'] = df_final['disasters_within_10km'].fillna(0).astype(int)
df_final['disasters_within_20km'] = df_final['disasters_within_20km'].fillna(0).astype(int)

print("\nFinal Risk Level Distribution:")
print(df_final['level_kerawanan'].value_counts())

df_final.to_csv(PESANTREN_OUT, index=False, encoding="utf-8")
print(f"Saved final polished output to {PESANTREN_OUT}")
