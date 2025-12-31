<?php
session_start();
require '../db_connect.php';
require '../functions.php';

// Set header JSON cho tất cả response
header('Content-Type: application/json');

// Kiểm tra quyền admin (dùng hàm có sẵn)
if (!isset($_SESSION['vai_tro']) || $_SESSION['vai_tro'] != 'admin') {
    exit(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    exit(json_encode(['success' => false, 'error' => 'Invalid ID']));
}

$sql = "SELECT * FROM phong_tro WHERE id = ?";
$stmt = mysqli_prepare($ket_noi, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['success' => true, 'data' => $row]);
} else {
    echo json_encode(['success' => false, 'error' => 'Not found']);
}

mysqli_stmt_close($stmt);
mysqli_close($ket_noi);
?>