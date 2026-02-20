<?php
// Admin-only: generate and download a ZIP of all PDFs in output/vendors/
session_start();
require_once __DIR__ . '/config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden'); echo 'Forbidden'; exit();
}

$dir = __DIR__ . '/output/vendors';
if (!is_dir($dir)) { echo 'No exports available'; exit(); }

$zipPath = __DIR__ . '/output/vendors_export_' . date('Ymd_His') . '.zip';
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== true) { echo 'Failed to create ZIP'; exit(); }

$files = glob($dir . '/*.pdf');
foreach ($files as $f) {
    $zip->addFile($f, basename($f));
}
$zip->close();

if (!file_exists($zipPath)) { echo 'ZIP failed'; exit(); }

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($zipPath) . '"');
header('Content-Length: ' . filesize($zipPath));
readfile($zipPath);
// Optionally remove zip after download
unlink($zipPath);
exit();

?>
