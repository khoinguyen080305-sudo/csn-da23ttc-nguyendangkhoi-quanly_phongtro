<?php
session_start();
require '../db_connect.php';
require '../functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['vai_tro']) || $_SESSION['vai_tro'] !== 'admin') {
    exit(json_encode(['error' => 'Unauthorized']));
}

// Set header JSON
header('Content-Type: application/json');

// Lấy ID từ URL
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    exit(json_encode(['error' => 'Invalid ID']));
}

// Query lấy thông tin người thuê
$sql = "SELECT id, ho_ten, sdt, email 
        FROM nguoi_dung 
        WHERE id = ? AND vai_tro = 'nguoithue'";

$stmt = mysqli_prepare($ket_noi, $sql);

if (!$stmt) {
    exit(json_encode(['error' => 'Database error']));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'success' => true,
        'id' => $row['id'],
        'ho_ten' => $row['ho_ten'],
        'sdt' => $row['sdt'],
        'email' => $row['email'],
    ]);
} else {
    echo json_encode(['error' => 'Tenant not found']);
}
mysqli_stmt_close($stmt);
mysqli_close($ket_noi);
?>