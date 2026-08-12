import pandas as pd
import os

files = {
    "2022.csv": "2022.csv",
    "bencana 2024.xlsx": "bencana 2024.xlsx",
    "data pesantren jatim.xlsx": "data pesantren jatim.xlsx"
}

for name, path in files.items():
    print(f"=== Inspecting {name} ===")
    if not os.path.exists(path):
        print(f"File not found: {path}")
        continue
    
    if name.endswith(".csv"):
        # Try different encodings/delimiters if needed
        try:
            df = pd.read_csv(path, nrows=5)
            print("Shape:", pd.read_csv(path).shape)
        except Exception as e:
            print(f"Error reading csv: {e}")
            continue
    else:
        try:
            # Let's inspect sheet names first
            xl = pd.ExcelFile(path)
            print("Sheets:", xl.sheet_names)
            df = xl.parse(xl.sheet_names[0], nrows=5)
            print("Shape of first sheet:", xl.parse(xl.sheet_names[0]).shape)
        except Exception as e:
            print(f"Error reading excel: {e}")
            continue
            
    print("Columns:", list(df.columns))
    print("Sample Data:")
    print(df.to_string())
    print("\n" + "="*40 + "\n")
