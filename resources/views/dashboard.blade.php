<?php ob_start(); ?>

<!-- Hero Section -->
<section class="hero" id="home" style="position: relative; overflow: hidden;">
    <!-- Background Image removed as it is now in the container -->
    
    <div class="container" style="position: relative; z-index: 1;">
        <div class="hero-container">
            <div class="hero-content">
                <span class="hero-tag"><i class="fas fa-star me-1"></i> Pusat Data & Inovasi Pesantren Jawa Timur</span>
                <h1>Membangun Generasi dan Peradaban Pesantren Jawa Timur yang Mandiri, Cerdas, dan Tangguh</h1>
                <p>Ekosistem Terintegrasi Pendataan Pondok Entrepreneur, Digital, Ekoteologi, Karya/Kitab, Aman, dan Ramah</p>

            </div>
            <div class="hero-image-container" style="display: flex; justify-content: center; align-items: flex-end; height: 100%;">
                <img src="<?php echo url('/assets/images/p_imam.png'); ?>" alt="Person" style="max-height: 100%; max-width: 100%; object-fit: contain; drop-shadow: 0 10px 20px rgba(0,0,0,0.1);">
            </div>
        </div>
    </div>
</section>


<!-- Portal Integrasi Data & Peta Live -->
<section class="portal-dashboard" id="portal">
    <div class="container">
        <div class="section-title">
            <h2>Portal Integrasi Data Pesantren</h2>
            <p>Peta sebaran & statistik pesantren se-Jawa Timur</p>
        </div>

        <!-- Total Pesantren Card -->
        <div style="display: flex; justify-content: center; margin-bottom: 20px;">
            <div class="stat-card-live" style="border-left-color: #00A896; min-width: 250px; text-align: center;">
                <span class="title" style="text-align: center;">Total Pesantren</span>
                <span class="value" id="stat-total" style="text-align: center; justify-content: center;"><?php echo number_format($stats['total_pesantren'] ?? 0); ?></span>
            </div>
        </div>

        <!-- Stats Cards Grid -->
        <div class="stats-grid-live">
            <div class="stat-card-live" style="border-left-color: #6366F1;">
                <span class="title">Enterpreneur</span>
                <span class="value" id="stat-opop"><?php echo number_format($stats['enterpreneur'] ?? 0); ?></span>
            </div>
            <div class="stat-card-live" style="border-left-color: #38BDF8;">
                <span class="title">Digital</span>
                <span class="value" id="stat-dig"><?php echo number_format($stats['digital'] ?? 0); ?></span>
            </div>
            <div class="stat-card-live" style="border-left-color: #10B981;">
                <span class="title">Ecoteologi</span>
                <span class="value" id="stat-eco"><?php echo number_format($stats['ecoteologi'] ?? 0); ?></span>
            </div>
            <div class="stat-card-live" style="border-left-color: #EC4899;">
                <span class="title">Karya Kitab</span>
                <span class="value" id="stat-kitab"><?php echo number_format($stats['karya_kitab'] ?? 0); ?></span>
            </div>
            <div class="stat-card-live" style="border-left-color: #EF4444;">
                <span class="title">Aman</span>
                <span class="value" id="stat-aman"><?php echo number_format($stats['aman'] ?? 0); ?></span>
            </div>
            <div class="stat-card-live" style="border-left-color: #F59E0B;">
                <span class="title">Ramah Anak</span>
                <span class="value" id="stat-ramah"><?php echo number_format($stats['ramah_anak'] ?? 0); ?></span>
            </div>
        </div>

        <!-- Filter & Map Grid -->
        <div class="dashboard-grid">
            <!-- Filter Panel -->
            <div class="filter-card">
                <h3><i class="fas fa-filter"></i> Filter & Pencarian</h3>
                <div class="form-group">
                    <label for="search-input">Cari Nama / NSPP Pesantren</label>
                    <input type="text" id="search-input" class="form-control" placeholder="Ketik nama pesantren atau NSPP...">
                </div>
                <div class="form-group">
                    <label for="filter-region">Kabupaten / Kota</label>
                    <select id="filter-region" class="form-control">
                        <option value="">-- Semua Kabupaten/Kota --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filter-category">Kategori / Program</label>
                    <select id="filter-category" class="form-control">
                        <option value="">-- Semua Kategori --</option>
                        <option value="digital">Digital Pesantren</option>
                        <option value="ecoteologi">Ecoteologi</option>
                        <option value="ramah">Ramah Anak</option>
                        <option value="opop">Enterpreneur / OPOP</option>
                        <option value="kitab">Karya Kitab / Ilmiah</option>
                    </select>
                </div>
            </div>

            <!-- Map Panel -->
            <div class="map-card">
                <div id="map"></div>
            </div>
        </div>

        <!-- Analytics Charts -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3><i class="fas fa-chart-bar"></i> Sebaran Wilayah Kabupaten/Kota</h3>
                <canvas id="chartKabupaten" height="200"></canvas>
            </div>
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Proporsi Kategori Inovasi</h3>
                <canvas id="chartCategory" height="200"></canvas>
            </div>
        </div>
    </div>
</section>




<?php 
$slot = ob_get_clean(); 
include __DIR__ . '/layouts/app.blade.php';
?>
