<?php
session_start();
require '../db_connect.php';
require '../functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['vai_tro']) || $_SESSION['vai_tro'] != 'admin') {
    header("Location: ../index.php?error=access_denied");
    exit;
}

$thong_bao_thanh_cong = '';
$thong_bao_loi = '';

// Duyệt yêu cầu
if (isset($_POST['duyet'])) {
    $id = intval($_POST['id']);

    mysqli_begin_transaction($ket_noi);
    try {
        // Lấy thông tin liên hệ
        $stmt = mysqli_prepare($ket_noi, "SELECT * FROM lien_he WHERE id = ? AND trang_thai = 'cho_duyet'");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 0) {
            throw new Exception("Yêu cầu không tồn tại hoặc đã xử lý!");
        }
        
        $lien_he = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        $ten_phong = trim($lien_he['phong']);
        $ten_nguoi = trim($lien_he['ten']);
        $sdt = trim($lien_he['sdt']);
        $email = trim($lien_he['email']);

        // Lấy thông tin phòng
        $stmt = mysqli_prepare($ket_noi, "SELECT id, trang_thai FROM phong_tro WHERE ten_phong = ?");
        mysqli_stmt_bind_param($stmt, "s", $ten_phong);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 0) {
            throw new Exception("Phòng không tồn tại!");
        }
        
        $phong = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($phong['trang_thai'] == 'da_thue') {
            throw new Exception("Phòng đã có người thuê!");
        }
        
        $id_phong = $phong['id'];

        // Tạo tài khoản người thuê (chưa có username)
        $mat_khau = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($ket_noi, 
            "INSERT INTO nguoi_dung (mat_khau, ho_ten, vai_tro, sdt, email, trang_thai) 
             VALUES (?, ?, 'nguoithue', ?, ?, 'hoat_dong')"
        );
        mysqli_stmt_bind_param($stmt, "ssss", $mat_khau, $ten_nguoi, $sdt, $email);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Lỗi khi tạo tài khoản!");
        }
        
        $id_nguoi_thue = mysqli_insert_id($ket_noi);
        mysqli_stmt_close($stmt);

        // Tạo username từ tên + ID
        $base_username = bo_dau_tieng_viet($ten_nguoi);
        if (empty($base_username)) {
            $base_username = 'user';
        }
        $username = $base_username . $id_nguoi_thue;

        // Cập nhật username vào database
        $stmt = mysqli_prepare($ket_noi, "UPDATE nguoi_dung SET ten_dang_nhap = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $username, $id_nguoi_thue);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        // Gán người thuê vào phòng và cập nhật trạng thái
        $stmt = mysqli_prepare($ket_noi, 
            "UPDATE phong_tro 
             SET id_nguoi_thue = ?, trang_thai = 'da_thue', ngay_bat_dau = CURDATE() 
             WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "ii", $id_nguoi_thue, $id_phong);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Cập nhật trạng thái yêu cầu
        $stmt = mysqli_prepare($ket_noi, 
            "UPDATE lien_he 
             SET trang_thai = 'da_xu_ly', nguoi_xu_ly = ?, ngay_xu_ly = NOW() 
             WHERE id = ?"
        );
        $admin_id = $_SESSION['user_id'];
        mysqli_stmt_bind_param($stmt, "ii", $admin_id, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($ket_noi);
        
        $thong_bao_thanh_cong = "Đã duyệt thành công!\n\n" .
            "Thông tin đăng nhập:\n" .
            "Tài khoản: $username\n" .
            "Mật khẩu: 123456\n" .
            "Phòng: $ten_phong";
            
    } catch (Exception $e) {
        mysqli_rollback($ket_noi);
        $thong_bao_loi = $e->getMessage();
    }
}

