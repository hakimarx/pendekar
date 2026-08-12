import re
import pandas as pd

def is_float(val):
    try:
        float(val)
        return True
    except ValueError:
        return False

def is_integer_or_dash(val):
    val_strip = val.strip()
    if val_strip in ('', '-', 'NaN'):
        return True
    return val_strip.isdigit()

def parse_robust_row(r, idx):
    if r.startswith('"') and r.endswith('"'):
        r_strip = r[1:-1]
    elif r.startswith('"'):
        r_strip = r[1:]
    elif r.endswith('"'):
        r_strip = r[:-1]
    else:
        r_strip = r
        
    parts = r_strip.split('""')
    
    # 1. Parse left-side simple columns: id_logs, id_disastertype, eventdate, regency
    id_logs = parts[0]
    id_disastertype = parts[1]
    eventdate = parts[2]
    regency = parts[3]
    
    # 2. Find Longitude (which is always in range 110.0 to 117.0 for East Java)
    lon_idx = -1
    for i in range(4, len(parts)):
        if is_float(parts[i]):
            val = float(parts[i])
            if 110.0 <= val <= 117.0:
                lon_idx = i
                break
                
    if lon_idx == -1:
        # Fallback if no longitude matches the range
        for i in range(4, len(parts)):
            if is_float(parts[i]):
                lon_idx = i
                break
                
    if lon_idx == -1 or lon_idx == 4:
        raise ValueError(f"Could not find coordinates in row {idx}: {parts[:10]}")
        
    lat_idx = lon_idx - 1
    
    area = '""'.join(parts[4:lat_idx])
    latitude = parts[lat_idx]
    longitude = parts[lon_idx]
    weather = parts[lon_idx + 1]
    
    # 3. Parse right-side simple columns: source, status, level
    source = parts[-3]
    status = parts[-2]
    level = parts[-1]
    
    # 4. Now we have the middle section between weather (lon_idx + 1) and source (index -3)
    middle_parts = parts[lon_idx + 2 : -3]
    
    # Let's locate the 4 casualty fields (dead, missing, serious_wound, minor_injuries)
    cas_start_idx = -1
    for i in range(1, len(middle_parts) - 4):
        if (is_integer_or_dash(middle_parts[i]) and 
            is_integer_or_dash(middle_parts[i+1]) and 
            is_integer_or_dash(middle_parts[i+2]) and 
            is_integer_or_dash(middle_parts[i+3])):
            cas_start_idx = i
            break
            
    if cas_start_idx == -1:
        cas_start_idx = 1
        
    chronology = '""'.join(middle_parts[:cas_start_idx])
    dead = middle_parts[cas_start_idx]
    missing = middle_parts[cas_start_idx + 1]
    serious_wound = middle_parts[cas_start_idx + 2]
    minor_injuries = middle_parts[cas_start_idx + 3]
    
    # Remaining elements for damage, losses, response, photos
    remaining = middle_parts[cas_start_idx + 4 :]
    
    photos_idx = -1
    for i, item in enumerate(remaining):
        item_strip = item.strip()
        if item_strip.startswith('<div') or 'r-images' in item_strip or 'mfhandler' in item_strip or 'file.php' in item_strip:
            photos_idx = i
            break
            
    if photos_idx == -1:
        photos_idx = len(remaining) - 1
        
    damage = remaining[0] if len(remaining) > 0 else '-'
    losses = remaining[1] if len(remaining) > 1 else '-'
    
    response = '""'.join(remaining[2:photos_idx])
    photos = '""'.join(remaining[photos_idx:])
    
    return {
        "id_logs": id_logs,
        "id_disastertype": id_disastertype,
        "eventdate": eventdate,
        "regency": regency,
        "area": area,
        "latitude": latitude,
        "longitude": longitude,
        "weather": weather,
        "chronology": chronology,
        "dead": dead,
        "missing": missing,
        "serious_wound": serious_wound,
        "minor_injuries": minor_injuries,
        "damage": damage,
        "losses": losses,
        "response": response,
        "photos": photos,
        "source": source,
        "status": status,
        "level": level
    }

# Let's test this parser on all rows
with open("2022.csv", "r", encoding="utf-8", errors="ignore") as f:
    content = f.read()

if content.startswith('\ufeff'):
    content = content[1:]

pattern = r'\n"(\d+)""(\d+)""'
matches = list(re.finditer(pattern, content))

rows = []
header_end = content.find('\n')
prev_idx = header_end + 1 if header_end != -1 else 0

for m in matches:
    start = m.start()
    row_text = content[prev_idx:start].strip()
    if row_text:
        rows.append(row_text)
    prev_idx = start + 1

last_row = content[prev_idx:].strip()
if last_row:
    rows.append(last_row)

print("Total extracted rows:", len(rows))

parsed_data = []
success_count = 0
fail_count = 0

for idx, r in enumerate(rows):
    try:
        p = parse_robust_row(r, idx)
        parsed_data.append(p)
        success_count += 1
    except Exception as e:
        print(f"Failed parsing row {idx}: {e}")
        fail_count += 1

print(f"Parsing complete. Success: {success_count}, Fails: {fail_count}")

if parsed_data:
    df_parsed = pd.DataFrame(parsed_data)
    print("\nParsed DataFrame Info:")
    print(df_parsed.info())
    print("\nFirst 3 parsed rows:")
    print(df_parsed.head(3)[["id_logs", "id_disastertype", "latitude", "longitude", "level"]].to_string())
