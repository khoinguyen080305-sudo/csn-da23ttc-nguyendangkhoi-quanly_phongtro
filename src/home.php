<?php
  require 'db_connect.php';
  require 'functions.php';

  if (isset($_SESSION['vai_tro'])) {
      if ($_SESSION['vai_tro'] == 'admin') {
          header('Location: admin/dashboard.php'); 
          exit();
      } elseif ($_SESSION['vai_tro'] == 'nguoithue') {
          header('Location: tenant/tenant_home.php');
          exit();
      }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Trang chủ - DK BOARDING HOUSE</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Nhà trọ DK - Hiện đại, sạch sẽ, tiện nghi, an ninh tại Trà Vinh">
  <link rel="icon" type="image/png" href="assets/image/logo1.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="assets/css/style_home.css?v=<?php echo time(); ?>">
</head>
<body>
  <!--header -->
  <nav class="navbar navbar-expand-lg p-3 fixed-top">
    <div class="container-fluid">
      <p class="navbar-brand">
        <img src="assets/image/logo1.png" alt="logo">
        <span class="navbar-brand1">DK BOARDING HOUSE - since 2023</h4></span>
      </p>
      <h3 style="color: #caf0f8;"><strong>Hiện đại - Tiện nghi - An ninh -  Sạch sẽ</strong></h3>
      <!-- xu ly login -->
      <?php if (!isset($_SESSION['ho_ten'])): ?>
        <a href="index.php" class="btn btn-outline-light">
          <i class="fa-solid fa-right-to-bracket"></i> Đăng nhập
        </a>
      <?php else: ?>
        <span class="text-white me-3">
          Xin chào, <?= lam_sach_html($_SESSION['ho_ten']) ?>
        </span>
        <a href="logout.php" class="btn btn-outline-light">
          <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
        </a>
      <?php endif; ?>
    </div>
  </nav>
  <!--thong bao-->
  <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 9999;">
    <?php
      //tb thanh cong
      if (isset($_GET['success']) && $_GET['success'] == 'sent') {
        echo '<div class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
          <div class="d-flex">
            <div class="toast-body">
              <i class="fas fa-check-circle me-2"></i>
              <strong>Bạn đã gửi yêu cầu thành công!</strong> Chúng tôi sẽ liên hệ với bạn sớm nhất.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
          </div>
        </div>';
      }
      //tb loi
      if (isset($_GET['error'])) {
        $thong_bao_loi = '';
        switch($_GET['error']){
          case 'empty': 
            $thong_bao_loi = 'Vui lòng nhập đầy đủ thông tin!'; 
            break;
          case 'not_found': 
            $thong_bao_loi = 'Phòng không tồn tại hoặc đã được thuê!'; 
            break;
          case 'unavailable': 
            $thong_bao_loi = 'Phòng đã được đăng ký hoặc thuê!'; 
            break;
          case 'failed': 
            $thong_bao_loi = 'Có lỗi xảy ra, vui lòng thử lại!'; 
            break;
          default: 
            $thong_bao_loi = 'Có lỗi xảy ra!'; 
            break;
        }
        echo '<div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
          <div class="d-flex">
            <div class="toast-body">
              <i class="fas fa-exclamation-circle me-2"></i>
              <strong>Lỗi!</strong> ' . lam_sach_html($thong_bao_loi) . '
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
          </div>
        </div>';
      }
    ?>
  </div>
  <!-- content -->
  <div class="container-fluid mt-2 mb-2 main-content">
    <div class="row g-2">
      <!-- SIDEBAR - CAROUSEL & noi quy -->
      <div class="col-md-3">
        <div class="sidebar-wrapper sidebar-fixed">
          <!-- Carousel image -->
          <div id="homeCarousel" class="carousel slide mb-3" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-indicators">
              <?php 
              $tong_so_anh = 14;
              for ($chi_so = 0; $chi_so < $tong_so_anh; $chi_so++) {
                $lop_active = ($chi_so == 0) ? 'active' : '';
                echo '<button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="' . $chi_so . '" class="' . $lop_active . '"></button>';
              }
              ?>
            </div>
            <div class="carousel-inner">
              <div class="carousel-item active"><img src="assets/image/vvip3.jpg" class="d-block w-100 rounded shadow" alt="Phòng VVIP 3"></div>
              <div class="carousel-item"><img src="assets/image/vvip2.jpg" class="d-block w-100 rounded shadow" alt="Phòng VVIP 2"></div>
              <div class="carousel-item"><img src="assets/image/vvip1.jpg" class="d-block w-100 rounded shadow" alt="Phòng VVIP 1"></div>
              <div class="carousel-item"><img src="assets/image/vvip.jpg" class="d-block w-100 rounded shadow" alt="Phòng VVIP"></div>
              <div class="carousel-item"><img src="assets/image/vip3.jpg" class="d-block w-100 rounded shadow" alt="Phòng VIP 3"></div>
              <div class="carousel-item"><img src="assets/image/vip2.jpg" class="d-block w-100 rounded shadow" alt="Phòng VIP 2"></div>
              <div class="carousel-item"><img src="assets/image/vip1.jpg" class="d-block w-100 rounded shadow" alt="Phòng VIP 1"></div>
              <div class="carousel-item"><img src="assets/image/vip.jpg" class="d-block w-100 rounded shadow" alt="Phòng VIP"></div>
              <div class="carousel-item"><img src="assets/image/p4.jpg" class="d-block w-100 rounded shadow" alt="Phòng 4"></div>
              <div class="carousel-item"><img src="assets/image/p3.jpg" class="d-block w-100 rounded shadow" alt="Phòng 3"></div>
              <div class="carousel-item"><img src="assets/image/p2.jpg" class="d-block w-100 rounded shadow" alt="Phòng 2"></div>
              <div class="carousel-item"><img src="assets/image/p1.jpg" class="d-block w-100 rounded shadow" alt="Phòng 1"></div>
              <div class="carousel-item"><img src="assets/image/bt.jpg" class="d-block w-100 rounded shadow" alt="Ban công"></div>
              <div class="carousel-item"><img src="assets/image/bt1.jpg" class="d-block w-100 rounded shadow" alt="Ban công 1"></div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Ảnh trước</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Ảnh sau</span>
            </button>
          </div>
          <!-- noi quy -->
          <div class="group-rules">
            <h2 class="mb-3" style="text-align: center;">
              <i class="fas fa-list-ul"></i> Nội quy phòng trọ
            </h2>
            <hr>
            <ul>
              <li><i class="fas fa-broom text-warning"></i> Giữ gìn vệ sinh chung</li>
              <li><i class="fas fa-ban text-danger"></i> Không tổ chức tiệc tùng, cờ bạc</li>
              <li><i class="fas fa-moon text-primary"></i> Giữ yên tĩnh từ 22h - 6h</li>
              <li><i class="fas fa-paw text-secondary"></i> Không nuôi thú cưng</li>
              <li><i class="fas fa-bell text-success"></i> Báo chủ trọ khi có vấn đề</li>
              <li><i class="fas fa-id-card text-info"></i> Khai báo tạm trú đầy đủ</li>
              <li><i class="fas fa-regular fa-bell"></i>Báo động khi có hỏa hoạn</li>
            </ul>
          </div>
          <!-- tt lien he -->
          <div class="contact-box mt-4">
            <h4><i class="fas fa-phone-alt"></i> Liên hệ</h4>
            <p><i class="fas fa-map-marker-alt"></i><a href="https://maps.app.goo.gl/sY4SzpW8tdJrRFdU7" class="adress"> Nguyễn Chí Thanh, P6, TP.Trà Vinh</a></p>
            <p><i class="fas fa-phone"></i><a href="tel:0349801407" class="hotline">0349 801 407</a></p>
            <p><i class="fas fa-envelope"></i>dkboardinghouse@gmail.com</p>
          </div>
        </div>
      </div>
      <!-- ds phong trong -->
      <div class="col-md-9">
        <div class="room-wrapper room-scroll-container">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-door-open"></i> Phòng trọ còn trống</h1>
            <div class="room-count">
              <?php
                //dem so phong trong
                $cau_sql_dem = "SELECT COUNT(*) as tong_so FROM phong_tro WHERE trang_thai = 'trong'";
                $ket_qua_dem = mysqli_query($ket_noi, $cau_sql_dem);
                $so_luong_phong = mysqli_fetch_assoc($ket_qua_dem)['tong_so'];
                echo "<span class='badge bg-success fs-5'>" . lam_sach_html($so_luong_phong) . " phòng</span>";
              ?>
            </div>
          </div>
          <div class="row g-2">
            <?php
              // lay ds phong trong
              $cau_sql = "SELECT id, ten_phong, gia_thue, dien_tich, so_nguoi_o_toi_da, 
                          co_dieu_hoa, co_nong_lanh, co_tu_lanh, co_giu_xe, co_ban_cong, anh_phong 
                          FROM phong_tro 
                          WHERE trang_thai = 'trong' 
                          ORDER BY ten_phong ASC";
              $ket_qua = mysqli_query($ket_noi, $cau_sql);
              if ($ket_qua && mysqli_num_rows($ket_qua) > 0) {
                while ($dong_du_lieu = mysqli_fetch_assoc($ket_qua)) {
                  //lay tt phong
                  $ma_phong = $dong_du_lieu['id'];
                  $ten_phong = lam_sach_html($dong_du_lieu['ten_phong']);
                  $gia_thue = number_format($dong_du_lieu['gia_thue'], 0, ',', '.');
                  $dien_tich = lam_sach_html($dong_du_lieu['dien_tich']);
                  $so_nguoi_toi_da = lam_sach_html($dong_du_lieu['so_nguoi_o_toi_da']);
                  //tien ich
                  $danh_sach_tien_ich = '';
                  if ($dong_du_lieu['co_dieu_hoa']) {
                    $danh_sach_tien_ich .= '<span class="badge bg-primary me-1 mb-1"><i class="fas fa-snowflake"></i> Điều hòa</span>';
                  }
                  if ($dong_du_lieu['co_nong_lanh']) {
                    $danh_sach_tien_ich .= '<span class="badge bg-info me-1 mb-1"><i class="fas fa-water"></i>Nước nóng, lạnh</span>';
                  }
                  if ($dong_du_lieu['co_tu_lanh']) {
                    $danh_sach_tien_ich .= '<span class="badge bg-success me-1 mb-1"><i class="fas fa-box"></i> Tủ lạnh</span>';
                  }
                  if ($dong_du_lieu['co_ban_cong']) {
                    $danh_sach_tien_ich .= '<span class="badge bg-warning me-1 mb-1"><i class="fas fa-hotel"></i> Ban công</span>';
                  }
                  if ($dong_du_lieu['co_giu_xe']) {
                    $danh_sach_tien_ich .= '<span class="badge bg-secondary me-1 mb-1"><i class="fas fa-motorcycle"></i> Giữ xe</span>';
                  }
                  //kt anh phong
                  $duong_dan_anh = 'assets/image/rooms/' . ($dong_du_lieu['anh_phong'] ? lam_sach_html($dong_du_lieu['anh_phong']) : 'default.jpg');
                  // hien thi card phong
                  echo "
                  <div class='col-md-4 mb-2'>
                    <div class='card h-100 room-card'>
                      <div class='position-relative'>
                        <img src='{$duong_dan_anh}' class='card-img-top' alt='Ảnh phòng {$ten_phong}' style='height: 230px; object-fit: cover;'>
                        <span class='badge bg-success position-absolute top-0 end-0 m-2'>Còn trống</span>
                      </div>
                      <div class='card-body d-flex flex-column'>
                        <h3 class='card-title'><i class='fas fa-home'></i> {$ten_phong}</h3>
                        <hr>
                        <p class='card-text mb-2'>
                          <i class='fas fa-money-bill-wave text-warning'></i> Giá thuê: 
                          <span class='gia-tien'>{$gia_thue} VNĐ/Tháng<br>Đã bao gồm: internet + dịch vụ</span>
                        </p>
                        <p class='card-text mb-2'>
                          <i class='fas fa-ruler-combined text-info'></i> Diện tích: 
                          <strong>{$dien_tich} m²</strong>
                        </p>
                        <p class='card-text mb-3'>
                          <i class='fas fa-user-friends text-success'></i> Tối đa: 
                          <strong>{$so_nguoi_toi_da} người</strong>
                        </p>
                        <div class='mb-3'>{$danh_sach_tien_ich}</div>
                        <a href='contact.php?phong=" . urlencode($ten_phong) . "&id={$ma_phong}' class='btn btn-primary mt-auto'>
                          <i class='fas fa-paper-plane'></i> Liên hệ thuê
                        </a>
                      </div>
                    </div>
                  </div>";
                }
              } else {
                //ko co phong trong
                echo "<div class='col-12'>
                  <div class='alert alert-info text-center' role='alert'>
                    <i class='fas fa-info-circle fa-2x mb-2'></i>
                    <h4>Hiện tại đã hết phòng trống</h4>
                    <p>Vui lòng liên hệ: <strong>0349 801 407</strong> để được tư vấn</p>
                  </div>
                </div>";
              }
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- footer -->
  <div class="footer text-center mt-2 p-3">
    <div>&copy; <?php echo date('Y'); ?> DK Boarding House</div>
  </div>
  <!-- Script xuly Toast -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      //lay tat ca toast
      var danh_sach_toast = [].slice.call(document.querySelectorAll('.toast'));
      var cac_toast = danh_sach_toast.map(function(phan_tu_toast) {
        return new bootstrap.Toast(phan_tu_toast);
      });
      // hien thi toat
      cac_toast.forEach(function(toast) {
        toast.show();
      });
      //tu xoa parameter khoi url sau 5s
      setTimeout(function() {
        if (window.location.search) {
          const duong_dan_sach = window.location.pathname;
          window.history.replaceState({}, document.title, duong_dan_sach);
        }
      }, 5000);
    });
    // Hiệu ứng navbar khi cuộn
  window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });
  </script>
</body>
</html>