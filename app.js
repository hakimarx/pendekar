// Application State
let pesantrenData = [];
let filteredData = [];
let currentPage = 1;
const itemsPerPage = 8;

let map;
let markerClusterGroup;
let activeMarkersMap = new Map(); // Keep track of active markers by NSPP

let focusedCircle = null;
let disasterMarkers = [];
let polylines = [];

let activeChart = null;
let currentChartType = 'risk'; // 'risk' or 'afiliasi'

// Elements
const elTotal = document.getElementById('stat-total');
const elDig = document.getElementById('stat-dig');
const elEco = document.getElementById('stat-eco');
const elRamah = document.getElementById('stat-ramah');
const elOpop = document.getElementById('stat-opop');
const elKitab = document.getElementById('stat-kitab');

const elSearch = document.getElementById('search-input');
const elFilterKab = document.getElementById('filter-kabupaten');
const elFilterKerawanan = document.getElementById('filter-kerawanan');
const elChkDig = document.getElementById('chk-digital');
const elChkEco = document.getElementById('chk-eco');
const elChkRamah = document.getElementById('chk-ramah');
const elChkOpop = document.getElementById('chk-opop');
const elChkKitab = document.getElementById('chk-kitab');
const elBtnReset = document.getElementById('btn-reset-filters');

const elListCount = document.getElementById('list-count');
const elListScroll = document.getElementById('pesantren-list');
const elBtnPrev = document.getElementById('btn-prev');
const elBtnNext = document.getElementById('btn-next');
const elPageInfo = document.getElementById('page-info');

const elModal = document.getElementById('detail-modal');
const elModalClose = document.getElementById('modal-close');
const elModalBody = document.getElementById('modal-body-content');

// 1. Initialize Map
function initMap() {
    // Center on East Java
    map = L.map('map', {
        zoomControl: true,
        maxZoom: 18,
        minZoom: 7
    }).setView([-7.7, 112.6], 8.5);

    // CartoDB Dark Matter Base Map
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    // Initialize marker cluster group
    markerClusterGroup = L.markerClusterGroup({
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        maxClusterRadius: 45
    });
    map.addLayer(markerClusterGroup);
}

// 2. Load Data
async function loadData() {
    try {
        const response = await fetch('/api/pesantren');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        let rawData = await response.json();
        // Handle if rawData is an object (dictionary) instead of array
        if (!Array.isArray(rawData)) {
            pesantrenData = Object.values(rawData);
        } else {
            pesantrenData = rawData;
        }
        filteredData = [...pesantrenData];
        
        console.log(`Loaded ${pesantrenData.length} pesantren records.`);
        
        populateKabupatenDropdown();        // Stat tracking is now handled by PHP via DashboardController
        // updateStats(); // Removed to prevent overriding PHP injected stats
        applyFilters();
        initChart();
    } catch (error) {
        console.error("Failed to load pesantren data:", error);
        elListScroll.innerHTML = `<div class="loading-spinner" style="color: var(--color-sangat-tinggi);">Gagal memuat data. Silakan refresh halaman.</div>`;
    }
}

// Populate Kabupaten Dropdown
function populateKabupatenDropdown() {
    // Get unique sorted regency names
    const kabupatens = [...new Set(pesantrenData.map(p => p.kabupaten))].sort();
    kabupatens.forEach(kab => {
        const option = document.createElement('option');
        option.value = kab;
        option.textContent = kab;
        elFilterKab.appendChild(option);
    });
}

// Stats are now populated server-side to ensure precise matching with actual menu data counts.
// 4. Filtering Logic
function applyFilters() {
    const searchVal = elSearch.value.toLowerCase().trim();
    const kabVal = elFilterKab.value;
    const kerawananVal = elFilterKerawanan.value;
    const chkDig = elChkDig.checked;
    const chkEco = elChkEco.checked;
    const chkRamah = elChkRamah.checked;
    const chkOpop = elChkOpop.checked;
    const chkKitab = elChkKitab.checked;

    filteredData = pesantrenData.filter(p => {
        // Search text: match name, NSPP, or OPOP product names
        if (searchVal) {
            const matchName = p.nama.toLowerCase().includes(searchVal);
            const matchNspp = p.nspp.includes(searchVal);
            const matchProduct = p.opop && p.opop.some(prod => prod.nama_produk.toLowerCase().includes(searchVal));
            if (!matchName && !matchNspp && !matchProduct) return false;
        }

        // Kabupaten
        if (kabVal && p.kabupaten !== kabVal) return false;

        // Level Kerawanan
        if (kerawananVal && p.bencana.level_kerawanan !== kerawananVal) return false;

        // Pillar Checkboxes
        if (chkDig && !(p.is_digital || (p.digital && (p.digital.is_digital || p.digital === 1)))) return false;
        if (chkEco && !(p.is_ecoteologi || (p.ecoteologi && (p.ecoteologi.is_ecoteologi || p.ecoteologi === 1)))) return false;
        if (chkRamah && !(p.is_ramah || (p.ramah_anak && (p.ramah_anak.is_ramah_anak || p.ramah_anak === 1)))) return false;
        if (chkOpop && !(p.is_opop || (Array.isArray(p.opop) && p.opop.length > 0) || p.opop === 1)) return false;
        if (chkKitab && !(p.is_kitab || (Array.isArray(p.karya_kitab) && p.karya_kitab.length > 0) || p.karya_kitab === 1)) return false;

        return true;
    });

    currentPage = 1;
    elListCount.textContent = `${filteredData.length.toLocaleString('id-ID')} Ditemukan`;
    
    clearDisasterOverlay();
    updateList();
    updateMapMarkers();
    updateChartData();
}

