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
