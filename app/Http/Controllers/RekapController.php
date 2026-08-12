<?php

namespace App\Http\Controllers;

class RekapController
{
    
    private function paginateAndFilter($dataList, $perPage = 20) {
        $search = $_GET['search'] ?? '';
        $kabupaten = $_GET['kabupaten'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        
        $sortBy = $_GET['sort_by'] ?? '';
        $sortDir = $_GET['sort_dir'] ?? 'asc';

        // Filter
        $filteredList = [];
        foreach ($dataList as $item) {
            $matchSearch = true;
            if ($search !== '') {
                $searchStr = strtolower($search);
                $nameMatch = isset($item['nama']) && strpos(strtolower($item['nama']), $searchStr) !== false;
                $nsppMatch = isset($item['nspp']) && strpos(strtolower($item['nspp']), $searchStr) !== false;
                $judulMatch = isset($item['judul']) && strpos(strtolower($item['judul']), $searchStr) !== false;
                $matchSearch = $nameMatch || $nsppMatch || $judulMatch;
            }

            $matchKab = true;
            if ($kabupaten !== '') {
                $matchKab = isset($item['kabupaten']) && strtolower($item['kabupaten']) === strtolower($kabupaten);
            }

            if ($matchSearch && $matchKab) {
                $filteredList[] = $item;
            }
        }

        if ($sortBy !== '') {
            usort($filteredList, function($a, $b) use ($sortBy, $sortDir) {
                $valA = $a[$sortBy] ?? '';
                $valB = $b[$sortBy] ?? '';
                
                if (is_numeric($valA) && is_numeric($valB)) {
                    $cmp = $valA <=> $valB;
                } else {
                    $cmp = strcasecmp((string)$valA, (string)$valB);
                }
                
                return $sortDir === 'asc' ? $cmp : -$cmp;
            });
        }

        // Pagination
        $totalRecords = count($filteredList);
        $totalPages = max(1, ceil($totalRecords / $perPage));
        $offset = ($page - 1) * $perPage;
        $paginatedList = array_slice($filteredList, $offset, $perPage);
        
        // Helper to generate sort link
        $sortLink = function($column, $label) use ($sortBy, $sortDir) {
            $q = $_GET;
            $q['sort_by'] = $column;
            $q['sort_dir'] = ($sortBy === $column && $sortDir === 'asc') ? 'desc' : 'asc';
            
            $icon = '';
            if ($sortBy === $column) {
                $icon = $sortDir === 'asc' ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>';
            } else {
                $icon = ' <i class="fas fa-sort" style="color: #ccc;"></i>';
            }
            
            $url = '?' . http_build_query($q);
            return '<a href="' . $url . '" style="color: inherit; text-decoration: none;">' . htmlspecialchars($label) . $icon . '</a>';
        };

        return [
            'list' => $paginatedList,
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => htmlspecialchars($search),
            'selectedKabupaten' => htmlspecialchars($kabupaten),
            'sortBy' => htmlspecialchars($sortBy),
            'sortDir' => htmlspecialchars($sortDir),
            'sortLink' => $sortLink
        ];
    }

    private function getKabupatenList($allData) {
        $kabs = [];
        foreach ($allData as $p) {
            if (!empty($p['kabupaten']) && $p['kabupaten'] !== '-') {
                $kabs[] = $p['kabupaten'];
            }
        }
        $kabs = array_unique($kabs);
        sort($kabs);
        return $kabs;
    }

    private function loadIntegratedData()
    {
        $dataPath = __DIR__ . '/../../../public/data/pesantren_integrated.json';
        if (!file_exists($dataPath)) {
            $dataPath = __DIR__ . '/../../../data/merged_pesantren_data.json';
        }

        if (file_exists($dataPath)) {
            $json = file_get_contents($dataPath);
            $data = json_decode($json, true);
            return is_array($data) ? $data : [];
        }
        return [];
    }

    public function pesantren()
    {
        $allData = $this->loadIntegratedData();
        $pesantrenList = [];

        foreach ($allData as $nspp => $p) {
            $pesantrenList[] = [
                'nspp' => $p['nspp'] ?? $nspp,
                'nama' => $p['nama'] ?? 'Pesantren',
                'jenjang' => $p['jenjang'] ?? '-',
                'alamat' => $p['alamat'] ?? '-',
                'kabupaten' => $p['kabupaten'] ?? '-',
                'kecamatan' => $p['kecamatan'] ?? '-',
                'afiliasi' => $p['afiliasi'] ?? 'Lainnya',
                'raw_json' => htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8')
            ];
        }

        
        $kabupatenList = $this->getKabupatenList($allData);
        $paginated = $this->paginateAndFilter($pesantrenList);
        return $this->renderView('rekap_pesantren', [
            'pageTitle' => 'Rekap Data Pesantren Jawa Timur | PENDEKAR',
            'pesantrenList' => $pesantrenList
        ,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList]);
    }

    public function bencana()
    {
        $allData = $this->loadIntegratedData();
        $bencanaList = [];

        foreach ($allData as $nspp => $p) {
            $b = $p['bencana'] ?? [];
            if (!empty($b)) {
                $bencanaList[] = [
                    'nama' => $p['nama'] ?? 'Pesantren',
                    'kabupaten' => $p['kabupaten'] ?? '-',
                    'level_kerawanan' => $b['level_kerawanan'] ?? 'Normal',
                    'tipe_terdekat' => $b['closest_disaster_type'] ?? 'Tidak Ada',
                    'jarak_km' => $b['closest_disaster_distance_km'] ?? null,
                    'tahun_kejadian' => $b['closest_disaster_year'] ?? '-',
                    'dalam_5km' => $b['disasters_within_5km'] ?? 0,
                    'dalam_10km' => $b['disasters_within_10km'] ?? 0,
                    'raw_json' => htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8')
                ];
            }
        }

        
        $kabupatenList = $this->getKabupatenList($allData);
        $paginated = $this->paginateAndFilter($bencanaList);
        return $this->renderView('rekap_bencana', [
            'pageTitle' => 'Rekap Risiko Bencana Pesantren | PENDEKAR',
            'bencanaList' => $bencanaList
        ,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList]);
    }

    public function ramah()
    {
        $allData = $this->loadIntegratedData();
        $ramahList = [];

        foreach ($allData as $nspp => $p) {
            $r = $p['ramah_anak'] ?? [];
            if (!empty($r['is_ramah_anak'])) {
                $ramahList[] = [
                    'nama' => $p['nama'] ?? 'Pesantren',
                    'kabupaten' => $p['kabupaten'] ?? '-',
                    'program' => $r['program'] ?? 'Program Pesantren Ramah Anak',
                    'kurikulum' => $r['kurikulum'] ?? 'Integrasi Kurikulum Akhlak',
                    'dampak' => $r['dampak'] ?? 'Peningkatan Karakter Santri',
                    'raw_json' => htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8')
                ];
            }
        }

        
        $kabupatenList = $this->getKabupatenList($allData);
        $paginated = $this->paginateAndFilter($ramahList);
        return $this->renderView('rekap_ramah', [
            'pageTitle' => 'Rekap Pesantren Ramah Anak | PENDEKAR',
            'ramahList' => $ramahList
        ,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList]);
    }

    public function digital()
    {
        $csvPath = __DIR__ . '/../../../digital/pesantren_digital_jatim.csv';
        $digitalList = [];
        if (file_exists($csvPath)) {
            $rows = array_map('str_getcsv', file($csvPath));
            $header = array_shift($rows);
            // Remove UTF-8 BOM if present in the first column header
            if (isset($header[0])) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            }
            foreach ($rows as $row) {
                if (count($row) == count($header)) {
                    $item = array_combine($header, $row);
                    $item['nama'] = $item['Nama Pesantren'] ?? '';
                    $item['kabupaten'] = $item['Kabupaten/Kota'] ?? '';
                    
                    // Add mock fields to prevent json parse errors in modal
                    $item['nspp'] = '-';
                    $item['alamat'] = $item['Alamat'] ?? '-';
                    $item['raw_json'] = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
                    
                    $digitalList[] = $item;
                }
            }
        }
        
        $kabs = [];
        foreach ($digitalList as $p) {
            if (!empty($p['Kabupaten/Kota'])) $kabs[] = $p['Kabupaten/Kota'];
        }
        $kabupatenList = array_unique($kabs);
        sort($kabupatenList);

        $paginated = $this->paginateAndFilter($digitalList, 15);
        return $this->renderView('rekap_digital', [
            'pageTitle' => 'Rekap Pesantren Digital | PENDEKAR',
            'digitalList' => $digitalList,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList
        ]);
    }

