with open("2022.csv", "r", encoding="utf-8", errors="ignore") as f:
    header = f.readline().strip('\ufeff\n\r')
    header_fields = header[1:-1].split('""')
    
    for idx in range(5):
        line = f.readline().strip('\ufeff\n\r')
        parts = line[1:-1].split('""')
        
        # If we take first 16 fields
        first_16 = parts[:16]
        # Last 3 fields
        last_3 = parts[-3:]
        # Rejoin the middle
        photos = '""'.join(parts[16:-3])
        
        reconstructed = first_16 + [photos] + last_3
        print(f"Row {idx} reconstructed len: {len(reconstructed)}")
        print(f"  ID: {reconstructed[0]}")
        print(f"  Type: {reconstructed[1]}")
        print(f"  Regency: {reconstructed[3]}")
        print(f"  Latitude: {reconstructed[5]}")
        print(f"  Longitude: {reconstructed[6]}")
        print(f"  Level: {reconstructed[19]}")
        print(f"  Photos length: {len(reconstructed[16])}")
        print(f"  Photos snippet: {reconstructed[16][:100]}")
        print("-" * 20)
