import os
import shutil
import json

print("=== PENDEKAR LARAVEL DEPLOYMENT & BUILD PREPARATION ===")

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PUBLIC_DIR = os.path.join(BASE_DIR, "public")
DATA_SRC = os.path.join(BASE_DIR, "data", "merged_pesantren_data.json")
DATA_DST_DIR = os.path.join(PUBLIC_DIR, "data")
DATA_DST = os.path.join(DATA_DST_DIR, "pesantren_integrated.json")

# Step 1: Ensure dataset sync & generate lightweight map dataset
print("[1/3] Syncing integrated JSON dataset to public/data...")
os.makedirs(DATA_DST_DIR, exist_ok=True)
if os.path.exists(DATA_SRC):
    shutil.copyfile(DATA_SRC, DATA_DST)
    print(f"      Successfully synced {os.path.getsize(DATA_DST)} bytes to {DATA_DST}")
    
    # Generate compact pesantren_map_data.json
    MAP_DST = os.path.join(DATA_DST_DIR, "pesantren_map_data.json")
    with open(DATA_SRC, 'r', encoding='utf-8') as f:
        data = json.load(f)
    compact = []
    for p in data:
        b_level = p.get('bencana', {}).get('level_kerawanan') if isinstance(p.get('bencana'), dict) else ''
        is_dig = bool(p.get('is_digital') or (isinstance(p.get('digital'), dict) and p['digital'].get('is_digital')) or p.get('digital') == 1)
        is_eco = bool(p.get('is_ecoteologi') or (isinstance(p.get('ecoteologi'), dict) and p['ecoteologi'].get('is_ecoteologi')) or p.get('ecoteologi') == 1)
        is_ramah = bool(p.get('is_ramah') or (isinstance(p.get('ramah_anak'), dict) and p['ramah_anak'].get('is_ramah_anak')) or p.get('ramah') == 1)
        is_opop = bool(p.get('is_opop') or (isinstance(p.get('opop'), list) and len(p['opop']) > 0) or p.get('opop') == 1)
        is_kitab = bool(p.get('is_kitab') or (isinstance(p.get('karya_kitab'), list) and len(p['karya_kitab']) > 0) or p.get('kitab') == 1)
        
        item = {
            'nspp': p.get('nspp', ''),
            'nama': p.get('nama', ''),
            'jenjang': p.get('jenjang', ''),
            'alamat': p.get('alamat', ''),
            'kabupaten': p.get('kabupaten', ''),
            'kecamatan': p.get('kecamatan', ''),
            'afiliasi': p.get('afiliasi', ''),
            'latitude': p.get('latitude'),
            'longitude': p.get('longitude'),
            'bencana': {'level_kerawanan': b_level},
            'digital': {'is_digital': is_dig},
            'ecoteologi': {'is_ecoteologi': is_eco},
            'ramah_anak': {'is_ramah_anak': is_ramah},
            'opop': p.get('opop') if isinstance(p.get('opop'), list) and len(p['opop']) > 0 else (1 if is_opop else 0),
            'karya_kitab': p.get('karya_kitab') if isinstance(p.get('karya_kitab'), list) and len(p['karya_kitab']) > 0 else (1 if is_kitab else 0)
        }
        compact.append(item)

    with open(MAP_DST, 'w', encoding='utf-8') as f:
        json.dump(compact, f, separators=(',', ':'))
    print(f"      Successfully generated lightweight map data ({os.path.getsize(MAP_DST)} bytes) at {MAP_DST}")
else:
    print(f"      [WARNING] Source dataset not found at {DATA_SRC}")

# Step 2: Validate core files
print("[2/3] Validating deployment file integrity...")
required_files = [
    os.path.join(PUBLIC_DIR, "index.php"),
    os.path.join(PUBLIC_DIR, "assets", "css", "style.css"),
    os.path.join(PUBLIC_DIR, "assets", "js", "app.js"),
    os.path.join(BASE_DIR, "routes", "web.php"),
    os.path.join(BASE_DIR, "routes", "api.php"),
    os.path.join(BASE_DIR, "app", "Http", "Controllers", "RekapController.php"),
    os.path.join(BASE_DIR, "app", "Http", "Controllers", "DashboardController.php"),
    os.path.join(BASE_DIR, "resources", "views", "dashboard.blade.php"),
    os.path.join(BASE_DIR, "resources", "views", "rekap_pesantren.blade.php"),
    os.path.join(BASE_DIR, "resources", "views", "rekap_bencana.blade.php"),
    os.path.join(BASE_DIR, "resources", "views", "rekap_ramah.blade.php"),
    os.path.join(BASE_DIR, "resources", "views", "rekap_ecoteologi.blade.php"),
    os.path.join(BASE_DIR, "resources", "views", "rekap_produk.blade.php"),
]

missing = [f for f in required_files if not os.path.exists(f)]
if not missing:
    print("      All required deployment files verified successfully!")
else:
    print(f"      [ERROR] Missing files: {missing}")

# Step 3: Deployment Ready Summary
print("[3/3] Deployment Preparation Complete!")
print("\n--- DEPLOYMENT OPTIONS ---")
print("1. Local Execution:")
print("   Run 'serve.bat' or 'php artisan serve --port=9000' or 'php -S localhost:9000 -t public'")
print("2. Shared Hosting / cPanel Deployment:")
print("   Point Document Root to the 'public' directory of this project.")
print("3. VPS / Cloud Server (Nginx/Apache):")
print("   Configure virtualhost to serve root at 'd:/Pendekar/public' with PHP-FPM.")
