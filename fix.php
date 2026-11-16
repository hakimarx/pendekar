<?php
/**
 * PENDEKAR - Automated Linux Permission Repair Utility
 * Jalankan file ini melalui browser: https://hafizjatim.my.id/1/fix.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<div style='font-family: Arial, sans-serif; padding: 30px; line-height: 1.6;'>";
echo "<h2 style='color: #2D5D7C;'>PENDEKAR Permission Fixer</h2>";

function fixDir($dir) {
    if (!file_exists($dir)) {
        echo "<span style='color: #888;'>Skip (not found): " . basename($dir) . "</span><br>";
        return;
    }
    @chmod($dir, 0755);
    $count = 0;
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                if (@chmod($item->getPathname(), 0755)) $count++;
            } else {
                if (@chmod($item->getPathname(), 0644)) $count++;
            }
        }
    } catch (\Throwable $e) {
        echo "<span style='color: red;'>Error reading $dir: " . $e->getMessage() . "</span><br>";
    }
    echo "<b>Berhasil set permissions:</b> " . basename($dir) . " ($count item)<br>";
}

$dirs = ['app', 'bootstrap', 'database', 'public', 'resources', 'routes', 'data'];
foreach ($dirs as $d) {
    fixDir(__DIR__ . '/' . $d);
}

@chmod(__DIR__ . '/database', 0775);
if (file_exists(__DIR__ . '/database/database.sqlite')) {
    @chmod(__DIR__ . '/database/database.sqlite', 0664);
    echo "<b>Berhasil set permissions:</b> database/database.sqlite (0664)<br>";
}

echo "<hr style='margin: 20px 0;'>";
echo "<h3 style='color: green;'>Permissions selesai diperbaiki!</h3>";
echo "<p><a href='/1/' style='display: inline-block; padding: 10px 20px; background: #2D5D7C; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>Buka Aplikasi PENDEKAR &rarr;</a></p>";
echo "</div>";
