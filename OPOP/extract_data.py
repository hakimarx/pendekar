import os
import re
import subprocess
import fitz
import pandas as pd

# Paths
pdf_path = r"d:\Pendekar\OPOP\opop 2024.pdf"
ocr_exe = r"C:\Users\pdpon\.gemini\antigravity-ide\brain\d3b8cb59-e53e-43b0-b549-a9c4cdf97443\scratch\WinOcrCoords.exe"
output_dir = r"d:\Pendekar\OPOP\extracted_images"
excel_path = r"d:\Pendekar\OPOP\extracted_data.xlsx"
csv_path = r"d:\Pendekar\OPOP\extracted_data.csv"

# Create output directories
os.makedirs(output_dir, exist_ok=True)

doc = fitz.open(pdf_path)
dpi_scale = 150.0 / 72.0

records = []

def parse_product_half(lines, is_top, is_even_page):
    # Sort lines by Y-coordinate
    lines = sorted(lines, key=lambda l: l['y'])
    
    ponpes_name = ""
    nspp = ""
    kabupaten = ""
    prod_name_lines = []
    desc_lines = []
    addr_lines = []
    phone_lines = []
    
    for line in lines:
        text = line['text'].strip()
        if not text:
            continue
            
        # Check for Ponpes name
        if not ponpes_name and any(k in text.upper() for k in ["PONPES", "PONDOK", "PP ", "YPP", "PP.", "PON-PES", "PLP"]):
            ponpes_name = text
            continue
            
        # Check for NSPP (12 digits)
        if not nspp and (re.search(r'\b\d{12}\b', text) or (len(text) == 12 and text.isdigit())):
            nspp = text
            continue
            
        # Check for Kabupaten
        if not kabupaten and any(k in text.upper() for k in ["KAB.", "KOTA"]):
            kabupaten = text
            continue
            
    if not ponpes_name and lines:
        ponpes_name = lines[0]['text'].strip()
        
    y_threshold = 450 if is_top else 1250
    block_y_threshold = 630 if is_top else 1380
    x_threshold = 380 if is_even_page else 940
    
    for line in lines:
        text = line['text'].strip()
        if text == ponpes_name or text == nspp or text == kabupaten:
            continue
            
        if line['y'] >= block_y_threshold:
            is_phone_col = (line['x'] >= x_threshold)
            if is_phone_col:
                cleaned = re.sub(r'(?i)(no\.?\s*telp\.?|telp\.?)\s*:?', '', text).strip()
                # Clean up if it's just garbage/page number
                if cleaned and not (cleaned.isdigit() and len(cleaned) <= 3):
                    phone_lines.append(cleaned)
            else:
                cleaned = re.sub(r'(?i)alamat\s*:?', '', text).strip()
                # Skip if it is just a page index like "03." or "05." at the very bottom
                if cleaned and not (cleaned.endswith('.') and cleaned[:-1].isdigit() and len(cleaned) <= 3):
                    addr_lines.append(cleaned)
        else:
            if line['y'] < y_threshold:
                prod_name_lines.append(text)
            else:
                desc_lines.append(text)
            
    product_name = " ".join(prod_name_lines).strip()
    description = " ".join(desc_lines).strip()
    address = ", ".join(addr_lines).strip()
    phone = " / ".join(phone_lines).strip()
    
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
                
    is_even_page = (page_num % 2 == 0)
    
    top_left = []
    top_right = []
    bottom_left = []
    bottom_right = []
    
    for line in lines:
        x, y = line['x'], line['y']
        w, h = line['w'], line['h']
        cx = x + w / 2
        cy = y + h / 2
        
        if cy < 877:
            if cx < 620:
                top_left.append(line)
            else:
                top_right.append(line)
        else:
            if cx < 620:
                bottom_left.append(line)
            else:
                bottom_right.append(line)
                
    products = []
    
    # Top Product
    top_text_lines = top_left if is_even_page else top_right
    has_top = any(any(k in l['text'].upper() for k in ["PONPES", "PONDOK", "PP ", "YPP", "PP."]) for l in top_text_lines)
    if has_top or (len(top_text_lines) > 3 and any("KAB." in l['text'].upper() or "KOTA" in l['text'].upper() for l in top_text_lines)):
        res = parse_product_half(top_text_lines, is_top=True, is_even_page=is_even_page)
        res["page"] = page_num
        res["product_idx"] = 1
        res["crop_box"] = (620 if is_even_page else 60, 80, 1200 if is_even_page else 620, 850)
        products.append(res)
        
    # Bottom Product
    bottom_text_lines = bottom_left if is_even_page else bottom_right
    has_bottom = any(any(k in l['text'].upper() for k in ["PONPES", "PONDOK", "PP ", "YPP", "PP."]) for l in bottom_text_lines)
    if has_bottom:
        res = parse_product_half(bottom_text_lines, is_top=False, is_even_page=is_even_page)
        res["page"] = page_num
        res["product_idx"] = 2
        res["crop_box"] = (620 if is_even_page else 60, 877, 1200 if is_even_page else 620, 1650)
        products.append(res)
        
    return products

print(f"Starting extraction on {len(doc)} pages...")

for page_idx in range(len(doc)):
    page_num = page_idx + 1
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
            # Remove helper key
            del prod["crop_box"]
            
            records.append(prod)
            print(f"  - Extracted: {prod['ponpes_name']} | Image: {image_name}")
    else:
        # Check if it's a section header page or intro page
        pass
        
    # Clean up temp image
    if os.path.exists(temp_img):
        os.remove(temp_img)

if records:
    df = pd.DataFrame(records)
    # Reorder columns for nice presentation
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
    print(f"\nExtraction complete! Extracted {len(records)} products.")
    print(f"Data saved to:")
    print(f"  Excel: {excel_path}")
    print(f"  CSV:   {csv_path}")
    print(f"Cropped photos saved to: {output_dir}")
else:
    print("\nNo products were found. Please check PDF content.")
