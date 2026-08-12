/**
 * PENDEKAR - Portal Integrasi Data Pesantren Jawa Timur
 * Public JS Engine: Leaflet Map, Filtering, Statistics & Chart Rendering
 */

document.addEventListener('DOMContentLoaded', () => {
    initDashboard();
});

let map;
let markersCluster;
let allPesantrenData = [];
let filteredData = [];
let kabChart, categoryChart;

async function initDashboard() {
    const mapElement = document.getElementById('map');
    if (mapElement) {
        initMap();
        // Immediately populate fallback data so map and charts are never empty on initial page load
        allPesantrenData = getDummyData();
        filteredData = [...allPesantrenData];
        populateRegionFilterOptions();
        updateDashboardUI();

        // Fetch real dataset asynchronously
        await loadPesantrenData();
    }
    setupEventListeners();
}

function initMap() {
    if (!document.getElementById('map')) return;
    if (map) return; // avoid re-initialization

    // Center map around East Java (Jawa Timur)
    map = L.map('map').setView([-7.5360639, 112.2384017], 8);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors | PENDEKAR JATIM'
    }).addTo(map);

    if (typeof L.markerClusterGroup === 'function') {
        markersCluster = L.markerClusterGroup({
            chunkedLoading: true,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true
        });
        map.addLayer(markersCluster);
    } else {
        markersCluster = L.layerGroup();
        map.addLayer(markersCluster);
    }
}

async function loadPesantrenData() {
    try {
        const response = await fetch('/api/pesantren');
        if (response.ok) {
            const data = await response.json();
            const fetched = Array.isArray(data) ? data : (data.data || []);
            if (fetched && fetched.length > 0) {
                allPesantrenData = fetched;
            }
        }
    } catch (e) {
        console.warn('Failed to fetch from /api/pesantren, retaining initial data:', e);
    }

    if (!allPesantrenData || allPesantrenData.length === 0) {
        allPesantrenData = getDummyData();
    }

    filteredData = [...allPesantrenData];
    populateRegionFilterOptions();
    updateDashboardUI(true);
}

function hasCategory(p, cat) {
    if (!p) return false;
    switch (cat) {
        case 'digital':
            if (typeof p.is_digital !== 'undefined') return Boolean(p.is_digital);
            if (p.digital && typeof p.digital === 'object') return Boolean(p.digital.is_digital);
            return p.digital === 1 || p.digital === true;
        case 'ecoteologi':
            if (typeof p.is_ecoteologi !== 'undefined') return Boolean(p.is_ecoteologi);
            if (p.ecoteologi && typeof p.ecoteologi === 'object') return Boolean(p.ecoteologi.is_ecoteologi);
            return p.ecoteologi === 1 || p.ecoteologi === true;
        case 'ramah':
        case 'ramah_anak':
            if (typeof p.is_ramah !== 'undefined') return Boolean(p.is_ramah);
            if (p.ramah_anak && typeof p.ramah_anak === 'object') return Boolean(p.ramah_anak.is_ramah_anak);
            return p.ramah === 1 || p.ramah === true || p.ramah_anak === 1 || p.ramah_anak === true;
        case 'opop':
        case 'entrepreneur':
            if (typeof p.is_opop !== 'undefined') return Boolean(p.is_opop);
            if (Array.isArray(p.opop)) return p.opop.length > 0;
            return p.opop === 1 || p.opop === true;
        case 'kitab':
        case 'karya_kitab':
            if (typeof p.is_kitab !== 'undefined') return Boolean(p.is_kitab);
            if (Array.isArray(p.karya_kitab)) return p.karya_kitab.length > 0;
            return p.kitab === 1 || p.kitab === true;
        default:
            return false;
    }
}

function populateRegionFilterOptions() {
    const regionSelect = document.getElementById('filter-region');
    if (!regionSelect) return;

    const currentVal = regionSelect.value;
    regionSelect.innerHTML = '<option value="">-- Semua Kabupaten/Kota --</option>';

    const regions = new Set();
    allPesantrenData.forEach(item => {
        const reg = item.kabupaten || item.kota || item.kab_kota;
        if (reg) regions.add(reg);
    });

    const sortedRegions = Array.from(regions).sort();
    sortedRegions.forEach(reg => {
        const opt = document.createElement('option');
        opt.value = reg;
        opt.textContent = reg;
        if (reg === currentVal) opt.selected = true;
        regionSelect.appendChild(opt);
    });
}

