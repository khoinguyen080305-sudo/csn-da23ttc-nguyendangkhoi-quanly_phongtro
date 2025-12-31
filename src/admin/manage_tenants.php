<?php
session_start();
require '../db_connect.php';
require '../functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['vai_tro']) || $_SESSION['vai_tro'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$thong_bao_thanh_cong = '';
$thong_bao_loi = '';

//Tạo tên đăng nhập từ họ tên + ID
function tao_ten_dang_nhap($ho_ten, $id) {
    return bo_dau_tieng_viet($ho_ten) . $id;
}
// Xử lý THÊM người thuê mới
if (isset($_POST['add'])) {
    $ho_ten = trim($_POST['ho_ten']);
    $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
    $sdt = !empty($_POST['sdt']) ? $_POST['sdt'] : null;
    $email = !empty($_POST['email']) ? $_POST['email'] : null;
    
    // Bước 1: INSERT người dùng (chưa có tên đăng nhập)
    $sql = "INSERT INTO nguoi_dung (ho_ten, mat_khau, sdt, email, vai_tro, trang_thai) 
            VALUES (?, ?, ?, ?, 'nguoithue', 'hoat_dong')";
    $stmt = mysqli_prepare($ket_noi, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $ho_ten, $mat_khau, $sdt, $email);
    
    if (mysqli_stmt_execute($stmt)) {
        // Bước 2: Lấy ID vừa tạo
        $id_moi = mysqli_insert_id($ket_noi);
        
        // Bước 3: Tạo tên đăng nhập tự động
        $ten_dang_nhap = tao_ten_dang_nhap($ho_ten, $id_moi);
        
        // Bước 4: Cập nhật tên đăng nhập
        $sql_update = "UPDATE nguoi_dung SET ten_dang_nhap = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($ket_noi, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "si", $ten_dang_nhap, $id_moi);
        mysqli_stmt_execute($stmt_update);
        
        $thong_bao_thanh_cong = "Thêm người thuê thành công! Tài khoản: <strong>$ten_dang_nhap</strong> - Mật khẩu đã nhập";
    } else {
        $thong_bao_loi = "Lỗi khi thêm người thuê: " . mysqli_error($ket_noi);
    }
}

// Xử lý SỬA thông tin người thuê
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $ho_ten = trim($_POST['ho_ten']);
    $sdt = !empty($_POST['sdt']) ? $_POST['sdt'] : null;
    $email = !empty($_POST['email']) ? $_POST['email'] : null;
    
    $sql = "UPDATE nguoi_dung 
            SET ho_ten = ?, sdt = ?, email = ?
            WHERE id = ? AND vai_tro = 'nguoithue'";
    $stmt = mysqli_prepare($ket_noi, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $ho_ten, $sdt, $email, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $thong_bao_thanh_cong = "Cập nhật thông tin thành công!";
    } else {
        $thong_bao_loi = "Lỗi khi cập nhật: " . mysqli_error($ket_noi);
    }
}

