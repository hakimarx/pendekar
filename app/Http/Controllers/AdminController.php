<?php

namespace App\Http\Controllers;

class AdminController
{
    public function __construct()
    {
        // Authenticate admin role
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login?info=' . urlencode('Akses dibatasi. Halaman ini memerlukan hak akses Admin.'));
            exit;
        }
    }

    public function index()
    {
        try {
            $pdo = \App\Database::getInstance()->getConnection();
            
            // Get submissions list
            $stmt = $pdo->prepare("SELECT * FROM submissions ORDER BY created_at DESC");
            $stmt->execute();
            $submissions = $stmt->fetchAll() ?: [];

            // Get users list
            $stmtUsers = $pdo->prepare("SELECT id, username, role FROM users ORDER BY username ASC");
            $stmtUsers->execute();
            $users = $stmtUsers->fetchAll() ?: [];

            // Compute statistics
            $stats = [
                'total' => count($submissions),
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0
            ];

            foreach ($submissions as $sub) {
                if ($sub['status'] === 'pending') $stats['pending']++;
                elseif ($sub['status'] === 'approved') $stats['approved']++;
                elseif ($sub['status'] === 'rejected') $stats['rejected']++;
            }
        } catch (\PDOException $e) {
            $submissions = [];
            $users = [];
            $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
            $error = "Terjadi kesalahan database: " . $e->getMessage();
        }

        return $this->renderView('admin', [
            'pageTitle' => 'Dashboard Admin | PENDEKAR',
            'submissions' => $submissions,
            'users' => $users,
            'stats' => $stats,
            'error' => $error ?? null
        ]);
    }

    public function addUser()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'verifikator';

        if (empty($username) || empty($password)) {
            $_SESSION['flash_error'] = "Username dan password wajib diisi.";
            header('Location: /admin');
            exit;
        }

        try {
            $pdo = \App\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
            $stmt->execute([
                ':username' => $username,
                ':password' => password_hash($password, PASSWORD_BCRYPT),
                ':role' => $role
            ]);

            $_SESSION['flash_success'] = "Pengguna baru '{$username}' berhasil didaftarkan sebagai " . ucfirst($role) . "!";
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'UNIQUE') !== false) {
                $_SESSION['flash_error'] = "Username '{$username}' sudah terdaftar.";
            } else {
                $_SESSION['flash_error'] = "Gagal mendaftarkan user: " . $e->getMessage();
            }
        }

        header('Location: /admin');
        exit;
    }

    public function edit()
    {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            header('Location: /admin');
            exit;
        }

        try {
            $pdo = \App\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM submissions WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $submission = $stmt->fetch();

            if (!$submission) {
                $_SESSION['flash_error'] = "Data pengajuan tidak ditemukan.";
                header('Location: /admin');
                exit;
            }
        } catch (\PDOException $e) {
            $_SESSION['flash_error'] = "Database error: " . $e->getMessage();
            header('Location: /admin');
            exit;
        }

        return $this->renderView('edit_submission', [
            'pageTitle' => 'Edit Pengajuan | PENDEKAR',
            'sub' => $submission
        ]);
    }

    public function update()
    {
        $id = $_POST['id'] ?? '';
        $namaPesantren = $_POST['nama_pesantren'] ?? '';
        $nspp = $_POST['nspp'] ?? '';
        $kabupaten = $_POST['kabupaten'] ?? '';
        $kategori = $_POST['kategori'] ?? '';
        $alamat = $_POST['alamat'] ?? '';
        $latitude = $_POST['latitude'] ?? null;
        $longitude = $_POST['longitude'] ?? null;

        if (empty($id) || empty($namaPesantren) || empty($nspp) || empty($kabupaten) || empty($kategori)) {
            $_SESSION['flash_error'] = "Semua field bertanda bintang (*) wajib diisi.";
            header("Location: /admin/edit?id=" . urlencode($id));
            exit;
        }

        // Collect dynamic details based on category
        $details = [];
        switch ($kategori) {
            case 'produk':
                $details = [
                    'produk_nama' => $_POST['produk_nama'] ?? '',
                    'produk_kontak' => $_POST['produk_kontak'] ?? '',
                    'produk_deskripsi' => $_POST['produk_deskripsi'] ?? '',
                ];
                break;
            case 'karya':
                $details = [
                    'karya_penulis' => $_POST['karya_penulis'] ?? '',
                    'karya_judul' => $_POST['karya_judul'] ?? '',
                ];
                break;
            case 'digital':
                $details = [
                    'digital_layanan' => $_POST['digital_layanan'] ?? '',
                    'digital_web' => $_POST['digital_web'] ?? '',
                ];
                break;
            case 'ecoteologi':
                $details = [
                    'eco_program' => $_POST['eco_program'] ?? '',
                ];
                break;
            case 'aman':
                $details = [
                    'aman_program' => $_POST['aman_program'] ?? '',
                ];
                break;
            case 'ramah_anak':
                $details = [
                    'ramah_program' => $_POST['ramah_program'] ?? '',
                ];
                break;
        }
        $detailsJson = json_encode($details);

        try {
            $pdo = \App\Database::getInstance()->getConnection();
            
            // Get old submission to check its status and old NSPP
            $stmtOld = $pdo->prepare("SELECT * FROM submissions WHERE id = :id");
            $stmtOld->execute([':id' => $id]);
            $oldSub = $stmtOld->fetch();

            if (!$oldSub) {
                $_SESSION['flash_error'] = "Data tidak ditemukan.";
                header('Location: /admin');
                exit;
            }

            // Update in DB
            $stmt = $pdo->prepare("UPDATE submissions SET nama_pesantren = :nama, nspp = :nspp, kabupaten = :kab, kategori = :kat, details = :details, alamat = :alamat, latitude = :lat, longitude = :lng WHERE id = :id");
            $stmt->execute([
                ':nama' => $namaPesantren,
                ':nspp' => $nspp,
                ':kab' => $kabupaten,
                ':kat' => $kategori,
                ':details' => $detailsJson,
                ':alamat' => $alamat,
                ':lat' => $latitude !== '' ? (float)$latitude : null,
                ':lng' => $longitude !== '' ? (float)$longitude : null,
                ':id' => $id
            ]);

            // If the submission was already approved, update the JSON files
            if ($oldSub['status'] === 'approved') {
                $this->updateJsonData($oldSub['nspp'], [
                    'nspp' => $nspp,
                    'nama' => strtoupper($namaPesantren),
                    'jenjang' => 'Salafiyah/Modern',
                    'alamat' => $alamat ?: '-',
                    'kabupaten' => strtoupper($kabupaten),
                    'kecamatan' => '-',
                    'afiliasi' => 'Umum',
                    'latitude' => $latitude !== '' ? (float)$latitude : -7.2504,
                    'longitude' => $longitude !== '' ? (float)$longitude : 112.7688,
                    'bencana' => [
                        'level_kerawanan' => 'RENDAH',
                        'closest_disaster_type' => 'Tidak Ada',
                    ],
                    'digital' => [
                        'is_digital' => ($kategori === 'digital')
                    ],
                    'ecoteologi' => [
                        'is_ecoteologi' => ($kategori === 'ecoteologi'),
                        'program' => $details['eco_program'] ?? '',
                        'fokus' => 'Pelestarian Lingkungan'
                    ],
                    'ramah_anak' => [
                        'is_ramah_anak' => ($kategori === 'ramah_anak'),
                        'program' => $details['ramah_program'] ?? '',
                        'kurikulum' => 'Anti-Bullying'
                    ],
                    'opop' => ($kategori === 'produk') ? [
                        [
                            'nama_produk' => $details['produk_nama'] ?? 'Produk Inovasi',
                            'deskripsi' => $details['produk_deskripsi'] ?? 'Produk santri',
                            'kategori' => 'OPOP / Enterpreneur',
                            'tahun' => date('Y'),
                            'telepon' => $details['produk_kontak'] ?? '-',
                            'gambar' => null
                        ]
                    ] : 0,
                    'karya_kitab' => ($kategori === 'karya') ? [
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
                ]);
            }

            $_SESSION['flash_success'] = "Data pengajuan berhasil diperbarui!";
            header('Location: /admin');
            exit;
        } catch (\PDOException $e) {
            $_SESSION['flash_error'] = "Database error: " . $e->getMessage();
            header("Location: /admin/edit?id=" . urlencode($id));
            exit;
        }
    }

    private function updateJsonData($oldNspp, $newRecord)
    {
        // 1. Update public/data/pesantren_integrated.json
        $dataPath = __DIR__ . '/../../../public/data/pesantren_integrated.json';
        if (!file_exists($dataPath)) {
            $dataPath = __DIR__ . '/../../../data/merged_pesantren_data.json';
        }
        if (file_exists($dataPath)) {
            $data = json_decode(file_get_contents($dataPath), true) ?: [];
            $updated = false;
            foreach ($data as $key => $item) {
                if ($item['nspp'] == $oldNspp) {
                    $data[$key] = array_merge($item, $newRecord);
                    $updated = true;
                    break;
                }
            }
            if (!$updated) {
                $data[] = $newRecord;
            }
            file_put_contents($dataPath, json_encode($data, JSON_PRETTY_PRINT));
        }

        // 2. Update public/data/pesantren_map_data.json
        $mapPath = __DIR__ . '/../../../public/data/pesantren_map_data.json';
        if (file_exists($mapPath)) {
            $mapData = json_decode(file_get_contents($mapPath), true) ?: [];
            $updatedMap = false;
            foreach ($mapData as $key => $item) {
                if ($item['nspp'] == $oldNspp) {
                    $mapData[$key] = array_merge($item, $newRecord);
                    $updatedMap = true;
                    break;
                }
            }
            if (!$updatedMap) {
                $mapData[] = $newRecord;
            }
            file_put_contents($mapPath, json_encode($mapData));
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
