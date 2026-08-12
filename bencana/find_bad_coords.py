import pandas as pd

df = pd.read_csv("bencana_jatim_2022_2024.csv")

# Find coordinates outside reasonable bounds for East Java
bad_lat = df[(df['latitude'] < -10.0) | (df['latitude'] > -5.0)]
bad_lon = df[(df['longitude'] < 110.0) | (df['longitude'] > 117.0)]

print(f"Total rows with bad latitude: {len(bad_lat)}")
print("Sample bad latitude rows:")
print(bad_lat[['id', 'tahun', 'kab_ko', 'area', 'latitude', 'longitude']].head(10).to_string())

print(f"\nTotal rows with bad longitude: {len(bad_lon)}")
print("Sample bad longitude rows:")
print(bad_lon[['id', 'tahun', 'kab_ko', 'area', 'latitude', 'longitude']].head(10).to_string())