// Xử lý XÓA người thuê
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // 1. Lấy thông tin khách hàng trước khi xóa
    $sql_get_user = "SELECT ho_ten, sdt FROM nguoi_dung WHERE id = ?";
    $stmt_get_user = mysqli_prepare($ket_noi, $sql_get_user);
    mysqli_stmt_bind_param($stmt_get_user, "i", $id);
    mysqli_stmt_execute($stmt_get_user);
    $result_user = mysqli_stmt_get_result($stmt_get_user);
    $user_data = mysqli_fetch_assoc($result_user);
    mysqli_stmt_close($stmt_get_user); // Đóng stmt này

    if (!$user_data) {
        $thong_bao_loi = "Người thuê không tồn tại!";
        // Quay lại ngay nếu không tìm thấy người dùng
    } else {
        $ten_cu = $user_data['ho_ten'];
        $sdt_cu = $user_data['sdt'];

        // Kiểm tra xem người thuê có đang thuê phòng không (vẫn như cũ)
        $sql_phong = "SELECT ten_phong FROM phong_tro WHERE id_nguoi_thue = ?";
        $stmt_phong = mysqli_prepare($ket_noi, $sql_phong);
        mysqli_stmt_bind_param($stmt_phong, "i", $id);
        mysqli_stmt_execute($stmt_phong);
        $result_phong = mysqli_stmt_get_result($stmt_phong);
        
        if (mysqli_num_rows($result_phong) > 0) {
            $phong = mysqli_fetch_assoc($result_phong);
            mysqli_stmt_close($stmt_phong); // Đóng stmt này
            
            if (!isset($_GET['confirm']) || $_GET['confirm'] != 'yes') {
                $thong_bao_loi = "Người này đang thuê phòng <strong>" . htmlspecialchars($phong['ten_phong']) . 
                    "</strong>. <a href='?delete={$id}&confirm=yes' class='btn btn-danger btn-sm ms-2'>Xác nhận xóa</a>";
            } else {
                // Xác nhận xóa - Sử dụng Transaction
                mysqli_begin_transaction($ket_noi);
                try {
                    // Bước 2: Giải phóng phòng
                    $sql_update_phong = "UPDATE phong_tro SET id_nguoi_thue = NULL, trang_thai = 'trong', ngay_bat_dau = NULL WHERE id_nguoi_thue = ?";
                    $stmt_update = mysqli_prepare($ket_noi, $sql_update_phong);
                    mysqli_stmt_bind_param($stmt_update, "i", $id);
                    mysqli_stmt_execute($stmt_update);
                    
                    // BƯỚC MỚI 3: Cập nhật tên và SĐT vào lịch sử thuê (ghi đè dữ liệu tĩnh)
                    $sql_update_lich_su = "
                        UPDATE lich_su_thue 
                        SET ho_ten_khach_thue = ?, sdt_khach_thue = ?, ngay_ket_thuc = CURDATE() 
                        WHERE id_nguoi_thue = ? AND ngay_ket_thuc IS NULL
                    ";
                    $stmt_update_lich_su = mysqli_prepare($ket_noi, $sql_update_lich_su);
                    mysqli_stmt_bind_param($stmt_update_lich_su, "ssi", $ten_cu, $sdt_cu, $id);
                    mysqli_stmt_execute($stmt_update_lich_su);
                    
                    // Bước 4: Xóa người dùng
                    $sql_delete = "DELETE FROM nguoi_dung WHERE id = ? AND vai_tro = 'nguoithue'";
                    $stmt_delete = mysqli_prepare($ket_noi, $sql_delete);
                    mysqli_stmt_bind_param($stmt_delete, "i", $id);
                    
                    if (mysqli_stmt_execute($stmt_delete)) {
                        mysqli_commit($ket_noi);
                        $thong_bao_thanh_cong = "Đã xóa người thuê và phòng trở về trạng thái ban đầu!";
                    } else {
                        throw new Exception('Lỗi khi xóa người dùng!');
                    }
                } catch (Exception $e) {
                    mysqli_rollback($ket_noi);
                    $thong_bao_loi = 'Lỗi khi xóa: ' . $e->getMessage();
                }
            }
        } else {
            // Không thuê phòng nào - xóa trực tiếp
            $sql = "DELETE FROM nguoi_dung WHERE id = ? AND vai_tro = 'nguoithue'";
            $stmt = mysqli_prepare($ket_noi, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            
            if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
                $thong_bao_thanh_cong = "Xóa người thuê thành công!";
            } else {
                $thong_bao_loi = "Không thể xóa người thuê này!";
            }
        }
    }
}


// Lấy danh sách người thuê
$sql_danh_sach = "
    SELECT 
        n.id, n.ho_ten, n.ten_dang_nhap, n.sdt, n.email,
        p.ten_phong, p.gia_thue, p.ngay_bat_dau
    FROM nguoi_dung n
    LEFT JOIN phong_tro p ON n.id = p.id_nguoi_thue
    WHERE n.vai_tro = 'nguoithue'
    ORDER BY n.id DESC
