<?php
session_start();
require '../db_connect.php';
require '../functions.php';

// Kiểm tra đăng nhập (admin hoặc tenant đều được đổi mật khẩu)
if (!isset($_SESSION['ten_dang_nhap'])) {
    header("Location: ../index.php");
    exit;
}

$thong_bao_thanh_cong = '';
$thong_bao_loi = '';

if (isset($_POST['change'])) {
    $mat_khau_cu = trim($_POST['old_pass']);
    $mat_khau_moi = trim($_POST['new_pass']);
    $xac_nhan = trim($_POST['confirm_pass']);

    // Validate
    if (empty($mat_khau_cu) || empty($mat_khau_moi) || empty($xac_nhan)) {
        $thong_bao_loi = "Vui lòng nhập đầy đủ thông tin!";
    } elseif (strlen($mat_khau_moi) < 6) {
        $thong_bao_loi = "Mật khẩu mới phải có ít nhất 6 ký tự!";
    } elseif (!preg_match('/[A-Za-z]/', $mat_khau_moi) || !preg_match('/[0-9]/', $mat_khau_moi)) {
        $thong_bao_loi = "Mật khẩu mới phải gồm cả chữ và số!";
    } elseif ($mat_khau_moi !== $xac_nhan) {
        $thong_bao_loi = "Mật khẩu mới và xác nhận không khớp!";
    } elseif ($mat_khau_cu === $mat_khau_moi) {
        $thong_bao_loi = "Mật khẩu mới phải khác mật khẩu cũ!";
    } else {
        // Lấy mật khẩu hiện tại trong DB
        $ten_dang_nhap = $_SESSION['ten_dang_nhap'];
        $cau_lenh = mysqli_prepare($ket_noi, "SELECT mat_khau FROM nguoi_dung WHERE ten_dang_nhap = ?");
        mysqli_stmt_bind_param($cau_lenh, "s", $ten_dang_nhap);
        mysqli_stmt_execute($cau_lenh);
        $ket_qua = mysqli_stmt_get_result($cau_lenh);
        
        if (mysqli_num_rows($ket_qua) == 0) {
            $thong_bao_loi = "Tài khoản không tồn tại!";
        } else {
            $nguoi_dung = mysqli_fetch_assoc($ket_qua);
            mysqli_stmt_close($cau_lenh);

            // Kiểm tra mật khẩu cũ
            if (password_verify($mat_khau_cu, $nguoi_dung['mat_khau'])) {
                $mat_khau_moi_ma_hoa = password_hash($mat_khau_moi, PASSWORD_DEFAULT);
                
                // Cập nhật mật khẩu mới
                $cau_lenh_cap_nhat = mysqli_prepare($ket_noi, "UPDATE nguoi_dung SET mat_khau = ? WHERE ten_dang_nhap = ?");
                mysqli_stmt_bind_param($cau_lenh_cap_nhat, "ss", $mat_khau_moi_ma_hoa, $ten_dang_nhap);
                
                if (mysqli_stmt_execute($cau_lenh_cap_nhat)) {
                    $thong_bao_thanh_cong = "Đổi mật khẩu thành công! Vui lòng đăng nhập lại.";
                    mysqli_stmt_close($cau_lenh_cap_nhat);
                } else {
                    $thong_bao_loi = "Lỗi khi cập nhật mật khẩu!";
                    mysqli_stmt_close($cau_lenh_cap_nhat);
                }
            } else {
                $thong_bao_loi = "Mật khẩu cũ không đúng!";
            }
        }
    }
}

// Xác định trang quay lại
$duong_dan_quay_lai = ($_SESSION['vai_tro'] == 'admin') ? 'dashboard.php' : '../tenant/tenant_home.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - DK BOARDING HOUSE</title>
    <link rel="icon" type="image/png" href="../assets/image/logo1.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style_change_password.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a href="<?= $duong_dan_quay_lai ?>" class="navbar-brand">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
            <span class="text-white">
                <i class="fas fa-user me-2"></i><?= htmlspecialchars($_SESSION['ho_ten']) ?>
            </span>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4"><i class="fas fa-key me-2"></i>Đổi mật khẩu</h2>

                <!-- Thông báo -->
                <?php if ($thong_bao_thanh_cong): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i><strong>Thành công!</strong> <?= htmlspecialchars($thong_bao_thanh_cong) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <script>
                        setTimeout(function() {
                            window.location.href = '../logout.php';
                        }, 2000);
                    </script>
                <?php endif; ?>

                <?php if ($thong_bao_loi): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i><strong>Lỗi!</strong> <?= htmlspecialchars($thong_bao_loi) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Card đổi mật khẩu -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Thay đổi mật khẩu</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock me-2"></i>Mật khẩu cũ <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="old_pass" class="form-control" 
                                       placeholder="Nhập mật khẩu cũ" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock-open me-2"></i>Mật khẩu mới <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="new_pass" class="form-control" 
                                       placeholder="Nhập mật khẩu mới" minlength="6" required>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Tối thiểu 6 ký tự, bao gồm chữ và số
                                </small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-check-double me-2"></i>Xác nhận mật khẩu mới <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="confirm_pass" class="form-control" 
                                       placeholder="Nhập lại mật khẩu mới" minlength="6" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="change" class="btn btn-success btn-lg">
                                    <i class="fas fa-check me-2"></i>Đổi mật khẩu
                                </button>
                                <a href="<?= $duong_dan_quay_lai ?>" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Hủy bỏ
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Card lưu ý -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Lưu ý quan trọng</h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Mật khẩu phải có <strong>ít nhất 6 ký tự</strong>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Phải bao gồm <strong>cả chữ và số</strong>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Mật khẩu mới phải <strong>khác mật khẩu cũ</strong>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                Bạn sẽ <strong>đăng xuất tự động</strong> sau khi đổi mật khẩu
                            </li>
                            <li>
                                <i class="fas fa-shield-alt text-primary me-2"></i>
                                Không chia sẻ mật khẩu với người khác
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto hide alerts after 5s
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                if (!alert.classList.contains('alert-success')) {
                    new bootstrap.Alert(alert).close();
                }
            });
        }, 5000);
    </script>
</body>
</html>
<?php mysqli_close($ket_noi); ?>