<?php

namespace App\Http\Controllers;

class LoginController
{
    public function index()
    {
        if (isset($_SESSION['role'])) {
            $this->redirectByRole($_SESSION['role']);
        }

        return $this->renderView('login', [
            'pageTitle' => 'Login | PENDEKAR PD PONTREN JATIM'
        ]);
    }

    public function login()
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            return $this->renderView('login', [
                'pageTitle' => 'Login | PENDEKAR PD PONTREN JATIM',
                'error' => 'Username dan password wajib diisi.'
            ]);
        }

        try {
            $pdo = \App\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                $this->redirectByRole($user['role']);
            } else {
                return $this->renderView('login', [
                    'pageTitle' => 'Login | PENDEKAR PD PONTREN JATIM',
                    'error' => 'Username atau password salah.'
                ]);
            }
        } catch (\PDOException $e) {
            return $this->renderView('login', [
                'pageTitle' => 'Login | PENDEKAR PD PONTREN JATIM',
                'error' => 'Kesalahan database: ' . $e->getMessage()
            ]);
        }
    }

    public function logout()
    {
        session_destroy();
        header('Location: ' . url('/'));
        exit;
    }

    public function settingsForm()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login?info=' . urlencode('Silakan login terlebih dahulu.'));
            exit;
        }

        return $this->renderView('settings', [
            'pageTitle' => 'Pengaturan Akun | PENDEKAR'
        ]);
    }

    public function updateSettings()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login?info=' . urlencode('Silakan login terlebih dahulu.'));
            exit;
        }

        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            return $this->renderView('settings', [
                'pageTitle' => 'Pengaturan Akun | PENDEKAR',
                'error' => 'Semua kolom wajib diisi.'
            ]);
        }

        if ($newPassword !== $confirmPassword) {
            return $this->renderView('settings', [
                'pageTitle' => 'Pengaturan Akun | PENDEKAR',
                'error' => 'Konfirmasi password baru tidak cocok.'
            ]);
        }

        if (strlen($newPassword) < 6) {
            return $this->renderView('settings', [
                'pageTitle' => 'Pengaturan Akun | PENDEKAR',
                'error' => 'Password baru minimal 6 karakter.'
            ]);
        }

        try {
            $pdo = \App\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user && password_verify($oldPassword, $user['password'])) {
                $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $updateStmt->execute([
                    ':password' => password_hash($newPassword, PASSWORD_BCRYPT),
                    ':id' => $_SESSION['user_id']
                ]);

                return $this->renderView('settings', [
                    'pageTitle' => 'Pengaturan Akun | PENDEKAR',
                    'success' => 'Password Anda berhasil diubah!'
                ]);
            } else {
                return $this->renderView('settings', [
                    'pageTitle' => 'Pengaturan Akun | PENDEKAR',
                    'error' => 'Password saat ini salah.'
                ]);
            }
        } catch (\PDOException $e) {
            return $this->renderView('settings', [
                'pageTitle' => 'Pengaturan Akun | PENDEKAR',
                'error' => 'Kesalahan database: ' . $e->getMessage()
            ]);
        }
    }

    private function redirectByRole($role)
    {
        if ($role === 'admin') {
            header('Location: ' . url('/admin'));
        } else {
            header('Location: ' . url('/verifikator'));
        }
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
