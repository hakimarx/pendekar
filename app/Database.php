<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $dbDir = __DIR__ . '/../database';
        if (!file_exists($dbDir)) {
            mkdir($dbDir, 0777, true);
        }

        $dbFile = $dbDir . '/database.sqlite';
        try {
            $this->pdo = new PDO("sqlite:" . $dbFile);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->initializeDatabase();
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    private function initializeDatabase()
    {
        // Create users table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL
        )");

        // Create submissions table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_pesantren TEXT NOT NULL,
            nspp TEXT NOT NULL,
            kabupaten TEXT NOT NULL,
            kategori TEXT NOT NULL,
            details TEXT,
            status TEXT DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Add additional columns dynamically if they don't exist
        try {
            $this->pdo->exec("ALTER TABLE submissions ADD COLUMN alamat TEXT");
        } catch (\PDOException $e) {}
        try {
            $this->pdo->exec("ALTER TABLE submissions ADD COLUMN latitude REAL");
        } catch (\PDOException $e) {}
        try {
            $this->pdo->exec("ALTER TABLE submissions ADD COLUMN longitude REAL");
        } catch (\PDOException $e) {}

        // Seed default users if table is empty
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $users = [
                [
                    'username' => 'admin',
                    'password' => password_hash('admin123', PASSWORD_BCRYPT),
                    'role' => 'admin'
                ],
                [
                    'username' => 'verifikator',
                    'password' => password_hash('verifikator123', PASSWORD_BCRYPT),
                    'role' => 'verifikator'
                ]
            ];

            $insertStmt = $this->pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
            foreach ($users as $user) {
                $insertStmt->execute([
                    ':username' => $user['username'],
                    ':password' => $user['password'],
                    ':role' => $user['role']
                ]);
            }
        }

        // Seed submissions from JSON & CSV if empty
        $stmtSub = $this->pdo->query("SELECT COUNT(id) FROM submissions");
        $subCount = $stmtSub->fetchColumn();

        if ($subCount == 0) {
            // 1. Seed from JSON
            $dataPath = __DIR__ . '/../public/data/pesantren_integrated.json';
            if (!file_exists($dataPath)) {
                $dataPath = __DIR__ . '/../data/merged_pesantren_data.json';
            }
            if (file_exists($dataPath)) {
                $json = file_get_contents($dataPath);
                $data = json_decode($json, true);
                if (is_array($data)) {
                    $insertStmt = $this->pdo->prepare("INSERT INTO submissions (nama_pesantren, nspp, kabupaten, kategori, details, status, alamat, latitude, longitude) VALUES (:nama, :nspp, :kab, :kat, :details, 'approved', :alamat, :lat, :lng)");
                    
                    $this->pdo->beginTransaction();
                    foreach ($data as $p) {
                        $nama = $p['nama'] ?? 'Pesantren';
                        $nspp = $p['nspp'] ?? '';
                        $kab = $p['kabupaten'] ?? '';
                        $alamat = $p['alamat'] ?? '';
                        $lat = $p['latitude'] ?? null;
                        $lng = $p['longitude'] ?? null;
                        
                        $kat = 'umum';
                        $details = [];
                        
                        if (!empty($p['digital']['is_digital'])) {
                            $kat = 'digital';
                            $details = [
                                'digital_layanan' => $p['digital']['digital_layanan'] ?? 'Sistem Informasi',
                                'digital_web' => $p['digital']['digital_web'] ?? '',
                            ];
                        } elseif (!empty($p['ecoteologi']['is_ecoteologi'])) {
                            $kat = 'ecoteologi';
                            $details = [
                                'eco_program' => $p['ecoteologi']['program'] ?? '',
                            ];
                        } elseif (!empty($p['ramah_anak']['is_ramah_anak'])) {
                            $kat = 'ramah_anak';
                            $details = [
                                'ramah_program' => $p['ramah_anak']['program'] ?? '',
                            ];
                        } elseif (is_array($p['opop']) && count($p['opop']) > 0) {
                            $kat = 'produk';
                            $item = $p['opop'][0];
                            $details = [
                                'produk_nama' => $item['nama_produk'] ?? '',
                                'produk_kontak' => $item['telepon'] ?? '',
                                'produk_deskripsi' => $item['deskripsi'] ?? '',
                            ];
                        } elseif (is_array($p['karya_kitab']) && count($p['karya_kitab']) > 0) {
                            $kat = 'karya';
                            $item = $p['karya_kitab'][0];
                            $details = [
                                'karya_penulis' => $item['pengarang'] ?? '',
                                'karya_judul' => $item['judul'] ?? '',
                            ];
                        }
                        
                        $insertStmt->execute([
                            ':nama' => $nama,
                            ':nspp' => $nspp,
                            ':kab' => $kab,
                            ':kat' => $kat,
                            ':details' => json_encode($details),
                            ':alamat' => $alamat,
                            ':lat' => $lat,
                            ':lng' => $lng
                        ]);
                    }
                    $this->pdo->commit();
                }
            }

            // 2. Seed from CSV (digital)
            $csvPath = __DIR__ . '/../digital/pesantren_digital_jatim.csv';
            if (file_exists($csvPath)) {
                $rows = array_map('str_getcsv', file($csvPath));
                $header = array_shift($rows);
                if (isset($header[0])) {
                    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
                }
                
                $insertStmt = $this->pdo->prepare("INSERT INTO submissions (nama_pesantren, nspp, kabupaten, kategori, details, status, alamat, latitude, longitude) VALUES (:nama, :nspp, :kab, 'digital', :details, 'approved', :alamat, :lat, :lng)");
                
                $this->pdo->beginTransaction();
                foreach ($rows as $row) {
                    if (count($row) == count($header)) {
                        $item = array_combine($header, $row);
                        $nama = $item['Nama Pesantren'] ?? '';
                        $kab = $item['Kabupaten/Kota'] ?? '';
                        $alamat = $item['Alamat'] ?? '';
                        
                        // Check if already seeded by checking name
                        $checkStmt = $this->pdo->prepare("SELECT COUNT(id) FROM submissions WHERE nama_pesantren = :nama");
                        $checkStmt->execute([':nama' => $nama]);
                        if ($checkStmt->fetchColumn() > 0) {
                            continue;
                        }
                        
                        $details = [
                            'digital_layanan' => $item['Layanan Digital'] ?? 'Sistem Informasi',
                            'digital_web' => $item['Website/Referensi'] ?? '',
                        ];
                        
                        $insertStmt->execute([
                            ':nama' => $nama,
                            ':nspp' => '-',
                            ':kab' => $kab,
                            ':details' => json_encode($details),
                            ':alamat' => $alamat,
                            ':lat' => -7.2504 + (rand(-100, 100) / 1000.0),
                            ':lng' => 112.7688 + (rand(-100, 100) / 1000.0)
                        ]);
                    }
                }
                $this->pdo->commit();
            }
        }
    }
}
