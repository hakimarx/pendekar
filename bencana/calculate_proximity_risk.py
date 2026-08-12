import pandas as pd
import numpy as np

# Files
PESANTREN_FILE = "pesantren_jatim_clean.csv"
BENCANA_FILE = "bencana_jatim_2022_2024.csv"
OUTPUT_FILE = "pesantren_jatim_risk_mapped.csv"

print("=== Loading Cleaned Datasets ===")
df_pes = pd.read_csv(PESANTREN_FILE)
df_dis = pd.read_csv(BENCANA_FILE)

print(f"Loaded {len(df_pes)} pesantren.")
print(f"Loaded {len(df_dis)} disasters.")

# Drop rows without coordinates for proximity matching
df_pes_coords = df_pes[df_pes['latitude'].notna() & df_pes['longitude'].notna()].copy()
df_dis_coords = df_dis[df_dis['latitude'].notna() & df_dis['longitude'].notna()].copy()

print(f"Pesantren with coordinates: {len(df_pes_coords)}")
print(f"Disasters with coordinates: {len(df_dis_coords)}")

# Pre-convert disaster coordinates to radians for vectorization
lat_dis_rad = np.radians(df_dis_coords['latitude'].values)
lon_dis_rad = np.radians(df_dis_coords['longitude'].values)
dis_types = df_dis_coords['type_bencana'].values
dis_years = df_dis_coords['tahun'].values

# Initialize new columns
df_pes_coords['disasters_within_5km'] = 0
df_pes_coords['disasters_within_10km'] = 0
df_pes_coords['disasters_within_20km'] = 0
df_pes_coords['closest_disaster_type'] = "None"
df_pes_coords['closest_disaster_distance_km'] = np.nan
df_pes_coords['closest_disaster_year'] = np.nan

print("\n=== Calculating Proximity Risks (Haversine Vectorized) ===")
# Iterate over each pesantren
count_5km = []
count_10km = []
count_20km = []
closest_type = []
closest_dist = []
closest_yr = []

total_pes = len(df_pes_coords)
for idx, row in enumerate(df_pes_coords.itertuples()):
    if idx % 1000 == 0:
        print(f"  Processed {idx}/{total_pes} pesantren...")
        
    lat_pes = np.radians(row.latitude)
    lon_pes = np.radians(row.longitude)
    
    # Haversine formula
    dlat = lat_dis_rad - lat_pes
    dlon = lon_dis_rad - lon_pes
    
    a = np.sin(dlat / 2.0)**2 + np.cos(lat_pes) * np.cos(lat_dis_rad) * np.sin(dlon / 2.0)**2
    c = 2.0 * np.arcsin(np.sqrt(a))
    distances = 6371.0 * c # Earth radius 6371 km
    
    # Proximity counts
    count_5 = np.sum(distances <= 5.0)
    count_10 = np.sum(distances <= 10.0)
    count_20 = np.sum(distances <= 20.0)
    
    # Closest disaster
    min_idx = np.argmin(distances)
    min_dist = distances[min_idx]
    
    count_5km.append(count_5)
    count_10km.append(count_10)
    count_20km.append(count_20)
    closest_type.append(dis_types[min_idx])
    closest_dist.append(round(min_dist, 2))
    closest_yr.append(int(dis_years[min_idx]))

# Assign arrays to DataFrame
df_pes_coords['disasters_within_5km'] = count_5km
df_pes_coords['disasters_within_10km'] = count_10km
df_pes_coords['disasters_within_20km'] = count_20km
df_pes_coords['closest_disaster_type'] = closest_type
df_pes_coords['closest_disaster_distance_km'] = closest_dist
df_pes_coords['closest_disaster_year'] = closest_yr

# Define risk level
def determine_risk(row):
    # If there are disasters within 5km, it is high risk
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

# Combine back with the original pesantren dataset (including those without coordinates)
print("\n=== Recombining Datasets ===")
df_final = df_pes.merge(
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

print("\nRisk Level Distribution:")
print(df_final['level_kerawanan'].value_counts())

# Save final risk mapped dataset
df_final.to_csv(OUTPUT_FILE, index=False, encoding="utf-8")
print(f"\nSaved final output to {OUTPUT_FILE}")
