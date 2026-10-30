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