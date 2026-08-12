import requests
from bs4 import BeautifulSoup
import urllib.parse
import time
import random
import csv
import json
import os
import re

# Base verified pesantren data with name and address
verified_pesantren = [
    {
        "nama": "Pondok Pesantren Modern Al-Amanah Junwangi",
        "alamat": "Jl. Raya Junwangi No. 43, RT 09/RW 03, Desa Junwangi, Kecamatan Krian, Sidoarjo, Jawa Timur 61262",
        "tema": "Ramah Anak"
    },
    {
        "nama": "Pondok Pesantren Darul Ulum",
        "alamat": "Jl. Rejoso No. 36, Desa Rejoso, Kecamatan Peterongan, Jombang, Jawa Timur 61481",
        "tema": "Ramah Anak & Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Nurul Jadid",
        "alamat": "Jl. KH. Zaini Mun'im, Dusun Tanjung Lor, Desa Karanganyar, Kecamatan Paiton, Probolinggo, Jawa Timur 67291",
        "tema": "Ramah Anak"
    },
    {
        "nama": "Pondok Pesantren Al-Qodiri 1",
        "alamat": "Jl. Manggar No. 139A, Kelurahan Gebang, Kecamatan Patrang, Jember, Jawa Timur 68117",
        "tema": "Ramah Anak"
    },
    {
        "nama": "Pondok Pesantren Al-Hayatul Islamiyah",
        "alamat": "Jl. KH. Malik Dalam No. 01, RT 01 RW 04, Kelurahan Kedungkandang, Kecamatan Kedungkandang, Kota Malang, Jawa Timur 65137",
        "tema": "Ramah Anak"
    },
    {
        "nama": "Pondok Pesantren Darun Nun",
        "alamat": "Perum Bukit Cemara Tidar F3, No. 4, Karangbesuki, Kecamatan Sukun, Kota Malang, Jawa Timur 65146",
        "tema": "Ramah Anak"
    },
    {
        "nama": "Pondok Pesantren Imam Asy-Syafi'i (PPIA)",
        "alamat": "Jl. KH. Hasyim Asy'ari No. 99E, Dusun Krajan, Desa Kembiritan, Kecamatan Genteng, Kabupaten Banyuwangi, Jawa Timur 68465",
        "tema": "Ramah Anak"
    },
    {
        "nama": "Pondok Pesantren Zainul Hasan Genggong (Zainul Hasanain)",
        "alamat": "Desa Karangbong, Kecamatan Pajarakan, Kabupaten Probolinggo, Jawa Timur 67281",
        "tema": "Ecoteologi & Ramah Anak"
    },
    {
        "nama": "Pondok Pesantren Mamba'ul Hikam",
        "alamat": "Desa Diwek, Kecamatan Diwek, Kabupaten Jombang, Jawa Timur 61471",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Al-Umm",
        "alamat": "Jl. Joyo Agung No.1, Merjosari, Kecamatan Lowokwaru, Kota Malang, Jawa Timur 65144",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Maftahul 'Uluum Jatinom",
        "alamat": "Jatinom, Kecamatan Kanigoro, Kabupaten Blitar, Jawa Timur 66181",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Darun Najah",
        "alamat": "Desa Petahunan, Kecamatan Sumbersuko, Kabupaten Lumajang, Jawa Timur 67316",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Wali Barokah",
        "alamat": "Jl. Hos Cokroaminoto No. 195, Burengan, Kecamatan Pesantren, Kota Kediri, Jawa Timur 64131",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Gadingmangu",
        "alamat": "Gadingmangu, Kecamatan Perak, Kabupaten Jombang, Jawa Timur 61461",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Al Ubaidah",
        "alamat": "Jl. KH. Wahid Hasyim No. 23, Kertosono, Kecamatan Kertosono, Kabupaten Nganjuk, Jawa Timur 64311",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Tahfidz Al Qur'an Darul Fikri (Dafi) Anggaswangi",
        "alamat": "Desa Anggaswangi, Kecamatan Sukodono, Kabupaten Sidoarjo, Jawa Timur 61258",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Tarbiyatul Iman (PPTI)",
        "alamat": "Lowokwaru, Kota Malang, Jawa Timur 65141",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Tahfidzul Qur'an (PPTQ) Bahrusysyfa",
        "alamat": "Kelurahan Jogotrunan, Kecamatan Lumajang, Kabupaten Lumajang, Jawa Timur 67316",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Asy Syarifiy",
        "alamat": "Desa Pandawangi, Kecamatan Tempeh, Kabupaten Lumajang, Jawa Timur 67371",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Darul Hikmah",
        "alamat": "Merjosari, Kecamatan Lowokwaru, Kota Malang, Jawa Timur 65144",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Tebuireng Putri (Unit Kesamben)",
        "alamat": "Jl. Kesamben-Sumobito, Dusun Ngembul, Desa Kesamben, Kecamatan Kesamben, Kabupaten Jombang, Jawa Timur 61484",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Nuris (Nurul Islam) Jember",
        "alamat": "Jl. Pangandaran No. 48, Lingkungan Antirogo, Kecamatan Sumbersari, Kabupaten Jember, Jawa Timur 68125",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren Millenium Alfiena Nganjuk",
        "alamat": "Jl. Merdeka Timur, Desa Lengkong, Kecamatan Lengkong, Kabupaten Nganjuk, Jawa Timur 64353",
        "tema": "Ecoteologi"
    },
    {
        "nama": "Pondok Pesantren As-Sa'idiyyah 2 Tambakberas",
        "alamat": "Jl. KH. Abdul Wahab Hasbullah No. 24, Dusun Tambakberas, Desa Tambakrejo, Kecamatan Jombang, Kabupaten Jombang, Jawa Timur 61471",
        "tema": "Ramah Anak"
    }
]

