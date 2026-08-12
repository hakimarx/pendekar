import pandas as pd
import json
import os
import re
import numpy as np

# Input paths
BENCANA_FILE = r"d:\Pendekar\bencana\pesantren_jatim_risk_mapped.csv"
BENCANA_HISTORIS_FILE = r"d:\Pendekar\bencana\bencana_jatim_2022_2024.csv"
DIGITAL_FILE = r"d:\Pendekar\digital\pesantren_digital_jatim.csv"
ECOTEOLOGI_FILE = r"d:\Pendekar\ecoteologi\pesantren_ecoteologi_jatim.csv"
RAMAH_FILE = r"d:\Pendekar\ramah\pesantren_ramah_jatim.json"
OPOP_2024_FILE = r"d:\Pendekar\OPOP\extracted_data.csv"
OPOP_2023_FILE = r"d:\Pendekar\OPOP\extracted_data_2023.csv"

# Output paths
OUTPUT_DIR = r"d:\Pendekar\data"
OUTPUT_JSON = os.path.join(OUTPUT_DIR, "merged_pesantren_data.json")

print("=== STARTING DATA INTEGRATION (WITH KARYA KITAB & DISASTER DETAILS) ===")

# Create output dir
os.makedirs(OUTPUT_DIR, exist_ok=True)

# Load master list
print("Loading master list...")
df_pes = pd.read_csv(BENCANA_FILE)
print(f"Master list has {len(df_pes)} pesantren.")

# Load historical disasters for proximity mapping
print("Loading historical disaster data for detailed coordinates...")
df_dis = pd.read_csv(BENCANA_HISTORIS_FILE)
df_dis_coords = df_dis[df_dis['latitude'].notna() & df_dis['longitude'].notna()].copy()
lat_dis_rad = np.radians(df_dis_coords['latitude'].values)
lon_dis_rad = np.radians(df_dis_coords['longitude'].values)
dis_types = df_dis_coords['type_bencana'].values
dis_years = df_dis_coords['tahun'].values
dis_areas = df_dis_coords['area'].values
dis_lats = df_dis_coords['latitude'].values
dis_lons = df_dis_coords['longitude'].values
print(f"Loaded {len(df_dis_coords)} disaster records with coordinates.")

# Load sub-datasets
print("Loading sub-datasets...")
df_dig = pd.read_csv(DIGITAL_FILE)
df_eco = pd.read_csv(ECOTEOLOGI_FILE, names=['Nama Pesantren', 'Alamat', 'Latitude', 'Longitude', 'Jenis Ecoteologi'], header=0, index_col=False)
with open(RAMAH_FILE, 'r', encoding='utf-8') as f:
    df_ramah = pd.DataFrame(json.load(f))
df_opop_2024 = pd.read_csv(OPOP_2024_FILE)
df_opop_2023 = pd.read_csv(OPOP_2023_FILE)

# Helper lists
regencies = df_pes['Kabupaten'].unique()

def clean_val(val, default=""):
    if pd.isna(val) or val is None:
        return default
    if isinstance(val, float):
        if val.is_integer():
            return str(int(val))
        return str(val)
    return str(val).strip()

def clean_name(name):
    if not isinstance(name, str):
        return ""
    name = name.lower()
    name = name.replace("pondok pesantren", "").replace("ponpes", "").replace("pp.", "").replace("pp", "")
    name = name.replace("modern", "").replace("putri", "").replace("putra", "").replace("islam", "")
    # Remove non-alphanumeric
    name = "".join([c for c in name if c.isalnum()])
    return name.strip()

# Add clean name to master
df_pes['clean_name'] = df_pes['Nama Lembaga'].apply(clean_name)
df_pes['nspp_str'] = df_pes['NSPP'].astype(str).str.replace(r'\.0$', '', regex=True).str.strip()