// Reset Filters
elBtnReset.addEventListener('click', () => {
    elSearch.value = "";
    elFilterKab.value = "";
    elFilterKerawanan.value = "";
    elChkDig.checked = false;
    elChkEco.checked = false;
    elChkRamah.checked = false;
    elChkOpop.checked = false;
    elChkKitab.checked = false;
    clearDisasterOverlay();
    applyFilters();
});

// Event Listeners for Filters
elSearch.addEventListener('input', applyFilters);
elFilterKab.addEventListener('change', applyFilters);
elFilterKerawanan.addEventListener('change', applyFilters);
elChkDig.addEventListener('change', applyFilters);
elChkEco.addEventListener('change', applyFilters);
elChkRamah.addEventListener('change', applyFilters);
elChkOpop.addEventListener('change', applyFilters);
elChkKitab.addEventListener('change', applyFilters);

// 5. Update Map Markers
function updateMapMarkers() {
    markerClusterGroup.clearLayers();
    activeMarkersMap.clear();

    const markersToCluster = [];

    filteredData.forEach(p => {
        // Only map those with coordinates
        if (p.latitude !== null && p.longitude !== null && !isNaN(p.latitude) && !isNaN(p.longitude)) {
            // Determine color class based on level_kerawanan
            let colorClass = 'a'; // default aman
            const lk = p.bencana.level_kerawanan;
            if (lk === 'SANGAT TINGGI') colorClass = 'st';
            else if (lk === 'TINGGI') colorClass = 't';
            else if (lk === 'SEDANG') colorClass = 'sd';
            else if (lk === 'RENDAH') colorClass = 'r';
            else if (lk === 'TIDAK ADA RIWAYAT BENCANA') colorClass = 'a';
            
            // Custom icon based on risk level
            const customIcon = L.divIcon({
                className: 'custom-pin-container',
                html: `<div class="custom-pin ${colorClass}"></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 24],
                popupAnchor: [0, -20]
            });

            const marker = L.marker([p.latitude, p.longitude], { icon: customIcon });

            // Popup HTML
            let badgesHtml = '';
            if (p.digital.is_digital) badgesHtml += `<span class="badge dig">D</span> `;
            if (p.ecoteologi.is_ecoteologi) badgesHtml += `<span class="badge eco">E</span> `;
            if (p.ramah_anak.is_ramah_anak) badgesHtml += `<span class="badge ramah">RA</span> `;
            if (p.opop && p.opop.length > 0) badgesHtml += `<span class="badge opop">O</span> `;
            if (p.karya_kitab && p.karya_kitab.length > 0) badgesHtml += `<span class="badge" style="background: rgba(0, 168, 150, 0.2); color: var(--accent-kitab); padding: 0.1rem 0.3rem; border-radius: 4px; font-size: 0.6rem; font-weight: bold; border: 1px solid rgba(0, 168, 150, 0.4)">K</span> `;

            const popupContent = `
                <div class="popup-content">
                    <h3>${p.nama}</h3>
                    <p style="font-size:0.7rem; color:var(--text-muted); margin-bottom:0.15rem;">NSPP: ${p.nspp}</p>
                    <p>${p.kecamatan}, ${p.kabupaten}</p>
                    <div style="margin-bottom:0.6rem;">${badgesHtml}</div>
                    <a href="#" class="popup-link-btn" onclick="openDetailModal('${p.nspp}'); return false;">Lihat Detail</a>
                </div>
            `;
            marker.bindPopup(popupContent);
            
            markersToCluster.push(marker);
            activeMarkersMap.set(p.nspp, marker);
        }
    });

    markerClusterGroup.addLayers(markersToCluster);
}

// 6. Update List View (Paginated)
function updateList() {
    elListScroll.innerHTML = "";

    if (filteredData.length === 0) {
        elListScroll.innerHTML = `<div class="loading-spinner" style="background:none;">Data tidak ditemukan.</div>`;
        elBtnPrev.disabled = true;
        elBtnNext.disabled = true;
        elPageInfo.textContent = "Halaman 0 dari 0";
        return;
    }

    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    if (currentPage > totalPages) currentPage = totalPages;

    const startIdx = (currentPage - 1) * itemsPerPage;
    const endIdx = Math.min(startIdx + itemsPerPage, filteredData.length);
    const paginatedItems = filteredData.slice(startIdx, endIdx);

    paginatedItems.forEach(p => {
        const item = document.createElement('div');
        item.className = 'pesantren-item';
        
        // Badges construction
        let badgesHtml = '';
        
        // Risk badge
        const lk = p.bencana.level_kerawanan;
        if (lk === 'SANGAT TINGGI') badgesHtml += `<span class="badge risk-st">Sangat Rawan</span> `;
        else if (lk === 'TINGGI') badgesHtml += `<span class="badge risk-t">Rawan Tinggi</span> `;
        else if (lk === 'SEDANG') badgesHtml += `<span class="badge risk-sd">Rawan Sedang</span> `;
        else if (lk === 'RENDAH') badgesHtml += `<span class="badge risk-r">Rawan Rendah</span> `;
        else if (lk === 'TIDAK ADA RIWAYAT BENCANA') badgesHtml += `<span class="badge risk-a">Aman</span> `;
        else if (lk === 'TIDAK ADA DATA KOORDINAT') badgesHtml += `<span class="badge risk-nc">Tanpa Koordinat</span> `;

        if (p.digital.is_digital) badgesHtml += `<span class="badge dig">Digital</span> `;
        if (p.ecoteologi.is_ecoteologi) badgesHtml += `<span class="badge eco">Eco</span> `;
        if (p.ramah_anak.is_ramah_anak) badgesHtml += `<span class="badge ramah">Ramah Anak</span> `;
        if (p.opop && p.opop.length > 0) badgesHtml += `<span class="badge opop">OPOP</span> `;
        if (p.karya_kitab && p.karya_kitab.length > 0) badgesHtml += `<span class="badge" style="background: rgba(0, 168, 150, 0.15); color: var(--accent-kitab); border: 1px solid rgba(0, 168, 150, 0.3)">Kitab</span> `;

        item.innerHTML = `
            <div class="item-main">
                <span class="item-name">${p.nama}</span>
                <span class="item-nspp">${p.nspp}</span>
            </div>
            <div class="item-location">${p.kecamatan ? p.kecamatan + ', ' : ''}${p.kabupaten}</div>
            <div class="item-badges">${badgesHtml}</div>
        `;

        item.addEventListener('click', () => {
            focusOnPesantren(p);
        });

        elListScroll.appendChild(item);
    });

    // Pagination buttons state
    elBtnPrev.disabled = currentPage === 1;
    elBtnNext.disabled = currentPage === totalPages;
    elPageInfo.textContent = `Halaman ${currentPage} dari ${totalPages}`;
}

// Pagination Event Listeners
elBtnPrev.addEventListener('click', () => {
    if (currentPage > 1) {
        currentPage--;
        updateList();
    }
});

elBtnNext.addEventListener('click', () => {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        updateList();
    }
});

// Clear existing disaster overlays
function clearDisasterOverlay() {
    if (focusedCircle) {
        map.removeLayer(focusedCircle);
        focusedCircle = null;
    }
    disasterMarkers.forEach(m => map.removeLayer(m));
    disasterMarkers = [];
    polylines.forEach(l => map.removeLayer(l));
    polylines = [];
}

// Zoom to a disaster coordinate from the modal table
window.zoomToDisaster = function(nspp, disLat, disLon, disTipe, disJarak, disTahun, disArea) {
    // Close modal
    elModal.classList.remove('active');
    
    // Zoom map
    map.setView([disLat, disLon], 15);
    
    // Open a popup at the coordinates
    setTimeout(() => {
        L.popup()
            .setLatLng([disLat, disLon])
            .setContent(`
                <div class="popup-content" style="border-top: 2px solid #ff4757;">
                    <h3 style="color:#ff4757; margin-bottom:0.25rem;">${disTipe}</h3>
                    <p style="font-size:0.75rem; margin-bottom:0.15rem;"><b>Jarak:</b> ${disJarak} km dari pesantren</p>
                    <p style="font-size:0.75rem; margin-bottom:0.15rem;"><b>Tahun:</b> ${disTahun}</p>
                    <p style="font-size:0.75rem; color:var(--text-muted);">${disArea || '-'}</p>
                </div>
            `)
            .openOn(map);
    }, 300);
};

// Focus on map and open popup
function focusOnPesantren(p) {
    clearDisasterOverlay();
    
    if (p.latitude !== null && p.longitude !== null && !isNaN(p.latitude) && !isNaN(p.longitude)) {
        map.setView([p.latitude, p.longitude], 13);
        const marker = activeMarkersMap.get(p.nspp);
        if (marker) {
            setTimeout(() => {
                marker.openPopup();
            }, 300);
        }
        
        // Draw risk circle (5km radius)
        focusedCircle = L.circle([p.latitude, p.longitude], {
            color: '#ff4757',
            fillColor: '#ff4757',
            fillOpacity: 0.04,
            weight: 1.5,
            radius: 5000
        }).addTo(map);
        
        // Draw closest disasters (top 5)
        if (p.bencana && p.bencana.closest_disasters && p.bencana.closest_disasters.length > 0) {
            p.bencana.closest_disasters.forEach(dis => {
                const disasterIcon = L.divIcon({
                    className: 'custom-disaster-marker-container',
                    html: `<div class="disaster-marker-ping" style="background-color: #ff4757; width: 10px; height: 10px; border-radius: 50%; border: 1.5px solid #000; box-shadow: 0 0 8px #ff4757;"></div>`,
                    iconSize: [10, 10],
                    iconAnchor: [5, 5]
                });
                
                const dMarker = L.marker([dis.latitude, dis.longitude], { icon: disasterIcon })
                    .bindPopup(`
                        <div class="popup-content" style="border-top: 2px solid #ff4757;">
                            <h4 style="color:#ff4757; margin-bottom:0.25rem;">${dis.tipe}</h4>
                            <p style="font-size:0.75rem; margin-bottom:0.15rem;"><b>Jarak:</b> ${dis.jarak_km} km</p>
                            <p style="font-size:0.75rem; margin-bottom:0.15rem;"><b>Tahun:</b> ${dis.tahun}</p>
                            <p style="font-size:0.75rem; color:var(--text-muted);">${dis.area || '-'}</p>
                        </div>
                    `)
                    .addTo(map);
                    
                disasterMarkers.push(dMarker);
                
                // Draw polyline connecting them
                const line = L.polyline([[p.latitude, p.longitude], [dis.latitude, dis.longitude]], {
                    color: '#ff4757',
                    weight: 1.5,
                    opacity: 0.5,
                    dashArray: '4, 8'
                }).addTo(map);
                
                polylines.push(line);
            });
        }
    } else {
        // If no coordinates, open detail modal directly
        openDetailModal(p.nspp);
    }
}

// 7. Modal detail view content injection
window.openDetailModal = function(nspp) {
    const p = pesantrenData.find(x => x.nspp === nspp);
    if (!p) return;

    // Build risk badge class
    let riskBadgeClass = 'risk-nc';
    let riskLabel = 'Tanpa Koordinat';
    const lk = p.bencana.level_kerawanan;
    if (lk === 'SANGAT TINGGI') { riskBadgeClass = 'risk-st'; riskLabel = 'Kerawanan Sangat Tinggi'; }
    else if (lk === 'TINGGI') { riskBadgeClass = 'risk-t'; riskLabel = 'Kerawanan Tinggi'; }
    else if (lk === 'SEDANG') { riskBadgeClass = 'risk-sd'; riskLabel = 'Kerawanan Sedang'; }
    else if (lk === 'RENDAH') { riskBadgeClass = 'risk-r'; riskLabel = 'Kerawanan Rendah'; }
    else if (lk === 'TIDAK ADA RIWAYAT BENCANA') { riskBadgeClass = 'risk-a'; riskLabel = 'Aman (Tanpa Riwayat)'; }
    
    // 1. PILAR KERAWANAN BENCANA (AMAN)
    let bencanaHtml = `
        <div class="risk-panel">
            <div class="risk-level-display" style="margin-bottom: 0.75rem;">
                <span class="badge ${riskBadgeClass} risk-large-badge">${riskLabel}</span>
            </div>
    `;
    if (p.latitude !== null) {
        let disastersTableHtml = '';
        if (p.bencana.closest_disasters && p.bencana.closest_disasters.length > 0) {
            disastersTableHtml = `
                <div style="margin-top: 1rem; overflow-x: auto;">
                    <p style="margin-bottom: 0.5rem; font-size: 0.8rem; font-weight: 600; color: #ff4757;">Top 5 Kejadian Bencana Terdekat (Radius):</p>
                    <table style="width:100%; border-collapse:collapse; font-size:0.72rem; text-align:left;">
                        <thead>
                            <tr style="border-bottom:1px solid rgba(255,255,255,0.1); color:var(--text-secondary);">
                                <th style="padding:0.4rem 0.2rem;">Tipe</th>
                                <th style="padding:0.4rem 0.2rem;">Jarak</th>
                                <th style="padding:0.4rem 0.2rem;">Tahun</th>
                                <th style="padding:0.4rem 0.2rem;">Area</th>
                                <th style="padding:0.4rem 0.2rem; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            p.bencana.closest_disasters.forEach(dis => {
                disastersTableHtml += `
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:0.4rem 0.2rem; font-weight:600; color:#ff4757;">${dis.tipe}</td>
                        <td style="padding:0.4rem 0.2rem;">${dis.jarak_km} km</td>
                        <td style="padding:0.4rem 0.2rem;">${dis.tahun}</td>
                        <td style="padding:0.4rem 0.2rem; color:var(--text-muted);">${dis.area || '-'}</td>
                        <td style="padding:0.4rem 0.2rem; text-align:center;">
                            <button onclick="zoomToDisaster('${p.nspp}', ${dis.latitude}, ${dis.longitude}, '${dis.tipe}', ${dis.jarak_km}, ${dis.tahun}, '${dis.area.replace(/'/g, "\\'")}')" style="background:rgba(255,71,87,0.15); color:#ff4757; border:1px solid rgba(255,71,87,0.3); padding:0.15rem 0.4rem; border-radius:4px; font-size:0.65rem; font-weight:600; cursor:pointer;">Peta</button>
                        </td>
                    </tr>
                `;
            });
            disastersTableHtml += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        bencanaHtml += `
            <div class="closest-disaster-info">
                <p><strong>Analisis Radius Bencana:</strong></p>
                <p>• Dalam radius 5 KM: <strong>${p.bencana.disasters_within_5km}</strong> kejadian bencana</p>
                <p>• Dalam radius 10 KM: <strong>${p.bencana.disasters_within_10km}</strong> kejadian bencana</p>
                <p>• Dalam radius 20 KM: <strong>${p.bencana.disasters_within_20km}</strong> kejadian bencana</p>
                
                <p style="margin-top: 0.5rem;"><strong>Kejadian Bencana Terdekat:</strong></p>
                <p>Tipe bencana <strong>${p.bencana.closest_disaster_type}</strong> sejauh <strong>${p.bencana.closest_disaster_distance_km} KM</strong> pada tahun <strong>${p.bencana.closest_disaster_year}</strong>.</p>
                ${disastersTableHtml}
            </div>
        `;
    } else {
        bencanaHtml += `<p class="text-secondary" style="font-size: 0.8rem;">Pesantren ini tidak memiliki data koordinat geografis yang valid untuk perhitungan kerawanan bencana secara spasial.</p>`;
    }
    bencanaHtml += `</div>`;

    // 2. PILAR EKONOMI (ENTERPRENEUR / OPOP)
    let opopHtml = `<p class="text-secondary" style="font-size: 0.85rem; margin-bottom: 0.5rem;">Belum terdaftar program produk unggulan OPOP.</p>`;
    if (p.opop && p.opop.length > 0) {
        opopHtml = `<div class="opop-product-grid">`;
        p.opop.forEach(prod => {
            // Image handling
            let imgHtml = `<div class="product-no-img">Gambar Tidak Tersedia</div>`;
            if (prod.gambar) {
                const folder = prod.tahun === 2024 ? 'OPOP/extracted_images' : 'OPOP/extracted_images_2023';
                const imgSrc = `${folder}/${prod.gambar}`;
                imgHtml = `<img class="product-img" src="${imgSrc}" alt="${prod.nama_produk}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"><div class="product-no-img" style="display:none;">Gagal Memuat Gambar</div>`;
            }

            // WhatsApp link
            let waHtml = '';
            if (prod.telepon) {
                let cleanPhone = prod.telepon.replace(/\D/g, '');
                if (cleanPhone.startsWith('0')) {
                    cleanPhone = '62' + cleanPhone.substring(1);
                }
                const msg = encodeURIComponent(`Halo, saya tertarik dengan produk ${prod.nama_produk} dari pesantren ${p.nama}. Apakah produk ini tersedia?`);
                waHtml = `
                    <a href="https://wa.me/${cleanPhone}?text=${msg}" target="_blank" class="wa-btn">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.731-1.456L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.625 1.451 5.437.002 9.861-4.416 9.863-9.848.001-2.63-1.019-5.101-2.871-6.957C16.408 1.986 13.93 1.015 11.3 1.015c-5.443 0-9.87 4.414-9.873 9.848-.001 2.03.535 4.02 1.547 5.765l-.973 3.55 3.64-.954zm11.378-5.322c-.29-.145-1.713-.846-1.977-.942-.265-.096-.458-.145-.65.145-.192.29-.746.942-.914 1.135-.168.192-.336.216-.626.072-1.353-.678-2.28-1.196-3.23-2.825-.252-.43-.252-.776-.046-.995.184-.196.408-.48.612-.722.203-.242.271-.413.407-.69.136-.277.068-.518-.033-.717-.1-.2-.846-2.04-.158-2.724-.265-.638-.528-.553-.722-.553h-.612c-.204 0-.535.077-.814.385-.28.308-1.07 1.047-1.07 2.553 0 1.506 1.096 2.964 1.246 3.166.15.203 2.158 3.295 5.228 4.62.73.315 1.3.504 1.743.644.733.233 1.399.2 1.925.12.587-.087 1.714-.7 1.956-1.374.24-.674.24-1.253.168-1.374-.072-.12-.265-.192-.555-.337z"/></svg>
                        Hubungi WA
                    </a>
                `;
            }
            opopHtml += `
                <div class="opop-product-card">
                    <div class="product-img-wrapper">${imgHtml}</div>
                    <div class="product-details">
                        <h4>${prod.nama_produk}</h4>
                        <p class="product-desc">${prod.deskripsi || 'Tidak ada deskripsi produk.'}</p>
                        <div class="product-footer">
                            <span class="product-year-badge">${typeof prod.tahun === 'number' ? 'OPOP ' + prod.tahun : prod.tahun}</span>
                            ${waHtml}
                        </div>
                    </div>
                </div>
            `;
        });
        opopHtml += `</div>`;
    }

    // 3. PILAR DIGITALISASI
    let digitalHtml = `<p class="text-secondary" style="font-size: 0.85rem;">Belum ada laporan implementasi nontunai/aplikasi digital.</p>`;
    if (p.digital.is_digital) {
        digitalHtml = `
            <div class="pillar-desc-list">
                <div class="pillar-desc-item">
                    <strong>Aplikasi Unggulan:</strong>
                    <span>${p.digital.aplikasi}</span>
                </div>
                <div class="pillar-desc-item">
                    <strong>Layanan Nontunai / Layanan Digital:</strong>
                    <span>${p.digital.layanan}</span>
                </div>
                <div class="pillar-desc-item">
                    <strong>Dampak Kualitas:</strong>
                    <span>${p.digital.dampak}</span>
                </div>
        `;
        if (p.digital.website) {
            digitalHtml += `
                <a href="${p.digital.website}" target="_blank" class="link-btn">
                    Buka Website Resmi &rarr;
                </a>
            `;
        }
        digitalHtml += `</div>`;
    }

    // 4. PILAR ECOTEOLOGI (EKOLOGI)
    let ecoHtml = `<p class="text-secondary" style="font-size: 0.85rem;">Belum ada laporan implementasi program pelestarian ecoteologi.</p>`;
    if (p.ecoteologi.is_ecoteologi) {
        ecoHtml = `
            <div class="pillar-desc-list">
                <div class="pillar-desc-item">
                    <strong>Fokus & Program Ecoteologi:</strong>
                    <span>${p.ecoteologi.jenis}</span>
                </div>
                <div class="pillar-desc-item">
                    <strong>Alamat Lokasi Ecoteologi:</strong>
                    <span>${p.ecoteologi.alamat_detail}</span>
                </div>
            </div>
        `;
    }

    // 5. PILAR RAMAH ANAK
    let ramahHtml = `<p class="text-secondary" style="font-size: 0.85rem;">Belum terdaftar program khusus Pesantren Ramah Anak.</p>`;
    if (p.ramah_anak.is_ramah_anak) {
        ramahHtml = `
            <div class="pillar-desc-list">
                <div class="pillar-desc-item">
                    <strong>Program Ramah Anak & Hak Santri:</strong>
                    <span>${p.ramah_anak.program}</span>
                </div>
                <div class="pillar-desc-item">
                    <strong>Integrasi Kurikulum Anti-Bullying / Kesehatan:</strong>
                    <span>${p.ramah_anak.kurikulum}</span>
                </div>
                <div class="pillar-desc-item">
                    <strong>Dampak Pembangunan Karakter:</strong>
                    <span>${p.ramah_anak.dampak}</span>
                </div>
            </div>
        `;
    }

    // 6. PILAR KARYA KITAB
    let kitabHtml = `<p class="text-secondary" style="font-size: 0.85rem; margin-bottom: 0.5rem;">Belum terdaftar karya ilmiah / kitab kuning.</p>`;
    if (p.karya_kitab && p.karya_kitab.length > 0) {
        kitabHtml = `<div class="kitab-grid" style="display:flex; flex-direction:column; gap:0.75rem;">`;
        p.karya_kitab.forEach(kit => {
            let detailsList = [];
            if (kit.pengarang) detailsList.push(`<strong>Pengarang:</strong> ${kit.pengarang}`);
            if (kit.tahun) detailsList.push(`<strong>Tahun Dibuat:</strong> ${kit.tahun}`);
            if (kit.halaman) detailsList.push(`<strong>Halaman:</strong> ${kit.halaman}`);
            if (kit.jilid) detailsList.push(`<strong>Jilid:</strong> ${kit.jilid}`);
            if (kit.ukuran) detailsList.push(`<strong>Ukuran:</strong> ${kit.ukuran}`);
            if (kit.isbn) detailsList.push(`<strong>ISBN:</strong> ${kit.isbn}`);
            if (kit.haki) {
                if (kit.haki.startsWith('http')) {
                    detailsList.push(`<strong>HAKI:</strong> <a href="${kit.haki}" target="_blank" style="color:var(--accent-kitab); text-decoration:underline;">Link HAKI</a>`);
                } else {
                    detailsList.push(`<strong>HAKI:</strong> ${kit.haki}`);
                }
            }
            if (kit.penerbit) detailsList.push(`<strong>Penerbit:</strong> ${kit.penerbit}`);
            
            let linkHtml = '';
            if (kit.link_karya) {
                linkHtml = `<a href="${kit.link_karya}" target="_blank" class="kitab-link-btn" style="background:var(--accent-kitab); color:#000; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.7rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.25rem;">Buka Karya</a>`;
            }
            
            let waHtml = '';
            if (kit.telepon) {
                let cleanPhone = kit.telepon.replace(/\D/g, '');
                if (cleanPhone.startsWith('0')) {
                    cleanPhone = '62' + cleanPhone.substring(1);
                }
                const msg = encodeURIComponent(`Halo, saya tertarik dengan karya kitab "${kit.judul}" dari pesantren ${p.nama}. Bisa info lebih lanjut?`);
                waHtml = `
                    <a href="https://wa.me/${cleanPhone}?text=${msg}" target="_blank" class="wa-btn" style="display:inline-flex; align-items:center; gap:0.25rem; font-size:0.7rem; padding:0.25rem 0.6rem; background:rgba(46, 213, 115, 0.15); color:#2ed573; border:1px solid rgba(46, 213, 115, 0.3); border-radius:4px; text-decoration:none;">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.731-1.456L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.625 1.451 5.437.002 9.861-4.416 9.863-9.848.001-2.63-1.019-5.101-2.871-6.957C16.408 1.986 13.93 1.015 11.3 1.015c-5.443 0-9.87 4.414-9.873 9.848-.001 2.03.535 4.02 1.547 5.765l-.973 3.55 3.64-.954zm11.378-5.322c-.29-.145-1.713-.846-1.977-.942-.265-.096-.458-.145-.65.145-.192.29-.746.942-.914 1.135-.168.192-.336.216-.626.072-1.353-.678-2.28-1.196-3.23-2.825-.252-.43-.252-.776-.046-.995.184-.196.408-.48.612-.722.203-.242.271-.413.407-.69.136-.277.068-.518-.033-.717-.1-.2-.846-2.04-.158-2.724-.265-.638-.528-.553-.722-.553h-.612c-.204 0-.535.077-.814.385-.28.308-1.07 1.047-1.07 2.553 0 1.506 1.096 2.964 1.246 3.166.15.203 2.158 3.295 5.228 4.62.73.315 1.3.504 1.743.644.733.233 1.399.2 1.925.12.587-.087 1.714-.7 1.956-1.374.24-.674.24-1.253.168-1.374-.072-.12-.265-.192-.555-.337z"/></svg>
                        Hubungi WA
                    </a>
                `;
            }

            kitabHtml += `
                <div class="kitab-card" style="background:rgba(255,255,255,0.02); border:1px solid var(--border-subtle); border-radius:10px; padding:0.75rem;">
                    <h4 style="color:var(--accent-kitab); font-size:0.95rem; margin-bottom:0.4rem; font-weight:600;">${kit.judul}</h4>
                    <p style="font-size:0.75rem; color:var(--text-secondary); margin-bottom:0.5rem; line-height:1.4;">${kit.deskripsi}</p>
                    <div style="font-size:0.7rem; color:var(--text-muted); display:flex; flex-wrap:wrap; gap:0.5rem 1.25rem; margin-bottom:0.5rem;">
                        ${detailsList.map(item => `<span>${item}</span>`).join('')}
                    </div>
                    <div style="display:flex; gap:0.5rem; margin-top:0.4rem;">
                        ${linkHtml}
                        ${waHtml}
                    </div>
                </div>
            `;
        });
        kitabHtml += `</div>`;
    }

    // Inject all content into modal body
    elModalBody.innerHTML = `
        <div class="modal-title-area">
            <h2>${p.nama}</h2>
            <span class="modal-subtitle">Kecamatan ${p.kecamatan}, Kabupaten ${p.kabupaten} • NSPP ${p.nspp}</span>
        </div>
        
        <div class="modal-grid">
            <!-- Left Side: Identity & Disaster -->
            <div style="display:flex; flex-direction:column; gap:1.25rem;">
                <div class="detail-section">
                    <h3>Identitas Pesantren</h3>
                    <div class="identity-list">
                        <div class="identity-row">
                            <div class="identity-label">Nama Resmi</div>
                            <div class="identity-value">${p.nama}</div>
                        </div>
                        <div class="identity-row">
                            <div class="identity-label">NSPP</div>
                            <div class="identity-value" style="font-family:monospace;">${p.nspp}</div>
                        </div>
                        <div class="identity-row">
                            <div class="identity-label">Jenjang</div>
                            <div class="identity-value">${p.jenjang || '-'}</div>
                        </div>
                        <div class="identity-row">
                            <div class="identity-label">Organisasi</div>
                            <div class="identity-value">${p.afiliasi}</div>
                        </div>
                        <div class="identity-row">
                            <div class="identity-label">Alamat</div>
                            <div class="identity-value">${p.alamat || '-'}</div>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Pilar Aman (Kerawanan Bencana)</h3>
                    ${bencanaHtml}
                </div>
            </div>
            
            <!-- Right Side: Specialized Pillars -->
            <div style="display:flex; flex-direction:column; gap:1.25rem;">
                ${p.karya_kitab && p.karya_kitab.length > 0 ? `
                    <div class="detail-section" style="border-left: 3px solid var(--accent-kitab)">
                        <h3 style="border-left:none; padding-left:0; color:var(--accent-kitab);">Pilar Karya Kitab</h3>
                        ${kitabHtml}
                    </div>
                ` : ''}

                ${p.digital.is_digital ? `
                    <div class="detail-section" style="border-left: 3px solid var(--accent-dig)">
                        <h3 style="border-left:none; padding-left:0; color:var(--accent-dig);">Pilar Nontunai & Digital</h3>
                        ${digitalHtml}
                    </div>
                ` : ''}
                
                ${p.ecoteologi.is_ecoteologi ? `
                    <div class="detail-section" style="border-left: 3px solid var(--accent-eco)">
                        <h3 style="border-left:none; padding-left:0; color:var(--accent-eco);">Pilar Ekologi / Ecoteologi</h3>
                        ${ecoHtml}
                    </div>
                ` : ''}
                
                ${p.ramah_anak.is_ramah_anak ? `
                    <div class="detail-section" style="border-left: 3px solid var(--accent-ramah)">
                        <h3 style="border-left:none; padding-left:0; color:var(--accent-ramah);">Pilar Ramah Anak</h3>
                        ${ramahHtml}
                    </div>
                ` : ''}
                
                ${p.opop && p.opop.length > 0 ? `
                    <div class="detail-section" style="border-left: 3px solid var(--accent-opop)">
                        <h3 style="border-left:none; padding-left:0; color:var(--accent-opop);">Pilar Enterpreneur (OPOP)</h3>
                        ${opopHtml}
                    </div>
                ` : ''}

                <!-- Empty state for specialized pillars if none is active -->
                ${(!p.digital.is_digital && !p.ecoteologi.is_ecoteologi && !p.ramah_anak.is_ramah_anak && (!p.opop || p.opop.length === 0) && (!p.karya_kitab || p.karya_kitab.length === 0)) ? `
                    <div class="detail-section">
                        <h3>Pilar Unggulan Pendekar</h3>
                        <p class="text-muted" style="font-size: 0.8rem;">Pesantren ini belum terdaftar dalam program khusus unggulan digitalisasi, pelestarian lingkungan ecoteologi, ramah anak, kemandirian ekonomi OPOP, atau karya ilmiah/kitab kuning.</p>
                    </div>
                ` : ''}
            </div>
        </div>
    `;

    // Show modal
    elModal.classList.add('active');
};

// Modal Close logic
elModalClose.addEventListener('click', () => {
    elModal.classList.remove('active');
});

// Click outside modal card to close
elModal.addEventListener('click', (e) => {
    if (e.target === elModal) {
        elModal.classList.remove('active');
    }
});

// 8. Chart.js Implementation
function initChart() {
    const ctx = document.getElementById('chart-canvas').getContext('2d');
    
    // Set custom styling defaults for Chart
    Chart.defaults.color = '#9aa5b8';
    Chart.defaults.font.family = 'Inter';
    
    activeChart = new Chart(ctx, {
        type: 'bar',
        data: getChartData(),
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(18, 22, 33, 0.95)',
                    titleColor: '#fff',
                    bodyColor: '#f1f3f9',
                    borderColor: 'rgba(255, 255, 255, 0.08)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.03)'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.03)'
                    },
                    ticks: {
                        beginAtZero: true
                    }
                }
            }
        }
    });

    // Add Tab Click Listeners
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            tabBtns.forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
            
            currentChartType = e.target.getAttribute('data-chart');
            updateChartData();
        });
    });
}

