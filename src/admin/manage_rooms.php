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

    // Xử lý thêm phòng hoặc cập nhật phòng
    if (isset($_POST['save_room'])) {
        $id_phong = !empty($_POST['room_id']) ? intval($_POST['room_id']) : null;
        $ten_phong = trim($_POST['ten']);
        $gia_thue = floatval($_POST['gia']);
        $dien_tich = floatval($_POST['dien_tich']);
        $tien_coc = floatval($_POST['tien_coc']);
        $so_nguoi_o_toi_da = intval($_POST['so_nguoi_o_toi_da']);
        $ghi_chu = trim($_POST['ghi_chu']);
        // Tiện ích (checkbox)
        $co_dieu_hoa = isset($_POST['co_dieu_hoa']) ? 1 : 0;
        $co_nong_lanh = isset($_POST['co_nong_lanh']) ? 1 : 0;
        $co_tu_lanh = isset($_POST['co_tu_lanh']) ? 1 : 0;
        $co_giu_xe = isset($_POST['co_giu_xe']) ? 1 : 0;
        $co_ban_cong = isset($_POST['co_ban_cong']) ? 1 : 0;
        
        // Upload ảnh
        $file_anh = null;
        if (!empty($_FILES['anh_phong']['name'])) {
            $ten_tap_tin = time() . "_" . basename($_FILES['anh_phong']['name']);
            $duong_dan_luu = "../assets/image/rooms/" . $ten_tap_tin;
            if (!is_dir("../assets/image/rooms")) mkdir("../assets/image/rooms", 0777, true);
            if (move_uploaded_file($_FILES['anh_phong']['tmp_name'], $duong_dan_luu)) {
                $file_anh = $ten_tap_tin;
            }
        }

        if (empty($ten_phong) || $gia_thue <= 0 || $dien_tich <= 0) {
            $thong_bao_loi = "Vui lòng nhập đầy đủ thông tin hợp lệ!";
        } else {
            if ($id_phong) {
                // Cập nhật phòng
                if ($file_anh) {
                    $sql = mysqli_prepare($ket_noi, "UPDATE phong_tro SET ten_phong=?, gia_thue=?, tien_coc=?, dien_tich=?, so_nguoi_o_toi_da=?, ghi_chu=?, anh_phong=?, co_dieu_hoa=?, co_nong_lanh=?, co_tu_lanh=?, co_giu_xe=?, co_ban_cong=? WHERE id=?");
                    mysqli_stmt_bind_param($sql, "sdddissiiiiii", $ten_phong, $gia_thue, $tien_coc, $dien_tich, $so_nguoi_o_toi_da, $ghi_chu, $file_anh, $co_dieu_hoa, $co_nong_lanh, $co_tu_lanh, $co_giu_xe, $co_ban_cong, $id_phong);
                } else {
                    $sql = mysqli_prepare($ket_noi, "UPDATE phong_tro SET ten_phong=?, gia_thue=?, tien_coc=?, dien_tich=?, so_nguoi_o_toi_da=?, ghi_chu=?, co_dieu_hoa=?, co_nong_lanh=?, co_tu_lanh=?, co_giu_xe=?, co_ban_cong=? WHERE id=?");
                    mysqli_stmt_bind_param($sql, "sdddisiiiiii", $ten_phong, $gia_thue, $tien_coc, $dien_tich, $so_nguoi_o_toi_da, $ghi_chu, $co_dieu_hoa, $co_nong_lanh, $co_tu_lanh, $co_giu_xe, $co_ban_cong, $id_phong);
                }
                if (mysqli_stmt_execute($sql)) {
                    $thong_bao_thanh_cong = "Cập nhật phòng thành công!";
                    mysqli_stmt_close($sql);
                    header('Location: manage_rooms.php?updated=1');
                    exit;
                } else {
                    $thong_bao_loi = "Lỗi khi cập nhật phòng: " . mysqli_stmt_error($sql);
                    mysqli_stmt_close($sql);
                }
            } else {
                // Kiểm tra trùng tên
                $check = mysqli_prepare($ket_noi, "SELECT id FROM phong_tro WHERE LOWER(TRIM(ten_phong)) = LOWER(TRIM(?))");
                mysqli_stmt_bind_param($check, "s", $ten_phong);
                mysqli_stmt_execute($check);
                $rs = mysqli_stmt_get_result($check);

                if (mysqli_num_rows($rs) > 0) {
                    $thong_bao_loi = "Tên phòng đã tồn tại!";
                } else {
                    $sql = mysqli_prepare($ket_noi, "INSERT INTO phong_tro(ten_phong, gia_thue, tien_coc, dien_tich, so_nguoi_o_toi_da, ghi_chu, anh_phong, co_dieu_hoa, co_nong_lanh, co_tu_lanh, co_giu_xe, co_ban_cong, trang_thai) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'trong')");
                    mysqli_stmt_bind_param($sql, "sdddissiiiii", $ten_phong, $gia_thue, $tien_coc, $dien_tich, $so_nguoi_o_toi_da, $ghi_chu, $file_anh, $co_dieu_hoa, $co_nong_lanh, $co_tu_lanh, $co_giu_xe, $co_ban_cong);
                    if (mysqli_stmt_execute($sql)) {
                        $thong_bao_thanh_cong = "Thêm phòng thành công!";
                        mysqli_stmt_close($sql);
                        header('Location: manage_rooms.php?added=1');
                        exit;
                    } else {
                        $thong_bao_loi = "Lỗi khi thêm phòng: " . mysqli_stmt_error($sql);
                        mysqli_stmt_close($sql);
                    }
                }
                mysqli_stmt_close($check);
            }
        }
    }

    // Xóa phòng
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $cau_lenh_kiem_tra = mysqli_prepare($ket_noi, "SELECT trang_thai FROM phong_tro WHERE id = ?");
        mysqli_stmt_bind_param($cau_lenh_kiem_tra, "i", $id);
        mysqli_stmt_execute($cau_lenh_kiem_tra);
        $ket_qua_kiem_tra = mysqli_stmt_get_result($cau_lenh_kiem_tra);

        if (mysqli_num_rows($ket_qua_kiem_tra) == 0) {
            $thong_bao_loi = "Phòng không tồn tại!";
        } else {
            $dong = mysqli_fetch_assoc($ket_qua_kiem_tra);
            if ($dong['trang_thai'] != 'trong') {
                $thong_bao_loi = "Không thể xóa phòng này vì đang có người thuê hoặc đang chờ duyệt!";
            } else {
                $cau_lenh_xoa = mysqli_prepare($ket_noi, "DELETE FROM phong_tro WHERE id = ?");
                mysqli_stmt_bind_param($cau_lenh_xoa, "i", $id);
                if (mysqli_stmt_execute($cau_lenh_xoa)) {
                    $thong_bao_thanh_cong = 'Xóa phòng thành công!';
                    mysqli_stmt_close($cau_lenh_xoa);
                    header('Location: manage_rooms.php?deleted=1');
                    exit;
                } else {
                    $thong_bao_loi = 'Lỗi khi xóa phòng!';
                    mysqli_stmt_close($cau_lenh_xoa);
                }
            }
        }
        mysqli_stmt_close($cau_lenh_kiem_tra);
    }

    // Hiển thị thông báo từ URL
    if (isset($_GET['added'])) $thong_bao_thanh_cong = "Thêm phòng thành công!";
    if (isset($_GET['updated'])) $thong_bao_thanh_cong = "Cập nhật phòng thành công!";
    if (isset($_GET['deleted'])) $thong_bao_thanh_cong = "Xóa phòng thành công!";

    // Lấy danh sách phòng
    $ket_qua = mysqli_query($ket_noi, "SELECT * FROM phong_tro ORDER BY 
        CASE trang_thai 
            WHEN 'trong' THEN 1 
            WHEN 'cho_duyet' THEN 2 
            WHEN 'da_thue' THEN 3 
        END, id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý phòng - DK BOARDING HOUSE</title>
  <link rel="icon" type="image/png" href="../assets/image/logo1.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style_manage_rooms.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fa-solid fa-door-open me-2"></i>Quản lý phòng trọ</h2>
        <a href="dashboard.php" class="btn btn-outline-primary">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại 
        </a>
    </div>

    <?php if (!empty($thong_bao_thanh_cong)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-check-circle me-2"></i><?= lam_sach_html($thong_bao_thanh_cong) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($thong_bao_loi)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fa-solid fa-exclamation-triangle me-2"></i><?= lam_sach_html($thong_bao_loi) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0" id="form-title">
                <i class="fas fa-plus-circle me-2"></i>Thêm phòng mới
            </h5>
        </div>
        <div class="card-body">
            <form class="row g-3" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="room_id" id="room_id">
                
                <div class="col-md-4">
                    <label class="form-label">Tên phòng <span class="text-danger">*</span></label>
                    <input class="form-control" name="ten" id="ten" placeholder="VD: Phòng 101" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Giá thuê (VNĐ) <span class="text-danger">*</span></label>
                    <input class="form-control" name="gia" id="gia" type="number" min="0" step="1000" placeholder="VD: 2000000" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tiền cọc (VNĐ)</label>
                    <input class="form-control" name="tien_coc" id="tien_coc" type="number" min="0" step="1000" placeholder="VD: 2000000" value="0">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Diện tích (m²) <span class="text-danger">*</span></label>
                    <input class="form-control" name="dien_tich" id="dien_tich" type="number" min="0" step="0.1" placeholder="VD: 25" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Số người ở tối đa</label>
                    <input class="form-control" name="so_nguoi_o_toi_da" id="so_nguoi_o_toi_da" type="number" min="1" value="2">
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Ảnh phòng</label>
                    <input type="file" name="anh_phong" id="anh_phong" class="form-control" accept="image/*">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Chấp nhận: JPG, PNG, GIF (tối đa 10MB)
                    </small>
                    <div class="mt-2">
                        <img id="preview_anh" src="" alt="Preview" style="max-width:200px; max-height:200px; display:none; border-radius:8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    </div>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Tiện nghi phòng</label>
                    <div class="row">
                        <div class="col-md-2 col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="co_dieu_hoa" id="co_dieu_hoa" value="1">
                                <label class="form-check-label" for="co_dieu_hoa">
                                    <i class="fas fa-snowflake text-primary"></i> Điều hòa
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="co_nong_lanh" id="co_nong_lanh" value="1">
                                <label class="form-check-label" for="co_nong_lanh">
                                    <i class="fas fa-water text-info"></i>Nước nóng, lạnh
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="co_tu_lanh" id="co_tu_lanh" value="1">
                                <label class="form-check-label" for="co_tu_lanh">
                                    <i class="fas fa-box text-success"></i> Tủ lạnh
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="co_giu_xe" id="co_giu_xe" value="1">
                                <label class="form-check-label" for="co_giu_xe">
                                    <i class="fas fa-motorcycle text-secondary"></i> Giữ xe
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="co_ban_cong" id="co_ban_cong" value="1">
                                <label class="form-check-label" for="co_ban_cong">
                                    <i class="fas fa-hotel text-warning"></i> Ban công
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Ghi chú phòng</label>
                    <textarea name="ghi_chu" class="form-control" id="mo_ta" rows="3" placeholder="Nhập ghi chú về phòng..."></textarea>
                </div>
                
                <div class="col-md-12">
                    <button class="btn btn-success" name="save_room">
                        <i class="fa-solid fa-save me-2"></i>Lưu phòng
                    </button>
                    <button type="button" class="btn btn-secondary" id="cancel_edit" style="display:none;">
                        <i class="fa-solid fa-xmark me-2"></i>Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách phòng</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered text-center mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Tên phòng</th>
                            <th>Ảnh</th>
                            <th>Giá thuê</th>
                            <th>Diện tích</th>
                            <th>Tiện nghi</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($ket_qua) > 0): 
                            while($dong = mysqli_fetch_assoc($ket_qua)):
                                $lop_trang_thai = '';
                                $nhan_trang_thai = '';
                                switch($dong['trang_thai']) {
                                    case 'trong': $lop_trang_thai='bg-success'; $nhan_trang_thai='Trống'; break;
                                    case 'cho_duyet': $lop_trang_thai='bg-warning'; $nhan_trang_thai='Chờ duyệt'; break;
                                    default: $lop_trang_thai='bg-danger'; $nhan_trang_thai='Đã thuê'; break;
                                }
                                
                                $tien_ich = '';
                                if ($dong['co_dieu_hoa']) $tien_ich .= '<i class="fas fa-snowflake text-primary" title="Điều hòa"></i> ';
                                if ($dong['co_nong_lanh']) $tien_ich .= '<i class="fas fa-water text-info" title="Nước nóng, lạnh"></i> ';
                                if ($dong['co_tu_lanh']) $tien_ich .= '<i class="fas fa-box text-success" title="Tủ lạnh"></i> ';
                                if ($dong['co_giu_xe']) $tien_ich .= '<i class="fas fa-motorcycle text-secondary" title="Giữ xe"></i> ';
                                if ($dong['co_ban_cong']) $tien_ich .= '<i class="fas fa-hotel text-warning" title="Ban công"></i> ';
                        ?>
                        <tr>
                            <td><?= $dong['id'] ?></td>
                            <td>
                                <strong><?= lam_sach_html($dong['ten_phong']) ?></strong>
                                <i class="fas fa-info-circle text-primary ms-1" 
                                title="<?= lam_sach_html($dong['ghi_chu']) ?>" 
                                data-bs-toggle="tooltip"></i>
                            </td>
                            <td>
                                <?php if(!empty($dong['anh_phong'])): ?>
                                    <img src="../assets/image/rooms/<?= $dong['anh_phong'] ?>" style="width:80px; height:60px; object-fit:cover; border-radius:5px;">
                                <?php else: ?>
                                    <span class="text-muted">Chưa có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($dong['gia_thue']) ?> đ</td>
                            <td><?= $dong['dien_tich'] ?> m²</td>
                            <td><?= $tien_ich ?: '<span class="text-muted">-</span>' ?></td>
                            <td><span class="badge <?= $lop_trang_thai ?>"><?= $nhan_trang_thai ?></span></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick='editRoom(<?= json_encode($dong) ?>)'>
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <a href="?delete=<?= $dong['id'] ?>" onclick="return confirm('Xóa phòng <?= lam_sach_html($dong['ten_phong']) ?>?')" class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="8" class="text-muted">Chưa có phòng nào</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('anh_phong').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('preview_anh');
    
    if (file) {
        if (!file.type.startsWith('image/')) {
            alert('Vui lòng chọn file ảnh!');
            this.value = '';
            preview.style.display = 'none';
            return;
        }
        
        if (file.size > 10 * 1024 * 1024) {
            alert('Kích thước ảnh không được vượt quá 10MB!');
            this.value = '';
            preview.style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Khởi tạo tất cả tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

function editRoom(dong) {
    document.getElementById('form-title').innerHTML = '<i class="fas fa-edit me-2"></i>Sửa phòng ID ' + dong.id;
    document.getElementById('room_id').value = dong.id;
    document.getElementById('ten').value = dong.ten_phong;
    document.getElementById('gia').value = dong.gia_thue;
    document.getElementById('tien_coc').value = dong.tien_coc;
    document.getElementById('dien_tich').value = dong.dien_tich;
    document.getElementById('so_nguoi_o_toi_da').value = dong.so_nguoi_o_toi_da;
    document.getElementById('ghi_chu').value = dong.ghi_chu || '';
    
    document.getElementById('co_dieu_hoa').checked = dong.co_dieu_hoa == 1;
    document.getElementById('co_nong_lanh').checked = dong.co_nong_lanh == 1;
    document.getElementById('co_tu_lanh').checked = dong.co_tu_lanh == 1;
    document.getElementById('co_giu_xe').checked = dong.co_giu_xe == 1;
    document.getElementById('co_ban_cong').checked = dong.co_ban_cong == 1;
    
    const preview = document.getElementById('preview_anh');
    if(dong.anh_phong){
        preview.src = "../assets/image/rooms/"+dong.anh_phong;
        preview.style.display = "block";
    } else preview.style.display = "none";
    
    document.getElementById('cancel_edit').style.display = "inline-block";
    window.scrollTo({top:0, behavior:"smooth"});
}

document.getElementById('cancel_edit').addEventListener('click', function(){
    document.getElementById('form-title').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Thêm phòng mới';
    document.querySelector('form').reset();
    document.getElementById('room_id').value = "";
    document.getElementById('preview_anh').style.display = "none";
    this.style.display = "none";
});

setTimeout(function() {
    document.querySelectorAll('.alert').forEach(alert => {
        new bootstrap.Alert(alert).close();
    });
}, 5000);
</script>
</body>
</html>
<?php mysqli_close($ket_noi); ?>