# Map input address to master regency names
def extract_regency(address_text, default=""):
    if not isinstance(address_text, str):
        return default
    text = address_text.upper()
    if "KOTA MALANG" in text:
        return "KOTA MALANG"
    if "MALANG" in text:
        return "MALANG"
    if "KOTA KEDIRI" in text:
        return "KOTA KEDIRI"
    if "KEDIRI" in text:
        return "KEDIRI"
    if "KOTA BLITAR" in text:
        return "KOTA BLITAR"
    if "BLITAR" in text:
        return "BLITAR"
    if "KOTA MADIUN" in text:
        return "KOTA MADIUN"
    if "MADIUN" in text:
        return "MADIUN"
    if "KOTA PROBOLINGGO" in text:
        return "KOTA PROBOLINGGO"
    if "PROBOLINGGO" in text:
        return "PROBOLINGGO"
    if "KOTA PASURUAN" in text:
        return "KOTA PASURUAN"
    if "PASURUAN" in text:
        return "PASURUAN"
    if "KOTA MOJOKERTO" in text:
        return "KOTA MOJOKERTO"
    if "MOJOKERTO" in text:
        return "MOJOKERTO"
    if "SURABAYA" in text:
        return "KOTA SURABAYA"
    if "BATU" in text:
        return "KOTA BATU"
        
    for r in regencies:
        r_clean = r.replace("KABUPATEN", "").replace("KAB.", "").strip()
        if r_clean in text:
            return r
    return default

def find_best_match(name, address_text, target_kab=None):
    if not target_kab:
        target_kab = extract_regency(address_text)
        
    df_sub = df_pes
    if target_kab:
        df_sub = df_pes[df_pes['Kabupaten'] == target_kab]
        if df_sub.empty:
            df_sub = df_pes
            
    c_name = clean_name(name)
    
    # 1. Exact match in regency
    match = df_sub[df_sub['clean_name'] == c_name]
    if not match.empty:
        return match.index[0]
        
    # 2. Substring match
    match = df_sub[df_sub['clean_name'].str.contains(c_name, na=False) | df_sub['Nama Lembaga'].str.lower().str.contains(name.lower(), na=False)]
    if not match.empty:
        return match.index[0]
        
    # 3. Reverse substring match
    match = df_sub[df_sub['clean_name'].apply(lambda x: len(x) > 3 and x in c_name)]
    if not match.empty:
        return match.index[0]
        
    # 4. Global exact
    match = df_pes[df_pes['clean_name'] == c_name]
    if not match.empty:
        return match.index[0]
        
    # 5. Global contain
    match = df_pes[df_pes['clean_name'].str.contains(c_name, na=False)]
    if not match.empty:
        return match.index[0]
        
    return None

# Build properties structure on master list
merged_data = {}
print("Processing master list and calculating top 5 closest disasters...")
for idx, row in df_pes.iterrows():
    nspp_key = row['nspp_str']
    
    # Check for NaN in coordinate fields
    lat = float(row['latitude']) if not pd.isna(row['latitude']) else None
    lon = float(row['longitude']) if not pd.isna(row['longitude']) else None
    
    # Vectorized Haversine to calculate top 5 closest disasters
    closest_disasters = []
    if lat is not None and lon is not None:
        lat_pes = np.radians(lat)
        lon_pes = np.radians(lon)
        dlat = lat_dis_rad - lat_pes
        dlon = lon_dis_rad - lon_pes
        a = np.sin(dlat / 2.0)**2 + np.cos(lat_pes) * np.cos(lat_dis_rad) * np.sin(dlon / 2.0)**2
        c = 2.0 * np.arcsin(np.sqrt(a))
        distances = 6371.0 * c
        
        # Get top 5 closest
        closest_indices = np.argsort(distances)[:5]
        for c_idx in closest_indices:
            closest_disasters.append({
                "tipe": str(dis_types[c_idx]),
                "jarak_km": round(float(distances[c_idx]), 2),
                "tahun": int(dis_years[c_idx]),
                "area": str(dis_areas[c_idx]) if not pd.isna(dis_areas[c_idx]) else "",
                "latitude": float(dis_lats[c_idx]),
                "longitude": float(dis_lons[c_idx])
            })
            
    merged_data[nspp_key] = {
        "nspp": nspp_key,
        "nama": row['Nama Lembaga'],
        "jenjang": row['Jenjang'] if not pd.isna(row['Jenjang']) else "",
        "alamat": row['Alamat'] if not pd.isna(row['Alamat']) else "",
        "kabupaten": row['Kabupaten'],
        "kecamatan": row['Kecamatan'] if not pd.isna(row['Kecamatan']) else "",
        "afiliasi": row['Afiliasi Organisasi'] if not pd.isna(row['Afiliasi Organisasi']) else "Lainnya",
        "latitude": lat,
        "longitude": lon,
        "bencana": {
            "level_kerawanan": row['level_kerawanan'],
            "disasters_within_5km": int(row['disasters_within_5km']),
            "disasters_within_10km": int(row['disasters_within_10km']),
            "disasters_within_20km": int(row['disasters_within_20km']),
            "closest_disaster_type": row['closest_disaster_type'] if not pd.isna(row['closest_disaster_type']) else "Tidak Ada",
            "closest_disaster_distance_km": float(row['closest_disaster_distance_km']) if not pd.isna(row['closest_disaster_distance_km']) else None,
            "closest_disaster_year": int(row['closest_disaster_year']) if not pd.isna(row['closest_disaster_year']) else None,
            "closest_disasters": closest_disasters
        },
        "digital": {"is_digital": False},
        "ecoteologi": {"is_ecoteologi": False},
        "ramah_anak": {"is_ramah_anak": False},
        "opop": [],
        "karya_kitab": []
    }