// Từ chối yêu cầu
if (isset($_POST['tu_choi'])) {
    $id = intval($_POST['id']);
    
    mysqli_begin_transaction($ket_noi);
    try {
        // Lấy thông tin phòng
        $stmt = mysqli_prepare($ket_noi, "SELECT phong FROM lien_he WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 0) {
            throw new Exception("Yêu cầu không tồn tại!");
        }
        
        $lien_he = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        $ten_phong = $lien_he['phong'];

        // Cập nhật trạng thái yêu cầu
        $stmt = mysqli_prepare($ket_noi, 
            "UPDATE lien_he 
             SET trang_thai = 'tu_choi', nguoi_xu_ly = ?, ngay_xu_ly = NOW() 
             WHERE id = ?"
        );
        $admin_id = $_SESSION['user_id'];
        mysqli_stmt_bind_param($stmt, "ii", $admin_id, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Trả phòng về trạng thái trống (nếu đang chờ duyệt)
        $stmt = mysqli_prepare($ket_noi, 
            "UPDATE phong_tro 
             SET trang_thai = 'trong', id_nguoi_thue = NULL, ngay_bat_dau = NULL 
             WHERE ten_phong = ? AND trang_thai = 'cho_duyet'"
        );
        mysqli_stmt_bind_param($stmt, "s", $ten_phong);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($ket_noi);
        $thong_bao_thanh_cong = "Đã từ chối yêu cầu và trả phòng về trạng thái trống!";
        
    } catch (Exception $e) {
        mysqli_rollback($ket_noi);
        $thong_bao_loi = $e->getMessage();
    }
}

// Xóa yêu cầu
if (isset($_POST['xoa'])) {
    $id = intval($_POST['id']);
    
    $stmt = mysqli_prepare($ket_noi, "DELETE FROM lien_he WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $thong_bao_thanh_cong = "Đã xóa yêu cầu thành công!";
    } else {
        $thong_bao_loi = "Không thể xóa yêu cầu!";
    }
    mysqli_stmt_close($stmt);
}

// Lấy danh sách liên hệ
$danh_sach_lien_he = mysqli_query($ket_noi, 
    "SELECT lh.*, nd.ho_ten as nguoi_xu_ly_ten 
     FROM lien_he lh
     LEFT JOIN nguoi_dung nd ON lh.nguoi_xu_ly = nd.id
     ORDER BY 
        CASE lh.trang_thai 
            WHEN 'cho_duyet' THEN 1 
            WHEN 'da_lien_he' THEN 2 
            WHEN 'da_xu_ly' THEN 3 
            WHEN 'tu_choi' THEN 4
            ELSE 5 
        END, 
        lh.ngay_gui DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý liên hệ - DK BOARDING HOUSE</title>
    <link rel="icon" type="image/png" href="../assets/image/logo1.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style_manage_contacts.css?v=<?php echo time(); ?>">
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
        <h2><i class="fas fa-envelope me-2"></i>Quản lý yêu cầu thuê phòng</h2>

        <!-- Thông báo -->
        <?php if (!empty($thong_bao_thanh_cong)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><strong>Thành công!</strong><br>
                <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;"><?= htmlspecialchars($thong_bao_thanh_cong) ?></pre>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($thong_bao_loi)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i><strong>Lỗi!</strong> <?= htmlspecialchars($thong_bao_loi) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Lưu ý:</strong> Khi duyệt, hệ thống sẽ tự động tạo tài khoản với mật khẩu mặc định: <code>123456</code>
        </div>

        <!-- Danh sách yêu cầu -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách yêu cầu</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>ID</th>
                                <th>Họ tên</th>
                                <th>SĐT</th>
                                <th>Email</th>
                                <th>Phòng</th>
                                <th>Ghi chú</th>
                                <th>Ngày gửi</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($danh_sach_lien_he) > 0) {
                                while ($row = mysqli_fetch_assoc($danh_sach_lien_he)) {
                                    $badge_class = '';
                                    $status_text = '';
                                    
                                    switch($row['trang_thai']) {
                                        case 'cho_duyet':
                                            $badge_class = 'warning';
                                            $status_text = 'Chờ duyệt';
                                            break;
                                        case 'da_lien_he':
                                            $badge_class = 'info';
                                            $status_text = 'Đã liên hệ';
                                            break;
                                        case 'da_xu_ly':
                                            $badge_class = 'success';
                                            $status_text = 'Đã duyệt';
                                            break;
                                        case 'tu_choi':
                                            $badge_class = 'danger';
                                            $status_text = 'Từ chối';
                                            break;
                                        default:
                                            $badge_class = 'secondary';
                                            $status_text = ucfirst($row['trang_thai']);
                                    }
                            ?>
                                <tr>
                                    <td class="text-center"><?= $row['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($row['ten']) ?></strong></td>
                                    <td class="text-center">
                                        <a href="tel:<?= $row['sdt'] ?>" class="text-decoration-none">
                                            <i class="fas fa-phone text-success"></i> <?= htmlspecialchars($row['sdt']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($row['email'] ?: 'N/A') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= htmlspecialchars($row['phong']) ?></span>
                                    </td>
                                    <td><small><?= htmlspecialchars($row['ghi_chu'] ?: 'Không có') ?></small></td>
                                    <td class="text-center">
                                        <small><?= date('d/m/Y H:i', strtotime($row['ngay_gui'])) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $badge_class ?>"><?= $status_text ?></span>
                                        <?php if ($row['nguoi_xu_ly']): ?>
                                            <br><small class="text-muted">bởi <?= htmlspecialchars($row['nguoi_xu_ly_ten']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['trang_thai'] == 'cho_duyet'): ?>
                                            <form method="POST" style="display:inline" onsubmit="return confirm('Xác nhận duyệt người thuê này?')">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button name="duyet" class="btn btn-sm btn-success" title="Duyệt">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display:inline" onsubmit="return confirm('Xác nhận từ chối yêu cầu này?')">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button name="tu_choi" class="btn btn-sm btn-warning" title="Từ chối">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($row['trang_thai'] != 'cho_duyet'): ?>
                                            <form method="POST" style="display:inline" onsubmit="return confirm('Xóa yêu cầu này?')">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button name="xoa" class="btn btn-sm btn-danger" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center text-muted'>Chưa có yêu cầu nào</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 8000);
    </script>
</body>
</html>
<?php mysqli_close($ket_noi); ?>