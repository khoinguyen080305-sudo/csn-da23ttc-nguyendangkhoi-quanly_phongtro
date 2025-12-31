<?php 
    $may_chu = "localhost"; 
    $ten_dang_nhap = "root";
    $mat_khau = "";
    $ten_csdl = "quanly_phongtro";
    $ket_noi = mysqli_connect($may_chu, $ten_dang_nhap, $mat_khau, $ten_csdl);
    if (!$ket_noi) {
        die("Kết nối cơ sở dữ liệu đã thất bại! Lỗi: " . mysqli_connect_error());
    }
    mysqli_set_charset($ket_noi, "utf8mb4");
?>