print("\n--- Merging Digital Pesantren ---")
for idx, row in df_dig.iterrows():
    kab_input = row['Kabupaten/Kota'].upper().strip()
    target_kab = None
    if "SURABAYA" in kab_input:
        target_kab = "KOTA SURABAYA"
    elif "MALANG" in kab_input:
        target_kab = "KOTA MALANG" if "KOTA" in kab_input else "MALANG"
    elif "KEDIRI" in kab_input:
        target_kab = "KOTA KEDIRI" if "KOTA" in kab_input else "KEDIRI"
    elif "PROBOLINGGO" in kab_input:
        target_kab = "KOTA PROBOLINGGO" if "KOTA" in kab_input else "PROBOLINGGO"
    elif "PASURUAN" in kab_input:
        target_kab = "KOTA PASURUAN" if "KOTA" in kab_input else "PASURUAN"
    else:
        for r in regencies:
            if kab_input in r:
                target_kab = r
                break
            
    match_idx = find_best_match(row['Nama Pesantren'], row['Alamat'], target_kab)
    if match_idx is not None:
        nspp_key = df_pes.iloc[match_idx]['nspp_str']
        merged_data[nspp_key]["digital"] = {
            "is_digital": True,
            "aplikasi": clean_val(row['Unggulan Aplikasi']),
            "layanan": clean_val(row['Layanan Digital']),
            "dampak": clean_val(row['Dampak Kualitas']),
            "website": clean_val(row['Website/Referensi'])
        }
        print(f"Merged Digital: {row['Nama Pesantren']} -> {df_pes.iloc[match_idx]['Nama Lembaga']}")
    else:
        print(f"WARNING: Could not match digital pesantren: {row['Nama Pesantren']}")

print("\n--- Merging Ecoteologi Pesantren ---")
for idx, row in df_eco.iterrows():
    match_idx = find_best_match(row['Nama Pesantren'], row['Alamat'])
    if match_idx is not None:
        nspp_key = df_pes.iloc[match_idx]['nspp_str']
        merged_data[nspp_key]["ecoteologi"] = {
            "is_ecoteologi": True,
            "jenis": clean_val(row['Jenis Ecoteologi']),
            "alamat_detail": clean_val(row['Alamat'])
        }
        print(f"Merged Ecoteologi: {row['Nama Pesantren']} -> {df_pes.iloc[match_idx]['Nama Lembaga']}")
    else:
        print(f"WARNING: Could not match ecoteologi pesantren: {row['Nama Pesantren']}")

print("\n--- Merging Ramah Anak Pesantren ---")
for idx, row in df_ramah.iterrows():
    match_idx = find_best_match(row['nama'], row['alamat'])
    if match_idx is not None:
        nspp_key = df_pes.iloc[match_idx]['nspp_str']
        merged_data[nspp_key]["ramah_anak"] = {
            "is_ramah_anak": True,
            "program": clean_val(row['program_ramah_anak']),
            "kurikulum": clean_val(row['integrasi_kurikulum']),
            "dampak": clean_val(row['dampak_karakter']),
            "alamat_detail": clean_val(row['alamat'])
        }
        print(f"Merged Ramah Anak: {row['nama']} -> {df_pes.iloc[match_idx]['Nama Lembaga']}")
    else:
        print(f"WARNING: Could not match ramah anak pesantren: {row['nama']}")

