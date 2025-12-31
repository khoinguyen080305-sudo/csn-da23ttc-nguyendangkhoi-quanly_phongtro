<?php
session_start();
require '../db_connect.php';
require '../functions.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['vai_tro']) || $_SESSION['vai_tro'] != 'nguoithue') {
    echo "<script>alert('Bạn không có quyền truy cập!');location='../index.php';</script>";
    exit;
}

$thong_bao_thanh_cong = '';
$thong_bao_loi = '';

if (isset($_POST['update'])) {
    // ⭐ SỬA: Dùng 'id' thay vì 'user_id'
    $id = intval($_SESSION['user_id']);
    $mat_khau_cu = trim($_POST['oldpass']);
    $mat_khau_moi = trim($_POST['newpass']);
    $xac_nhan_mat_khau = trim($_POST['confirmpass']);

    // Validate
    if (empty($mat_khau_cu) || empty($mat_khau_moi) || empty($xac_nhan_mat_khau)) {
        $thong_bao_loi = "Vui lòng nhập đầy đủ thông tin!";
    } elseif (strlen($mat_khau_moi) < 6) {
        $thong_bao_loi = "Mật khẩu mới phải có ít nhất 6 ký tự!";
    } elseif ($mat_khau_moi !== $xac_nhan_mat_khau) {
        $thong_bao_loi = "Mật khẩu mới và xác nhận không khớp!";
    } elseif ($mat_khau_cu === $mat_khau_moi) {
        $thong_bao_loi = "Mật khẩu mới phải khác mật khẩu cũ!";
    } else {
        // Lấy mật khẩu hiện tại
        $cau_lenh = mysqli_prepare($ket_noi, "SELECT mat_khau FROM nguoi_dung WHERE id = ?");
        mysqli_stmt_bind_param($cau_lenh, "i", $id);
        mysqli_stmt_execute($cau_lenh);
        $ket_qua = mysqli_stmt_get_result($cau_lenh);
        
        if (mysqli_num_rows($ket_qua) == 0) {
            $thong_bao_loi = "Tài khoản không tồn tại!";
        } else {
            $nguoi_dung = mysqli_fetch_assoc($ket_qua);
            mysqli_stmt_close($cau_lenh);

            // Kiểm tra mật khẩu cũ
            if (password_verify($mat_khau_cu, $nguoi_dung['mat_khau'])) {
                $mat_khau_ma_hoa = password_hash($mat_khau_moi, PASSWORD_DEFAULT);
                
                // Cập nhật mật khẩu mới
                $cau_lenh_cap_nhat = mysqli_prepare($ket_noi, "UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?");
                mysqli_stmt_bind_param($cau_lenh_cap_nhat, "si", $mat_khau_ma_hoa, $id);
                
                if (mysqli_stmt_execute($cau_lenh_cap_nhat)) {
                    $thong_bao_thanh_cong = "Đổi mật khẩu thành công!";
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - DK BOARDING HOUSE</title>
    <link rel="icon" type="image/png" href="../assets/image/logo1.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_tenant_profile.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a href="tenant_home.php" class="navbar-brand">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
            <span class="text-white">
                <i class="fas fa-user me-2"></i><?= htmlspecialchars($_SESSION['ho_ten']) ?>
            </span>
        </div>
    </nav>

    <div class="container mt-5 pt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <!-- Card đổi mật khẩu -->
                <div class="card password-card">
                    <div class="card-header text-center py-4">
                        <h2 class="mb-0">
                            <i class="fa-solid fa-key me-2"></i>Đổi mật khẩu
                            <br>
                            <small>Thay đổi mật khẩu tài khoản của bạn</small>
                        </h2>
                    </div>
                    <div class="card-body p-4">
                        <!-- Thông báo thành công -->
                        <?php if ($thong_bao_thanh_cong): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fa-solid fa-check-circle me-2"></i>
                                <strong>Thành công!</strong> <?= htmlspecialchars($thong_bao_thanh_cong) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = 'tenant_home.php';
                                }, 2000);
                            </script>
                        <?php endif; ?>

                        <!-- Thông báo lỗi -->
                        <?php if ($thong_bao_loi): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                <strong>Lỗi!</strong> <?= htmlspecialchars($thong_bao_loi) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Form đổi mật khẩu -->
                        <form method="POST" id="changePasswordForm">
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fa-solid fa-lock text-secondary me-2"></i>Mật khẩu cũ
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <input type="password" 
                                           name="oldpass" 
                                           id="oldpass"
                                           class="form-control" 
                                           placeholder="Nhập mật khẩu hiện tại" 
                                           required>
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            onclick="togglePassword('oldpass')">
                                        <i class="fas fa-eye" id="oldpass-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fa-solid fa-lock-open text-success me-2"></i>Mật khẩu mới
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <input type="password" 
                                           name="newpass" 
                                           id="newpass"
                                           class="form-control" 
                                           placeholder="Tối thiểu 6 ký tự" 
                                           minlength="6" 
                                           required>
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            onclick="togglePassword('newpass')">
                                        <i class="fas fa-eye" id="newpass-icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Mật khẩu phải có ít nhất 6 ký tự
                                </small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fa-solid fa-shield-halved text-warning me-2"></i>Xác nhận mật khẩu mới
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <input type="password" 
                                           name="confirmpass" 
                                           id="confirmpass"
                                           class="form-control" 
                                           placeholder="Nhập lại mật khẩu mới" 
                                           minlength="6" 
                                           required>
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            onclick="togglePassword('confirmpass')">
                                        <i class="fas fa-eye" id="confirmpass-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="update" class="btn btn-primary btn-lg">
                                    <i class="fa-solid fa-check me-2"></i>Cập nhật mật khẩu
                                </button>
                                <a href="tenant_home.php" class="btn btn-secondary btn-lg">
                                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Box thông tin bảo mật -->
                <div class="card mt-3 password-card">
                    <div class="card-body info-box p-3">
                        <h6 class="text-primary mb-3">
                            <i class="fa-solid fa-shield-halved me-2"></i>
                            Lưu ý bảo mật
                        </h6>
                        <ul class="mb-0 text-muted small">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Mật khẩu phải có <strong>ít nhất 6 ký tự</strong>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Không sử dụng mật khẩu quá đơn giản (VD: 123456, password)
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Mật khẩu mới phải <strong>khác mật khẩu cũ</strong>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Không chia sẻ</strong> mật khẩu với người khác
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Nên đổi mật khẩu <strong>định kỳ 3-6 tháng</strong>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Thông tin liên hệ -->
                <div class="text-center mt-3">
                    <small class="text-white">
                        <i class="fas fa-phone me-1"></i>
                        Cần hỗ trợ? Gọi: <strong>0349 801 407</strong>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hàm toggle hiển thị/ẩn mật khẩu
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Validate form trước khi submit
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const newPass = document.getElementById('newpass').value;
            const confirmPass = document.getElementById('confirmpass').value;
            
            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('Mật khẩu mới và xác nhận không khớp!');
                document.getElementById('confirmpass').focus();
            }
        });

        // Auto hide alerts sau 5 giây
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>