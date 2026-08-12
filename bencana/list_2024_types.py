import pandas as pd

df = pd.read_excel("bencana 2024.xlsx")
print("Unique Tipe Kejadian in 2024.xlsx:")
print(df["Tipe Kejadian"].value_counts())