print("\n--- Merging OPOP Products ---")
# Merge OPOP 2024
opop_2024_count = 0
for idx, row in df_opop_2024.iterrows():
    nspp_val = str(row.get('nspp', '')).replace('.0', '').strip()
    match_idx = None
    
    if nspp_val and nspp_val in merged_data:
        nspp_key = nspp_val
    else:
        kab = row.get('kabupaten', '')
        kab_clean = str(kab).upper().replace("KAB.", "").replace("KOTA", "").strip()
        target_kab = None
        for r in regencies:
            if kab_clean in r:
                target_kab = r
                break
        match_idx = find_best_match(row.get('ponpes_name', ''), row.get('address', ''), target_kab)
        if match_idx is not None:
            nspp_key = df_pes.iloc[match_idx]['nspp_str']
        else:
            nspp_key = None
            
    if nspp_key:
        product_item = {
            "tahun": 2024,
            "nama_produk": clean_val(row.get('product_name')),
            "deskripsi": clean_val(row.get('description')),
            "kategori": "",
            "telepon": clean_val(row.get('phone')),
            "gambar": clean_val(row.get('image_filename'))
        }
        merged_data[nspp_key]["opop"].append(product_item)
        opop_2024_count += 1

# Merge OPOP 2023
opop_2023_count = 0
for idx, row in df_opop_2023.iterrows():
    nspp_val = str(row.get('nspp', '')).replace('.0', '').strip()
    match_idx = None
    
    if nspp_val and nspp_val in merged_data:
        nspp_key = nspp_val
    else:
        kab = row.get('kabupaten', '')
        kab_clean = str(kab).upper().replace("KAB.", "").replace("KOTA", "").strip()
        target_kab = None
        for r in regencies:
            if kab_clean in r:
                target_kab = r
                break
        match_idx = find_best_match(row.get('ponpes_name', ''), row.get('address', ''), target_kab)
        if match_idx is not None:
            nspp_key = df_pes.iloc[match_idx]['nspp_str']
        else:
            nspp_key = None
            
    if nspp_key:
        product_item = {
            "tahun": 2023,
            "nama_produk": clean_val(row.get('product_name')),
            "deskripsi": clean_val(row.get('description')),
            "kategori": "",
            "telepon": clean_val(row.get('phone')),
            "gambar": clean_val(row.get('image_filename'))
        }
        merged_data[nspp_key]["opop"].append(product_item)
        opop_2023_count += 1

# Static new products from pendekar.pdpontrenjatim.my.id
print("\nMerging new products from pendekar.pdpontrenjatim.my.id...")
NEW_PRODUCTS = [
    {
        "nama_produk": "KOPI GANSARI",
        "nspp": "510335060178",
        "telepon": "085815032814",
        "deskripsi": "bubuk Kopi murni. Robusta aceh gayo."
    },
    {
        "nama_produk": "ALQOMAR BAKERY",
        "nspp": "510035710033",
        "telepon": "081232253915",
        "deskripsi": "Alqomar Bakery Merk roti unggulan yang berasal dari Pondok Pesantren Wali Barokah, Kota Kediri. Roti premium lembut higienis."
    },
    {
        "nama_produk": "KRIPIK PISANG \"GEDANG KOE\"",
        "nspp": "510035740011",
        "telepon": "082331928022",
        "deskripsi": "Keripik pisang Gedang koe merupakan jenis makanan ringan terbuat dari bahan buah pisang renyah & gurih."
    }
]

for prod in NEW_PRODUCTS:
    nspp_key = prod["nspp"]
    if nspp_key in merged_data:
        product_item = {
            "tahun": "Pendekar Jatim",
            "nama_produk": prod['nama_produk'],
            "deskripsi": prod['deskripsi'],
            "kategori": "Makanan & Minuman",
            "telepon": prod['telepon'],
            "gambar": ""
        }
        merged_data[nspp_key]["opop"].append(product_item)
        print(f"Merged New Product: {prod['nama_produk']} -> {merged_data[nspp_key]['nama']}")
    else:
        print(f"WARNING: Could not match new product: {prod['nama_produk']}")