";
$danh_sach = mysqli_query($ket_noi, $sql_danh_sach);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người thuê - DK BOARDING HOUSE</title>
    <link rel="icon" type="image/png" href="../assets/image/logo1.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_manage_tenants.css?v=<?php echo time(); ?>">
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
        <h2><i class="fas fa-user-group me-2"></i>Quản lý người thuê</h2>
        <!-- Thông báo -->
        <?php if (!empty($thong_bao_thanh_cong)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><strong>Thành công!</strong> <?= $thong_bao_thanh_cong ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($thong_bao_loi)): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i><strong>Cảnh báo!</strong> <?= $thong_bao_loi ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Form thêm người thuê - KHÔNG CẦN NHẬP TÊN ĐĂNG NHẬP
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Thêm người thuê</h5>
            </div>
            <div class="card-body">
                <form class="row g-3" method="POST">
                    <div class="col-md-4">
                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input class="form-control" name="ho_ten" required>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Tên đăng nhập sẽ tự động tạo từ họ tên + ID
                        </small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Số điện thoại</label>
                        <input class="form-control" name="sdt" pattern="0[0-9]{9,10}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-success" name="add">
                            <i class="fas fa-save me-2"></i>Thêm người thuê
                        </button>
                    </div>
                </form>
            </div>
        </div> -->
        <!-- Danh sách người thuê -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách người thuê</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>ID</th>
                                <th>Họ tên</th>
                                <th>Tài khoản</th>
                                <th>SĐT</th>
                                <th>Email</th>
                                <th>Phòng</th>
                                <th>Giá thuê</th>
                                <th>Ngày thuê</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($danh_sach) > 0) {
                                while ($row = mysqli_fetch_assoc($danh_sach)) {
                                    $phong_badge = $row['ten_phong'] 
                                        ? "<span class='badge bg-success'>" . htmlspecialchars($row['ten_phong']) . "</span>" 
                                        : "<span class='text-muted'>Chưa thuê</span>";
                                    $gia_thue = $row['gia_thue'] ? number_format($row['gia_thue']) . " đ" : "-";
                                    $ngay_thue = $row['ngay_bat_dau'] ? date('d/m/Y', strtotime($row['ngay_bat_dau'])) : "-";
                            ?>
                                <tr>
                                    <td class="text-center"><?= $row['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($row['ho_ten']) ?></strong></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= htmlspecialchars($row['ten_dang_nhap']) ?></span>
                                    </td>
                                    <td class="text-center"><?= htmlspecialchars($row['sdt'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($row['email'] ?: '-') ?></td>
                                    <td class="text-center"><?= $phong_badge ?></td>
                                    <td class="text-end"><?= $gia_thue ?></td>
                                    <td class="text-center"><?= $ngay_thue ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning" onclick="editTenant(<?= $row['id'] ?>)" title="Sửa thông tin">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Xóa người thuê này?')" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='10' class='text-center text-muted'>Chưa có người thuê nào</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div> <!-- Đóng container - dòng 321 -->
    
    <!-- Modal Sửa Thông Tin -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa thông tin người thuê</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div id="editFormContent">
                            <!-- Nội dung form sẽ được load bằng JavaScript -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Hủy
                        </button>
                        <button type="submit" name="edit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editTenant(id) {
            fetch('get_tenant_data.php?id=' + id)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        alert('Lỗi: ' + data.error);
                        return;
                    }
                    document.getElementById('edit_id').value = data.id;
                   document.getElementById('editFormContent').innerHTML = `
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Họ và tên</label>
                                <input name="ho_ten" class="form-control" value="${data.ho_ten || ''}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại</label>
                                <input name="sdt" class="form-control" value="${data.sdt || ''}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="${data.email || ''}">
                            </div>
                        </div>
                    `;
                    new bootstrap.Modal(document.getElementById('editModal')).show();
                })
                .catch(err => {
                    alert('Không thể tải dữ liệu: ' + err.message);
                });
        }
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => new bootstrap.Alert(alert).close());
        }, 5000);
    </script>
</body>
</html>