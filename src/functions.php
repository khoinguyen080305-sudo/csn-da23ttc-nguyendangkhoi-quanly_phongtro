<?php

    //lay thoi gian o DNA, Viet Nam -> Ho Chi Minh
    date_default_timezone_set('Asia/Ho_Chi_Minh'); 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    //ket noi den db
    if (!isset($ket_noi)) {
        require 'db_connect.php'; 
    }

    //ham bo dau tieng viet
    function bo_dau_tieng_viet($str) {
    $str = trim(mb_strtolower($str));
    $tim = array(
        'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
        'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
        'ì', 'í', 'ị', 'ỉ', 'ĩ',
        'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
        'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
        'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
        'đ',
        ' ', '-', '.'
    );
    $thay = array(
        'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
        'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
        'i', 'i', 'i', 'i', 'i',
        'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
        'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
        'y', 'y', 'y', 'y', 'y',
        'd',
        '', '', ''
    );
    $str = str_replace($tim, $thay, $str);
    $str = preg_replace('/[^a-z0-9]/', '', $str);
    return $str;
    }

    //ham lam sach data dau vao
    function lam_sach($du_lieu) {
        global $ket_noi;
        $du_lieu = trim($du_lieu);
        return mysqli_real_escape_string($ket_noi, $du_lieu);
    }

    //ham lam sach html output
    function lam_sach_html($du_lieu) {
        return htmlspecialchars($du_lieu, ENT_QUOTES, 'UTF-8');
    }

    //ham truy_van_an_toan
    function truy_van_an_toan($cau_sql, $tham_so = []) {
    global $ket_noi;
    $cau_lenh = mysqli_prepare($ket_noi, $cau_sql);
        if (!$cau_lenh) {
            //ghi log loi sql
            ghi_log("Lỗi Prepared Statement: " . mysqli_error($ket_noi) . " - SQL: " . $cau_sql, 'error');
            return false;
        }
        if (!empty($tham_so)) {
            $cac_kieu = '';
            foreach ($tham_so as $gia_tri) {
                if (is_int($gia_tri)) {
                    $cac_kieu .= 'i';
                } elseif (is_double($gia_tri) || is_float($gia_tri)) {
                    $cac_kieu .= 'd';
                } else {
                    $cac_kieu .= 's';
                }
            }
            //su dung phuong phap tuong thich voi moi phien ban PHP
            $tham_so_bind = array_merge([$cau_lenh, $cac_kieu], $tham_so);
            $refs = [];
            foreach ($tham_so_bind as $key => $value) {
                if ($key > 1) {//lay cac tham so tu vi tri 2 tro di
                    $refs[$key] = &$tham_so_bind[$key];
                } else {
                    $refs[$key] = $value;
                }
            }
            call_user_func_array('mysqli_stmt_bind_param', $refs); 
        }
        mysqli_stmt_execute($cau_lenh);
        return $cau_lenh;
    }

    //ham kt login
    function kiem_tra_dang_nhap() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php");
            exit();
        }
    }

    //ham kt quyen cua admin
    function kiem_tra_admin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] !== 'admin') {
            header("Location: index.php");
            exit();
        }
    }

    //ham kt quyen nguoi thue
    function kiem_tra_nguoi_thue() {
        if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] !== 'nguoithue') {
            header("Location: index.php");
            exit();
        }
    }

    //ham dinh dang tien 
    function dinh_dang_tien($so_tien) {
        return number_format($so_tien, 0, ',', '.') . ' đ';
    }
    //ham dinh dang ngay thang
    function dinh_dang_ngay($ngay) {
        if (!$ngay) return '';
        return date('d/m/Y', strtotime($ngay));
    }

    //ham dinh dang ngay/h
    function dinh_dang_ngay_gio($ngay_gio) {
        if (!$ngay_gio) return '';
        return date('d/m/Y H:i', strtotime($ngay_gio));
    }

    //ham upload file
    function tai_len_file($tep_tin, $thu_muc_dich) {
        if (!file_exists($thu_muc_dich)) {
            mkdir($thu_muc_dich, 0755, true);
        }
        ///kt upload
        if (!isset($tep_tin) || $tep_tin['error'] != 0) {
            return false;
        }
        //kt kich thuoc file(maximum 10MB)(1kb = 1024 bytes, 1mb = 1024kb)
        if ($tep_tin['size'] > 10 * 1024 * 1024) {
            return false;
        }
        //phan mo rong cua file
        $phan_mo_rong = strtolower(pathinfo($tep_tin["name"], PATHINFO_EXTENSION));     
        //dinh dang cho phep
        $dinh_dang_cho_phep = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        if (!in_array($phan_mo_rong, $dinh_dang_cho_phep)) {
            return false;
        }
        //tao ten file moi
        $ten_file_moi = time() . '_' . uniqid() . '.' . $phan_mo_rong;
        $duong_dan_day_du = $thu_muc_dich . $ten_file_moi;  
        //di chuyen file
        if (move_uploaded_file($tep_tin["tmp_name"], $duong_dan_day_du)) {
            return $ten_file_moi;
        }
        return false;
    }

    //delete file
    function xoa_file($duong_dan) {
        if (file_exists($duong_dan)) {
            return unlink($duong_dan);
        }
        return false;
    }
    //ham tao mk hash
    function tao_mat_khau_hash($mat_khau) {
        return password_hash($mat_khau, PASSWORD_DEFAULT);
    }

    //ham kt mk
    function kiem_tra_mat_khau($mat_khau, $mat_khau_hash) {
        return password_verify($mat_khau, $mat_khau_hash);
    }

    //ham tao so hoa don tu dong
    // function tao_so_hoa_don() {
    //     global $ket_noi;
    //     do {
    //         $thang = date('m');
    //         $nam = date('Y');
    //         $so_ngau_nhien = rand(1000, 9999);
    //         $ma_hoa_don = "HD{$nam}{$thang}{$so_ngau_nhien}";
    //         //kt trung
    //         $cau_sql = "SELECT id FROM hoa_don WHERE id = ?";
    //         $cau_lenh = truy_van_an_toan($cau_sql, [$ma_hoa_don]);
    //         $ket_qua = mysqli_stmt_get_result($cau_lenh);
            
    //     } while (mysqli_num_rows($ket_qua) > 0);
    //     return $ma_hoa_don;
    // }

    //ham lay tt phong
    function lay_trang_thai_phong($trang_thai) {
        $danh_sach_trang_thai = [
            'trong' => '<span class="badge bg-success">Phòng đang trống</span>',
            'da_thue' => '<span class="badge bg-info">Phòng đã thuê</span>',
            'cho_duyet' => '<span class="badge bg-warning">Phòng chờ duyệt</span>',
            'bao_tri' => '<span class="badge bg-danger">Phòng đang bảo trì</span>'
        ];
        return $danh_sach_trang_thai[$trang_thai] ?? '<span class="badge bg-secondary">Không xác định...</span>';
    }

    //ham lay trang thai hoa don
    function lay_trang_thai_hoa_don($trang_thai) {
        $danh_sach_trang_thai = [
            'chua_thanh_toan' => '<span class="badge bg-warning">Chưa thanh toán</span>',
            'da_thanh_toan' => '<span class="badge bg-success">Đã thanh toán</span>',
            'qua_han' => '<span class="badge bg-danger">Quá hạn</span>'
        ];
        return $danh_sach_trang_thai[$trang_thai] ?? '<span class="badge bg-secondary">Không xác định...</span>';
    }

    //ham kt email hop le
    function kiem_tra_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    //ham kt sdt hop le 10 so bat dau = 0
    function kiem_tra_sdt($so_dien_thoai) {
        return preg_match('/^0[0-9]{9}$/', $so_dien_thoai);
    }

    //ham kt mk manh
    function kiem_tra_mat_khau_manh($mat_khau) {
        return strlen($mat_khau) >= 8 && 
            preg_match('/[A-Z]/', $mat_khau) && 
            preg_match('/[a-z]/', $mat_khau) && 
            preg_match('/[0-9]/', $mat_khau);
    }

    //ham tinh so ngay giua 2 ngay
    function tinh_so_ngay($ngay_bat_dau, $ngay_ket_thuc) {
        $ngay_1 = new DateTime($ngay_bat_dau);
        $ngay_2 = new DateTime($ngay_ket_thuc);
        $khoang_cach = $ngay_1->diff($ngay_2);
        return $khoang_cach->days;
    }

    //ham tinh so thang giua 2 ngay
    function tinh_so_thang($ngay_bat_dau, $ngay_ket_thuc) {
        $ngay_1 = new DateTime($ngay_bat_dau);
        $ngay_2 = new DateTime($ngay_ket_thuc);
        $khoang_cach = $ngay_1->diff($ngay_2);
        return ($khoang_cach->y * 12) + $khoang_cach->m;
    }

    //ham tb thanh cong
    function thong_bao_thanh_cong($noi_dung) {
        return '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i> ' . lam_sach_html($noi_dung) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    }

    //ham tb loi
    function thong_bao_loi($noi_dung) {
        return '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> ' . lam_sach_html($noi_dung) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    }

    //ham canh bao
    function thong_bao_canh_bao($noi_dung) {
        return '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i> ' . lam_sach_html($noi_dung) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    }

    //ham tb thong tin
    function thong_bao_thong_tin($noi_dung) {
        return '<div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill"></i> ' . lam_sach_html($noi_dung) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    }

    //ham phan trang
    function phan_trang($tong_ban_ghi, $so_ban_ghi_moi_trang, $trang_hien_tai, $duong_dan) {
        $tong_so_trang = ceil($tong_ban_ghi / $so_ban_ghi_moi_trang);
        if ($tong_so_trang <= 1) return '';
        $ky_tu_noi = (strpos($duong_dan, '?') !== false) ? '&' : '?';
        $html = '<nav aria-label="Phân trang"><ul class="pagination justify-content-center">';
        if ($trang_hien_tai > 1) {
            $html.= '<li class="page-item">
                        <a class="page-link" href="' . $duong_dan . $ky_tu_noi . 'page=' . ($trang_hien_tai - 1) . '" aria-label="Trang trước">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>';
        } else {
            $html.= '<li class="page-item disabled">
                        <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                    </li>';
        }

        //hien thi toi da 5 trang
        $trang_bat_dau = max(1, $trang_hien_tai - 2);
        $trang_ket_thuc = min($tong_so_trang, $trang_hien_tai + 2);

        //trang dau
        if ($trang_bat_dau > 1) {
            $html .= '<li class="page-item">
                        <a class="page-link" href="' . $duong_dan . $ky_tu_noi . 'page=1">1</a>
                    </li>';
            if ($trang_bat_dau > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        //cac so trang
        for ($i = $trang_bat_dau; $i <= $trang_ket_thuc; $i++) {
            $lop_active = ($i == $trang_hien_tai) ? 'active' : '';
            $html .= '<li class="page-item ' . $lop_active . '">
                        <a class="page-link" href="' . $duong_dan . $ky_tu_noi . 'page=' . $i . '">' . $i . '</a>
                    </li>';
        }

        // trang cuoi
        if ($trang_ket_thuc < $tong_so_trang) {
            if ($trang_ket_thuc < $tong_so_trang - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item">
                        <a class="page-link" href="' . $duong_dan . $ky_tu_noi . 'page=' . $tong_so_trang . '">' . $tong_so_trang . '</a>
                    </li>';
        }

        //Next
        if ($trang_hien_tai < $tong_so_trang) {
            $html .= '<li class="page-item">
                        <a class="page-link" href="' . $duong_dan . $ky_tu_noi . 'page=' . ($trang_hien_tai + 1) . '" aria-label="Trang sau">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>';
        } else {
            $html .= '<li class="page-item disabled">
                        <span class="page-link"><i class="bi bi-chevron-right"></i></span>
                    </li>';
        }
        
        $html .= '</ul></nav>';
        return $html;
    }

    //ham debug
    function debug($du_lieu) {
        echo '<pre>';
        print_r($du_lieu);
        echo '</pre>';
    }
    
    //ham ghi log loi
    function ghi_log($noi_dung, $loai = 'info') {
        $thu_muc_log = 'logs/';
        if (!file_exists($thu_muc_log)) {
            mkdir($thu_muc_log, 0755, true);
        }    
        $ten_file = $thu_muc_log . date('Y-m-d') . '.log';
        $thoi_gian = date('Y-m-d H:i:s');
        $dong_log = "[{$thoi_gian}] [{$loai}] {$noi_dung}\n";
        
        file_put_contents($ten_file, $dong_log, FILE_APPEND);
    }
?>