# Static Karya Kitab from pendekar.pdpontrenjatim.my.id
print("\n--- Merging Karya Kitab / Karya Ilmiah ---")
KARYA_KITAB_LIST = [
    {
        "judul": "ILMU MANASIK VERSI ANDROID",
        "nspp": "510035140247",
        "pengarang": "DR. H. NUR YASIN SHIROTOL MUSTAQIM, MA",
        "tahun": "2023",
        "halaman": "",
        "jilid": "",
        "ukuran": "",
        "isbn": "",
        "haki": "",
        "paten": "",
        "penerbit": "",
        "link_karya": "https://play.google.com/store/apps/details?id=com.ilmumanasik.android",
        "telepon": "081230000177",
        "deskripsi": "APLIKASI ILMU MANASIK INI BERBASIS ANDROID, BERSUMBER DARI BUKU MANASIK DAN TUNTUNAN DOA KEMENTERIAN AGAMA REPUBLIK INDONESIA, YANG DILENGKAPI DENGAN AUDIO, AGAR JAMAAH HAJI ATAU UMROH MUDAH MEMPELAJARI DAN MENGIKUTINYA, SEAKAN-AKAN ADA YANG MEMIMPIN DOA, APABILA SUDAH TERUNDUH MAKA APLIKASI INI MENJADI OFFLINE (TIDAK PERLU INTERNET), SEMOGA BERMANFAAT, AMIN."
    },
    {
        "judul": "KEPEMIMPINAN PENDIDIKAN (INTEGRASI TEORI DAN PRAKTIK)",
        "nspp": "510335160041",
        "pengarang": "WAHYU SYAF'AAT",
        "tahun": "2024",
        "halaman": "80",
        "jilid": "",
        "ukuran": "A5; 21 cm x 14.8 cm",
        "isbn": "978-623-92462-2-8",
        "haki": "https://pdki-indonesia.dgip.go.id/detail/e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855",
        "paten": "",
        "penerbit": "Staisam Pustaka",
        "link_karya": "https://penerbit.staisam.ac.id/books/kepemimpinan-pendidikan-integrasi-teori-dan-praktik/",
        "telepon": "085730552525",
        "deskripsi": "Buku ini ditulis sebagai buku ajar mahasiswa, diharapkan dapat memberikan kontribusi yang berharga dalam pengembangan kepemimpinan pendidikan dan dapat memberikan inspirasi serta panduan bagi mereka yang berkomitmen untuk menciptakan perubahan positif di dunia pendidikan, demi masa depan yang lebih baik bagi generasi yang akan datang."
    },
    {
        "judul": "FIQIH ASASI",
        "nspp": "510335060178",
        "pengarang": "LUKMAN H. ZA",
        "tahun": "2017",
        "halaman": "262",
        "jilid": "1",
        "ukuran": "",
        "isbn": "",
        "haki": "",
        "paten": "",
        "penerbit": "",
        "link_karya": "",
        "telepon": "085815032814",
        "deskripsi": "Terjemah kitab mabadil fiqhi"
    },
    {
        "judul": "SYAIR AKHLAQ BAHASA MADURA",
        "nspp": "510035170112",
        "pengarang": "DRS. ACHMAD MUNIR MAS / أحمد منير بن مبر بن معروف بن عبد الصمد بن صبران",
        "tahun": "1966",
        "halaman": "23",
        "jilid": "1",
        "ukuran": "",
        "isbn": "",
        "haki": "",
        "paten": "",
        "penerbit": "",
        "link_karya": "https://drive.google.com/file/d/1OAEeoQoaGSaW5ud1H0xX_KbmTVAYAx8h/view",
        "telepon": "081230000177",
        "deskripsi": "KITAB INI BERISI SYAIR BERBAHASA MADURA TENTANG AKHLAQ"
    },
    {
        "judul": "TABEL QIRO'AH SAB'AH (TUJUH) VERSI PDF",
        "nspp": "510035140247",
        "pengarang": "DR. H. NUR YASIN SHIROTOL MUSTAQIM, MA",
        "tahun": "2002",
        "halaman": "3",
        "jilid": "",
        "ukuran": "",
        "isbn": "",
        "haki": "",
        "paten": "",
        "penerbit": "",
        "link_karya": "https://drive.google.com/file/d/1kjhhTi9LXqYqUvJRZA99xg6ZmSPd6DWy/view",
        "telepon": "081230000177",
        "deskripsi": "TABEL BERISI RANGKUMAN KAIDAH-KAIDAH DALAM QIRO'AH SAB'AH ATAU TUJUH DALAM BENTUK FILE PDF"
    },
    {
        "judul": "سبل الرضوان لنيل الغفران (Subulur Ridwan)",
        "nspp": "510035170112",
        "pengarang": "DRS. ACHMAD MUNIR MAS / أحمد منير بن مبر بن معروف بن عبد الصمد بن صبران",
        "tahun": "1997",
        "halaman": "172",
        "jilid": "",
        "ukuran": "",
        "isbn": "",
        "haki": "",
        "paten": "",
        "penerbit": "",
        "link_karya": "https://drive.google.com/file/d/1K30YyjSBx79ZFsP2ocLh4P902XTDobEY/view",
        "telepon": "081230000177",
        "deskripsi": "KITAB INI BERISI TENTANG SHOLAT DAN KESUNNAHAN-KESUNNAHANNYA"
    },
    {
        "judul": "الغرائب في القرآن (Al-Gharaib fi al-Qur'an)",
        "nspp": "510035140247",
        "pengarang": "DR. H. NUR YASIN SHIROTOL MUSTAQIM, MA",
        "tahun": "1999",
        "halaman": "132",
        "jilid": "",
        "ukuran": "",
        "isbn": "",
        "haki": "",
        "paten": "",
        "penerbit": "",
        "link_karya": "https://drive.google.com/file/d/1clowGMf_cNTCkaaqULmoKmcULm2w2NTm/view",
        "telepon": "081230000177",
        "deskripsi": "KITAB BERBAHASA ARAB TENTANG HAL-HAL YANG ASING DALAM AL-QUR'AN BAIK BACAAN MAUPUN TULISAN MENURUT RIWAYAT HAFSH DARI IMAM 'ASHIM"
    },
    {
        "judul": "ثمرة الفkrية بالشعر المندورية (Tsamratul Fikriyah)",
        "nspp": "510035170112",
        "pengarang": "DRS. ACHMAD MUNIR MAS / أحمد منير بن مبر بن معروف بن عبد الصمد بن صبران",
        "tahun": "1988",
        "halaman": "73",
        "jilid": "",
        "ukuran": "",
        "isbn": "",
        "haki": "",
        "paten": "",
        "penerbit": "",
        "link_karya": "https://drive.google.com/file/d/1vwAU6pdRJEafnNuD2qenxz62uj43KFyu/view",
        "telepon": "081230000177",
        "deskripsi": "KITAB INI BERISI SYAIR BERBAHASA MADURA TENTANG ILMU PEDOMAN THORIQOT AL-QOODIRIYYAH WAN NAQSYABANDIYYAH"
    },
    {
        "judul": "METODE CEPAT MEMBACA KITAB KUNING ULUL ALBAB",
        "nspp": "510035710048",
        "pengarang": "DR. KH. AHMAD RIFA'I, M.PD",
        "tahun": "2022",
        "halaman": "140",
        "jilid": "1",
        "ukuran": "14,8 X 21 cm",
        "isbn": "978-623-5874-95-1",
        "haki": "000661383",
        "paten": "",
        "penerbit": "Global Aksara",
        "link_karya": "https://drive.google.com/file/d/1cn_od-qz9uhrmr1oa24kpXCCv2opGV1Z/view?usp=drive_link",
        "telepon": "085736237570",
        "deskripsi": "Kitab Turats merupakan kekayaan intelektual klasik yang dipakai sebagai materi pelajaran baik di pondok pesantren atau di madrasah. Menuntun pemahaman cepat Nahwu Shorof secara efisien."
    },
    {
        "judul": "المصدر الكائن (Al-Masdar Al-Ka'in)",
        "nspp": "510035070092",
        "pengarang": "KH. M. BASORI ALWI",
        "tahun": "1990",
        "halaman": "16",
        "jilid": "",
        "ukuran": "",
        "isbn": "",
        "haki": "",
        "paten": "",
        "penerbit": "",
        "link_karya": "https://drive.google.com/file/d/1YSHXPAUVA8rStHZurNdm7mEm574aGKOZ/view?pli=1",
        "telepon": "081230000177",
        "deskripsi": "Contoh penggunaan masdar shorih atau asli dan masdar muawwal dalam beberapa kedudukan"
    },
    {
        "judul": "E-MAQRA",
        "nspp": "510035140247",
        "pengarang": "DR. H. NUR YASIN SHIROTOL MUSTAQIM, MA & TIM",
        "tahun": "2017",
        "halaman": "",
        "jilid": "",
        "ukuran": "",
        "isbn": "",
        "haki": "EC00201932950, 15 Maret 2019",
        "paten": "",
        "penerbit": "",
        "link_karya": "https://081230000177.blogspot.com/2019/11/emaqra.html?m=1",
        "telepon": "081230000177",
        "deskripsi": "E-MAQRA adalah aplikasi pengacak dan penampil soal dan musabaqah secara transparan dan langsung"
    },
    {
        "judul": "E-MAQRA ONLINE",
        "nspp": "510035140247",
        "pengarang": "DR. H. NUR YASIN SHIROTOL MUSTAQIM, MA & TIM",
        "tahun": "2017",
        "halaman": "",
        "jilid": "",
        "ukuran": "",
        "isbn": "",
        "haki": "",
        "paten": "",
        "penerbit": "",
        "link_karya": "",
        "telepon": "081230000177",
        "deskripsi": "E-MAQRA Online merupakan versi online dari aplikasi E-MAQRA."
    }
]

