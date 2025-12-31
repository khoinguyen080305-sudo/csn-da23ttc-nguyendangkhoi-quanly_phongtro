<?php
    // Bắt đầu session nếu chưa được bắt đầu
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Require file functions de dung ham ghi_log
    require 'db_connect.php';
    require 'functions.php';

    $vai_tro = isset($_SESSION['vai_tro']) ? $_SESSION['vai_tro'] : null;
    $ten_dang_nhap = isset($_SESSION['ten_dang_nhap']) ? $_SESSION['ten_dang_nhap'] : 'Không xác định';
    $ho_ten = isset($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Không xác định';

    // Ghi log logout
    if ($ten_dang_nhap !== 'Không xác định') {
        ghi_log("Người dùng '{$ten_dang_nhap}' ({$ho_ten}) đã đăng xuất khỏi hệ thống với vai trò '{$vai_tro}'", 'info');
    }

    //xoa tat ca bien session
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $thong_so_cookie = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $thong_so_cookie["path"], 
            $thong_so_cookie["domain"],
            $thong_so_cookie["secure"], 
            $thong_so_cookie["httponly"]
        );
    }
    session_destroy();
    //tro ve home.php voi thong bao logout thanh cong
    header("Location: home.php?logout=success");
    exit();
?>