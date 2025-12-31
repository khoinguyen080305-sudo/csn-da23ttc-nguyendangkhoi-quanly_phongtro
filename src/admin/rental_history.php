
<?php
session_start();
// Kiểm tra quyền admin
if (!isset($_SESSION['vai_tro']) || $_SESSION['vai_tro'] != 'admin') {
    header("Location: ../index.php?error=access_denied");
    exit;
}

require '../db_connect.php';
require '../functions.php';

// ⭐ THÊM HÀM FORMAT THỜI GIAN
function format_thoi_gian_thue($so_ngay) {
    if ($so_ngay === null || $so_ngay < 0) {
        return '-';
    }
    
    // Tính năm, tháng, ngày
    $nam = floor($so_ngay / 365);
    $so_ngay_con_lai = $so_ngay % 365;
    
    $thang = floor($so_ngay_con_lai / 30);
    $ngay = $so_ngay_con_lai % 30;
    
    // Xây dựng chuỗi kết quả
    $parts = [];
    
    if ($nam > 0) {
        $parts[] = $nam . ' năm';
    }
    
    if ($thang > 0) {
        $parts[] = $thang . ' tháng';
    }
    
    if ($ngay > 0) {
        $parts[] = $ngay . ' ngày';
    }
    
    // Nếu không có gì (0 ngày)
    if (empty($parts)) {
        return '0 ngày';
    }
    
    return implode(' ', $parts);
}

// Lấy giá trị lọc từ URL
$filter_phong = isset($_GET['phong']) ? intval($_GET['phong']) : 0;
$filter_quy = isset($_GET['quy']) ? intval($_GET['quy']) : 0;
$filter_nam = isset($_GET['nam']) ? intval($_GET['nam']) : 0;

