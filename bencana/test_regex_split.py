import re

with open("2022.csv", "r", encoding="utf-8", errors="ignore") as f:
    content = f.read()

# Let's clean BOM
if content.startswith('\ufeff'):
    content = content[1:]

# A row start is: at the beginning of the file, or after a newline followed by "[digits]""[digits]""
# Let's find all occurrences of this pattern
pattern = r'\n"(\d+)""(\d+)""'
matches = list(re.finditer(pattern, content))
print("Number of row-start matches found:", len(matches))

# Let's see if the number of matches matches the expected number of rows (1717)
# Note that the first row doesn't start with \n, and the header also doesn't.
# Let's check the first few matches
for i, m in enumerate(matches[:5]):
    print(f"Match {i}: pos={m.start()}, groups={m.groups()}")
