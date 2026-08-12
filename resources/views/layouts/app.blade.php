<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta property="og:title" content="PENDEKAR | PD PONTREN JAWA TIMUR">
    <meta property="og:description" content="PENDEKAR">
    <meta property="og:url" content="http://pendekar.my.id">
    <meta property="og:type" content="website">

    <title><?php echo $pageTitle ?? 'PENDEKAR | PD PONTREN JAWA TIMUR'; ?></title>
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet Map & MarkerCluster CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- App Theme Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="/" class="logo">
                <div class="logo-icon-box">
                    <i class="fas fa-book-quran"></i>
                </div>
                <div class="logo-text">
                    <h1>PENDEKAR</h1>
                </div>
            </a>
            <ul class="nav-menu">
                <li><a href="/#home">Beranda</a></li>
                <li><a href="/rekap/pesantren"><b style="font-size: 1.3em; color: #FCA311;">P</b>esantren</a></li>
                <li><a href="/rekap/produk"><b style="font-size: 1.3em; color: #FCA311;">EN</b>trepreneur</a></li>
                <li><a href="/rekap/digital"><b style="font-size: 1.3em; color: #FCA311;">D</b>igital</a></li>
                <li><a href="/rekap/ecoteologi"><b style="font-size: 1.3em; color: #FCA311;">E</b>coteologi</a></li>
                <li><a href="/rekap/kitab"><b style="font-size: 1.3em; color: #FCA311;">K</b>arya</a></li>
                <li><a href="/rekap/bencana"><b style="font-size: 1.3em; color: #FCA311;">A</b>man</a></li>
                <li><a href="/rekap/ramah"><b style="font-size: 1.3em; color: #FCA311;">R</b>amah</a></li>
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="/admin" style="color: #10B981;"><i class="fas fa-lock-open"></i> Admin</a></li>
                    <?php endif; ?>
                    <li><a href="/verifikator" style="color: #FCA311;"><i class="fas fa-clipboard-check"></i> Verifikasi</a></li>
                    <li><a href="/logout" style="color: #EF4444;"><i class="fas fa-right-from-bracket"></i> Keluar</a></li>
                <?php else: ?>
                    <li><a href="/login" style="color: #E2E8F0; background-color: #2D5D7C; padding: 5px 12px; border-radius: 4px;"><i class="fas fa-right-to-bracket"></i> Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-wrapper">
        <?php if (isset($slot)) { echo $slot; } ?>
    </div>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-about">
                    <div class="footer-logo">
                        <h2>PENDEKAR</h2>
                    </div>
                    <p>Sistem ini mendedikasikan diri untuk menghimpun, mengelola, dan mempublikasikan data keunggulan pesantren—mulai dari kemandirian ekonomi (Entrepreneur), transformasi tata kelola (Digital), kepedulian lingkungan (Ekoteologi), pelestarian literasi ulama (Karya/Kitab), hingga budaya mitigasi bencana dan program pesantren perlindungan anak (Aman & Ramah).</p>
                </div>
                <div class="footer-links">
                    <h3>Integrasi Data</h3>
                    <ul>
                        <li><a href="/rekap/pesantren">Data Pesantren Jatim</a></li>
                        <li><a href="/rekap/bencana">Risiko & Kerawanan Bencana</a></li>
                        <li><a href="/rekap/ramah">Pesantren Ramah Anak</a></li>
                        <li><a href="/rekap/ecoteologi">Program Ecoteologi</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3>Layanan & Rekap</h3>
                    <ul>
                        <li><a href="/pendataan">Isi Pendataan Baru</a></li>
                        <li><a href="/rekap/produk">Data Produk Santri (OPOP)</a></li>
                        <li><a href="/rekap/kitab">Data Karya / Kitab Pesantren</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> PD Pontren Kanwil Jatim. | Developed by hakim.luk81@gmail.com</p>
                <p style="margin-top: 10px; font-size: 0.85rem; color: #a0aec0;">Peringatan: Data yang terdapat pada situs ini hanya sebagai informasi saja dan dapat berubah sewaktu-waktu, berhati-hatilah untuk tidak menggunakan data ini sebagai referensi.</p>
            </div>
        </div>
    </footer>

    <style>
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
        }
        .modal-title {
            color: #2D5D7C;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .modal-detail-row {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .modal-detail-row strong {
            display: inline-block;
            width: 150px;
            color: #495057;
        }
    </style>

    <div id="detailModal" class="modal-overlay" onclick="if(event.target === this) this.classList.remove('active')">
        <div class="modal-content">
            <span class="modal-close" onclick="document.getElementById('detailModal').classList.remove('active')">&times;</span>
            <div class="modal-title" id="modalTitle">Detail Pesantren</div>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
    function showPesantrenDetail(el) {
        try {
            var data = JSON.parse(el.getAttribute('data-pesantren'));
            
            document.getElementById('modalTitle').innerText = data.nama || 'Detail Pesantren';
            
            var html = '';
            var addRow = function(label, value) {
                if (value) {
                    html += '<div class="modal-detail-row"><strong>' + label + '</strong>: ' + value + '</div>';
                }
            };
            
            addRow('NSPP', data.nspp);
            addRow('Jenjang', data.jenjang);
            addRow('Afiliasi', data.afiliasi);
            addRow('Alamat', data.alamat);
            addRow('Kecamatan', data.kecamatan);
            addRow('Kabupaten/Kota', data.kabupaten);
            addRow('Provinsi', data.provinsi);
            
            if (data.latitude && data.longitude) {
                var mapLink = '<a href="https://www.google.com/maps/search/?api=1&query=' + data.latitude + ',' + data.longitude + '" target="_blank" style="color: #00A896; text-decoration: none;"><i class="fas fa-map-marker-alt"></i> Lihat di Peta</a>';
                addRow('Koordinat', data.latitude + ', ' + data.longitude + ' (' + mapLink + ')');
            }
            
            if (data.bencana && data.bencana.level_kerawanan) {
                html += '<h4 style="margin-top:20px; color:#2D5D7C;">Data Bencana</h4>';
                addRow('Level Kerawanan', data.bencana.level_kerawanan);
                addRow('Tipe Terdekat', data.bencana.closest_disaster_type);
            }
            
            if (data.ramah_anak && data.ramah_anak.is_ramah_anak) {
                html += '<h4 style="margin-top:20px; color:#2D5D7C;">Program Ramah Anak</h4>';
                addRow('Program', data.ramah_anak.program);
                addRow('Kurikulum', data.ramah_anak.kurikulum);
            }
            
            if (data.ecoteologi && data.ecoteologi.is_ecoteologi) {
                html += '<h4 style="margin-top:20px; color:#2D5D7C;">Program Ecoteologi</h4>';
                addRow('Program', data.ecoteologi.program);
                addRow('Fokus', data.ecoteologi.fokus);
            }
            
            if (data['Layanan Digital'] || data['Dampak Kualitas']) {
                html += '<h4 style="margin-top:20px; color:#2D5D7C;">Detail Digitalisasi</h4>';
                addRow('Unggulan Aplikasi', data['Unggulan Aplikasi']);
                addRow('Layanan Digital', data['Layanan Digital']);
                addRow('Dampak Kualitas', data['Dampak Kualitas']);
                if (data['Website/Referensi']) {
                    addRow('Website/Referensi', '<a href="' + data['Website/Referensi'] + '" target="_blank">' + data['Website/Referensi'] + '</a>');
                }
            }
            
            document.getElementById('modalBody').innerHTML = html;
            document.getElementById('detailModal').classList.add('active');
        } catch (e) {
            console.error('Error parsing pesantren data', e);
        }
    }
    </script>

    <!-- Leaflet JS & App JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