    public function ecoteologi()
    {
        $allData = $this->loadIntegratedData();
        $ecoList = [];

        foreach ($allData as $nspp => $p) {
            $e = $p['ecoteologi'] ?? [];
            if (!empty($e['is_ecoteologi'])) {
                $ecoList[] = [
                    'nama' => $p['nama'] ?? 'Pesantren',
                    'kabupaten' => $p['kabupaten'] ?? '-',
                    'jenis' => $e['jenis'] ?? 'Pengelolaan Eco-Green & Konservasi',
                    'alamat' => $e['alamat_detail'] ?? ($p['alamat'] ?? '-'),
                    'raw_json' => htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8')
                ];
            }
        }

        
        $kabupatenList = $this->getKabupatenList($allData);
        $paginated = $this->paginateAndFilter($ecoList);
        return $this->renderView('rekap_ecoteologi', [
            'pageTitle' => 'Rekap Pesantren Ecoteologi | PENDEKAR',
            'ecoList' => $ecoList
        ,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList]);
    }

    public function produk()
    {
        $allData = $this->loadIntegratedData();
        $produkList = [];

        foreach ($allData as $nspp => $p) {
            $opopItems = $p['opop'] ?? [];
            if (is_array($opopItems)) {
                foreach ($opopItems as $item) {
                    $produkList[] = [
                        'pesantren' => $p['nama'] ?? 'Pesantren',
                        'kabupaten' => $p['kabupaten'] ?? '-',
                        'nama_produk' => $item['nama_produk'] ?? 'Produk Inovasi',
                        'deskripsi' => $item['deskripsi'] ?? 'Produk unggulan santri & kopontren',
                        'kategori' => !empty($item['kategori']) ? $item['kategori'] : 'OPOP / Enterpreneur',
                        'tahun' => $item['tahun'] ?? '-',
                        'kontak' => $item['telepon'] ?? '-',
                        'gambar' => $item['gambar'] ?? null
                    ];
                }
            }
        }

        if (empty($produkList)) {
            $produkList = [
                ['pesantren' => 'PP. Sidogiri', 'kabupaten' => 'Pasuruan', 'nama_produk' => 'Air Mineral Basmalah', 'deskripsi' => 'Minimarket & Produk Air Mineral Santri', 'kategori' => 'OPOP / Enterpreneur', 'tahun' => 2024, 'kontak' => '081234567890'],
                ['pesantren' => 'PP. Sunan Drajat', 'kabupaten' => 'Lamongan', 'nama_produk' => 'Pupuk Organik Ecoteologi', 'deskripsi' => 'Olahan Pupuk Organik & Hasil Laut', 'kategori' => 'OPOP / Ecoteologi', 'tahun' => 2024, 'kontak' => '081987654321']
            ];
        }

        
        $kabupatenList = $this->getKabupatenList($allData);
        $paginated = $this->paginateAndFilter($produkList);
        return $this->renderView('rekap_produk', [
            'pageTitle' => 'Rekap Data Produk Pesantren (OPOP) | PENDEKAR',
            'produkList' => $produkList
        ,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList]);
    }