// Xây dựng điều kiện WHERE
$where_conditions = [];
if ($filter_phong > 0) {
    $where_conditions[] = "ls.id_phong = $filter_phong";
}
if ($filter_quy > 0) {
    $where_conditions[] = "QUARTER(ls.ngay_bat_dau) = $filter_quy";
}
if ($filter_nam > 0) {
    $where_conditions[] = "YEAR(ls.ngay_bat_dau) = $filter_nam";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$cau_truy_van = "
    SELECT 
    ls.id,
    ls.id_nguoi_thue,
    ls.id_phong,
    ls.ngay_bat_dau,
    ls.ngay_ket_thuc, 
    ls.gia_thue,
    ls.tien_coc,
    ls.ly_do_ket_thuc,
    ls.ghi_chu,
    
    n.ho_ten,
    n.sdt,
    p.ten_phong,

    QUARTER(ls.ngay_bat_dau) AS quy_bat_dau,
    YEAR(ls.ngay_bat_dau) AS nam_bat_dau,

    CASE 
        WHEN ls.ngay_ket_thuc IS NULL THEN 'Đang thuê'
        ELSE 'Đã kết thúc'
    END AS trang_thai_hien_thi,

    DATEDIFF(
        COALESCE(ls.ngay_ket_thuc, CURDATE()), 
        ls.ngay_bat_dau
    ) AS so_ngay_thue

FROM lich_su_thue ls
LEFT JOIN nguoi_dung n ON ls.id_nguoi_thue = n.id
LEFT JOIN phong_tro p ON ls.id_phong = p.id
$where_clause
ORDER BY ls.ngay_bat_dau DESC
";

$ket_qua = mysqli_query($ket_noi, $cau_truy_van);

// Lấy danh sách phòng cho dropdown
$phong_list = mysqli_query($ket_noi, "SELECT id, ten_phong FROM phong_tro ORDER BY ten_phong");

// Thống kê
$stats_sql = "SELECT 
    COUNT(*) as tong_so,
    SUM(CASE WHEN ngay_ket_thuc IS NULL THEN 1 ELSE 0 END) as dang_thue,
    SUM(CASE WHEN ngay_ket_thuc IS NOT NULL THEN 1 ELSE 0 END) as da_ket_thuc
FROM lich_su_thue ls $where_clause";
$stats = mysqli_fetch_assoc(mysqli_query($ket_noi, $stats_sql));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lịch sử thuê phòng - DK BOARDING HOUSE</title>
<link rel="icon" type="image/png" href="../assets/image/logo1.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style_manage_contacts.css?v=<?php echo time(); ?>">
<style>
.filter-box {
    background: #f8f9fa;
    border: 2px solid #0d6efd;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}
.stat-card {
    text-align: center;
    padding: 15px;
    border-radius: 8px;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.stat-card h3 {
    margin: 0;
    color: #0d6efd;
}
</style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a href="dashboard.php" class="navbar-brand">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
            <span class="text-white">
                <i class="fas fa-user-shield me-2"></i><?= htmlspecialchars($_SESSION['ho_ten']) ?>
            </span>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>
            <i class="fas fa-history me-2"></i>Lịch sử thuê phòng
            <?php if ($filter_phong || $filter_quy || $filter_nam): ?>
                <span class="badge bg-warning text-dark">Đang lọc</span>
            <?php endif; ?>
        </h2>

        <!-- BỘ LỌC -->
        <div class="filter-box">
            <h5 class="text-primary mb-3"><i class="fas fa-filter me-2"></i>Bộ lọc theo yêu cầu</h5>
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-door-open me-1"></i> Chọn phòng</label>
                    <select name="phong" class="form-select">
                        <option value="0">Tất cả phòng</option>
                        <?php 
                        mysqli_data_seek($phong_list, 0); // Reset pointer
                        while ($phong = mysqli_fetch_assoc($phong_list)): 
                        ?>
                            <option value="<?= $phong['id'] ?>" <?= $filter_phong == $phong['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($phong['ten_phong']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calendar-alt me-1"></i> Chọn quý</label>
                    <select name="quy" class="form-select">
                        <option value="0">Tất cả quý</option>
                        <option value="1" <?= $filter_quy == 1 ? 'selected' : '' ?>>Quý 1 (Tháng 1-3)</option>
                        <option value="2" <?= $filter_quy == 2 ? 'selected' : '' ?>>Quý 2 (Tháng 4-6)</option>
                        <option value="3" <?= $filter_quy == 3 ? 'selected' : '' ?>>Quý 3 (Tháng 7-9)</option>
                        <option value="4" <?= $filter_quy == 4 ? 'selected' : '' ?>>Quý 4 (Tháng 10-12)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calendar me-1"></i> Chọn năm</label>
                    <select name="nam" class="form-select">
                        <option value="0">-- Tất cả năm --</option>
                        <?php for ($year = date('Y'); $year >= 2023; $year--): ?>
                            <option value="<?= $year ?>" <?= $filter_nam == $year ? 'selected' : '' ?>>
                                Năm <?= $year ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Lọc
                    </button>
                </div>
            </form>

            <?php if ($filter_phong || $filter_quy || $filter_nam): ?>
                <div class="mt-3">
                    <a href="rental_history.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i> Xóa bộ lọc
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- THỐNG KÊ -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="stat-card border-primary">
                    <h3><?= $stats['tong_so'] ?></h3>
                    <p class="mb-0 text-muted">Tổng số bản ghi</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card border-success">
                    <h3 class="text-success"><?= $stats['dang_thue'] ?></h3>
                    <p class="mb-0 text-muted">Đang thuê</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card border-secondary">
                    <h3 class="text-secondary"><?= $stats['da_ket_thuc'] ?></h3>
                    <p class="mb-0 text-muted">Đã kết thúc</p>
                </div>
            </div>
        </div>

        <!-- BẢNG DỮ LIỆU -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Danh sách lịch sử thuê
                    <span class="badge bg-light text-dark"><?= mysqli_num_rows($ket_qua) ?> kết quả</span>
                </h5>
                <!-- <button onclick="window.print()" class="btn btn-light btn-sm">
                    <i class="fas fa-print"></i> In danh sách
                </button> -->
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>ID</th>
                                <th>Người thuê</th>
                                <th>SĐT</th>
                                <th>Phòng</th>
                                <th>Quý/Năm</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Thời gian thuê</th>
                                <th>Giá thuê</th>
                                <th>Tiền cọc</th>
                                <th>Lý do kết thúc</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
if ($ket_qua && mysqli_num_rows($ket_qua) > 0) {
    while ($row = mysqli_fetch_assoc($ket_qua)) {
        $ho_ten = htmlspecialchars($row['ho_ten'] ?? 'N/A');
        $sdt = htmlspecialchars($row['sdt'] ?? '-');
        $ten_phong = htmlspecialchars($row['ten_phong'] ?? '-');        
        
        $ngay_bat_dau = $row['ngay_bat_dau'] ? date('d/m/Y', strtotime($row['ngay_bat_dau'])) : '-';
        $ngay_ket_thuc = $row['ngay_ket_thuc'] ? date('d/m/Y', strtotime($row['ngay_ket_thuc'])) : '-';
        
        // Hiển thị Quý/Năm
        $quy_nam = "Q{$row['quy_bat_dau']}/{$row['nam_bat_dau']}";
        
        // ⭐ SỬA: Dùng hàm format mới
        $so_ngay = $row['so_ngay_thue'];
        $thoi_gian_thue = format_thoi_gian_thue($so_ngay);
        
        $gia_thue = number_format($row['gia_thue']) . ' đ';
        $tien_coc = number_format($row['tien_coc']) . ' đ';
        
        $ly_do = htmlspecialchars($row['ly_do_ket_thuc'] ?? '-');
        
        $trang_thai = $row['trang_thai_hien_thi'];
        $badge_class = ($trang_thai == 'Đang thuê') ? 'success' : 'secondary';
        
        echo "<tr>
            <td class='text-center'><strong>#{$row['id']}</strong></td>
            <td><strong>{$ho_ten}</strong></td>
            <td class='text-center'><a href='tel:{$sdt}' class='text-decoration-none'>{$sdt}</a></td>
            <td><strong>{$ten_phong}</strong></td>
            <td class='text-center'><span class='badge bg-info'>{$quy_nam}</span></td>
            <td class='text-center'>{$ngay_bat_dau}</td>
            <td class='text-center'>{$ngay_ket_thuc}</td>
            <td class='text-center'><strong class='text-primary'>{$thoi_gian_thue}</strong></td>
            <td class='text-end'>{$gia_thue}</td>
            <td class='text-end'>{$tien_coc}</td>
            <td><small>{$ly_do}</small></td>
            <td class='text-center'>
                <span class='badge bg-{$badge_class}'>{$trang_thai}</span>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='12' class='text-center text-muted py-4'>
            <i class='fas fa-inbox fa-2x mb-2 d-block'></i>
            Không tìm thấy kết quả
          </td></tr>";
}

mysqli_close($ket_noi);
?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- HƯỚNG DẪN -->
        <div class="alert alert-info mt-3">
            <h6><i class="fas fa-info-circle me-2"></i>Hướng dẫn sử dụng bộ lọc:</h6>
            <ul class="mb-0 small">
                <li><strong>Lọc theo phòng:</strong> Xem lịch sử thuê của một phòng cụ thể</li>
                <li><strong>Lọc theo quý:</strong> Xem ai thuê phòng trong quý nào (Q1: Tháng 1-3, Q2: Tháng 4-6, Q3: Tháng 7-9, Q4: Tháng 10-12)</li>
                <li><strong>Lọc theo năm:</strong> Xem lịch sử thuê theo năm</li>
                <li><strong>Kết hợp:</strong> Có thể chọn cả 3 tiêu chí cùng lúc (VD: Phòng 05 - Q4 - 2025)</li>
            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>