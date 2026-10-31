<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/auth.php';
include '../config/db.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT file_name FROM materials WHERE id = ? AND status = 'approved'");
$stmt->bind_param("i", $id);
$stmt->execute();
$material = $stmt->get_result()->fetch_assoc();

if (!$material) {
    die("Material not found.");
}

$file = __DIR__ . '/../uploads/' . basename($material['file_name']);

if (!file_exists($file)) {
    die("File is missing on the server.");
}


$upd = $conn->prepare("UPDATE materials SET downloads = downloads + 1 WHERE id = ?");
$upd->bind_param("i", $id);
$upd->execute();

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;