# Match and merge Karya Kitab
kitab_count = 0
for kit in KARYA_KITAB_LIST:
    nspp_key = kit["nspp"]
    if nspp_key in merged_data:
        kitab_item = {
            "judul": kit['judul'],
            "pengarang": kit['pengarang'],
            "tahun": kit['tahun'],
            "halaman": kit['halaman'],
            "jilid": kit['jilid'],
            "ukuran": kit['ukuran'],
            "isbn": kit['isbn'],
            "haki": kit['haki'],
            "paten": kit['paten'],
            "penerbit": kit['penerbit'],
            "link_karya": kit['link_karya'],
            "telepon": kit['telepon'],
            "deskripsi": kit['deskripsi']
        }
        merged_data[nspp_key]["karya_kitab"].append(kitab_item)
        kitab_count += 1
        try:
            print(f"Merged Kitab: {kit['judul']} -> {merged_data[nspp_key]['nama']}")
        except UnicodeEncodeError:
            print(f"Merged Kitab (Arabic Title) -> {merged_data[nspp_key]['nama']}")
    else:
        print(f"WARNING: Could not match pesantren for Karya Kitab: {kit['judul']} at NSPP {nspp_key}")

print(f"Successfully integrated {kitab_count} Karya Kitab records.")

# Convert merged dictionary back to a list
final_list = list(merged_data.values())

