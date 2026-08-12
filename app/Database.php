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
    }
}
