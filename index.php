<?php
/**
 * PENDEKAR Application - Root Entry Point
 * Auto-repairs Linux file permissions on shared hosting
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Function to recursively ensure folders have 0755 (+x) and files have 0644
function autoFixPermissions($path) {
    if (!file_exists($path)) return;
    if (is_dir($path)) {
        @chmod($path, 0755);
        $dh = @opendir($path);
        if ($dh) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') continue;
                autoFixPermissions($path . '/' . $file);
            }
            closedir($dh);
        }
    } else {
        @chmod($path, 0644);
    }
}

// Auto-repair if permissions were restricted during zip extraction
if (!is_readable(__DIR__ . '/app/Http/Controllers/DashboardController.php')) {
    autoFixPermissions(__DIR__ . '/app');
    autoFixPermissions(__DIR__ . '/bootstrap');
    autoFixPermissions(__DIR__ . '/database');
    autoFixPermissions(__DIR__ . '/public');
    autoFixPermissions(__DIR__ . '/resources');
    autoFixPermissions(__DIR__ . '/routes');
    @chmod(__DIR__ . '/database', 0775);
    if (file_exists(__DIR__ . '/database/database.sqlite')) {
        @chmod(__DIR__ . '/database/database.sqlite', 0664);
    }
}

require_once __DIR__ . '/public/index.php';
