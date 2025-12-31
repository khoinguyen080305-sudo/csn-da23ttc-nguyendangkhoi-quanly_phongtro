<?php
    require '../db_connect.php';
    require '../functions.php';
    kiem_tra_admin();

    $cau_sql_thong_ke = "SELECT 
        (SELECT COUNT(*) FROM phong_tro) as tong_phong,
        (SELECT COUNT(*) FROM phong_tro WHERE trang_thai = 'trong') as phong_trong,
        (SELECT COUNT(*) FROM phong_tro WHERE trang_thai = 'da_thue') as phong_da_thue,
        (SELECT COUNT(*) FROM phong_tro WHERE trang_thai = 'cho_duyet') as phong_cho_duyet,
        (SELECT COUNT(*) FROM nguoi_dung WHERE vai_tro = 'nguoithue') as tong_nguoi_thue,
        (SELECT COUNT(*) FROM lien_he WHERE trang_thai = 'cho_duyet') as lien_he_moi,
        (SELECT COUNT(*) FROM hoa_don WHERE trang_thai = 'chua_thanh_toan') as hoa_don_chua_tt,
        (SELECT COUNT(*) FROM hoa_don WHERE trang_thai = 'qua_han') as hoa_don_qua_han";

    $ket_qua_thong_ke = mysqli_query($ket_noi, $cau_sql_thong_ke);

    if (!$ket_qua_thong_ke) {
        ghi_log("Lỗi query thống kê dashboard: " . mysqli_error($ket_noi), 'error');
        die("Lỗi tải dữ liệu thống kê!");
    }
    $thong_ke = mysqli_fetch_assoc($ket_qua_thong_ke);

    //tinh doanh thu theo thang
    $thang_hien_tai = date('n');
    $nam_hien_tai = date('Y');

    $cau_sql_doanh_thu = "SELECT SUM(tong_tien) as doanh_thu 
                        FROM hoa_don 
                        WHERE thang = ? AND nam = ? AND trang_thai = 'da_thanh_toan'";

    $cau_lenh_doanh_thu = mysqli_prepare($ket_noi, $cau_sql_doanh_thu);

    if ($cau_lenh_doanh_thu) {
        mysqli_stmt_bind_param($cau_lenh_doanh_thu, "ii", $thang_hien_tai, $nam_hien_tai);
        mysqli_stmt_execute($cau_lenh_doanh_thu);
        $ket_qua_doanh_thu = mysqli_stmt_get_result($cau_lenh_doanh_thu);
        $du_lieu_doanh_thu = mysqli_fetch_assoc($ket_qua_doanh_thu);
        $doanh_thu_thang = $du_lieu_doanh_thu['doanh_thu'] ?? 0;
        mysqli_stmt_close($cau_lenh_doanh_thu);
    } else {
        ghi_log("Lỗi prepare statement doanh thu: " . mysqli_error($ket_noi), 'error');
        $doanh_thu_thang = 0;
    }
    //tinh doanh thu thang truoc
    $thang_truoc = ($thang_hien_tai == 1) ? 12 : $thang_hien_tai - 1;
    $nam_thang_truoc = ($thang_hien_tai == 1) ? $nam_hien_tai - 1 : $nam_hien_tai;

    $cau_sql_doanh_thu_truoc = "SELECT SUM(tong_tien) as doanh_thu 
                                FROM hoa_don 
                                WHERE thang = ? AND nam = ? AND trang_thai = 'da_thanh_toan'";

    $cau_lenh_doanh_thu_truoc = mysqli_prepare($ket_noi, $cau_sql_doanh_thu_truoc);

    if ($cau_lenh_doanh_thu_truoc) {
        mysqli_stmt_bind_param($cau_lenh_doanh_thu_truoc, "ii", $thang_truoc, $nam_thang_truoc);
        mysqli_stmt_execute($cau_lenh_doanh_thu_truoc);
        $ket_qua_doanh_thu_truoc = mysqli_stmt_get_result($cau_lenh_doanh_thu_truoc);
        $du_lieu_doanh_thu_truoc = mysqli_fetch_assoc($ket_qua_doanh_thu_truoc);
        $doanh_thu_thang_truoc = $du_lieu_doanh_thu_truoc['doanh_thu'] ?? 0;
        mysqli_stmt_close($cau_lenh_doanh_thu_truoc);
    } else {
        $doanh_thu_thang_truoc = 0;
    }
    //tinh % phat trien
    if ($doanh_thu_thang_truoc > 0) {
        $ty_le_tang_truong = (($doanh_thu_thang - $doanh_thu_thang_truoc) / $doanh_thu_thang_truoc) * 100;
    } else {
        $ty_le_tang_truong = 0;
    }
    mysqli_close($ket_noi);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - DK BOARDING HOUSE</title>
    <meta name="description" content="Trang quản lý tổng quan hệ thống nhà trọ DK Boarding House">
    <link rel="icon" type="image/png" href="../assets/image/logo1.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../assets/css/style_dashboard.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard Admin
                    </h2>
                    <p class="mb-0 mt-1 text-white-50">
                        Xin chào, <strong><?= lam_sach_html($_SESSION['ho_ten']) ?></strong> 
                        | <span class="badge bg-light text-dark">Admin</span>
                    </p>
                </div>
                <div class="col-auto">
                    <span class="text-white-50 me-3">
                        <i class="far fa-clock"></i>
                        <?php echo dinh_dang_ngay_gio(date('Y-m-d H:i')); ?>
                    </span>
                    <a href="../logout.php" class="btn btn-light">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <h4 class="mb-3">
            <i class="fas fa-door-open me-2"></i>Tổng quan phòng trọ
        </h4>
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= lam_sach_html($thong_ke['tong_phong']) ?></h3>
                        <p>Tổng phòng</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= lam_sach_html($thong_ke['phong_trong']) ?></h3>
                        <p>Phòng trống</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= lam_sach_html($thong_ke['phong_da_thue']) ?></h3>
                        <p>Đã cho thuê</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= lam_sach_html($thong_ke['phong_cho_duyet']) ?></h3>
                        <p>Chờ duyệt</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- <h4 class="mb-3">
            <i class="fas fa-chart-pie me-2"></i>Thống kê khác
        </h4>
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-secondary">
                    <div class="stat-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= lam_sach_html($thong_ke['tong_nguoi_thue']) ?></h3>
                        <p>Người thuê</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= lam_sach_html($thong_ke['lien_he_moi']) ?></h3>
                        <p>Liên hệ mới</p>
                    </div>
                </div>
            </div>
        </div> -->
        <h4 class="mb-3">
            <i class="fas fa-money-bill-wave me-2"></i>Doanh thu
        </h4>
        <div class="row g-3 mb-4">
            <div class="col-md-4 col-sm-6">
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= number_format($doanh_thu_thang / 1000000, 1) ?>M</h3>
                        <p>Doanh thu tháng <?= $thang_hien_tai ?>/<?= $nam_hien_tai ?></p>
                        <?php if ($ty_le_tang_truong != 0): ?>
                            <small class="<?= $ty_le_tang_truong > 0 ? 'text-success' : 'text-danger' ?>">
                                <i class="fas fa-<?= $ty_le_tang_truong > 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                                <?= abs(round($ty_le_tang_truong, 1)) ?>% so với tháng trước
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= lam_sach_html($thong_ke['hoa_don_chua_tt']) ?></h3>
                        <p>HĐ chưa thanh toán</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="stat-card stat-danger">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= lam_sach_html($thong_ke['hoa_don_qua_han']) ?></h3>
                        <p>HĐ quá hạn</p>
                    </div>
                </div>
            </div>
        </div>
        <h4 class="mb-3">
            <i class="fas fa-th-large me-2"></i>Chức năng quản lý
        </h4>
        <div class="row g-3 mb-4">
            <!-- Quản lý phòng -->
            <div class="col-md-4 col-lg-3 col-sm-6">
                <a href="manage_rooms.php" class="menu-card">
                    <div class="menu-icon bg-primary">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <h5>Quản lý phòng</h5>
                    <p>Thêm, sửa, xóa phòng trọ</p>
                </a>
            </div>
            <!-- Quản lý người thuê -->
            <div class="col-md-4 col-lg-3 col-sm-6">
                <a href="manage_tenants.php" class="menu-card">
                    <div class="menu-icon bg-info">
                        <i class="fas fa-user-group"></i>
                    </div>
                    <h5>Quản lý người thuê</h5>
                    <p>Thông tin khách thuê</p>
                </a>
            </div>
            <!-- Quản lý hóa đơn -->
            <div class="col-md-4 col-lg-3 col-sm-6">
                <a href="manage_bills.php" class="menu-card">
                    <div class="menu-icon bg-success">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h5>Quản lý hóa đơn</h5>
                    <p>Tạo và theo dõi hóa đơn</p>
                    <?php if ($thong_ke['hoa_don_qua_han'] > 0): ?>
                        <span class="badge-notification bg-danger"><?= $thong_ke['hoa_don_qua_han'] ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <!-- Liên hệ khách -->
            <div class="col-md-4 col-lg-3 col-sm-6">
                <a href="manage_contacts.php" class="menu-card">
                    <div class="menu-icon bg-warning">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h5>Liên hệ khách</h5>
                    <p>Xử lý yêu cầu thuê phòng</p>
                    <?php if ($thong_ke['lien_he_moi'] > 0): ?>
                        <span class="badge-notification"><?= $thong_ke['lien_he_moi'] ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <!-- Lịch sử thuê -->
            <div class="col-md-4 col-lg-3 col-sm-6">
                <a href="rental_history.php" class="menu-card">
                    <div class="menu-icon bg-secondary">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>
                    <h5>Lịch sử thuê phòng</h5>
                    <p>Xem lịch sử thuê trọ</p>
                </a>
            </div>
            <!-- Đổi mật khẩu -->
            <div class="col-md-4 col-lg-3 col-sm-6">
                <a href="change_password.php" class="menu-card">
                    <div class="menu-icon bg-dark">
                        <i class="fas fa-key"></i>
                    </div>
                    <h5>Đổi mật khẩu</h5>
                    <p>Thay đổi mật khẩu</p>
                </a>
            </div>
        </div>
    </div>
    <!-- Custom Scripts -->
    <script>
        // Làm mới trang mỗi 5 phút để cập nhật thống kê
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 phút = 300000ms
    </script>
</body>
</html>