function getChartData() {
    if (currentChartType === 'risk') {
        // Count distribution of level_kerawanan in filteredData
        const counts = {
            'SANGAT TINGGI': 0,
            'TINGGI': 0,
            'SEDANG': 0,
            'RENDAH': 0,
            'TIDAK ADA RIWAYAT BENCANA': 0,
            'TIDAK ADA DATA KOORDINAT': 0
        };

        filteredData.forEach(p => {
            counts[p.bencana.level_kerawanan]++;
        });

        return {
            labels: ['Sangat Tinggi', 'Tinggi', 'Sedang', 'Rendah', 'Aman', 'Tanpa Koordinat'],
            datasets: [{
                data: [
                    counts['SANGAT TINGGI'],
                    counts['TINGGI'],
                    counts['SEDANG'],
                    counts['RENDAH'],
                    counts['TIDAK ADA RIWAYAT BENCANA'],
                    counts['TIDAK ADA DATA KOORDINAT']
                ],
                backgroundColor: [
                    'rgba(255, 71, 87, 0.75)',
                    'rgba(255, 159, 67, 0.75)',
                    'rgba(236, 204, 104, 0.75)',
                    'rgba(46, 213, 115, 0.75)',
                    'rgba(30, 144, 255, 0.75)',
                    'rgba(116, 125, 140, 0.75)'
                ],
                borderColor: [
                    '#ff4757',
                    '#ff9f43',
                    '#eccc68',
                    '#2ed573',
                    '#1e90ff',
                    '#747d8c'
                ],
                borderWidth: 1.5,
                borderRadius: 6
            }]
        };
    } else {
        // Group by Afiliasi
        const counts = {};
        filteredData.forEach(p => {
            const org = p.afiliasi || 'Lainnya';
            counts[org] = (counts[org] || 0) + 1;
        });

        // Sort by counts and get top 5, plus others
        const sorted = Object.entries(counts).sort((a, b) => b[1] - a[1]);
        const top5 = sorted.slice(0, 5);
        const othersCount = sorted.slice(5).reduce((acc, curr) => acc + curr[1], 0);

        const labels = top5.map(x => x[0]);
        const data = top5.map(x => x[1]);

        if (othersCount > 0) {
            labels.push('Lainnya');
            data.push(othersCount);
        }

        return {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.75)',
                    'rgba(16, 185, 129, 0.75)',
                    'rgba(168, 85, 247, 0.75)',
                    'rgba(244, 63, 94, 0.75)',
                    'rgba(245, 158, 11, 0.75)',
                    'rgba(100, 116, 139, 0.75)'
                ],
                borderColor: [
                    '#3b82f6',
                    '#10b981',
                    '#a855f7',
                    '#f43f5e',
                    '#f59e0b',
                    '#64748b'
                ],
                borderWidth: 1.5,
                borderRadius: 6
            }]
        };
    }
}

function updateChartData() {
    if (activeChart) {
        activeChart.data = getChartData();
        activeChart.update();
    }
}

// 9. Startup Execution
window.addEventListener('DOMContentLoaded', () => {
    initMap();
    loadData();
});