def search_ddg(query):
    print(f"Searching DDG for: '{query}'...")
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.0.0 Safari/537.36'
    }
    url = f"https://html.duckduckgo.com/html/?q={urllib.parse.quote(query)}"
    try:
        response = requests.get(url, headers=headers, timeout=10)
        if response.status_code == 200:
            soup = BeautifulSoup(response.text, 'html.parser')
            results = []
            for result in soup.find_all('div', class_='result'):
                title_a = result.find('a', class_='result__url')
                snippet_a = result.find('a', class_='result__snippet')
                if title_a:
                    title = title_a.text.strip()
                    snippet = snippet_a.text.strip() if snippet_a else ""
                    results.append({'title': title, 'snippet': snippet})
            return results
    except Exception as e:
        print(f"Error searching: {e}")
    return []

def extract_pesantren(results, current_list):
    extracted = []
    # Keywords to identify pesantren names
    name_patterns = [
        r'(PP\s+[A-Za-z0-9\-\'\s]+)',
        r'(Ponpes\s+[A-Za-z0-9\-\'\s]+)',
        r'(Pondok\s+Pesantren\s+[A-Za-z0-9\-\'\s]+)'
    ]
    
    # Heuristics to check if it contains East Java regions
    jatim_regions = ["Surabaya", "Malang", "Sidoarjo", "Gresik", "Mojokerto", "Jombang", "Kediri", "Nganjuk",
                     "Madiun", "Blitar", "Pasuruan", "Probolinggo", "Lumajang", "Jember", "Banyuwangi", 
                     "Situbondo", "Bondowoso", "Tuban", "Lamongan", "Bojonegoro", "Sumenep", "Pamekasan",
                     "Sampang", "Bangkalan", "Ponorogo", "Trenggalek", "Tulungagung", "Ngawi", "Magetan", "Pacitan"]

    existing_names = {p['nama'].lower().strip() for p in current_list}

    for item in results:
        text_to_search = f"{item['title']} {item['snippet']}"
        
        # Try to find name matching pattern
        for pattern in name_patterns:
            matches = re.findall(pattern, text_to_search, re.IGNORECASE)
            for m in matches:
                # Clean up the name
                name = m.strip()
                # Remove common garbage at the end
                name = re.split(r'\b(di|dan|yang|adalah|telah|merupakan|pada|untuk|melalui|dengan|sebagai)\b', name, flags=re.IGNORECASE)[0].strip()
                name = re.sub(r'[\,\.\:\-\_]$', '', name).strip()
                
                # Heuristics for valid name length
                if len(name.split()) < 3 or len(name.split()) > 8:
                    continue
                
                # Check if it's already in the dataset
                if name.lower() in existing_names:
                    continue
                
                # Check if it mentions a Jatim region to confirm it is in East Java
                found_region = "Jawa Timur"
                for region in jatim_regions:
                    if region.lower() in text_to_search.lower():
                        found_region = f"{region}, Jawa Timur"
                        break
                
                # Check theme
                tema = "Ramah Lingkungan" if "eco" in text_to_search.lower() else "Ramah Anak"
                
                if name.lower() not in [x['nama'].lower() for x in extracted]:
                    extracted.append({
                        "nama": name,
                        "alamat": found_region,
                        "tema": tema
                    })
                    print(f"Found new candidate: {name} in {found_region}")
                    
    return extracted

def main():
    compiled_list = verified_pesantren.copy()
    
    # Try dynamic scraping
    queries = [
        '"eco pesantren" jawa timur daftar',
        '"pesantren ramah anak" jawa timur nama',
        'peraih "eco pesantren" jawa timur',
        'penerima penghargaan "eco pesantren" jatim'
    ]
    
    for q in queries:
        results = search_ddg(q)
        new_items = extract_pesantren(results, compiled_list)
        for item in new_items:
            # Check if name is already added to avoid duplicates
            if item['nama'].lower().strip() not in {p['nama'].lower().strip() for p in compiled_list}:
                compiled_list.append(item)
        time.sleep(random.uniform(2, 4))
        
    # Write to files
    csv_file = "scraped_pesantren.csv"
    json_file = "scraped_pesantren.json"
    
    # Save CSV
    try:
        with open(csv_file, mode='w', newline='', encoding='utf-8') as f:
            writer = csv.DictWriter(f, fieldnames=["nama", "alamat", "tema"])
            writer.writeheader()
            for row in compiled_list:
                writer.writerow(row)
        print(f"\nSaved CSV: {os.path.abspath(csv_file)}")
    except Exception as e:
        print(f"Error saving CSV: {e}")
        
    # Save JSON
    try:
        with open(json_file, mode='w', encoding='utf-8') as f:
            json.dump(compiled_list, f, indent=4, ensure_ascii=False)
        print(f"Saved JSON: {os.path.abspath(json_file)}")
    except Exception as e:
        print(f"Error saving JSON: {e}")
        
    # Print total found
    print(f"\nTotal Pesantren Compiled: {len(compiled_list)}")

if __name__ == "__main__":
    main()
