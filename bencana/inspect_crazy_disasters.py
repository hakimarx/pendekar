import pandas as pd

df = pd.read_csv("bencana_jatim_2022_2024.csv")

# Filter rows in 2022.csv with crazy coordinates
crazy_coords = df[(df['latitude'] < -15.0) | (df['latitude'] > 0.0) | (df['longitude'] < 105.0) | (df['longitude'] > 120.0)]
print(f"Total rows with crazy coordinates: {len(crazy_coords)}")
print(crazy_coords[['id', 'tahun', 'kab_ko', 'area', 'latitude', 'longitude']].head(10).to_string())