function animateValue(obj, start, end, duration) {
    if (!obj) return;
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const currentVal = Math.floor(progress * (end - start) + start);
        obj.textContent = currentVal.toLocaleString('id-ID');
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

function updateDashboardUI(animate = false) {
    renderMarkers();
    updateStatsCards(animate);
    renderCharts();
}

function updateStatsCards(animate = false) {
    const elTotal = document.getElementById('stat-total');
    const elOpop = document.getElementById('stat-opop');
    const elDig = document.getElementById('stat-dig');
    const elEco = document.getElementById('stat-eco');
    const elKitab = document.getElementById('stat-kitab');
    const elAman = document.getElementById('stat-aman');
    const elRamah = document.getElementById('stat-ramah');

    if (!elTotal || !allPesantrenData) return;

    let cDig = 0, cEco = 0, cRamah = 0, cOpop = 0, cKitab = 0;
    filteredData.forEach(p => {
        if (hasCategory(p, 'digital')) cDig++;
        if (hasCategory(p, 'ecoteologi')) cEco++;
        if (hasCategory(p, 'ramah')) cRamah++;
        if (hasCategory(p, 'opop')) cOpop++;
        if (hasCategory(p, 'kitab')) cKitab++;
    });

    const targetTotal = filteredData.length;
    const targetAman = filteredData.length;

    if (animate) {
        const parseVal = (el) => {
            if (!el) return 0;
            const val = parseInt(el.textContent.replace(/\./g, ''));
            return isNaN(val) ? 0 : val;
        };

        animateValue(elTotal, parseVal(elTotal), targetTotal, 1500);
        animateValue(elOpop, parseVal(elOpop), cOpop, 1500);
        animateValue(elDig, parseVal(elDig), cDig, 1500);
        animateValue(elEco, parseVal(elEco), cEco, 1500);
        animateValue(elKitab, parseVal(elKitab), cKitab, 1500);
        animateValue(elAman, parseVal(elAman), targetAman, 1500);
        animateValue(elRamah, parseVal(elRamah), cRamah, 1500);
    } else {
        elTotal.textContent = targetTotal.toLocaleString('id-ID');
        if (elOpop) elOpop.textContent = cOpop.toLocaleString('id-ID');
        if (elDig) elDig.textContent = cDig.toLocaleString('id-ID');
        if (elEco) elEco.textContent = cEco.toLocaleString('id-ID');
        if (elKitab) elKitab.textContent = cKitab.toLocaleString('id-ID');
        if (elAman) elAman.textContent = targetAman.toLocaleString('id-ID');
        if (elRamah) elRamah.textContent = cRamah.toLocaleString('id-ID');
    }
}

function renderMarkers() {
    if (!markersCluster) return;
    markersCluster.clearLayers();

    filteredData.forEach(p => {
        const lat = parseFloat(p.latitude || p.lat);
        const lng = parseFloat(p.longitude || p.lng || p.long);

        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0 && lat < 0 && lng > 100) {
            const marker = L.marker([lat, lng]);
            const popupContent = `
                <div style="font-family: 'Poppins', sans-serif; padding: 5px; max-width: 250px;">
                    <h4 style="color: #00A896; margin-bottom: 6px; font-size: 1rem;">${p.nama || p.nama_pesantren || 'Pesantren'}</h4>
                    <p style="font-size: 0.82rem; color: #6C757D; margin-bottom: 4px;"><strong>NSPP:</strong> ${p.nspp || '-'}</p>
                    <p style="font-size: 0.82rem; color: #6C757D; margin-bottom: 4px;"><strong>Kab/Kota:</strong> ${p.kabupaten || p.kota || '-'}</p>
                    <p style="font-size: 0.82rem; color: #6C757D; margin-bottom: 6px;"><strong>Kategori:</strong> ${getCategoryBadge(p)}</p>
                    <button onclick="showPesantrenDetail(this)" data-pesantren='${JSON.stringify(p).replace(/'/g, "&apos;")}' style="background: #00A896; color: white; border: none; padding: 4px 10px; border-radius: 4px; font-size: 0.78rem; cursor: pointer;">Lihat Detail</button>
                </div>
            `;
            marker.bindPopup(popupContent);
            markersCluster.addLayer(marker);
        }
    });
}

function getCategoryBadge(p) {
    const cats = [];
    if (hasCategory(p, 'digital')) cats.push('Digital');
    if (hasCategory(p, 'ecoteologi')) cats.push('Ecoteologi');
    if (hasCategory(p, 'ramah')) cats.push('Ramah Anak');
    if (hasCategory(p, 'opop')) cats.push('Entrepreneur');
    if (hasCategory(p, 'kitab')) cats.push('Karya Kitab');
    return cats.length > 0 ? cats.join(', ') : 'Pesantren Terdaftar';
}

