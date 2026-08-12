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

// Route Definitions
$dashboardController = new App\Http\Controllers\DashboardController();
$pendataanController = new App\Http\Controllers\PendataanController();
$rekapController = new App\Http\Controllers\RekapController();
$loginController = new App\Http\Controllers\LoginController();
$verifikatorController = new App\Http\Controllers\VerifikatorController();
$adminController = new App\Http\Controllers\AdminController();

switch ($uri) {
    case '/':
    case '/index.php':
        $dashboardController->index();
        break;
    case '/pendataan':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pendataanController->store();
        } else {
            $pendataanController->index();
        }
        break;
    case '/rekap/pesantren':
        $rekapController->pesantren();
        break;
    case '/rekap/bencana':
        $rekapController->bencana();
        break;
    case '/rekap/ramah':
        $rekapController->ramah();
        break;
    case '/rekap/digital':
        $rekapController->digital();
        break;
    case '/rekap/ecoteologi':
        $rekapController->ecoteologi();
        break;
    case '/rekap/karyailmiah':
    case '/rekap/kitab':
        $rekapController->karyailmiah();
        break;
    case '/rekap/produk':
        $rekapController->produk();
        break;
    case '/api/pesantren':
        $dashboardController->apiPesantren();
        break;
    case '/api/stats':
        $dashboardController->apiStats();
        break;
    case '/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $loginController->login();
        } else {
            $loginController->index();
        }
        break;
    case '/logout':
        $loginController->logout();
        break;
    case '/verifikator':
        $verifikatorController->index();
        break;
    case '/verifikator/approve':
        $verifikatorController->approve();
        break;
    case '/verifikator/reject':
        $verifikatorController->reject();
        break;
    case '/admin':
        $adminController->index();
        break;
    case '/admin/users':
        $adminController->addUser();
        break;
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
