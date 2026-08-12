import os
import json
import time
import urllib.request
import urllib.parse
import pandas as pd
import numpy as np

# Files
PESANTREN_FILE = "data pesantren jatim.xlsx"
CACHE_FILE = "kecamatan_coordinates.json"
DERIVED_FILE = "derived_coordinates.json"
PROGRESS_FILE = "geocoding_progress.txt"

print("=== Loading Pesantren Data ===")
df = pd.read_excel(PESANTREN_FILE)

# Clean columns
df['Kabupaten'] = df['Kabupaten'].astype(str).str.strip().str.upper()
df['Kecamatan'] = df['Kecamatan'].astype(str).str.strip().str.upper()
df['NSPP'] = df['NSPP'].astype(str).str.strip()
df['Nama Lembaga'] = df['Nama Lembaga'].astype(str).str.strip()
df['Jenjang'] = df['Jenjang'].astype(str).str.strip()
df['Alamat'] = df['Alamat'].astype(str).str.strip()
df['Afiliasi Organisasi'] = df['Afiliasi Organisasi'].astype(str).str.strip()

# Standardize names (e.g. replace double spaces)
df['Kabupaten'] = df['Kabupaten'].replace(r'\s+', ' ', regex=True)
df['Kecamatan'] = df['Kecamatan'].replace(r'\s+', ' ', regex=True)

# Find unique Kabupaten and Kecamatan pairs
unique_districts = df[['Kabupaten', 'Kecamatan']].drop_duplicates().values.tolist()
print(f"Total unique districts in pesantren data: {len(unique_districts)}")

# Load Cache (or initialize with derived coordinates)
cache = {}
if os.path.exists(CACHE_FILE):
    try:
        with open(CACHE_FILE, "r") as f:
            cache = json.load(f)
        print(f"Loaded {len(cache)} existing coordinates from cache {CACHE_FILE}")
    except Exception as e:
        print(f"Failed to load cache: {e}")

if not cache and os.path.exists(DERIVED_FILE):
    try:
        with open(DERIVED_FILE, "r") as f:
            cache = json.load(f)
        print(f"Initialized cache with {len(cache)} derived coordinates from {DERIVED_FILE}")
    except Exception as e:
        print(f"Failed to load derived coordinates: {e}")

# Filter out already cached districts
to_geocode = []
for kab, kec in unique_districts:
    key = f"{kec}, {kab}"
    if key not in cache or cache[key].get("latitude") is None or pd.isna(cache[key].get("latitude")):
        to_geocode.append((kab, kec))

print(f"Remaining districts to geocode: {len(to_geocode)}")

def geocode_district(kab, kec):
    # Formulate query
    # E.g. "TANJUNG BUMI, BANGKALAN, JAWA TIMUR, INDONESIA"
    clean_kab = kab
    if clean_kab.endswith(" KABUPATEN"):
        clean_kab = clean_kab.replace(" KABUPATEN", "")
    elif clean_kab.endswith(" KOTA"):
        clean_kab = clean_kab.replace(" KOTA", "")
        
    query = f"{kec}, {clean_kab}, Jawa Timur, Indonesia"
    url = "https://nominatim.openstreetmap.org/search?q=" + urllib.parse.quote(query) + "&format=json&limit=1"
    
    req = urllib.request.Request(
        url,
        headers={'User-Agent': 'AntigravityPesantrenBencanaGeocodeClient/2.0'}
    )
    
    try:
        with urllib.request.urlopen(req, timeout=10) as response:
            data = json.loads(response.read().decode())
            if data:
                lat = float(data[0]['lat'])
                lon = float(data[0]['lon'])
                return lat, lon
            else:
                # Try fallback query without "Indonesia" or "Jawa Timur" if empty
                fallback_query = f"{kec}, {clean_kab}"
                url_fb = "https://nominatim.openstreetmap.org/search?q=" + urllib.parse.quote(fallback_query) + "&format=json&limit=1"
                req_fb = urllib.request.Request(url_fb, headers={'User-Agent': 'AntigravityPesantrenBencanaGeocodeClient/2.0'})
                with urllib.request.urlopen(req_fb, timeout=10) as fb_response:
                    data_fb = json.loads(fb_response.read().decode())
                    if data_fb:
                        return float(data_fb[0]['lat']), float(data_fb[0]['lon'])
    except Exception as e:
        print(f"Error querying {query}: {e}")
        time.sleep(2) # Extra sleep if error
        
    return None

# Start geocoding if needed
if to_geocode:
    success_count = 0
    total_jobs = len(to_geocode)

    with open(PROGRESS_FILE, "w") as pf:
        pf.write(f"Starting geocoding at {time.strftime('%Y-%m-%d %H:%M:%S')}\n")
        pf.write(f"Total to geocode: {total_jobs}\n\n")

    for idx, (kab, kec) in enumerate(to_geocode):
        key = f"{kec}, {kab}"
        print(f"[{idx+1}/{total_jobs}] Geocoding: {kec}, {kab}")
        
        coords = geocode_district(kab, kec)
        if coords:
            lat, lon = coords
            cache[key] = {"latitude": lat, "longitude": lon}
            success_count += 1
            print(f"  Result: {lat}, {lon}")
        else:
            cache[key] = {"latitude": None, "longitude": None}
            print(f"  Result: NOT FOUND")
            
        # Write to Cache after each query
        with open(CACHE_FILE, "w") as f:
            json.dump(cache, f, indent=2)
            
        # Write progress
        msg = f"Progress: {idx+1}/{total_jobs} complete. Success: {success_count}. Last: {kec}, {kab} -> {coords}\n"
        with open(PROGRESS_FILE, "a") as pf:
            pf.write(msg)
            
        # Respect rate limits
        time.sleep(1.2)

# Save final coordinates cache
with open(CACHE_FILE, "w") as f:
    json.dump(cache, f, indent=2)

print("\n=== Geocoding Complete ===")
print(f"Total cached: {len(cache)}")

# Map pesantren to coordinates
print("\n=== Mapping Pesantren to Coordinates ===")
def get_lat(row):
    key = f"{row['Kecamatan']}, {row['Kabupaten']}"
    return cache.get(key, {}).get("latitude", np.nan)

def get_lon(row):
    key = f"{row['Kecamatan']}, {row['Kabupaten']}"
    return cache.get(key, {}).get("longitude", np.nan)

df['latitude'] = df.apply(get_lat, axis=1)
df['longitude'] = df.apply(get_lon, axis=1)

# Report mapping statistics
mapped_count = df['latitude'].notna().sum()
total_pesantren = len(df)
print(f"Mapped {mapped_count} out of {total_pesantren} pesantren ({mapped_count/total_pesantren*100:.2f}%)")

# Save cleaned pesantren dataset
df.to_csv("pesantren_jatim_clean.csv", index=False, encoding="utf-8")
print("Saved clean pesantren data to pesantren_jatim_clean.csv")