function renderCharts() {
    const ctxKab = document.getElementById('chartKabupaten');
    const ctxCat = document.getElementById('chartCategory');

    if (!ctxKab || !ctxCat || typeof Chart === 'undefined') return;

    // Aggregate by region
    const regionCounts = {};
    filteredData.forEach(p => {
        const reg = p.kabupaten || p.kota || 'Lainnya';
        if (reg) {
            regionCounts[reg] = (regionCounts[reg] || 0) + 1;
        }
    });

    const topRegions = Object.entries(regionCounts)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 7);

    const kabLabels = topRegions.map(r => r[0]);
    const kabData = topRegions.map(r => r[1]);

    if (kabChart) kabChart.destroy();
    kabChart = new Chart(ctxKab, {
        type: 'bar',
        data: {
            labels: kabLabels.length > 0 ? kabLabels : ['Tanpa Data'],
            datasets: [{
                label: 'Jumlah Pesantren',
                data: kabData.length > 0 ? kabData : [0],
                backgroundColor: '#00A896',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Category distribution
    let cDig = 0, cEco = 0, cRamah = 0, cOpop = 0, cKitab = 0;
    filteredData.forEach(p => {
        if (hasCategory(p, 'digital')) cDig++;
        if (hasCategory(p, 'ecoteologi')) cEco++;
        if (hasCategory(p, 'ramah')) cRamah++;
        if (hasCategory(p, 'opop')) cOpop++;
        if (hasCategory(p, 'kitab')) cKitab++;
    });

    if (categoryChart) categoryChart.destroy();
    categoryChart = new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: ['Digital', 'Ecoteologi', 'Ramah Anak', 'Entrepreneur', 'Karya Kitab'],
            datasets: [{
                data: [cDig, cEco, cRamah, cOpop, cKitab],
                backgroundColor: ['#00A896', '#2D5D7C', '#38BDF8', '#F59E0B', '#10B981']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

function setupEventListeners() {
    const searchInput = document.getElementById('search-input');
    const filterRegion = document.getElementById('filter-region');
    const filterCategory = document.getElementById('filter-category');

    const applyFilters = () => {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedRegion = filterRegion ? filterRegion.value : '';
        const selectedCategory = filterCategory ? filterCategory.value : '';

        filteredData = allPesantrenData.filter(p => {
            const nameMatch = (p.nama || p.nama_pesantren || '').toLowerCase().includes(query) ||
                              (p.nspp || '').toString().includes(query);

            const reg = p.kabupaten || p.kota || p.kab_kota || '';
            const regionMatch = !selectedRegion || reg === selectedRegion;

            let categoryMatch = true;
            if (selectedCategory) {
                categoryMatch = hasCategory(p, selectedCategory);
            }

            return nameMatch && regionMatch && categoryMatch;
        });

        updateDashboardUI();
    };

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (filterRegion) filterRegion.addEventListener('change', applyFilters);
    if (filterCategory) filterCategory.addEventListener('change', applyFilters);
}

function getDummyData() {
    return [
        { nama: "PP. Lirboyo", nspp: "510035710001", kabupaten: "Kota Kediri", latitude: -7.8114, longitude: 112.0003, digital: 1, ecoteologi: 1, ramah: 1, opop: 1, kitab: 1 },
        { nama: "PP. Tebuireng", nspp: "510035170002", kabupaten: "Jombang", latitude: -7.5891, longitude: 112.2335, digital: 1, ecoteologi: 1, ramah: 1, opop: 1, kitab: 1 },
        { nama: "PP. Sidogiri", nspp: "510035140003", kabupaten: "Pasuruan", latitude: -7.6989, longitude: 112.8765, digital: 1, ecoteologi: 0, ramah: 1, opop: 1, kitab: 1 },
        { nama: "PP. Nurul Jadid", nspp: "510035130004", kabupaten: "Probolinggo", latitude: -7.7812, longitude: 113.4891, digital: 1, ecoteologi: 1, ramah: 1, opop: 0, kitab: 1 },
        { nama: "PP. Salafiyah Syafi'iyah", nspp: "510035120005", kabupaten: "Situbondo", latitude: -7.7423, longitude: 114.2389, digital: 0, ecoteologi: 1, ramah: 1, opop: 1, kitab: 1 },
        { nama: "PP. Sunan Drajat", nspp: "510035240006", kabupaten: "Lamongan", latitude: -6.8791, longitude: 112.3561, digital: 1, ecoteologi: 1, ramah: 0, opop: 1, kitab: 1 },
        { nama: "PP. Gontor Kampus 2", nspp: "510035020007", kabupaten: "Ponorogo", latitude: -7.8689, longitude: 111.4623, digital: 1, ecoteologi: 0, ramah: 1, opop: 1, kitab: 1 },
        { nama: "PP. Amanatul Ummah", nspp: "510035160008", kabupaten: "Mojokerto", latitude: -7.6189, longitude: 112.5412, digital: 1, ecoteologi: 1, ramah: 1, opop: 1, kitab: 0 }
    ];
}

