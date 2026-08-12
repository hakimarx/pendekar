import os
import re
import subprocess
import fitz
import pandas as pd

# Paths
pdf_path = r"d:\Pendekar\OPOP\opop 2023.pdf"
ocr_exe = r"C:\Users\pdpon\.gemini\antigravity-ide\brain\d3b8cb59-e53e-43b0-b549-a9c4cdf97443\scratch\WinOcrCoords.exe"
output_dir = r"d:\Pendekar\OPOP\extracted_images_2023"
excel_path = r"d:\Pendekar\OPOP\extracted_data_2023.xlsx"
csv_path = r"d:\Pendekar\OPOP\extracted_data_2023.csv"

# Create output directories
os.makedirs(output_dir, exist_ok=True)

doc = fitz.open(pdf_path)
dpi_scale = 150.0 / 72.0

records = []

def clean_product_name(name):
    name = name.strip()
    if re.match(r'^[\d\W_]+$', name) or len(name) <= 3:
        return ""
    name = re.sub(r'^ppooto\s*', '', name, flags=re.IGNORECASE).strip()
    return name

def parse_product_half(lines, is_top):
    # Sort lines by Y-coordinate
    lines = sorted(lines, key=lambda l: l['y'])
    
    ponpes_name = ""
    nspp = ""
    kabupaten = ""
    
    for line in lines:
        text = line['text'].strip()
        if not text:
            continue
            
        if not ponpes_name and any(k in text.upper() for k in ["PONPES", "PONDOK", "PP ", "YPP", "PP.", "PON-PES", "PLP", "P3TQ"]):
            ponpes_name = text
            continue
            
        if not nspp and (re.search(r'\b\d{12}\b', text) or (len(text) == 12 and text.isdigit())):
            nspp = text
            continue
            
        if not kabupaten and any(k in text.upper() for k in ["KAB.", "KOTA"]):
            kabupaten = text
            continue
            
    if not ponpes_name and lines:
        ponpes_name = lines[0]['text'].strip()
        
    # Separate lines into Contact block and Product block
    contact_lines = []
    product_lines = []
    
    for line in lines:
        text = line['text'].strip()
        if text == ponpes_name or text == nspp or text == kabupaten:
            continue
            
        is_contact = False
        if is_top:
            # Top Product Contact is on the far right (X >= 900)
            if line['x'] >= 900:
                is_contact = True
        else:
            # Bottom Product Contact is on the far left (X < 200)
            if line['x'] < 200:
                is_contact = True
                
        if is_contact:
            contact_lines.append(line)
        else:
            product_lines.append(line)
            
    # Parse contact lines (stacked vertically: first Address, then Phone)
    addr_parts = []
    phone_parts = []
    in_phone = False
    
    for line in sorted(contact_lines, key=lambda l: l['y']):
        text = line['text'].strip()
        if any(k in text.upper() for k in ["NO. TELP", "NO TELP", "TELP.", "TELP"]):
            in_phone = True
            cleaned = re.sub(r'(no\.?\s*telp\.?|telp\.?)\s*:?', '', text, flags=re.IGNORECASE).strip()
            if cleaned:
                phone_parts.append(cleaned)
            continue
            
        if in_phone:
            phone_parts.append(text)
        else:
            cleaned = re.sub(r'alamat\s*:?', '', text, flags=re.IGNORECASE).strip()
            if cleaned:
                addr_parts.append(cleaned)
                
    # Parse product lines (sorted by Y: first Product Name, then Description)
    name_y_threshold = 220 if is_top else 650
    prod_name_parts = []
    desc_parts = []
    
    for line in sorted(product_lines, key=lambda l: l['y']):
        text = line['text'].strip()
        if text.upper() in ["MULAI", "RP.", "MUTAI", "RP", "MULAI RP.", "MULAI RP"]:
            continue
        if line['y'] < name_y_threshold:
            prod_name_parts.append(text)
        else:
            desc_parts.append(text)
            
    product_name = " ".join(prod_name_parts).strip()
    product_name = clean_product_name(product_name)
    description = " ".join(desc_parts).strip()
    address = ", ".join(addr_parts).strip()
    phone = " / ".join(phone_parts).strip()
    
    # Final cleanup of common OCR artifacts or page numbers in address/phone
    address = re.sub(r',\s*\d{2}\.\s*$', '', address).strip()
    
    return {
        "ponpes_name": ponpes_name,
        "nspp": nspp,
        "kabupaten": kabupaten,
        "product_name": product_name,
        "description": description,
        "address": address,
        "phone": phone
    }

