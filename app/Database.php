<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo = null;
    private $error = null;

    private function __construct()
    {
        $dbDir = __DIR__ . '/../database';
        if (!file_exists($dbDir)) {
            @mkdir($dbDir, 0777, true);
        }

        $dbFile = $dbDir . '/database.sqlite';
        try {
            if (!class_exists('PDO')) {
                throw new \Exception("PDO extension is not loaded in PHP.");
            }
            if (!in_array('sqlite', PDO::getAvailableDrivers())) {
                throw new \Exception("SQLite PDO driver (pdo_sqlite) is not enabled in PHP.");
            }

            $this->pdo = new PDO("sqlite:" . $dbFile);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->initializeDatabase();
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
            error_log("PENDEKAR Database Connection Error: " . $e->getMessage());
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
        if ($this->pdo === null) {
            $msg = $this->error ?: "Database connection unavailable.";
            throw new \Exception("Database Error: " . $msg);
        }
        return $this->pdo;
    }

    public function getError()
    {
        return $this->error;
    }

    private function initializeDatabase()
    {
        if (!$this->pdo) {
            return;
        }

        try {
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
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                alamat TEXT,
                latitude REAL,
                longitude REAL
            )");

            // Add additional columns dynamically if needed
            try { $this->pdo->exec("ALTER TABLE submissions ADD COLUMN alamat TEXT"); } catch (\Throwable $e) {}
            try { $this->pdo->exec("ALTER TABLE submissions ADD COLUMN latitude REAL"); } catch (\Throwable $e) {}
            try { $this->pdo->exec("ALTER TABLE submissions ADD COLUMN longitude REAL"); } catch (\Throwable $e) {}

            // Seed default users if table is empty
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
            $count = $stmt ? $stmt->fetchColumn() : 0;

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

            // Seed submissions from JSON & CSV only if empty
            $stmtSub = $this->pdo->query("SELECT COUNT(id) FROM submissions");
            $subCount = $stmtSub ? $stmtSub->fetchColumn() : 0;

            if ($subCount < 100) {
                @ini_set('memory_limit', '512M');
                @set_time_limit(120);

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
                            } elseif (is_array($p['opop'] ?? null) && count($p['opop']) > 0) {
                                $kat = 'produk';
                                $item = $p['opop'][0];
                                $details = [
                                    'produk_nama' => $item['nama_produk'] ?? '',
                                    'produk_kontak' => $item['telepon'] ?? '',
                                    'produk_deskripsi' => $item['deskripsi'] ?? '',
                                ];
                            } elseif (is_array($p['karya_kitab'] ?? null) && count($p['karya_kitab']) > 0) {
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
            }
        } catch (\Throwable $e) {
            error_log("Database initialization error: " . $e->getMessage());
        }
    }
}
