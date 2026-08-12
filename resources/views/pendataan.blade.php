<?php ob_start(); ?>

<section style="padding: 60px 0; background-color: #F8FAFC;">
    <div class="container" style="max-width: 800px;">
        <div class="section-title">
            <h2>Pendataan "Pendekar"</h2>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div style="background-color: #D1FAE5; color: #065F46; padding: 16px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-circle-check" style="font-size: 1.2rem;"></i>
                <span><?php echo htmlspecialchars($successMessage); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div style="background-color: #FEE2E2; color: #991B1B; padding: 16px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-circle-xmark" style="font-size: 1.2rem;"></i>
                <span><?php echo htmlspecialchars($errorMessage); ?></span>
            </div>
        <?php endif; ?>

        <div style="background: white; border-radius: 12px; padding: 35px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
            <form action="/pendataan" method="POST">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nama_pesantren" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Nama Pesantren <span style="color: red;">*</span></label>
                    <input type="text" id="nama_pesantren" name="nama_pesantren" class="form-control" required placeholder="Contoh: PP. Lirboyo">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="nspp" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">NSPP (Nomor Statistik) <span style="color: red;">*</span></label>
                        <input type="text" id="nspp" name="nspp" class="form-control" required placeholder="12 Digit NSPP">
                    </div>
                    <div class="form-group">
                        <label for="kabupaten" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Kabupaten / Kota <span style="color: red;">*</span></label>
                        <select id="kabupaten" name="kabupaten" class="form-control" required style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; background: white;">
                            <option value="">-- Pilih Kabupaten / Kota --</option>
                            <option value="KABUPATEN BANGKALAN">Kabupaten Bangkalan</option>
                            <option value="KABUPATEN BANYUWANGI">Kabupaten Banyuwangi</option>
                            <option value="KABUPATEN BLITAR">Kabupaten Blitar</option>
                            <option value="KABUPATEN BOJONEGORO">Kabupaten Bojonegoro</option>
                            <option value="KABUPATEN BONDOWOSO">Kabupaten Bondowoso</option>
                            <option value="KABUPATEN GRESIK">Kabupaten Gresik</option>
                            <option value="KABUPATEN JEMBER">Kabupaten Jember</option>
                            <option value="KABUPATEN JOMBANG">Kabupaten Jombang</option>
                            <option value="KABUPATEN KEDIRI">Kabupaten Kediri</option>
                            <option value="KABUPATEN LAMONGAN">Kabupaten Lamongan</option>
                            <option value="KABUPATEN LUMAJANG">Kabupaten Lumajang</option>
                            <option value="KABUPATEN MADIUN">Kabupaten Madiun</option>
                            <option value="KABUPATEN MAGETAN">Kabupaten Magetan</option>
                            <option value="KABUPATEN MALANG">Kabupaten Malang</option>
                            <option value="KABUPATEN MOJOKERTO">Kabupaten Mojokerto</option>
                            <option value="KABUPATEN NGANJUK">Kabupaten Nganjuk</option>
                            <option value="KABUPATEN NGAWI">Kabupaten Ngawi</option>
                            <option value="KABUPATEN PACITAN">Kabupaten Pacitan</option>
                            <option value="KABUPATEN PAMEKASAN">Kabupaten Pamekasan</option>
                            <option value="KABUPATEN PASURUAN">Kabupaten Pasuruan</option>
                            <option value="KABUPATEN PONOROGO">Kabupaten Ponorogo</option>
                            <option value="KABUPATEN PROBOLINGGO">Kabupaten Probolinggo</option>
                            <option value="KABUPATEN SAMPANG">Kabupaten Sampang</option>
                            <option value="KABUPATEN SIDOARJO">Kabupaten Sidoarjo</option>
                            <option value="KABUPATEN SITUBONDO">Kabupaten Situbondo</option>
                            <option value="KABUPATEN SUMENEP">Kabupaten Sumenep</option>
                            <option value="KABUPATEN TRENGGALEK">Kabupaten Trenggalek</option>
                            <option value="KABUPATEN TUBAN">Kabupaten Tuban</option>
                            <option value="KABUPATEN TULUNGAGUNG">Kabupaten Tulungagung</option>
                            <option value="KOTA BATU">Kota Batu</option>
                            <option value="KOTA BLITAR">Kota Blitar</option>
                            <option value="KOTA KEDIRI">Kota Kediri</option>
                            <option value="KOTA MADIUN">Kota Madiun</option>
                            <option value="KOTA MALANG">Kota Malang</option>
                            <option value="KOTA MOJOKERTO">Kota Mojokerto</option>
                            <option value="KOTA PASURUAN">Kota Pasuruan</option>
                            <option value="KOTA PROBOLINGGO">Kota Probolinggo</option>
                            <option value="KOTA SURABAYA">Kota Surabaya</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="kategori" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Jenis Pendataan <span style="color: red;">*</span></label>
                        <select id="kategori" name="kategori" class="form-control" required style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; background: white;">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="produk" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'produk') ? 'selected' : ''; ?>>ENtrepreneur</option>
                            <option value="digital" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'digital') ? 'selected' : ''; ?>>Digital</option>
                            <option value="ecoteologi" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'ecoteologi') ? 'selected' : ''; ?>>Ecoteologi</option>
                            <option value="karya" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'karya') ? 'selected' : ''; ?>>Karya</option>
                            <option value="aman" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'aman') ? 'selected' : ''; ?>>Aman</option>
                            <option value="ramah_anak" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'ramah_anak') ? 'selected' : ''; ?>>Ramah</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="alamat" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Alamat Lengkap Pesantren</label>
                    <textarea id="alamat" name="alamat" class="form-control" placeholder="Contoh: Jl. Raya Lirboyo, Mojoroto" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;" rows="2"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Lokasi Peta (Klik Pada Peta untuk Menentukan Koordinat)</label>
                    <div id="map-select" style="height: 320px; border-radius: 8px; border: 1px solid #CBD5E1; margin-bottom: 15px; position: relative; z-index: 1;"></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label for="latitude" style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 5px;">Latitude</label>
                            <input type="text" id="latitude" name="latitude" class="form-control" readonly placeholder="Klik pada peta" style="background: #F1F5F9; width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                        <div>
                            <label for="longitude" style="display: block; font-size: 0.85rem; color: #475569; margin-bottom: 5px;">Longitude</label>
                            <input type="text" id="longitude" name="longitude" class="form-control" readonly placeholder="Klik pada peta" style="background: #F1F5F9; width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                    </div>
                </div>

                <!-- Dynamic Fields Container -->
                <div id="dynamic-fields" style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 25px; display: none;">
                    <h4 style="margin-top: 0; color: #0f172a; margin-bottom: 15px; font-size: 1rem; border-bottom: 1px solid #cbd5e1; padding-bottom: 8px;">Detail Pendataan</h4>
                    
                    <!-- Entrepreneur Fields -->
                    <div id="fields-produk" class="cat-fields" style="display: none;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Nama Produk Inovasi</label>
                            <input type="text" name="produk_nama" class="form-control" placeholder="Contoh: Kopi Santri, Kerajinan Tangan" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Kontak / Telepon</label>
                            <input type="text" name="produk_kontak" class="form-control" placeholder="Nomor WhatsApp/Telepon pemasaran" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                        <div class="form-group">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Deskripsi Produk</label>
                            <textarea name="produk_deskripsi" class="form-control" rows="3" placeholder="Jelaskan produk secara singkat..." style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;"></textarea>
                        </div>
                    </div>

                    <!-- Karya Fields -->
                    <div id="fields-karya" class="cat-fields" style="display: none;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Penulis / Santri / Kiai</label>
                            <input type="text" name="karya_penulis" class="form-control" placeholder="Nama pengarang" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Judul Karya / Kitab</label>
                            <input type="text" name="karya_judul" class="form-control" placeholder="Masukkan judul karya ilmiah atau kitab" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                    </div>

                    <!-- Digital Fields -->
                    <div id="fields-digital" class="cat-fields" style="display: none;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Layanan Digital Utama</label>
                            <input type="text" name="digital_layanan" class="form-control" placeholder="Contoh: E-learning, Pembayaran Cashless" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Website / Referensi</label>
                            <input type="text" name="digital_web" class="form-control" placeholder="https://..." style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                    </div>

                    <!-- Ecoteologi Fields -->
                    <div id="fields-ecoteologi" class="cat-fields" style="display: none;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Program Lingkungan</label>
                            <input type="text" name="eco_program" class="form-control" placeholder="Contoh: Bank Sampah, Fikih Bi'ah" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                    </div>

                    <!-- Aman Fields -->
                    <div id="fields-aman" class="cat-fields" style="display: none;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Program Mitigasi Bencana</label>
                            <input type="text" name="aman_program" class="form-control" placeholder="Contoh: Satgas Bencana Pesantren" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                    </div>

                    <!-- Ramah Anak Fields -->
                    <div id="fields-ramah_anak" class="cat-fields" style="display: none;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Program Ramah Anak</label>
                            <input type="text" name="ramah_program" class="form-control" placeholder="Contoh: Satgas Anti Bullying" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                        </div>
                    </div>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="btn-primary" style="padding: 12px 35px; font-size: 1rem;">
                        <i class="fas fa-paper-plane"></i> Kirim Pendataan
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
        const container = document.getElementById('dynamic-fields');
        const catFields = document.querySelectorAll('.cat-fields');

        function updateFields() {
            const val = catSelect.value;
            // Hide all
            catFields.forEach(el => el.style.display = 'none');
            
            if (val) {
                container.style.display = 'block';
                const target = document.getElementById('fields-' + val);
                if(target) target.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }

        catSelect.addEventListener('change', updateFields);
        // Run on load in case a category is pre-selected
        updateFields();

        // Leaflet Map Initialization
        var map = L.map('map-select').setView([-7.5360, 112.2384], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker;

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