def process_ocr_output(ocr_stdout, page_num):
    lines = []
    for l in ocr_stdout.split("\n"):
        if l.startswith("LINE: "):
            parts = l[6:].split(" | ", 1)
            if len(parts) == 2:
                coords_str, text = parts
                coords = [float(x) for x in coords_str.split(",")]
                lines.append({
                    "x": coords[0],
                    "y": coords[1],
                    "w": coords[2],
                    "h": coords[3],
                    "text": text
                })
                
    top_text_lines = []
    bottom_text_lines = []
    
    for line in lines:
        x, y = line['x'], line['y']
        w, h = line['w'], line['h']
        cx = x + w / 2
        cy = y + h / 2
        
        if cy < 437:
            if cx >= 430:
                top_text_lines.append(line)
        else:
            if cx < 790:
                bottom_text_lines.append(line)
                
    products = []
    
    # Check Top Product
    has_top = any(any(k in l['text'].upper() for k in ["PONPES", "PONDOK", "PP ", "YPP", "PP.", "P3TQ"]) for l in top_text_lines)
    if has_top or (len(top_text_lines) > 3 and any("KAB." in l['text'].upper() or "KOTA" in l['text'].upper() for l in top_text_lines)):
        res = parse_product_half(top_text_lines, is_top=True)
        res["page"] = page_num
        res["product_idx"] = 1
        res["crop_box"] = (60, 40, 620, 420)
        products.append(res)
        
    # Check Bottom Product
    has_bottom = any(any(k in l['text'].upper() for k in ["PONPES", "PONDOK", "PP ", "YPP", "PP.", "P3TQ"]) for l in bottom_text_lines)
    if has_bottom:
        res = parse_product_half(bottom_text_lines, is_top=False)
        res["page"] = page_num
        res["product_idx"] = 2
        res["crop_box"] = (620, 437, 1200, 820)
        products.append(res)
        
    return products

print(f"Starting extraction on {len(doc)} pages...")

for page_idx in range(len(doc)):
    page_num = page_idx + 1
    if page_num < 12 or page_num > 180:
        continue
    page = doc[page_idx]
    
    # 1. Render page to temporary PNG
    temp_img = os.path.join(output_dir, f"temp_page_{page_num}.png")
    pix = page.get_pixmap(dpi=150)
    pix.save(temp_img)
    
    # 2. Run OCR tool
    result = subprocess.run([ocr_exe, temp_img], capture_output=True, text=True, encoding='utf-8', errors='ignore')
    ocr_stdout = result.stdout
    
    # 3. Parse products
    products = process_ocr_output(ocr_stdout, page_num)
    
    if products:
        print(f"Page {page_num}: Found {len(products)} product(s)")
        for prod in products:
            # 4. Crop image
            x0, y0, x1, y1 = prod["crop_box"]
            rect = fitz.Rect(x0 / dpi_scale, y0 / dpi_scale, x1 / dpi_scale, y1 / dpi_scale)
            
            image_name = f"page_{page_num}_prod_{prod['product_idx']}.png"
            image_path = os.path.join(output_dir, image_name)
            
            # Crop using PyMuPDF
            crop_pix = page.get_pixmap(clip=rect, dpi=150)
            crop_pix.save(image_path)
            
            # Save image reference in data record
            prod["image_filename"] = image_name
            del prod["crop_box"]
            
            records.append(prod)
            print(f"  - Extracted: {prod['ponpes_name']} | Image: {image_name}")
            
    # Clean up temp image
    if os.path.exists(temp_img):
        os.remove(temp_img)

# Save to Excel and CSV
if records:
    df = pd.DataFrame(records)
    cols = ["page", "product_idx", "ponpes_name", "nspp", "kabupaten", "product_name", "description", "address", "phone", "image_filename"]
    df = df[cols]
    
    # Clean up illegal characters for Excel/openpyxl
    def clean_illegal_chars(val):
        if isinstance(val, str):
            return re.sub(r'[\x00-\x08\x0b\x0c\x0e-\x1f]', '', val)
        return val

    for col in df.columns:
        df[col] = df[col].apply(clean_illegal_chars)
        
    df.to_csv(csv_path, index=False)
    df.to_excel(excel_path, index=False)
    print(f"\nExtraction complete! Extracted {len(records)} products from 2023 catalog.")
    print(f"Data saved to:")
    print(f"  Excel: {excel_path}")
    print(f"  CSV:   {csv_path}")
    print(f"Cropped photos saved to: {output_dir}")
else:
    print("\nNo products were found. Please check PDF content.")