    public function karyailmiah()
    {
        $allData = $this->loadIntegratedData();
        $karyaList = [];

        foreach ($allData as $nspp => $p) {
            $kitabItems = $p['karya_kitab'] ?? [];
            if (is_array($kitabItems)) {
                foreach ($kitabItems as $item) {
                    $karyaList[] = [
                        'pesantren' => $p['nama'] ?? 'Pesantren',
                        'kabupaten' => $p['kabupaten'] ?? '-',
                        'judul' => $item['judul'] ?? 'Karya Kitab / Ilmiah',
                        'penulis' => !empty($item['pengarang']) ? $item['pengarang'] : 'Santri / Pengasuh',
                        'tahun' => $item['tahun'] ?? '-',
                        'halaman' => $item['halaman'] ?? '-',
                        'jilid' => $item['jilid'] ?? '-',
                        'isbn' => $item['isbn'] ?? '-',
                        'haki' => $item['haki'] ?? '-',
                        'penerbit' => $item['penerbit'] ?? '-',
                        'link_karya' => $item['link_karya'] ?? '',
                        'telepon' => $item['telepon'] ?? '-',
                        'deskripsi' => $item['deskripsi'] ?? 'Karya tulisan / kitab keilmuan pesantren.',
                        'kategori' => 'Karya Kitab / Ilmiah',
                        'raw_json' => htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8')
                    ];
                }
            }
        }

        if (empty($karyaList)) {
            $karyaList = [
                ['pesantren' => 'PP. Lirboyo', 'kabupaten' => 'Kota Kediri', 'judul' => 'ILMU MANASIK VERSI ANDROID', 'penulis' => 'DR. H. NUR YASIN SHIROTOL MUSTAQIM, MA', 'tahun' => '2023', 'halaman' => 'Digital App', 'jilid' => '1', 'isbn' => '-', 'haki' => '-', 'penerbit' => 'Mandiri', 'link_karya' => 'https://play.google.com/store/apps/details?id=com.ilmumanasik.android', 'telepon' => '081230000177', 'deskripsi' => 'Aplikasi ilmu manasik haji berbasis Android.', 'kategori' => 'Karya Kitab / Digital'],
                ['pesantren' => 'PP. Tebuireng', 'kabupaten' => 'Jombang', 'judul' => 'KEPEMIMPINAN PENDIDIKAN (INTEGRASI TEORI DAN PRAKTIK)', 'penulis' => 'WAHYU SYAF\'AAT', 'tahun' => '2024', 'halaman' => '80', 'jilid' => '1', 'isbn' => '978-623-92462-2-8', 'haki' => 'PDKI Kemenkumham', 'penerbit' => 'Staisam Pustaka', 'link_karya' => '', 'telepon' => '085730552525', 'deskripsi' => 'Buku ajar kepemimpinan pendidikan.', 'kategori' => 'Karya Kitab / Ilmiah',
                        'raw_json' => htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8')]
            ];
        }

        
        $kabupatenList = $this->getKabupatenList($allData);
        $paginated = $this->paginateAndFilter($karyaList);
        return $this->renderView('rekap_karyailmiah', [
            'pageTitle' => 'Rekap Data Karya & Kitab Pesantren | PENDEKAR JATIM',
            'karyaList' => $karyaList
        ,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList]);
    }

    private function renderView($viewName, $data = [])
    {
        extract($data);
        $viewFile = __DIR__ . "/../../../resources/views/{$viewName}.blade.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "View {$viewName} not found.";
        }
    }
}
