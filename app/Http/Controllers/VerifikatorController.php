<?php

namespace App\Http\Controllers;

class VerifikatorController
{
    public function __construct()
    {
        // Authenticate verifikator or admin role
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'verifikator' && $_SESSION['role'] !== 'admin')) {
            header('Location: ' . url('/login?info=' . urlencode('Akses dibatasi. Silakan login terlebih dahulu.'));
            exit;
        }
    }

    public function index()
    {
        try {
            $pdo = \App\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM submissions WHERE status = 'pending' ORDER BY created_at DESC");
            $stmt->execute();
            $pendingList = $stmt->fetchAll();
        } catch (\PDOException $e) {
            $pendingList = [];
            $error = "Gagal memuat data pendataan: " . $e->getMessage();
        }

        return $this->renderView('verifikator', [
            'pageTitle' => 'Dashboard Verifikator | PENDEKAR',
            'pendingList' => $pendingList,
            'error' => $error ?? null
        ]);
    }

    public function approve()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/verifikator'));
            exit;
        }

        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            header('Location: ' . url('/verifikator'));
            exit;
        }

        try {
            $pdo = \App\Database::getInstance()->getConnection();
            
            // Get submission details
            $stmt = $pdo->prepare("SELECT * FROM submissions WHERE id = :id AND status = 'pending'");
            $stmt->execute([':id' => $id]);
            $sub = $stmt->fetch();

            if (!$sub) {
                $_SESSION['flash_error'] = "Data pendataan tidak ditemukan atau sudah diproses.";
                header('Location: ' . url('/verifikator'));
                exit;
            }

            // Update status in DB
            $updateStmt = $pdo->prepare("UPDATE submissions SET status = 'approved' WHERE id = :id");
            $updateStmt->execute([':id' => $id]);

            // Append to JSON files
            $this->appendToIntegratedData($sub);

            $_SESSION['flash_success'] = "Data pendataan untuk '" . htmlspecialchars($sub['nama_pesantren']) . "' berhasil disetujui!";
        } catch (\PDOException $e) {
            $_SESSION['flash_error'] = "Terjadi kesalahan database: " . $e->getMessage();
        }

        header('Location: ' . url('/verifikator'));
        exit;
    }

    public function reject()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/verifikator'));
            exit;
        }

        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            header('Location: ' . url('/verifikator'));
            exit;
        }

        try {
            $pdo = \App\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("UPDATE submissions SET status = 'rejected' WHERE id = :id AND status = 'pending'");
            $stmt->execute([':id' => $id]);

            $_SESSION['flash_success'] = "Data pendataan berhasil ditolak.";
        } catch (\PDOException $e) {
            $_SESSION['flash_error'] = "Gagal memproses penolakan: " . $e->getMessage();
        }

        header('Location: ' . url('/verifikator'));
        exit;
    }

    private function appendToIntegratedData($sub)
    {
        $details = json_decode($sub['details'], true) ?: [];

        // Build data structure matching pesantren schemas
        $newRecord = [
            'nspp' => $sub['nspp'],
            'nama' => strtoupper($sub['nama_pesantren']),
            'jenjang' => 'Salafiyah/Modern',
            'alamat' => !empty($sub['alamat']) ? $sub['alamat'] : '-',
            'kabupaten' => strtoupper($sub['kabupaten']),
            'kecamatan' => '-',
            'afiliasi' => 'Umum',
            'latitude' => !empty($sub['latitude']) ? (float)$sub['latitude'] : (-7.2504 + (rand(-100, 100) / 1000.0)),
            'longitude' => !empty($sub['longitude']) ? (float)$sub['longitude'] : (112.7688 + (rand(-100, 100) / 1000.0)),
            'bencana' => [
                'level_kerawanan' => 'RENDAH',
                'closest_disaster_type' => 'Tidak Ada',
            ],
            'digital' => [
                'is_digital' => ($sub['kategori'] === 'digital')
            ],
            'ecoteologi' => [
                'is_ecoteologi' => ($sub['kategori'] === 'ecoteologi'),
                'program' => $details['eco_program'] ?? '',
                'fokus' => 'Pelestarian Lingkungan'
            ],
            'ramah_anak' => [
                'is_ramah_anak' => ($sub['kategori'] === 'ramah_anak'),
                'program' => $details['ramah_program'] ?? '',
                'kurikulum' => 'Anti-Bullying'
            ],
            'opop' => ($sub['kategori'] === 'produk') ? [
                [
                    'nama_produk' => $details['produk_nama'] ?? 'Produk Inovasi',
                    'deskripsi' => $details['produk_deskripsi'] ?? 'Produk santri',
                    'kategori' => 'OPOP / Enterpreneur',
                    'tahun' => date('Y'),
                    'telepon' => $details['produk_kontak'] ?? '-',
                    'gambar' => null
                ]
            ] : 0,
            'karya_kitab' => ($sub['kategori'] === 'karya') ? [
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

        // 1. Update public/data/pesantren_integrated.json
        $dataPath = __DIR__ . '/../../../public/data/pesantren_integrated.json';
        if (!file_exists($dataPath)) {
            $dataPath = __DIR__ . '/../../../data/merged_pesantren_data.json';
        }
        if (file_exists($dataPath)) {
            $data = json_decode(file_get_contents($dataPath), true) ?: [];
            $data[] = $newRecord;
            file_put_contents($dataPath, json_encode($data, JSON_PRETTY_PRINT));
        }

        // 2. Update public/data/pesantren_map_data.json
        $mapPath = __DIR__ . '/../../../public/data/pesantren_map_data.json';
        if (file_exists($mapPath)) {
            $mapData = json_decode(file_get_contents($mapPath), true) ?: [];
            $mapData[] = $newRecord;
            file_put_contents($mapPath, json_encode($mapData));
        }

        // 3. Append to CSV if Digital category
        if ($sub['kategori'] === 'digital') {
            $csvPath = __DIR__ . '/../../../digital/pesantren_digital_jatim.csv';
            if (file_exists($csvPath)) {
                $fp = fopen($csvPath, 'a');
                fputcsv($fp, [
                    strtoupper($sub['nama_pesantren']),
                    strtoupper($sub['kabupaten']),
                    '-',
                    $details['digital_layanan'] ?? 'Sistem Informasi',
                    $details['digital_layanan'] ?? 'Sistem Informasi',
                    'Peningkatan Efisiensi',
                    $details['digital_web'] ?? 'https://'
                ]);
                fclose($fp);
            }
        }
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
