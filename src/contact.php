    <?php
    require 'db_connect.php';
    require 'functions.php';

    $ma_phong = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $ten_phong_url = isset($_GET['phong']) ? trim($_GET['phong']) : '';

    //kiem tra tham so hop le
    if ($ma_phong <= 0 || empty($ten_phong_url)) {
        ghi_log("Lỗi: Truy cập contact.php với tham số không hợp lệ - ID: {$ma_phong}, Tên phòng: {$ten_phong_url}", 'warning');
        header('Location: home.php?error=invalid_params');
        exit();
    }

    //lam sach ten phong tu url
    $ten_phong_url = lam_sach($ten_phong_url);
    //kiem tra phong trong database
    $cau_sql_kiem_tra = "SELECT id, ten_phong, gia_thue, tien_coc, dien_tich, so_nguoi_o_toi_da, 
                                anh_phong, trang_thai, 
                                co_dieu_hoa, co_nong_lanh, co_tu_lanh, co_giu_xe, co_ban_cong
                        FROM phong_tro 
                        WHERE id = ? AND trang_thai = 'trong'";

    $cau_lenh = mysqli_prepare($ket_noi, $cau_sql_kiem_tra);

    if (!$cau_lenh) {
        ghi_log("Lỗi prepare statement kiểm tra phòng ID {$ma_phong}: " . mysqli_error($ket_noi), 'error');
        mysqli_close($ket_noi);
        header('Location: home.php?error=system_error');
        exit();
    }

    mysqli_stmt_bind_param($cau_lenh, "i", $ma_phong);
    mysqli_stmt_execute($cau_lenh);
    $ket_qua = mysqli_stmt_get_result($cau_lenh);

    //kiem tra co phong khong
    if (mysqli_num_rows($ket_qua) == 0) {
        ghi_log("Lỗi: Phòng ID {$ma_phong} không tồn tại hoặc không còn trống", 'warning');
        mysqli_stmt_close($cau_lenh);
        mysqli_close($ket_noi);
        header('Location: home.php?error=not_found');
        exit();
    }

    $thong_tin_phong = mysqli_fetch_assoc($ket_qua);
    mysqli_stmt_close($cau_lenh);

    //kiem tra ten phong co khop khong
    if ($thong_tin_phong['ten_phong'] !== $ten_phong_url) {
        ghi_log("Cảnh báo: Tên phòng không khớp - URL: {$ten_phong_url}, DB: {$thong_tin_phong['ten_phong']}", 'warning');
    }

    //lay ten phong chinh xac tu database
    $ten_phong = $thong_tin_phong['ten_phong'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ thuê phòng <?php echo lam_sach_html($ten_phong); ?> - DK BOARDING HOUSE</title>
    <meta name="description" content="Đăng ký thuê phòng <?php echo lam_sach_html($ten_phong); ?> tại DK Boarding House - Nhà trọ chất lượng tại Trà Vinh">
    <link rel="icon" type="image/png" href="assets/image/logo1.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/style_contact.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a href="home.php" class="navbar-brand">
                <img src="assets/image/logo1.png" alt="Logo DK Boarding House">
                <span class="navbar-brand1">DK BOARDING HOUSE</span>
            </a>
            <a href="home.php" class="btn btn-outline-light">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </nav>
    <!-- content -->
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="contact-card">
                    <!-- Header -->
                    <div class="card-header-custom">
                        <h2><i class="fas fa-home"></i> Đăng ký thuê phòng</h2>
                        <p class="mb-0">Vui lòng điền thông tin để chúng tôi liên hệ lại với bạn</p>
                    </div>
                    <!-- tt phong -->
                    <div class="room-info-box">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <?php 
                                //xl anh phong
                                $duong_dan_anh = 'assets/image/rooms/';
                                $ten_file_anh = $thong_tin_phong['anh_phong'] ? $thong_tin_phong['anh_phong'] : 'default.jpg';
                                $anh_day_du = $duong_dan_anh . $ten_file_anh;
                                
                                //kiem tra file anh ton tai khong
                                if (!file_exists($anh_day_du)) {
                                    $anh_day_du = $duong_dan_anh . 'default.jpg';
                                }
                                ?>
                                <img src="<?php echo lam_sach_html($anh_day_du); ?>" 
                                    alt="Ảnh phòng <?php echo lam_sach_html($ten_phong); ?>" 
                                    class="img-fluid rounded shadow-sm"
                                    onerror="this.src='assets/image/rooms/default.jpg'">
                            </div>
                            <div class="col-md-8">
                                <h4 class="room-name">
                                    <i class="fas fa-door-open text-primary"></i> 
                                    <?php echo lam_sach_html($ten_phong); ?>
                                </h4>
                                <div class="room-details">
                                    <p>
                                        <i class="fas fa-money-bill-wave text-success"></i> Giá thuê: 
                                        <strong><?php echo dinh_dang_tien($thong_tin_phong['gia_thue']); ?></strong>
                                    </p>
                                    <p>
                                        <i class="fas fa-shield-alt text-warning"></i> Tiền cọc: 
                                        <strong><?php echo dinh_dang_tien($thong_tin_phong['tien_coc']); ?></strong>
                                    </p>
                                    <p>
                                        <i class="fas fa-ruler-combined text-info"></i> Diện tích: 
                                        <strong><?php echo lam_sach_html($thong_tin_phong['dien_tich']); ?> m²</strong>
                                    </p>
                                    <p>
                                        <i class="fas fa-users text-warning"></i> Số người: 
                                        <strong>Tối đa <?php echo lam_sach_html($thong_tin_phong['so_nguoi_o_toi_da']); ?> người</strong>
                                    </p>
                                    
                                    <!-- tien ich -->
                                    <div class="mt-2">
                                        <?php
                                        if ($thong_tin_phong['co_dieu_hoa']) {
                                            echo '<span class="badge bg-primary me-1"><i class="fas fa-snowflake"></i> Điều hòa</span>';
                                        }
                                        if ($thong_tin_phong['co_nong_lanh']) {
                                            echo '<span class="badge bg-info me-1"><i class="fas fa-water"></i>Nước nóng, lạnh</span>';
                                        }
                                        if ($thong_tin_phong['co_tu_lanh']) {
                                            echo '<span class="badge bg-success me-1"><i class="fas fa-box"></i> Tủ lạnh</span>';
                                        }
                                        if ($thong_tin_phong['co_ban_cong']) {
                                            echo '<span class="badge bg-warning me-1"><i class="fas fa-hotel"></i> Ban công</span>';
                                        }
                                        if ($thong_tin_phong['co_giu_xe']) {
                                            echo '<span class="badge bg-secondary me-1"><i class="fas fa-motorcycle"></i> Gửi xe</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- hien thong bao loi tu url -->
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> 
                            <?php
                                $thong_bao_loi = '';
                                switch($_GET['error']) {
                                    case 'empty': 
                                        $thong_bao_loi = 'Vui lòng điền đầy đủ thông tin bắt buộc!'; 
                                        break;
                                    case 'invalid_name':
                                        $thong_bao_loi = 'Tên không hợp lệ! (Phải từ 2-100 ký tự)';
                                        break;
                                    case 'invalid_phone': 
                                        $thong_bao_loi = 'Số điện thoại không hợp lệ! (Phải có 10 số và bắt đầu bằng 0)'; 
                                        break;
                                    case 'invalid_email': 
                                        $thong_bao_loi = 'Email không hợp lệ!'; 
                                        break;
                                    case 'failed': 
                                        $thong_bao_loi = 'Có lỗi xảy ra, vui lòng thử lại sau!'; 
                                        break;
                                    default: 
                                        $thong_bao_loi = 'Có lỗi xảy ra!'; 
                                        break;
                                }
                                echo lam_sach_html($thong_bao_loi);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form lien he -->
                    <form method="POST" action="send_contact.php" class="contact-form" id="formLienHe">
                        <!-- Hidden inputs -->
                        <input type="hidden" name="phong_id" value="<?php echo $ma_phong; ?>">
                        <input type="hidden" name="ten_phong" value="<?php echo lam_sach_html($ten_phong); ?>">

                        <div class="row">
                            <!-- ho ten -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> Họ và tên <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                    name="ten" 
                                    id="hoTen"
                                    class="form-control" 
                                    placeholder="Nhập họ tên đầy đủ" 
                                    minlength="2"
                                    maxlength="100"
                                    required>
                            </div>
                            
                            <!-- sđt -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-phone"></i> Số điện thoại <span class="text-danger">*</span>
                                </label>
                                <input type="tel" 
                                    name="sdt" 
                                    id="soDienThoai"
                                    class="form-control" 
                                    placeholder="Ví dụ: 0349801407" 
                                    pattern="0[0-9]{9}"
                                    title="Số điện thoại phải có 10 số và bắt đầu bằng 0"
                                    minlength="10"
                                    maxlength="10"
                                    required>
                                <small class="text-muted">Số điện thoại 10 số, bắt đầu bằng 0</small>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" 
                                name="email" 
                                id="email"
                                class="form-control" 
                                placeholder="Nhập địa chỉ email (không bắt buộc)"
                                maxlength="100">
                            <small class="text-muted">Email phải đúng dạng ...@gmail.com</small>
                        </div>

                        <!-- note -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-comment-dots"></i> Ghi chú thêm
                            </label>
                            <textarea name="ghi_chu" 
                                    id="ghiChu"
                                    class="form-control" 
                                    rows="4" 
                                    maxlength="500"
                                    placeholder="Thời gian bạn thuê dự kiến, số người ở, yêu cầu khác..."></textarea>
                            <small class="text-muted">Tối đa 500 ký tự</small>
                        </div>
                        <!-- luu y -->
                        <div class="form-note alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Lưu ý:</strong> Sau khi gửi yêu cầu, phòng này sẽ được giữ chỗ tạm thời cho bạn và chúng tôi sẽ liên hệ trong vòng 24h để xác nhận.
                        </div>
                        <!-- nut -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-paper-plane"></i> Gửi yêu cầu thuê phòng
                            </button>
                            <a href="home.php" class="btn btn-cancel ms-2">
                                <i class="fas fa-times"></i> Hủy bỏ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- footer -->
    <div class="footer text-center mt-1 p-2">
        <div>&copy; <?php echo date('Y'); ?> DK Boarding House</div>
    </div>
    <!-- Script xl -->
    <script>
        //tu dong dong canh bao sau 5s
        setTimeout(function() {
            const cac_canh_bao = document.querySelectorAll('.alert');
            cac_canh_bao.forEach(function(canh_bao) {
                const bo_canh_bao = new bootstrap.Alert(canh_bao);
                bo_canh_bao.close();
            });
        }, 5000);
        
        //xac nhan trc khi gui form lien he
        document.getElementById('formLienHe').addEventListener('submit', function(su_kien) {
            const ho_ten = document.getElementById('hoTen').value;
            const so_dien_thoai = document.getElementById('soDienThoai').value;
            const ten_phong = '<?php echo addslashes($ten_phong); ?>';
            
            const xac_nhan = confirm(
                'Xác nhận gửi yêu cầu thuê phòng?\n\n' +
                'Phòng: ' + ten_phong + '\n' +
                'Họ tên: ' + ho_ten + '\n' +
                'SĐT: ' + so_dien_thoai + '\n\n' +
                'Phòng sẽ được giữ chỗ tạm thời và chờ chủ trọ xác nhận.'
            );
            
            if (!xac_nhan) {
                su_kien.preventDefault();
            }
        });
        
        // so dien thoai chi nhap so va gioi han 10 so
        document.getElementById('soDienThoai').addEventListener('input', function(su_kien) {
            //chi cho phep nhap so
            this.value = this.value.replace(/[^0-9]/g, '');
            //nhap toi da 10 so
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    </script>
</body>
</html>
<?php 
//dong ket noi
mysqli_close($ket_noi); 
?>