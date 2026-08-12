import re

with open("2022.csv", "r", encoding="utf-8", errors="ignore") as f:
    content = f.read()

if content.startswith('\ufeff'):
    content = content[1:]

# Split the content into rows.
# Since a row start is \n followed by "digits""digits"", we can find the indices and split
pattern = r'\n"(\d+)""(\d+)""'
matches = list(re.finditer(pattern, content))

rows = []
prev_idx = 0
# The first row starts at index 0 (after header). Let's find where the header ends.
header_end = content.find('\n')
if header_end == -1:
    header_end = 0
else:
    header_end += 1

prev_idx = header_end

for m in matches:
    start = m.start()
    # The row is from prev_idx to start
    row_text = content[prev_idx:start].strip()
    if row_text:
        rows.append(row_text)
    prev_idx = start + 1 # skip the newline

# Don't forget the last row
last_row = content[prev_idx:].strip()
if last_row:
    rows.append(last_row)

print("Total extracted rows:", len(rows))

errors = 0
for idx, r in enumerate(rows):
    # Strip leading/trailing quote of the entire row
    if r.startswith('"') and r.endswith('"'):
        r_strip = r[1:-1]
    elif r.startswith('"'):
        r_strip = r[1:]
    elif r.endswith('"'):
        r_strip = r[:-1]
    else:
        r_strip = r
        
    parts = r_strip.split('""')
    
    if len(parts) < 20:
        print(f"Row {idx} has too few parts: {len(parts)}")
        errors += 1
        continue
        
    # Apply proposed parsing
    id_logs = parts[0]
    id_dis = parts[1]
    ev_date = parts[2]
    reg = parts[3]
    area = parts[4]
    lat = parts[5]
    lon = parts[6]
    weather = parts[7]
    chron = parts[8]
    dead = parts[9]
    missing = parts[10]
    serious = parts[11]
    minor = parts[12]
    damage = parts[13]
    losses = parts[14]
    resp = parts[15]
    source = parts[-3]
    status = parts[-2]
    level = parts[-1]
    
    # Rejoin photos
    photos = '""'.join(parts[16:-3])
    
    # Validate if dead, missing, serious, minor are digits/numbers or '-'
    def is_valid_num(val):
        return val.strip().replace('-', '').isdigit() or val.strip() == '' or val.strip() == '-'
        
    if not (is_valid_num(dead) and is_valid_num(missing) and is_valid_num(serious) and is_valid_num(minor)):
        print(f"Row {idx} parsing anomaly!")
        print(f"  parts length: {len(parts)}")
        print(f"  dead: {repr(dead)}")
        print(f"  missing: {repr(missing)}")
        print(f"  serious: {repr(serious)}")
        print(f"  minor: {repr(minor)}")
        print(f"  damage: {repr(damage)}")
        print(f"  losses: {repr(losses)}")
        print(f"  first 8: {parts[:8]}")
        print(f"  last 3: {parts[-3:]}")
        errors += 1
        if errors > 10:
            print("Too many errors, stopping.")
            break

print("Total rows with parsing anomalies:", errors)