# Count active metrics
total_dig = sum(1 for p in final_list if p['digital']['is_digital'])
total_eco = sum(1 for p in final_list if p['ecoteologi']['is_ecoteologi'])
total_ramah = sum(1 for p in final_list if p['ramah_anak']['is_ramah_anak'])
total_kitab = sum(1 for p in final_list if len(p['karya_kitab']) > 0)
total_with_opop = sum(1 for p in final_list if len(p['opop']) > 0)
total_products = sum(len(p['opop']) for p in final_list)

print(f"\nIntegration Summary:")
print(f"  Total Pesantren in final JSON: {len(final_list)}")
print(f"  Total Digitalized: {total_dig}")
print(f"  Total Eco-Pesantren: {total_eco}")
print(f"  Total Child-Friendly: {total_ramah}")
print(f"  Total with Karya Kitab: {total_kitab}")
print(f"  Total Pesantren with OPOP products: {total_with_opop}")
print(f"  Total OPOP products: {total_products}")

# Save JSON
print(f"\nSaving merged data to: {OUTPUT_JSON}")
with open(OUTPUT_JSON, 'w', encoding='utf-8') as f:
    json.dump(final_list, f, indent=2, ensure_ascii=False)
print("=== DATA INTEGRATION COMPLETED SUCCESSFULLY ===")
