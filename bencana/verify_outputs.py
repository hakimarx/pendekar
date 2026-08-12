import pandas as pd
import numpy as np

print("=== VERIFYING OUTPUT DATASETS ===")

# Load datasets
try:
    df_dis = pd.read_csv("bencana_jatim_2022_2024.csv")
    df_pes = pd.read_csv("pesantren_jatim_risk_mapped.csv")
except Exception as e:
    print(f"FAILED TO LOAD DATASETS: {e}")
    exit(1)

# Check shapes
print(f"Disasters dataset rows: {len(df_dis)}")
print(f"Pesantren dataset rows: {len(df_pes)}")

# Verify disaster columns
dis_cols = list(df_dis.columns)
required_dis_cols = ["type_bencana", "kab_ko", "area", "latitude", "longitude", "photos", "source", "level"]
print("\nDisaster Columns check:")
for col in required_dis_cols:
    status = "OK" if col in dis_cols else "MISSING"
    print(f"  - {col}: {status}")

# Verify pesantren columns
pes_cols = list(df_pes.columns)
required_pes_cols = ["NSPP", "Nama Lembaga", "Jenjang", "Kabupaten", "Kecamatan", "Alamat", "Afiliasi Organisasi", "latitude", "longitude", "level_kerawanan"]
print("\nPesantren Columns check:")
for col in required_pes_cols:
    status = "OK" if col in pes_cols else "MISSING"
    print(f"  - {col}: {status}")

# Verify coordinate ranges
print("\nCoordinate Ranges Check:")
# East Java bounds are approx: Lat [-9.0, -5.5], Lon [110.0, 116.5]
dis_valid_coords = df_dis[df_dis['latitude'].notna() & df_dis['longitude'].notna()]
pes_valid_coords = df_pes[df_pes['latitude'].notna() & df_pes['longitude'].notna()]

print(f"  Disaster Lat range: {dis_valid_coords['latitude'].min()} to {dis_valid_coords['latitude'].max()}")
print(f"  Disaster Lon range: {dis_valid_coords['longitude'].min()} to {dis_valid_coords['longitude'].max()}")
print(f"  Pesantren Lat range: {pes_valid_coords['latitude'].min()} to {pes_valid_coords['latitude'].max()}")
print(f"  Pesantren Lon range: {pes_valid_coords['longitude'].min()} to {pes_valid_coords['longitude'].max()}")

# Spot check coordinate anomalies (coordinates outside East Java)
def check_bounds(df, name):
    anomalies = df[(df['latitude'] < -10.0) | (df['latitude'] > -5.0) | (df['longitude'] < 110.0) | (df['longitude'] > 117.0)]
    print(f"  {name} coordinate anomalies count: {len(anomalies)}")
    if len(anomalies) > 0:
        print(f"    Sample anomalies in {name}:")
        print(anomalies[['Kabupaten' if 'Kabupaten' in df.columns else 'kab_ko', 
                        'Kecamatan' if 'Kecamatan' in df.columns else 'area', 
                        'latitude', 'longitude']].head(3).to_string())

check_bounds(dis_valid_coords, "Disasters")
check_bounds(pes_valid_coords, "Pesantren")

# Check risk level matches
print("\nRisk Level Match:")
print(df_pes['level_kerawanan'].value_counts())

print("\n=== VERIFICATION COMPLETE ===")
