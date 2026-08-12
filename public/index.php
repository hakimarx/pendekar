<?php

session_start();

define('LARAVEL_START', microtime(true));

// Auto-loader helper if vendor is present or manual fallback
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

// Require database
require_once __DIR__ . '/../app/Database.php';
\App\Database::getInstance();

// Basic Routing Engine for Laravel App Structure
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Serve static assets if exists
$publicFile = __DIR__ . $uri;
if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    $mime = mime_content_type($publicFile);
    if (str_ends_with($publicFile, '.css')) $mime = 'text/css';
    if (str_ends_with($publicFile, '.js')) $mime = 'application/javascript';
    if (str_ends_with($publicFile, '.json')) $mime = 'application/json';
    header("Content-Type: $mime");
    readfile($publicFile);
    exit;
}

// Load Controllers
require_once __DIR__ . '/../app/Http/Controllers/DashboardController.php';
require_once __DIR__ . '/../app/Http/Controllers/PendataanController.php';
require_once __DIR__ . '/../app/Http/Controllers/RekapController.php';
require_once __DIR__ . '/../app/Http/Controllers/LoginController.php';
require_once __DIR__ . '/../app/Http/Controllers/VerifikatorController.php';
require_once __DIR__ . '/../app/Http/Controllers/AdminController.php';

// Route Definitions and Execution
switch ($uri) {
    case '/':
    case '/index.php':
        $controller = new App\Http\Controllers\DashboardController();
        $controller->index();
        break;
    case '/pendataan':
        $controller = new App\Http\Controllers\PendataanController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        } else {
            $controller->index();
        }
        break;
    case '/rekap/pesantren':
        $controller = new App\Http\Controllers\RekapController();
        $controller->pesantren();
        break;
    case '/rekap/bencana':
        $controller = new App\Http\Controllers\RekapController();
        $controller->bencana();
        break;
    case '/rekap/ramah':
        $controller = new App\Http\Controllers\RekapController();
        $controller->ramah();
        break;
    case '/rekap/digital':
        $controller = new App\Http\Controllers\RekapController();
        $controller->digital();
        break;
    case '/rekap/ecoteologi':
        $controller = new App\Http\Controllers\RekapController();
        $controller->ecoteologi();
        break;
    case '/rekap/karyailmiah':
    case '/rekap/kitab':
        $controller = new App\Http\Controllers\RekapController();
        $controller->karyailmiah();
        break;
    case '/rekap/produk':
        $controller = new App\Http\Controllers\RekapController();
        $controller->produk();
        break;
    case '/api/pesantren':
        $controller = new App\Http\Controllers\DashboardController();
        $controller->apiPesantren();
        break;
    case '/api/stats':
        $controller = new App\Http\Controllers\DashboardController();
        $controller->apiStats();
        break;
    case '/login':
        $controller = new App\Http\Controllers\LoginController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            $controller->index();
        }
        break;
    case '/logout':
        $controller = new App\Http\Controllers\LoginController();
        $controller->logout();
        break;
    case '/change-password':
        $controller = new App\Http\Controllers\LoginController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->changePassword();
        } else {
            $controller->changePasswordForm();
        }
        break;
    case '/verifikator':
        $controller = new App\Http\Controllers\VerifikatorController();
        $controller->index();
        break;
    case '/verifikator/approve':
        $controller = new App\Http\Controllers\VerifikatorController();
        $controller->approve();
        break;
    case '/verifikator/reject':
        $controller = new App\Http\Controllers\VerifikatorController();
        $controller->reject();
        break;
    case '/admin':
        $controller = new App\Http\Controllers\AdminController();
        $controller->index();
        break;
    case '/admin/users':
        $controller = new App\Http\Controllers\AdminController();
        $controller->addUser();
        break;
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
