from collections import defaultdict

disaster_samples = defaultdict(list)

with open("2022.csv", "r", encoding="utf-8", errors="ignore") as f:
    header = f.readline().strip('\ufeff\n\r')
    header_fields = header[1:-1].split('""')
    
    for idx, line in enumerate(f):
        line = line.strip('\ufeff\n\r')
        if not line:
            continue
        parts = line[1:-1].split('""')
        if len(parts) >= 20:
            first_16 = parts[:16]
            last_3 = parts[-3:]
            photos = '""'.join(parts[16:-3])
            row = first_16 + [photos] + last_3
        else:
            # Skip or handle malformed
            continue
        
        dtype = row[1]
        chronology = row[8]
        disaster_samples[dtype].append(chronology)

for dtype, samples in sorted(disaster_samples.items(), key=lambda x: int(x[0]) if x[0].isdigit() else 999):
    print(f"Disaster Type ID: {dtype} (Total cases: {len(samples)})")
    print("Samples:")
    for s in samples[:3]:
        print(f"  - {s[:150]}...")
    print("-" * 40)
