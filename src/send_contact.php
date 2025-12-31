<?php
    require 'db_connect.php';
     require 'functions.php';

    $ten_nguoi_lien_he = trim($_POST['ten'] ?? '');
    $so_dien_thoai = trim($_POST['sdt'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $ghi_chu = trim($_POST['ghi_chu'] ?? '');
    $ma_phong = intval($_POST['phong_id'] ?? 0);
    $ten_phong = trim($_POST['ten_phong'] ?? '');

    //kta thong tin bat buoc
    if (empty($ten_nguoi_lien_he) || empty($so_dien_thoai) || $ma_phong <= 0 || empty($ten_phong)) {
        ghi_log("Lỗi: Thiếu thông tin bắt buộc khi liên hệ phòng ID: {$ma_phong}", 'warning');
        header("Location: home.php?error=empty");
        exit();
    }
    //ten nguoi lien he hop le
    if (strlen($ten_nguoi_lien_he) < 2 || strlen($ten_nguoi_lien_he) > 100) {
        ghi_log("Lỗi: Tên không hợp lệ: {$ten_nguoi_lien_he}", 'warning');
        header("Location: home.php?error=invalid_name");
        exit();
    }
    //sđt hop le
    if (!kiem_tra_sdt($so_dien_thoai)) {
        ghi_log("Lỗi: Số điện thoại không hợp lệ: {$so_dien_thoai}", 'warning');
        header("Location: home.php?error=invalid_phone");
        exit();
    }
    //email hop le neu co nhap
    if (!empty($email)) {
        if (!kiem_tra_email($email)) {
            ghi_log("Lỗi: Email không hợp lệ: {$email}", 'warning');
            header("Location: home.php?error=invalid_email");
            exit();
        }
    } else {
        //neu khong nhap email thi gan bang null
        $email = null;
    }
    //lam sach du lieu dau vao
    $ten_nguoi_lien_he = lam_sach($ten_nguoi_lien_he);
    $so_dien_thoai = lam_sach($so_dien_thoai);
    $ten_phong = lam_sach($ten_phong);
    $ghi_chu = lam_sach($ghi_chu);
    if ($email !== null) {
        $email = lam_sach($email);
    }

    $cau_sql_kiem_tra = "SELECT id, ten_phong, trang_thai FROM phong_tro WHERE id = ?";
    $cau_lenh_kiem_tra = mysqli_prepare($ket_noi, $cau_sql_kiem_tra);

    if (!$cau_lenh_kiem_tra) {
        ghi_log("Lỗi prepare statement kiểm tra phòng: " . mysqli_error($ket_noi), 'error');
        mysqli_close($ket_noi);
        header("Location: home.php?error=failed");
        exit();
    }

    mysqli_stmt_bind_param($cau_lenh_kiem_tra, "i", $ma_phong);
    mysqli_stmt_execute($cau_lenh_kiem_tra);
    $ket_qua_kiem_tra = mysqli_stmt_get_result($cau_lenh_kiem_tra);

    //kiem tra phong ton tai
    if (mysqli_num_rows($ket_qua_kiem_tra) == 0) {
        ghi_log("Lỗi: Phòng ID {$ma_phong} không tồn tại trong hệ thống", 'warning');
        mysqli_stmt_close($cau_lenh_kiem_tra);
        mysqli_close($ket_noi);
        header("Location: home.php?error=not_found");
        exit();
    }
    $thong_tin_phong = mysqli_fetch_assoc($ket_qua_kiem_tra);
    //kiem tra phong con trong khong
    if ($thong_tin_phong['trang_thai'] != 'trong') {
        ghi_log("Lỗi: Phòng '{$thong_tin_phong['ten_phong']}' (ID: {$ma_phong}) không còn trống. Trạng thái hiện tại: {$thong_tin_phong['trang_thai']}", 'warning');
        mysqli_stmt_close($cau_lenh_kiem_tra);
        mysqli_close($ket_noi);
        header("Location: home.php?error=unavailable");
        exit();
    }
    mysqli_stmt_close($cau_lenh_kiem_tra);

    // 3. TRANSACTION: CẬP NHẬT PHÒNG + THÊM LIÊN HỆ
    // Logic:
    // - Ai liên hệ trước -> Giữ chỗ trước (phòng chuyển 'trong' -> 'cho_duyet')
    // - Admin từ chối -> Phòng chuyển về 'trong'
    // - Admin duyệt -> Phòng chuyển 'cho_duyet' -> 'da_thue'

    mysqli_begin_transaction($ket_noi);

    try {
        // 3.1 CẬP NHẬT TRẠNG THÁI PHÒNG
        // Chuyển trạng thái từ 'trong' -> 'cho_duyet' (đang chờ duyệt)
        // Dùng WHERE trang_thai = 'trong' để tránh race condition
        
        $cau_sql_cap_nhat = "UPDATE phong_tro 
                            SET trang_thai = 'cho_duyet' 
                            WHERE id = ? AND trang_thai = 'trong'";
        
        $cau_lenh_cap_nhat = mysqli_prepare($ket_noi, $cau_sql_cap_nhat);
        
        if (!$cau_lenh_cap_nhat) {
            throw new Exception("Lỗi prepare statement cập nhật phòng: " . mysqli_error($ket_noi));
        }
        
        mysqli_stmt_bind_param($cau_lenh_cap_nhat, "i", $ma_phong);
        
        if (!mysqli_stmt_execute($cau_lenh_cap_nhat)) {
            throw new Exception("Lỗi execute cập nhật phòng: " . mysqli_stmt_error($cau_lenh_cap_nhat));
        }
        
        //kt phong co duoc cap nhat khong
        $so_dong_anh_huong = mysqli_stmt_affected_rows($cau_lenh_cap_nhat);
        
        if ($so_dong_anh_huong == 0) {
            throw new Exception("Phòng '{$ten_phong}' đã được đặt bởi người khác.");
        }
        
        mysqli_stmt_close($cau_lenh_cap_nhat);

        // 3.2 THÊM YÊU CẦU LIÊN HỆ
        // Lưu thông tin liên hệ vào bảng lien_he
        // Cột 'ngay_gui' tự động = CURRENT_TIMESTAMP (không cần thêm)
        
        $cau_sql_them = "INSERT INTO lien_he (ten, sdt, email, phong, ghi_chu, trang_thai) 
                        VALUES (?, ?, ?, ?, ?, 'cho_duyet')";
        $cau_lenh_them = mysqli_prepare($ket_noi, $cau_sql_them);
        if (!$cau_lenh_them) {
            throw new Exception("Lỗi prepare statement thêm liên hệ: " . mysqli_error($ket_noi));
        }
        
        mysqli_stmt_bind_param($cau_lenh_them, "sssss", 
            $ten_nguoi_lien_he, 
            $so_dien_thoai, 
            $email, 
            $ten_phong, 
            $ghi_chu
        );
        
        if (!mysqli_stmt_execute($cau_lenh_them)) {
            throw new Exception("Lỗi execute thêm liên hệ: " . mysqli_stmt_error($cau_lenh_them));
        }
        
        $ma_lien_he = mysqli_insert_id($ket_noi);
        mysqli_stmt_close($cau_lenh_them);

        mysqli_commit($ket_noi);
        // Ghi log thanh cong
        ghi_log("Liên hệ thành công: {$ten_nguoi_lien_he} (SĐT: {$so_dien_thoai}) đã đăng ký phòng '{$ten_phong}' (ID: {$ma_phong}). Mã liên hệ: {$ma_lien_he}", 'info');
        
        mysqli_close($ket_noi);

        //chuyen huong voi thong bao thanh cong
        header("Location: home.php?success=sent");
        exit();
        
    } catch (Exception $ngoai_le) {
        mysqli_rollback($ket_noi);
        
        // Ghi log loi chi tiet
        ghi_log("Lỗi khi xử lý liên hệ phòng '{$ten_phong}' (ID: {$ma_phong}): " . $ngoai_le->getMessage(), 'error');
        
        mysqli_close($ket_noi);
        
        //chuyen huong voi thong bao loi
        header("Location: home.php?error=failed"); 
        exit();
    }
?>