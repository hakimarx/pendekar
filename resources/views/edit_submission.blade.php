<?php 
ob_start(); 
$details = json_decode($sub['details'], true) ?: [];
?>

<section style="padding: 40px 0; background-color: #F8FAFC; min-height: 80vh;">
    <div class="container" style="max-width: 800px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #E2E8F0; padding-bottom: 15px;">
            <div>
                <h2 style="color: #2D5D7C; margin: 0; font-size: 1.6rem; font-weight: 700;">Edit Data Pengajuan</h2>
                <p style="color: #64748B; margin: 5px 0 0 0; font-size: 0.9rem;">Perbarui data pendataan untuk: <strong><?php echo htmlspecialchars($sub['nama_pesantren']); ?></strong></p>
            </div>
            <div>
                <a href="/admin" class="btn-primary" style="background-color: #64748B; border-color: #64748B; padding: 8px 16px; font-size: 0.85rem; text-decoration: none; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 35px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
            <form action="/admin/edit" method="POST">
                <input type="hidden" name="id" value="<?php echo $sub['id']; ?>">

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nama_pesantren" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Nama Pesantren <span style="color: red;">*</span></label>
                    <input type="text" id="nama_pesantren" name="nama_pesantren" class="form-control" required value="<?php echo htmlspecialchars($sub['nama_pesantren']); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="nspp" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">NSPP (Nomor Statistik) <span style="color: red;">*</span></label>
                        <input type="text" id="nspp" name="nspp" class="form-control" required value="<?php echo htmlspecialchars($sub['nspp']); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                    </div>
                    <div class="form-group">
                        <label for="kabupaten" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Kabupaten / Kota <span style="color: red;">*</span></label>
                        <select id="kabupaten" name="kabupaten" class="form-control" required style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; background: white;">
                            <option value="">-- Pilih Kabupaten / Kota --</option>
                            <?php
                            $kabList = [
                                "KABUPATEN BANGKALAN", "KABUPATEN BANYUWANGI", "KABUPATEN BLITAR", "KABUPATEN BOJONEGORO", 
                                "KABUPATEN BONDOWOSO", "KABUPATEN GRESIK", "KABUPATEN JEMBER", "KABUPATEN JOMBANG", 
                                "KABUPATEN KEDIRI", "KABUPATEN LAMONGAN", "KABUPATEN LUMAJANG", "KABUPATEN MADIUN", 
                                "KABUPATEN MAGETAN", "KABUPATEN MALANG", "KABUPATEN MOJOKERTO", "KABUPATEN NGANJUK", 
                                "KABUPATEN NGAWI", "KABUPATEN PACITAN", "KABUPATEN PAMEKASAN", "KABUPATEN PASURUAN", 
                                "KABUPATEN PONOROGO", "KABUPATEN PROBOLINGGO", "KABUPATEN SAMPANG", "KABUPATEN SIDOARJO", 
                                "KABUPATEN SITUBONDO", "KABUPATEN SUMENEP", "KABUPATEN TRENGGALEK", "KABUPATEN TUBAN", 
                                "KABUPATEN TULUNGAGUNG", "KOTA BATU", "KOTA BLITAR", "KOTA KEDIRI", "KOTA MADIUN", 
                                "KOTA MALANG", "KOTA MOJOKERTO", "KOTA PASURUAN", "KOTA PROBOLINGGO", "KOTA SURABAYA"
                            ];
                            foreach ($kabList as $kab) {
                                $selected = (strtoupper($sub['kabupaten']) === $kab) ? 'selected' : '';
                                echo '<option value="' . $kab . '" ' . $selected . '>' . ucwords(strtolower(str_replace('KABUPATEN', 'Kab.', str_replace('KOTA', 'Kota', $kab)))) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="kategori" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Jenis Pendataan <span style="color: red;">*</span></label>
                        <select id="kategori" name="kategori" class="form-control" required style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; background: white;">
                            <option value="produk" <?php echo ($sub['kategori'] === 'produk') ? 'selected' : ''; ?>>ENtrepreneur</option>
                            <option value="digital" <?php echo ($sub['kategori'] === 'digital') ? 'selected' : ''; ?>>Digital</option>
                            <option value="ecoteologi" <?php echo ($sub['kategori'] === 'ecoteologi') ? 'selected' : ''; ?>>Ecoteologi</option>
                            <option value="karya" <?php echo ($sub['kategori'] === 'karya') ? 'selected' : ''; ?>>Karya</option>
                            <option value="aman" <?php echo ($sub['kategori'] === 'aman') ? 'selected' : ''; ?>>Aman</option>
                            <option value="ramah_anak" <?php echo ($sub['kategori'] === 'ramah_anak') ? 'selected' : ''; ?>>Ramah</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="alamat" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Alamat Lengkap Pesantren</label>
                    <textarea id="alamat" name="alamat" class="form-control" placeholder="Contoh: Jl. Raya Lirboyo, Mojoroto" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;" rows="2"><?php echo htmlspecialchars($sub['alamat'] ?? ''); ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Lokasi Peta (Klik Pada Peta untuk Mengubah Koordinat)</label>
                    <div id="map-select" style="height: 300px; border-radius: 8px; border: 1px solid #CBD5E1; margin-bottom: 15px; position: relative; z-index: 1;"></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label for="latitude" style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 5px;">Latitude</label>
                            <input type="text" id="latitude" name="latitude" class="form-control" readonly value="<?php echo htmlspecialchars($sub['latitude'] ?? ''); ?>" style="background: #F1F5F9; width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                        <div>
                            <label for="longitude" style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 5px;">Longitude</label>
                            <input type="text" id="longitude" name="longitude" class="form-control" readonly value="<?php echo htmlspecialchars($sub['longitude'] ?? ''); ?>" style="background: #F1F5F9; width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                    </div>
                </div>

                <!-- Dynamic Fields Container -->
                <div id="dynamic-fields" style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                    <h4 style="margin-top: 0; color: #0f172a; margin-bottom: 15px; font-size: 1rem; border-bottom: 1px solid #cbd5e1; padding-bottom: 8px;">Detail Kategori</h4>
                    
                    <!-- Entrepreneur Fields -->
                    <div id="fields-produk" class="cat-fields" style="display: none;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Nama Produk Inovasi</label>
                            <input type="text" name="produk_nama" class="form-control" value="<?php echo htmlspecialchars($details['produk_nama'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Kontak / Telepon</label>
                            <input type="text" name="produk_kontak" class="form-control" value="<?php echo htmlspecialchars($details['produk_kontak'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                        <div class="form-group">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Deskripsi Produk</label>
                            <textarea name="produk_deskripsi" class="form-control" rows="3" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;"><?php echo htmlspecialchars($details['produk_deskripsi'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Karya Fields -->
                    <div id="fields-karya" class="cat-fields" style="display: none;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Penulis / Santri / Kiai</label>
                            <input type="text" name="karya_penulis" class="form-control" value="<?php echo htmlspecialchars($details['karya_penulis'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Judul Karya / Kitab</label>
                            <input type="text" name="karya_judul" class="form-control" value="<?php echo htmlspecialchars($details['karya_judul'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                    </div>

                    <!-- Digital Fields -->
                    <div id="fields-digital" class="cat-fields" style="display: none;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Layanan Digital Utama</label>
                            <input type="text" name="digital_layanan" class="form-control" value="<?php echo htmlspecialchars($details['digital_layanan'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Website / Referensi</label>
                            <input type="text" name="digital_web" class="form-control" value="<?php echo htmlspecialchars($details['digital_web'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                    </div>

                    <!-- Ecoteologi Fields -->
                    <div id="fields-ecoteologi" class="cat-fields" style="display: none;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Program Lingkungan</label>
                            <input type="text" name="eco_program" class="form-control" value="<?php echo htmlspecialchars($details['eco_program'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                    </div>

                    <!-- Aman Fields -->
                    <div id="fields-aman" class="cat-fields" style="display: none;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Program Mitigasi Bencana</label>
                            <input type="text" name="aman_program" class="form-control" value="<?php echo htmlspecialchars($details['aman_program'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                    </div>

                    <!-- Ramah Anak Fields -->
                    <div id="fields-ramah_anak" class="cat-fields" style="display: none;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Program Ramah Anak</label>
                            <input type="text" name="ramah_program" class="form-control" value="<?php echo htmlspecialchars($details['ramah_program'] ?? ''); ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                    </div>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="btn-primary" style="padding: 12px 35px; font-size: 1rem; background-color: #10B981; border-color: #10B981;">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php 
$slot = ob_get_clean(); 
include __DIR__ . '/layouts/app.blade.php';
?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const catSelect = document.getElementById('kategori');
        const catFields = document.querySelectorAll('.cat-fields');

        function updateFields() {
            const val = catSelect.value;
            catFields.forEach(el => el.style.display = 'none');
            
            if (val) {
                const target = document.getElementById('fields-' + val);
                if(target) target.style.display = 'block';
            }
        }

        catSelect.addEventListener('change', updateFields);
        updateFields();

        // Leaflet Map Initialization
        var initialLat = <?php echo !empty($sub['latitude']) ? (float)$sub['latitude'] : -7.5360; ?>;
        var initialLng = <?php echo !empty($sub['longitude']) ? (float)$sub['longitude'] : 112.2384; ?>;
        var initialZoom = <?php echo !empty($sub['latitude']) ? 12 : 8; ?>;

        var map = L.map('map-select').setView([initialLat, initialLng], initialZoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker;

        <?php if (!empty($sub['latitude']) && !empty($sub['longitude'])): ?>
            marker = L.marker([initialLat, initialLng]).addTo(map);
        <?php endif; ?>

        map.on('click', function(e) {
            var lat = e.latlng.lat.toFixed(6);
            var lng = e.latlng.lng.toFixed(6);
            
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });
    });
</script>
