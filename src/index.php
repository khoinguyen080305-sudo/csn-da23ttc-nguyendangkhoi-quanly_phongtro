<?php
  require 'db_connect.php';
  require 'functions.php';

  if (isset($_SESSION['vai_tro'])) {
      if ($_SESSION['vai_tro'] == 'admin') {
          header("Location: admin/dashboard.php");
          exit();
      } elseif ($_SESSION['vai_tro'] == 'nguoithue') {
          header("Location: tenant/tenant_home.php"); 
          exit();
      }
  }
  $thong_bao_loi = '';

  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $ten_dang_nhap = trim($_POST['username']);
    $mat_khau = trim($_POST['password']);
    if (empty($ten_dang_nhap) || empty($mat_khau)) {
        $thong_bao_loi = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!';
    } 
    elseif (strlen($ten_dang_nhap) < 3) {
        $thong_bao_loi = 'Tên đăng nhập phải có ít nhất 3 ký tự!';
    }
    elseif (strlen($mat_khau) < 6) {
        $thong_bao_loi = 'Mật khẩu phải có ít nhất 6 ký tự!';
    }
    else {
    //sd Prepared Statement de bao mat
      $cau_sql = "SELECT id, mat_khau, ho_ten, ten_dang_nhap, vai_tro, trang_thai 
                  FROM nguoi_dung 
                  WHERE ten_dang_nhap = ? AND trang_thai = 'hoat_dong'";
      
      $cau_lenh = mysqli_prepare($ket_noi, $cau_sql);   
      if ($cau_lenh) {
        mysqli_stmt_bind_param($cau_lenh, "s", $ten_dang_nhap);
        mysqli_stmt_execute($cau_lenh);
        $ket_qua = mysqli_stmt_get_result($cau_lenh);  
        if ($ket_qua && mysqli_num_rows($ket_qua) == 1) {
          $thong_tin_nguoi_dung = mysqli_fetch_assoc($ket_qua);
          //kt mk voi password_verify
          if (password_verify($mat_khau, $thong_tin_nguoi_dung['mat_khau'])) {
            //luu thong tin vao SESSION
            $_SESSION['ten_dang_nhap'] = $thong_tin_nguoi_dung['ten_dang_nhap'];
            $_SESSION['ho_ten'] = $thong_tin_nguoi_dung['ho_ten'];
            $_SESSION['vai_tro'] = $thong_tin_nguoi_dung['vai_tro'];
            $_SESSION['user_id'] = $thong_tin_nguoi_dung['id'];
            // Ghi log dn thanh cong
            ghi_log("Người dùng '{$ten_dang_nhap}' đăng nhập thành công với vai trò '{$thong_tin_nguoi_dung['vai_tro']}'", 'info');
            mysqli_stmt_close($cau_lenh);
            mysqli_close($ket_noi);
            //dieu huong nguoi dung theo vai tro
            if ($thong_tin_nguoi_dung['vai_tro'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: tenant/tenant_home.php");
            }
            exit(); 
          } else {
              $thong_bao_loi = 'Tên đăng nhập hoặc mật khẩu không đúng!';
              ghi_log("Đăng nhập thất bại - Sai mật khẩu cho tài khoản: {$ten_dang_nhap}", 'warning');
          }
        } else {
            $thong_bao_loi = 'Tên đăng nhập không tồn tại hoặc tài khoản đã bị khóa!';
            ghi_log("Đăng nhập thất bại - Tài khoản không tồn tại hoặc bị khóa: {$ten_dang_nhap}", 'warning');
        }
        mysqli_stmt_close($cau_lenh);
      } else {
          $thong_bao_loi = 'Lỗi hệ thống! Vui lòng thử lại sau.';
          ghi_log("Lỗi prepare statement: " . mysqli_error($ket_noi), 'error');
      }
    }  
    //dong ket noi
    if (isset($ket_noi)) {
      mysqli_close($ket_noi);
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập - DK BOARDING HOUSE</title>
  <meta name="description" content="Đăng nhập vào hệ thống quản lý phòng trọ DK Boarding House">
  <link rel="icon" type="image/png" href="assets/image/logo1.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="assets/css/style_index.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="login-container">
    <form class="login-form" action="" method="POST" autocomplete="on">
      <!-- Header -->
      <div class="form-heading">
        <img src="assets/image/logo1.png" alt="Logo DK Boarding House" class="logo-img mb-2" style="width: 60px;">
        <h3>ĐĂNG NHẬP HỆ THỐNG</h3>
        <p class="text-muted">DK BOARDING HOUSE</p>
      </div>
      <!-- tb loi -->
      <?php if (!empty($thong_bao_loi)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fa-solid fa-exclamation-triangle me-2"></i>
          <?= lam_sach_html($thong_bao_loi) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
      <?php endif; ?>
      <!-- Input ten login-->
      <div class="input-group mb-4">
        <label class="label" for="username">
          <i class="fa-solid fa-user"></i> Tên đăng nhập
        </label>
        <div class="position-relative w-100">
          <input 
            required 
            placeholder="Nhập tên đăng nhập" 
            name="username" 
            id="username" 
            type="text" 
            class="form-control"
            minlength="3"
            maxlength="50"
            value="<?= isset($_POST['username']) ? lam_sach_html($_POST['username']) : '' ?>" 
            autocomplete="username"
            autofocus>
        </div> 
      </div>
      <!-- Input mk -->
      <div class="input-group mb-4">
        <label class="label" for="password">
          <i class="fa-solid fa-lock"></i> Mật khẩu
        </label>
        <div class="position-relative w-100">
          <input 
            required 
            placeholder="Nhập mật khẩu" 
            name="password" 
            id="password" 
            type="password" 
            class="form-control"
            minlength="6"
            maxlength="255"
            autocomplete="current-password">
          <button 
            type="button" 
            class="btn btn-sm btn-link position-absolute top-50 end-0 translate-middle-y text-decoration-none" 
            onclick="hienThiMatKhau()"
            style="z-index: 10;">
            <i class="fa-solid fa-eye" id="icon-mat-khau"></i>
          </button>
        </div>
      </div>
      <!-- button submit -->
      <div class="button-group d-flex gap-2">
        <a href="home.php" class="btn btn-outline-secondary flex-fill">
          <i class="fa-solid fa-arrow-left"></i> Trang chủ
        </a>
        <button class="btn btn-primary flex-fill" type="submit">
          <i class="fa-solid fa-right-to-bracket"></i> Đăng nhập
        </button>
      </div>
      <!-- tt ho tro -->
      <div class="mt-3 text-center">
        <small class="text-muted">
          <i class="fa-solid fa-info-circle"></i> 
          Chào mừng đến với DK BOARDING HOUSE!
        </small>
      </div>
      <!-- Thông tin liên hệ -->
      <div class="mt-2 text-center">
        <small class="text-muted">
          Cần hỗ trợ? Liên hệ: 
          <a href="tel:0349801407" class="text-decoration-none">
            <i class="fa-solid fa-phone"></i> 0349 801 407
          </a>
        </small>
      </div>
    </form> 
  </div>
  <!-- Script xl-->
  <script>
    //tu dong an cac canh bao sau 5s
    setTimeout(function() {
      const cac_canh_bao = document.querySelectorAll('.alert');
      cac_canh_bao.forEach(function(canh_bao) {
        const bo_canh_bao = new bootstrap.Alert(canh_bao);
        bo_canh_bao.close();
      });
    }, 5000);
    
    //ham hien thi mat khau
    function hienThiMatKhau() {
      const o_nhap_mat_khau = document.getElementById('password');
      const bieu_tuong = document.getElementById('icon-mat-khau');
      
      if (o_nhap_mat_khau.type === 'password') {
        o_nhap_mat_khau.type = 'text';
        bieu_tuong.classList.remove('fa-eye');
        bieu_tuong.classList.add('fa-eye-slash');
      } else {
        o_nhap_mat_khau.type = 'password';
        bieu_tuong.classList.remove('fa-eye-slash');
        bieu_tuong.classList.add('fa-eye');
      }
    }
    //ngan submit form khi nhan Enter trong input 
    document.getElementById('password').addEventListener('keypress', function(su_kien) {
      if (su_kien.key === 'Enter') {
        document.querySelector('form').submit();
      }
    });
  </script>
</body>
</html>