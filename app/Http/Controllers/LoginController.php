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
        header('Location: /');
        exit;
    }

    private function redirectByRole($role)
    {
        if ($role === 'admin') {
            header('Location: /admin');
        } else {
            header('Location: /verifikator');
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
