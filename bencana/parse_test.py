import re

def parse_line(line):
    # Remove BOM and newline
    line = line.strip('\ufeff\n\r')
    
    # We want to find fields. The fields are wrapped in double quotes and touch each other, e.g. "field1""field2""field3"
    # But inside the fields, double quotes are escaped as ""
    # Let's see if we can parse this.
    # Since the columns are fixed (20 columns), let's see if we can write a regex or a parser.
    # A standard CSV parser might fail if there's no comma, but let's see.
    # Let's try to parse using a state machine:
    fields = []
    current = []
    in_quotes = False
    i = 0
    n = len(line)
    
    while i < n:
        char = line[i]
        if char == '"':
            if not in_quotes:
                # Starting a new field
                in_quotes = True
                i += 1
            else:
                # We are in quotes and hit a quote.
                # Check if it's an escaped quote: is the next character also a quote?
                # But wait, since fields are separated by nothing, the transition from field1 to field2 is:
                # "field1""field2" -> quote at the end of field1, quote at the start of field2.
                # So we see ""
                # How do we know if it's an escaped quote inside a field, or the end of one field and start of another?
                # Usually, an escaped quote in HTML would be like class=""r-images"" (which is inside the field).
                # The end of a field followed by the start of another field is also "".
                # Let's inspect the context.
                # If we split by "" when it matches a field boundary. Since we have exactly 20 columns,
                # let's look at the structure.
                # Let's see if there's any other way.
                pass
        i += 1

# Let's run a simple regex search to see where the splits are.
with open("2022.csv", "r", encoding="utf-8", errors="ignore") as f:
    header = f.readline().strip('\ufeff\n\r')
    print("Header:", header)
    # Split header by ""
    # Since the header doesn't contain HTML, it should be simple.
    # Header is: "id_logs""id_disastertype""eventdate""regency""area""latitude""longitude""weather""chronology""dead""missing""serious_wound""minor_injuries""damage""losses""response""photos""source""status""level"
    # If we strip the outer quotes and split by "", we get:
    header_fields = header[1:-1].split('""')
    print("Header fields:", header_fields)
    print("Num header fields:", len(header_fields))
    
    # Let's look at some rows
    for idx in range(5):
        line = f.readline().strip('\ufeff\n\r')
        # Let's see if we split by '""' how many fields we get
        parts = line[1:-1].split('""')
        print(f"Row {idx} split by '\"\"' length:", len(parts))
        if len(parts) != 20:
            print("  Warning: length is not 20!")
            # Let's print the parts
            # print("  Parts:", parts)
