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

    private function loadIntegratedDataFromDb($category = null)
    {
        try {
            $pdo = \App\Database::getInstance()->getConnection();
            if ($category) {
                $stmt = $pdo->prepare("SELECT * FROM submissions WHERE status = 'approved' AND kategori = :kat ORDER BY id DESC");
                $stmt->execute([':kat' => $category]);
            } else {
                $stmt = $pdo->query("SELECT * FROM submissions WHERE status = 'approved' ORDER BY id DESC");
            }
            $rows = $stmt->fetchAll() ?: [];
            
            $data = [];
            foreach ($rows as $r) {
                $details = json_decode($r['details'], true) ?: [];
                $data[] = [
                    'id' => $r['id'],
                    'nspp' => $r['nspp'],
                    'nama' => $r['nama_pesantren'],
                    'jenjang' => 'Salafiyah/Modern',
                    'alamat' => $r['alamat'] ?: '-',
                    'kabupaten' => $r['kabupaten'],
                    'kecamatan' => '-',
                    'afiliasi' => 'Umum',
                    'latitude' => $r['latitude'] ? (float)$r['latitude'] : null,
                    'longitude' => $r['longitude'] ? (float)$r['longitude'] : null,
                    'bencana' => [
                        'level_kerawanan' => 'RENDAH',
                        'closest_disaster_type' => 'Tidak Ada',
                    ],
                    'is_digital' => ($r['kategori'] === 'digital'),
                    'digital' => [
                        'is_digital' => ($r['kategori'] === 'digital'),
                        'digital_layanan' => $details['digital_layanan'] ?? '',
                        'digital_web' => $details['digital_web'] ?? '',
                    ],
                    'Unggulan Aplikasi' => $details['digital_layanan'] ?? 'Sistem Informasi',
                    'Website/Referensi' => $details['digital_web'] ?? '',
                    'ecoteologi' => [
                        'is_ecoteologi' => ($r['kategori'] === 'ecoteologi'),
                        'program' => $details['eco_program'] ?? '',
                        'fokus' => 'Pelestarian Lingkungan'
                    ],
                    'ramah_anak' => [
                        'is_ramah_anak' => ($r['kategori'] === 'ramah_anak'),
                        'program' => $details['ramah_program'] ?? '',
                        'kurikulum' => 'Anti-Bullying'
                    ],
                    'opop' => ($r['kategori'] === 'produk') ? [
                        [
                            'nama_produk' => $details['produk_nama'] ?? 'Produk Inovasi',
                            'deskripsi' => $details['produk_deskripsi'] ?? 'Produk santri',
                            'kategori' => 'OPOP / Enterpreneur',
                            'tahun' => date('Y'),
                            'telepon' => $details['produk_kontak'] ?? '-',
                            'gambar' => null
                        ]
                    ] : 0,
                    'karya_kitab' => ($r['kategori'] === 'karya') ? [
                        [
                            'judul' => $details['karya_judul'] ?? 'Kitab Unggulan',
                            'pengarang' => $details['karya_penulis'] ?? 'Santri',
                            'tahun' => date('Y'),
                            'halaman' => '100',
                            'jilid' => '1',
                            'isbn' => '-',
                            'haki' => 'Terdaftar',
                            'penerbit' => 'Mandiri',
                            'link_karya' => '',
                            'telepon' => '-',
                            'deskripsi' => 'Kitab keilmuan pesantren.'
                        ]
                    ] : 0
                ];
            }
            return $data;
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function pesantren()
    {
        $allData = $this->loadIntegratedDataFromDb();
        $pesantrenList = [];

        foreach ($allData as $p) {
            $pesantrenList[] = [
                'id' => $p['id'] ?? null,
                'nspp' => $p['nspp'] ?? '-',
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
            'pesantrenList' => $pesantrenList,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList
        ]);
    }

    public function bencana()
    {
        $allData = $this->loadIntegratedDataFromDb();
        $bencanaList = [];

        foreach ($allData as $p) {
            $b = $p['bencana'] ?? [];
            if (!empty($b)) {
                $bencanaList[] = [
                    'id' => $p['id'] ?? null,
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
            'bencanaList' => $bencanaList,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList
        ]);
    }

    public function ramah()
    {
        $allData = $this->loadIntegratedDataFromDb('ramah_anak');
        $ramahList = [];

        foreach ($allData as $p) {
            $r = $p['ramah_anak'] ?? [];
            if (!empty($r['is_ramah_anak'])) {
                $ramahList[] = [
                    'id' => $p['id'] ?? null,
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
            'ramahList' => $ramahList,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList
        ]);
    }

    public function digital()
    {
        $allData = $this->loadIntegratedDataFromDb('digital');
        $digitalList = [];

        foreach ($allData as $p) {
            $digitalList[] = [
                'id' => $p['id'] ?? null,
                'nama' => $p['nama'] ?? 'Pesantren',
                'kabupaten' => $p['kabupaten'] ?? '-',
                'alamat' => $p['alamat'] ?? '-',
                'nspp' => $p['nspp'] ?? '-',
                'Unggulan Aplikasi' => $p['Unggulan Aplikasi'] ?? 'Sistem Informasi',
                'Website/Referensi' => $p['Website/Referensi'] ?? '',
                'raw_json' => htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8')
            ];
        }

        $kabupatenList = $this->getKabupatenList($allData);
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
        $allData = $this->loadIntegratedDataFromDb('ecoteologi');
        $ecoList = [];

        foreach ($allData as $p) {
            $e = $p['ecoteologi'] ?? [];
            if (!empty($e['is_ecoteologi'])) {
                $ecoList[] = [
                    'id' => $p['id'] ?? null,
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
            'ecoList' => $ecoList,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList
        ]);
    }

    public function produk()
    {
        $allData = $this->loadIntegratedDataFromDb('produk');
        $produkList = [];

        foreach ($allData as $p) {
            $opopItems = $p['opop'] ?? [];
            if (is_array($opopItems)) {
                foreach ($opopItems as $item) {
                    $produkList[] = [
                        'id' => $p['id'] ?? null,
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

        $kabupatenList = $this->getKabupatenList($allData);
        $paginated = $this->paginateAndFilter($produkList);
        return $this->renderView('rekap_produk', [
            'pageTitle' => 'Rekap Data Produk Pesantren (OPOP) | PENDEKAR',
            'produkList' => $produkList,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList
        ]);
    }

    public function karyailmiah()
    {
        $allData = $this->loadIntegratedDataFromDb('karya');
        $karyaList = [];

        foreach ($allData as $p) {
            $kitabItems = $p['karya_kitab'] ?? [];
            if (is_array($kitabItems)) {
                foreach ($kitabItems as $item) {
                    $karyaList[] = [
                        'id' => $p['id'] ?? null,
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

        $kabupatenList = $this->getKabupatenList($allData);
        $paginated = $this->paginateAndFilter($karyaList);
        return $this->renderView('rekap_karyailmiah', [
            'pageTitle' => 'Rekap Data Karya & Kitab Pesantren | PENDEKAR JATIM',
            'karyaList' => $karyaList,
            'paginated' => $paginated,
            'kabupatenList' => $kabupatenList
        ]);
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
