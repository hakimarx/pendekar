<?php

session_start();

define('LARAVEL_START', microtime(true));

// Auto-loader helper if vendor is present or manual fallback
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

// Dynamically determine base URL path (e.g. '/1' or '' if running at root)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($scriptDir === '/' || $scriptDir === '.') {
    $basePath = '';
} else {
    $basePath = rtrim($scriptDir, '/');
}
// Normalize if running directly inside public
if (str_ends_with($basePath, '/public')) {
    $basePath = substr($basePath, 0, -7);
}

if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', $basePath);
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $p = '/' . ltrim($path, '/');
        if ($p === '/') {
            return APP_BASE_URL ?: '/';
        }
        return APP_BASE_URL . $p;
    }
}

// Require database
require_once __DIR__ . '/../app/Database.php';
\App\Database::getInstance();

// Parse Request URI
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$route = $requestUri;

// Strip APP_BASE_URL from request URI to get clean route
if (!empty(APP_BASE_URL) && str_starts_with($route, APP_BASE_URL)) {
    $route = substr($route, strlen(APP_BASE_URL));
}
// Strip /public if present
if (str_starts_with($route, '/public')) {
    $route = substr($route, 7);
}
$route = '/' . ltrim($route, '/');
if (strlen($route) > 1) {
    $route = rtrim($route, '/');
}

// Serve static assets if exists
$publicFile = __DIR__ . $route;
if ($route !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    $mime = mime_content_type($publicFile);
    if (str_ends_with($publicFile, '.css')) $mime = 'text/css';
    if (str_ends_with($publicFile, '.js')) $mime = 'application/javascript';
    if (str_ends_with($publicFile, '.json')) $mime = 'application/json';
    if (str_ends_with($publicFile, '.png')) $mime = 'image/png';
    if (str_ends_with($publicFile, '.jpg') || str_ends_with($publicFile, '.jpeg')) $mime = 'image/jpeg';
    if (str_ends_with($publicFile, '.svg')) $mime = 'image/svg+xml';
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
switch ($route) {
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
    case '/settings':
        $controller = new App\Http\Controllers\LoginController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->updateSettings();
        } else {
            $controller->settingsForm();
        }
        break;
    case '/admin/edit':
        $controller = new App\Http\Controllers\AdminController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update();
        } else {
            $controller->edit();
        }
        break;
    case '/admin/create':
        $controller = new App\Http\Controllers\AdminController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        } else {
            $controller->create();
        }
        break;
    case '/admin/delete':
        $controller = new App\Http\Controllers\AdminController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->delete();
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
