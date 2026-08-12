import pandas as pd

df = pd.read_excel("data pesantren jatim.xlsx")
# Convert columns to string and clean
df['Kabupaten'] = df['Kabupaten'].astype(str).str.strip().str.upper()
df['Kecamatan'] = df['Kecamatan'].astype(str).str.strip().str.upper()

unique_districts = df[['Kabupaten', 'Kecamatan']].drop_duplicates()
print("Number of unique (Kabupaten, Kecamatan) in Pesantren data:", len(unique_districts))
print("Sample districts:")
print(unique_districts.head(10).to_string(index=False))
