<?php
session_start();
require '../db_connect.php';
require '../functions.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra quyền truy cập
if (!isset($_SESSION['vai_tro']) || $_SESSION['vai_tro'] != 'nguoithue') {
    echo "<script>alert('Bạn không có quyền truy cập!');location='../index.php';</script>";
    exit;
}

// ⭐ SỬA: Dùng 'id' thay vì 'user_id'
$id_nguoi_thue = intval($_SESSION['user_id']);

// Tìm phòng của người thuê
$cau_lenh_phong = mysqli_prepare($ket_noi, "SELECT * FROM phong_tro WHERE id_nguoi_thue = ?");
mysqli_stmt_bind_param($cau_lenh_phong, "i", $id_nguoi_thue);
mysqli_stmt_execute($cau_lenh_phong);
$ket_qua_phong = mysqli_stmt_get_result($cau_lenh_phong);
$phong = mysqli_fetch_assoc($ket_qua_phong);
mysqli_stmt_close($cau_lenh_phong);

if ($phong) {
    $id_phong = $phong['id'];

    // ⭐ SỬA: Lấy danh sách hóa đơn với đầy đủ thông tin
    $cau_lenh_hoa_don = mysqli_prepare($ket_noi, "
        SELECT 
            id,
            thang, 
            nam, 
            chi_so_dien_cu, 
            chi_so_dien_moi,
            so_dien_tieu_thu,
            don_gia_dien,
            tien_dien,
            chi_so_nuoc_cu, 
            chi_so_nuoc_moi,
            so_nuoc_tieu_thu,
            don_gia_nuoc,
            tien_nuoc,
            tien_phong,
            phi_khac,
            mo_ta_phi_khac,
            tong_tien,
            trang_thai,
            han_thanh_toan,
            ngay_thanh_toan,
            phuong_thuc_thanh_toan
        FROM hoa_don 
        WHERE id_phong = ?
        ORDER BY nam DESC, thang DESC
    ");
    mysqli_stmt_bind_param($cau_lenh_hoa_don, "i", $id_phong);
    mysqli_stmt_execute($cau_lenh_hoa_don);
    $danh_sach_hoa_don = mysqli_stmt_get_result($cau_lenh_hoa_don);

    // Lấy ngày bắt đầu thuê
    $ngay_bat_dau = $phong['ngay_bat_dau'] ?? null;

    if ($ngay_bat_dau) {
        $ngay_bat_dau = date('Y-m-d', strtotime($ngay_bat_dau));
        $ngay_hien_tai = date('Y-m-d');

        // Tính số ngày thuê chính xác
        $so_ngay_thue = (strtotime($ngay_hien_tai) - strtotime($ngay_bat_dau)) / (60 * 60 * 24);
        $so_ngay_thue = max(0, floor($so_ngay_thue));
        
        // Tính ngày đủ 1 tháng
        $ngay_day_du_thang = date('Y-m-d', strtotime('+1 month', strtotime($ngay_bat_dau)));
        $du_1_thang = ($ngay_hien_tai >= $ngay_day_du_thang);
    } else {
        $so_ngay_thue = 0;
        $du_1_thang = false;
        $ngay_day_du_thang = null;
    }

} else {
    $danh_sach_hoa_don = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang người thuê - DK BOARDING HOUSE</title>
    <link rel="icon" type="image/png" href="../assets/image/logo1.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_tenant_home.css?v=<?php echo time(); ?>">
    <style>
        .overdue {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        .payment-option:hover {
            transform: scale(1.05);
            transition: all 0.3s ease;
        }
        
        .qr-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fa-solid fa-user"></i> Xin chào, <?= htmlspecialchars($_SESSION['ho_ten']) ?></h2>
            <div>
                <a href="tenant_profile.php" class="btn btn-primary">
                    <i class="fa-solid fa-key"></i> Đổi mật khẩu
                </a>
                <a href="../logout.php" class="btn btn-primary">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </a>
            </div>
        </div>

        <?php if ($phong) { ?>
            <div class="card mb-4 shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa-solid fa-door-open"></i> Thông tin phòng của bạn</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fa-solid fa-home"></i> Phòng:</strong> 
                                <span class="badge bg-primary fs-6"><?= htmlspecialchars($phong['ten_phong']) ?></span>
                            </p>
                            <p class="mb-2">
                                <strong><i class="fa-solid fa-money-bill"></i> Giá thuê:</strong> 
                                <span class="text-dark fw-bold"><?= number_format($phong['gia_thue']) ?> VNĐ/tháng</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fa-solid fa-calendar-check"></i> Ngày bắt đầu thuê:</strong> 
                                <?= $ngay_bat_dau ? date('d/m/Y', strtotime($ngay_bat_dau)) : '<span class="text-muted">Chưa xác định</span>' ?>
                            </p>
                            <p class="mb-2">
                                <strong><i class="fa-solid fa-clock"></i> Đã thuê:</strong> 
                                <span class="badge bg-info"><?= $so_ngay_thue ?> ngày</span>
                                <?php
                                    if ($ngay_bat_dau) {
                                        if ($du_1_thang) {
                                            echo " <span class='badge bg-danger'><i class='fa-solid fa-exclamation-triangle'></i> Đã đủ 1 tháng — cần thanh toán</span>";
                                        } else {
                                            $ngay_du = date('d/m/Y', strtotime($ngay_day_du_thang));
                                            echo " <span class='badge bg-warning text-dark'><i class='fa-solid fa-hourglass-half'></i> Chưa đủ tháng — sẽ đủ vào ngày $ngay_du</span>";
                                        }
                                    }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ⭐ BẢNG HÓA ĐƠN MỚI - CÓ CỘT THANH TOÁN -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa-solid fa-file-invoice-dollar"></i> Lịch sử hóa đơn</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered text-center mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tháng/Năm</th>
                                    <th>Điện</th>
                                    <th>Nước</th>
                                    <th>Tổng tiền</th>
                                    <th>Hạn thanh toán</th>
                                    <th>Trạng thái</th>
                                    <th>Thanh toán</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if ($danh_sach_hoa_don && mysqli_num_rows($danh_sach_hoa_don) > 0) {
                                        while ($hd = mysqli_fetch_assoc($danh_sach_hoa_don)) {
                                            // Tính trạng thái
                                            $today = date('Y-m-d');
                                            $han_tt = $hd['han_thanh_toan'];
                                            $is_overdue = ($han_tt && $han_tt < $today && $hd['trang_thai'] == 'chua_thanh_toan');
                                            
                                            // Badge trạng thái
                                            if ($hd['trang_thai'] == 'da_thanh_toan') {
                                                $badge_class = 'success';
                                                $badge_text = '<i class="fas fa-check-circle me-1"></i>Đã thanh toán';
                                            } else if ($is_overdue) {
                                                $badge_class = 'danger overdue';
                                                $badge_text = '<i class="fas fa-exclamation-triangle me-1"></i>Quá hạn';
                                            } else {
                                                $badge_class = 'warning';
                                                $badge_text = '<i class="fas fa-clock me-1"></i>Chưa thanh toán';
                                            }
                                            
                                            echo "<tr>
                                                    <td><strong>{$hd['thang']}/{$hd['nam']}</strong></td>
                                                    <td>
                                                        <small class='text-muted'>{$hd['chi_so_dien_cu']} → {$hd['chi_so_dien_moi']}</small>
                                                        <br><span class='badge bg-warning text-dark'>{$hd['so_dien_tieu_thu']} kWh</span>
                                                        <br><small class='text-muted'>(" . number_format($hd['tien_dien']) . " đ)</small>
                                                    </td>
                                                    <td>
                                                        <small class='text-muted'>{$hd['chi_so_nuoc_cu']} → {$hd['chi_so_nuoc_moi']}</small>
                                                        <br><span class='badge bg-info'>{$hd['so_nuoc_tieu_thu']} m³</span>
                                                        <br><small class='text-muted'>(" . number_format($hd['tien_nuoc']) . " đ)</small>
                                                    </td>
                                                    <td>
                                                        <strong class='text-danger fs-5'>" . number_format($hd['tong_tien']) . " đ</strong>
                                                    </td>
                                                    <td>";
                                            
                                            if ($hd['han_thanh_toan']) {
                                                $class_han = $is_overdue ? 'text-danger fw-bold' : '';
                                                echo "<span class='$class_han'>" . date('d/m/Y', strtotime($hd['han_thanh_toan'])) . "</span>";
                                            } else {
                                                echo "<span class='text-muted'>Chưa có</span>";
                                            }
                                            
                                            echo "</td>
                                                    <td>
                                                        <span class='badge bg-{$badge_class}'>{$badge_text}</span>
                                                    </td>
                                                    <td>";
                                            
                                            // Cột THANH TOÁN
                                            if ($hd['trang_thai'] == 'chua_thanh_toan') {
                                                // Chưa thanh toán → Hiển thị nút
                                                echo "<button class='btn btn-success btn-sm' onclick='showPaymentModal(" . json_encode($hd) . ", \"" . htmlspecialchars($phong['ten_phong']) . "\")'>
                                                        <i class='fas fa-credit-card me-1'></i>Thanh toán
                                                      </button>";
                                            } else {
                                                // Đã thanh toán
                                                echo "<span class='text-success'><i class='fas fa-check-double'></i> Hoàn tất</span>";
                                                if ($hd['ngay_thanh_toan']) {
                                                    echo "<br><small class='text-muted'>" . date('d/m/Y', strtotime($hd['ngay_thanh_toan'])) . "</small>";
                                                }
                                            }
                                            
                                            echo "</td>
                                                </tr>";
                                        }
                                        mysqli_stmt_close($cau_lenh_hoa_don);
                                    } else {
                                        echo "<tr><td colspan='7' class='text-muted'>
                                            <i class='fa-solid fa-inbox'></i> Chưa có hóa đơn nào!
                                        </td></tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="alert alert-warning shadow">
                <i class="fa-solid fa-exclamation-triangle"></i> 
                <strong>Thông báo:</strong> Bạn hiện chưa ở vào phòng nào. Vui lòng liên hệ chủ trọ để được hỗ trợ.
            </div>
        <?php } ?>

        <div class="mt-3 text-center text-muted">
            <small>Nhà Trọ DK BOARDING HOUSE - Hotline: 0349 801 407</small>
        </div>
    </div>

    <!-- ⭐ MODAL THANH TOÁN -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-money-check-alt me-2"></i>Thanh toán hóa đơn
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Thông tin hóa đơn -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Phòng:</strong> <span id="bill_room"></span><br>
                                <strong>Tháng:</strong> <span id="bill_month"></span>
                            </div>
                            <div class="col-md-6 text-end">
                                <strong>Tổng tiền:</strong><br>
                                <span class="fs-4 text-danger fw-bold" id="bill_amount"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Chi tiết hóa đơn -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="border-bottom pb-2 mb-3">Chi tiết hóa đơn</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><i class="fas fa-home text-primary me-2"></i>Tiền phòng: <span id="bill_room_price" class="float-end fw-bold"></span></p>
                                    <p class="mb-2"><i class="fas fa-bolt text-warning me-2"></i>Tiền điện: <span id="bill_electric" class="float-end fw-bold"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><i class="fas fa-tint text-info me-2"></i>Tiền nước: <span id="bill_water" class="float-end fw-bold"></span></p>
                                    <p class="mb-2"><i class="fas fa-plus-circle text-secondary me-2"></i>Phí khác: <span id="bill_other" class="float-end fw-bold"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2 Lựa chọn thanh toán -->
                    <h5 class="mb-3 text-center">
                        <i class="fas fa-hand-pointer me-2"></i>Chọn phương thức thanh toán
                    </h5>

                    <div class="row g-3">
                        <!-- Option 1: Chuyển khoản -->
                        <div class="col-md-6">
                            <div class="card h-100 border-primary payment-option" style="cursor: pointer;" onclick="showBankTransfer()">
                                <div class="card-body text-center">
                                    <i class="fas fa-university fa-3x text-primary mb-3"></i>
                                    <h5>💳 Chuyển khoản</h5>
                                    <p class="text-muted small">Quét mã QR hoặc chuyển khoản thủ công</p>
                                    <button type="button" class="btn btn-primary btn-sm">
                                        Xem thông tin
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Option 2: Tiền mặt -->
                        <div class="col-md-6">
                            <div class="card h-100 border-success payment-option" style="cursor: pointer;" onclick="showCashInfo()">
                                <div class="card-body text-center">
                                    <i class="fas fa-money-bill-wave fa-3x text-success mb-3"></i>
                                    <h5>💵 Tiền mặt</h5>
                                    <p class="text-muted small">Thanh toán trực tiếp với chủ trọ</p>
                                    <button type="button" class="btn btn-success btn-sm">
                                        Xem thông tin
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin chuyển khoản (ẩn mặc định) -->
                    <div id="bank_info" class="mt-4" style="display: none;">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-qrcode me-2"></i>Thông tin chuyển khoản</h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="qr-container mb-3">
                                    <img id="qr_image" src="" alt="QR Code" class="img-fluid rounded shadow" style="max-width: 300px;">
                                </div>
                                <div class="bank-details text-start">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Ngân hàng:</strong> MBBank</p>
                                            <p class="mb-2"><strong>Số tài khoản:</strong> <code>0349801407</code></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Chủ tài khoản:</strong> Nguyễn Đăng Khôi</p>
                                            <p class="mb-2"><strong>Số tiền:</strong> <span class="text-danger fw-bold" id="qr_amount"></span></p>
                                        </div>
                                    </div>
                                    <div class="alert alert-warning mt-3">
                                        <strong><i class="fas fa-exclamation-triangle me-2"></i>Nội dung chuyển khoản:</strong><br>
                                        <code id="transfer_content" class="fs-6"></code>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Sau khi chuyển khoản vui lòng chụp giao dịch lại và đợi chủ trọ xác nhận thanh toán
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin tiền mặt (ẩn mặc định) -->
                    <div id="cash_info" class="mt-4" style="display: none;">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-phone me-2"></i>Thông tin liên hệ</h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <i class="fas fa-user-tie fa-4x text-success mb-3"></i>
                                    <h5>Vui lòng liên hệ chủ trọ để thanh toán</h5>
                                </div>
                                <div class="contact-info">
                                    <div class="alert alert-success">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-2">
                                                    <i class="fas fa-phone-alt me-2"></i>
                                                    <strong>Số điện thoại:</strong><br>
                                                    <a href="tel:0349801407" class="fs-4 text-decoration-none">
                                                        📞 0349 801 407
                                                    </a>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-2">
                                                    <i class="fas fa-map-marker-alt me-2"></i>
                                                    <strong>Địa chỉ:</strong><br>
                                                    Nguyễn Chí Thanh, P.6, Trà Vinh
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-warning">
                                        <strong><i class="fas fa-clock me-2"></i>Giờ làm việc:</strong> 8:00 - 20:00 hàng ngày
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Sau khi thanh toán, chủ trọ sẽ xác nhận trên hệ thống
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentBill = null;

        function showPaymentModal(bill, roomName) {
            currentBill = bill;
            
            // Điền thông tin hóa đơn
            document.getElementById('bill_room').textContent = roomName;
            document.getElementById('bill_month').textContent = bill.thang + '/' + bill.nam;
            document.getElementById('bill_amount').textContent = formatMoney(bill.tong_tien) + ' đ';
            
            // Chi tiết
            document.getElementById('bill_room_price').textContent = formatMoney(bill.tien_phong) + ' đ';
            document.getElementById('bill_electric').textContent = formatMoney(bill.tien_dien) + ' đ';
            document.getElementById('bill_water').textContent = formatMoney(bill.tien_nuoc) + ' đ';
            document.getElementById('bill_other').textContent = formatMoney(bill.phi_khac || 0) + ' đ';
            
            // Ẩn các phần thông tin thanh toán
            document.getElementById('bank_info').style.display = 'none';
            document.getElementById('cash_info').style.display = 'none';
            
            // Hiển thị modal
            new bootstrap.Modal(document.getElementById('paymentModal')).show();
        }

        function showBankTransfer() {
            // Ẩn tiền mặt, hiện chuyển khoản
            document.getElementById('cash_info').style.display = 'none';
            document.getElementById('bank_info').style.display = 'block';
            
            // Tạo QR Code
            const amount = currentBill.tong_tien;
            const content = `Thanh toan phong ${document.getElementById('bill_room').textContent} thang ${currentBill.thang}/${currentBill.nam}`;
            
            // VietQR API
            const qrUrl = `https://img.vietqr.io/image/970422-0349801407-compact2.png?amount=${amount}&addInfo=${encodeURIComponent(content)}&accountName=NGUYEN DANG KHOI`;
            
            document.getElementById('qr_image').src = qrUrl;
            document.getElementById('qr_amount').textContent = formatMoney(amount) + ' đ';
            document.getElementById('transfer_content').textContent = content;
        }

        function showCashInfo() {
            // Ẩn chuyển khoản, hiện tiền mặt
            document.getElementById('bank_info').style.display = 'none';
            document.getElementById('cash_info').style.display = 'block';
        }

        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount);
        }
    </script>
</body>
</html>