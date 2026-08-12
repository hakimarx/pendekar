import pandas as pd
import os

print("=== Checking 2022.csv separator ===")
try:
    df_2022 = pd.read_csv("2022.csv", sep='"')
    print("CSV read successfully with sep='\"'")
    print("Shape:", df_2022.shape)
    print("Columns:", list(df_2022.columns))
    print("Unique id_disastertype:", df_2022['id_disastertype'].unique() if 'id_disastertype' in df_2022.columns else "Not found")
    print("Sample rows:")
    print(df_2022.head(2).to_string())
except Exception as e:
    print("Failed to read with sep='\"':", e)

print("\n=== Checking all sheets of bencana 2024.xlsx ===")
xl_2024 = pd.ExcelFile("bencana 2024.xlsx")
print("Sheets:", xl_2024.sheet_names)
for sheet in xl_2024.sheet_names:
    df_sheet = xl_2024.parse(sheet, nrows=5)
    print(f"Sheet '{sheet}' columns:", list(df_sheet.columns))

print("\n=== Checking all sheets of data pesantren jatim.xlsx ===")
xl_pesantren = pd.ExcelFile("data pesantren jatim.xlsx")
print("Sheets:", xl_pesantren.sheet_names)
for sheet in xl_pesantren.sheet_names:
    df_sheet = xl_pesantren.parse(sheet, nrows=5)
    print(f"Sheet '{sheet}' columns:", list(df_sheet.columns))
