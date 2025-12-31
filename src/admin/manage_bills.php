<?php
session_start();
require '../db_connect.php';
require '../functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['vai_tro']) || $_SESSION['vai_tro'] != 'admin') {
    header("Location: ../index.php?error=access_denied");
    exit;
}

$thong_bao_thanh_cong = "";
$thong_bao_loi = "";

// Xóa hóa đơn
if (isset($_POST['delete'])) {
    $id = intval($_POST['id']);
    $cau_lenh = mysqli_prepare($ket_noi, "DELETE FROM hoa_don WHERE id = ?");
    mysqli_stmt_bind_param($cau_lenh, "i", $id);
    
    if (mysqli_stmt_execute($cau_lenh)) {
        $thong_bao_thanh_cong = "Đã xóa hóa đơn thành công!";
    } else {
        $thong_bao_loi = "Không thể xóa hóa đơn!";
    }
    mysqli_stmt_close($cau_lenh);
}

// ⭐ CẬP NHẬT: Xác nhận thanh toán từ admin
if (isset($_POST['update_status'])) {
    $id = intval($_POST['id']);
    $trang_thai = $_POST['trang_thai'];
    $ngay_thanh_toan = ($trang_thai == 'da_thanh_toan') ? date('Y-m-d') : NULL;
    $phuong_thuc = $_POST['phuong_thuc'] ?? NULL;
    
    // Bắt đầu transaction
    mysqli_begin_transaction($ket_noi);
    
    try {
        // 1. Cập nhật trạng thái hóa đơn
        $sql = "UPDATE hoa_don SET trang_thai = ?, ngay_thanh_toan = ?, phuong_thuc_thanh_toan = ? WHERE id = ?";
        $stmt = mysqli_prepare($ket_noi, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $trang_thai, $ngay_thanh_toan, $phuong_thuc, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // ⭐ 2. Lưu vào bảng thanh_toan (nếu đã thanh toán)
        if ($trang_thai == 'da_thanh_toan') {
            // Lấy số tiền từ hóa đơn
            $stmt_tien = mysqli_prepare($ket_noi, "SELECT tong_tien FROM hoa_don WHERE id = ?");
            mysqli_stmt_bind_param($stmt_tien, "i", $id);
            mysqli_stmt_execute($stmt_tien);
            $result_tien = mysqli_stmt_get_result($stmt_tien);
            $row_tien = mysqli_fetch_assoc($result_tien);
            $so_tien = $row_tien['tong_tien'];
            mysqli_stmt_close($stmt_tien);
            
            // Thêm vào bảng thanh_toan
            $sql_tt = "INSERT INTO thanh_toan (id_hoa_don, so_tien, phuong_thuc, ngay_thanh_toan, ghi_chu) 
                       VALUES (?, ?, ?, ?, 'Admin xác nhận thanh toán')";
            $stmt_tt = mysqli_prepare($ket_noi, $sql_tt);
            mysqli_stmt_bind_param($stmt_tt, "idss", $id, $so_tien, $phuong_thuc, $ngay_thanh_toan);
            mysqli_stmt_execute($stmt_tt);
            mysqli_stmt_close($stmt_tt);
        }
        
        // Commit transaction
        mysqli_commit($ket_noi);
        $thong_bao_thanh_cong = "Cập nhật trạng thái thành công!";
        
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        mysqli_rollback($ket_noi);
        $thong_bao_loi = "Lỗi khi cập nhật: " . $e->getMessage();
    }
}

// ⭐ SỬA: Thêm hóa đơn mới - Phù hợp CSDL mới
if (isset($_POST['add'])) {
    $id_phong = intval($_POST['phong']);
    $thang = intval($_POST['thang']);
    $nam = intval($_POST['nam']);
    $dien_cu = intval($_POST['dien_cu']);
    $dien_moi = intval($_POST['dien_moi']);
    $nuoc_cu = intval($_POST['nuoc_cu']);
    $nuoc_moi = intval($_POST['nuoc_moi']);
    $han_thanh_toan = $_POST['han_thanh_toan'];
    $gia_dien = floatval($_POST['gia_dien']);
    $gia_nuoc = floatval($_POST['gia_nuoc']);
    
    // ⭐ THÊM: Phí khác (thay vì internet + rác)
    $phi_khac = floatval($_POST['phi_khac'] ?? 0);
    $mo_ta_phi_khac = $_POST['mo_ta_phi_khac'] ?? '';

    // Validate
    if ($id_phong <= 0 || $thang < 1 || $thang > 12 || $nam < 2000 || $nam > 2100) {
        $thong_bao_loi = "Thông tin không hợp lệ!";
    }
    elseif ($dien_moi < $dien_cu || $nuoc_moi < $nuoc_cu) {
        $thong_bao_loi = "Chỉ số mới phải lớn hơn hoặc bằng chỉ số cũ!";
    }
    elseif ($gia_dien <= 0 || $gia_nuoc <= 0) {
        $thong_bao_loi = "Giá điện và nước phải lớn hơn 0!";
    }
    else {
        // Kiểm tra hóa đơn đã tồn tại
        $stmt_check = mysqli_prepare($ket_noi, "SELECT id FROM hoa_don WHERE id_phong = ? AND thang = ? AND nam = ?");
        mysqli_stmt_bind_param($stmt_check, "iii", $id_phong, $thang, $nam);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) > 0) {
            $thong_bao_loi = "Hóa đơn tháng $thang/$nam của phòng này đã tồn tại!";
            mysqli_stmt_close($stmt_check);
        }
        else {
            mysqli_stmt_close($stmt_check);
            
            // Lấy thông tin phòng và người thuê
            $stmt_phong = mysqli_prepare($ket_noi, "SELECT gia_thue, id_nguoi_thue FROM phong_tro WHERE id = ?");
            mysqli_stmt_bind_param($stmt_phong, "i", $id_phong);
            mysqli_stmt_execute($stmt_phong);
            $result_phong = mysqli_stmt_get_result($stmt_phong);

            if (mysqli_num_rows($result_phong) == 0) {
                $thong_bao_loi = "Phòng không tồn tại!";
                mysqli_stmt_close($stmt_phong);
            }
            else {
                $phong = mysqli_fetch_assoc($result_phong);
                mysqli_stmt_close($stmt_phong);
                
                if (empty($phong['id_nguoi_thue'])) {
                    $thong_bao_loi = "Phòng này chưa có người thuê!";
                }
                else {
                    // ⭐ SỬA: Câu SQL phù hợp với CSDL mới
                    $sql = "INSERT INTO hoa_don(
                        id_phong, id_nguoi_thue, thang, nam,
                        chi_so_dien_cu, chi_so_dien_moi, don_gia_dien,
                        chi_so_nuoc_cu, chi_so_nuoc_moi, don_gia_nuoc,
                        tien_phong, phi_khac, mo_ta_phi_khac,
                        han_thanh_toan, trang_thai
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'chua_thanh_toan')";
                    
                    $stmt = mysqli_prepare($ket_noi, $sql);
                    mysqli_stmt_bind_param($stmt, "iiiiiidiidddss", 
                        $id_phong, $phong['id_nguoi_thue'], $thang, $nam,
                        $dien_cu, $dien_moi, $gia_dien,
                        $nuoc_cu, $nuoc_moi, $gia_nuoc,
                        $phong['gia_thue'], $phi_khac, $mo_ta_phi_khac,
                        $han_thanh_toan
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        $thong_bao_thanh_cong = "Thêm hóa đơn thành công!";
                    } else {
                        $thong_bao_loi = "Lỗi khi thêm hóa đơn: " . mysqli_error($ket_noi);
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

// Lấy danh sách hóa đơn
$danh_sach_hoa_don = mysqli_query($ket_noi, 
    "SELECT h.*, p.ten_phong, n.ho_ten, n.sdt
     FROM hoa_don h 
     LEFT JOIN phong_tro p ON h.id_phong = p.id 
     LEFT JOIN nguoi_dung n ON h.id_nguoi_thue = n.id  
     ORDER BY h.nam DESC, h.thang DESC, h.id DESC"
);

// Lấy danh sách phòng đã có người thuê
$danh_sach_phong = mysqli_query($ket_noi, 
    "SELECT p.*, n.ho_ten 
     FROM phong_tro p
     LEFT JOIN nguoi_dung n ON p.id_nguoi_thue = n.id
     WHERE p.trang_thai = 'da_thue' AND p.id_nguoi_thue IS NOT NULL
     ORDER BY p.ten_phong"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý hóa đơn - DK BOARDING HOUSE</title>
    <link rel="icon" type="image/png" href="../assets/image/logo1.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style_manage_bills.css?v=<?php echo time(); ?>">
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
        <h2><i class="fas fa-file-invoice-dollar me-2"></i>Quản lý hóa đơn</h2>

        <!-- Thông báo -->
        <?php if (!empty($thong_bao_thanh_cong)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($thong_bao_thanh_cong) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($thong_bao_loi)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($thong_bao_loi) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form thêm hóa đơn -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Thêm hóa đơn mới</h5>
            </div>
            <div class="card-body">
                <form class="row g-3" method="POST">
                    <div class="col-md-4">
                        <label class="form-label">Phòng <span class="text-danger">*</span></label>
                        <select name="phong" class="form-select" required>
                            <option value="">Chọn phòng</option>
                            <?php 
                            mysqli_data_seek($danh_sach_phong, 0);
                            while($phong = mysqli_fetch_assoc($danh_sach_phong)) {
                                echo "<option value='{$phong['id']}'>" 
                                    . htmlspecialchars($phong['ten_phong']) 
                                    . " - " . htmlspecialchars($phong['ho_ten'])
                                    . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tháng <span class="text-danger">*</span></label>
                        <input name="thang" type="number" min="1" max="12" class="form-control" value="<?= date('n') ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Năm <span class="text-danger">*</span></label>
                        <input name="nam" type="number" min="2000" max="2100" class="form-control" value="<?= date('Y') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hạn thanh toán</label>
                        <input name="han_thanh_toan" type="date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Điện cũ (kWh)</label>
                        <input name="dien_cu" type="number" min="0" class="form-control" value="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Điện mới (kWh)</label>
                        <input name="dien_moi" type="number" min="0" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Giá điện (đ/kWh)</label>
                        <input name="gia_dien" type="number" min="0" step="100" class="form-control" value="4000" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Nước cũ (m³)</label>
                        <input name="nuoc_cu" type="number" min="0" class="form-control" value="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nước mới (m³)</label>
                        <input name="nuoc_moi" type="number" min="0" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Giá nước (đ/m³)</label>
                        <input name="gia_nuoc" type="number" min="0" step="100" class="form-control" value="7000" required>
                    </div>
                    
                    <!-- ⭐ THAY ĐỔI: Phí khác thay vì Internet + Rác -->
                    <div class="col-md-3">
                        <label class="form-label">Phí khác (đ)</label>
                        <input name="phi_khac" type="number" min="0" step="1000" class="form-control" value="0" placeholder="VD: Rác...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mô tả phí khác</label>
                        <input name="mo_ta_phi_khac" type="text" class="form-control" placeholder="VD: Rác 50k">
                    </div>
                    
                    <div class="col-12">
                        <button class="btn btn-success" name="add">
                            <i class="fas fa-save me-2"></i>Tạo hóa đơn
                        </button>
                    </div>
                </form>
                <div class="mt-3 p-3 bg-light rounded">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Lưu ý:</strong> Giá điện/nước là: 3.000đ/kWh và 7.000đ/m³. Bạn có thể chỉnh sửa lại giá cho đúng với thị trường!
                    </small>
                </div>
            </div>
        </div>

        <!-- Danh sách hóa đơn -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách hóa đơn</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>ID</th>
                                <th>Phòng</th>
                                <th>Người thuê</th>
                                <th>Tháng/Năm</th>
                                <th>Điện (kWh)</th>
                                <th>Nước (m³)</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($danh_sach_hoa_don) > 0) {
                                while($hd = mysqli_fetch_assoc($danh_sach_hoa_don)) {
                                    $status_class = '';
                                    $status_text = '';
                                    switch($hd['trang_thai']) {
                                        case 'da_thanh_toan':
                                            $status_class = 'success';
                                            $status_text = 'Đã thanh toán';
                                            break;
                                        default:
                                            $status_class = 'warning';
                                            $status_text = 'Chưa thanh toán';
                                    }
                            ?>
                                <tr>
                                    <td class="text-center"><?= $hd['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($hd['ten_phong']) ?></strong></td>
                                    <td><?= htmlspecialchars($hd['ho_ten'] ?? 'N/A') ?></td>
                                    <td class="text-center"><?= $hd['thang'] ?>/<?= $hd['nam'] ?></td>
                                    <td class="text-center">
                                        <?= $hd['chi_so_dien_cu'] ?> → <?= $hd['chi_so_dien_moi'] ?>
                                        <br><small class="text-success"><?= $hd['so_dien_tieu_thu'] ?> kWh</small>
                                    </td>
                                    <td class="text-center">
                                        <?= $hd['chi_so_nuoc_cu'] ?> → <?= $hd['chi_so_nuoc_moi'] ?>
                                        <br><small class="text-info"><?= $hd['so_nuoc_tieu_thu'] ?> m³</small>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-danger"><?= number_format($hd['tong_tien']) ?> đ</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $status_class ?>"><?= $status_text ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($hd['trang_thai'] != 'da_thanh_toan'): ?>
                                        <button class="btn btn-sm btn-success" onclick="updateStatus(<?= $hd['id'] ?>)" title="Xác nhận thanh toán">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Xóa hóa đơn này?');">
                                            <input type="hidden" name="id" value="<?= $hd['id'] ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-danger" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center text-muted'>Chưa có hóa đơn nào</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal cập nhật trạng thái -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Xác nhận thanh toán</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="id" id="status_id">
                    <input type="hidden" name="trang_thai" value="da_thanh_toan">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Xác nhận rằng bạn đã nhận được tiền thanh toán từ người thuê
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phương thức thanh toán <span class="text-danger">*</span></label>
                            <select name="phuong_thuc" class="form-select" required>
                                <option value="">-- Chọn phương thức --</option>
                                <option value="tien_mat">💵 Tiền mặt</option>
                                <option value="chuyen_khoan">💳 Chuyển khoản</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Hủy
                        </button>
                        <button type="submit" name="update_status" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Xác nhận đã thanh toán
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStatus(id) {
            document.getElementById('status_id').value = id;
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }
        // Auto hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
</body>
</html>
<?php mysqli_close($ket_noi); ?>