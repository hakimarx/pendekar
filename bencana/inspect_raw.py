with open("2022.csv", "r", encoding="utf-8", errors="ignore") as f:
    for i in range(5):
        print(f"Line {i}: {repr(f.readline())}")
