-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 31, 2025 at 01:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quanly_phongtro`
--

-- --------------------------------------------------------

--
-- Table structure for table `hoa_don`
--

CREATE TABLE `hoa_don` (
  `id` int(11) NOT NULL,
  `id_phong` int(11) NOT NULL,
  `id_nguoi_thue` int(11) NOT NULL,
  `thang` tinyint(4) NOT NULL,
  `nam` smallint(6) NOT NULL,
  `chi_so_dien_cu` int(11) DEFAULT 0,
  `chi_so_dien_moi` int(11) DEFAULT 0,
  `so_dien_tieu_thu` int(11) GENERATED ALWAYS AS (`chi_so_dien_moi` - `chi_so_dien_cu`) STORED,
  `don_gia_dien` decimal(10,2) DEFAULT 0.00,
  `tien_dien` decimal(10,2) GENERATED ALWAYS AS (`so_dien_tieu_thu` * `don_gia_dien`) STORED,
  `chi_so_nuoc_cu` int(11) DEFAULT 0,
  `chi_so_nuoc_moi` int(11) DEFAULT 0,
  `so_nuoc_tieu_thu` int(11) GENERATED ALWAYS AS (`chi_so_nuoc_moi` - `chi_so_nuoc_cu`) STORED,
  `don_gia_nuoc` decimal(10,2) DEFAULT 0.00,
  `tien_nuoc` decimal(10,2) GENERATED ALWAYS AS (`so_nuoc_tieu_thu` * `don_gia_nuoc`) STORED,
  `tien_phong` decimal(10,2) NOT NULL,
  `phi_khac` decimal(10,2) DEFAULT 0.00,
  `mo_ta_phi_khac` text DEFAULT NULL,
  `tong_tien` decimal(10,2) GENERATED ALWAYS AS (`tien_phong` + `tien_dien` + `tien_nuoc` + `phi_khac`) STORED,
  `trang_thai` enum('chua_thanh_toan','da_thanh_toan') DEFAULT 'chua_thanh_toan',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `han_thanh_toan` date DEFAULT NULL,
  `ngay_thanh_toan` date DEFAULT NULL,
  `phuong_thuc_thanh_toan` enum('tien_mat','chuyen_khoan') DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hoa_don`
--

INSERT INTO `hoa_don` (`id`, `id_phong`, `id_nguoi_thue`, `thang`, `nam`, `chi_so_dien_cu`, `chi_so_dien_moi`, `don_gia_dien`, `chi_so_nuoc_cu`, `chi_so_nuoc_moi`, `don_gia_nuoc`, `tien_phong`, `phi_khac`, `mo_ta_phi_khac`, `trang_thai`, `ngay_tao`, `han_thanh_toan`, `ngay_thanh_toan`, `phuong_thuc_thanh_toan`, `ghi_chu`) VALUES
(113, 12, 17, 12, 2025, 100, 175, 3000.00, 10, 23, 7000.00, 1500000.00, 50000.00, 'Rác 50k', 'chua_thanh_toan', '2025-12-07 17:09:48', '2025-12-31', NULL, NULL, NULL),
(114, 14, 23, 12, 2025, 150, 225, 3000.00, 12, 18, 7000.00, 1500000.00, 0.00, 'Null', 'chua_thanh_toan', '2025-12-07 17:09:48', '2025-12-31', NULL, NULL, NULL),
(115, 15, 22, 12, 2025, 200, 255, 3000.00, 15, 21, 7000.00, 1500000.00, 50000.00, 'Rác 50k', 'chua_thanh_toan', '2025-12-07 17:09:48', '2025-12-31', NULL, NULL, NULL),
(116, 16, 21, 12, 2025, 300, 385, 3000.00, 20, 27, 7000.00, 1700000.00, 50000.00, 'Rác 50k', 'chua_thanh_toan', '2025-12-07 17:09:48', '2025-12-31', NULL, NULL, NULL),
(117, 17, 19, 12, 2025, 400, 478, 3000.00, 25, 31, 7000.00, 1700000.00, 50000.00, 'Rác 50k', 'chua_thanh_toan', '2025-12-07 17:09:48', '2025-12-31', NULL, NULL, NULL),
(118, 18, 20, 12, 2025, 500, 582, 3000.00, 30, 37, 7000.00, 1700000.00, 0.00, 'Null', 'chua_thanh_toan', '2025-12-07 17:09:48', '2025-12-31', NULL, NULL, NULL),
(119, 19, 12, 12, 2025, 100, 185, 3000.00, 10, 16, 7000.00, 1700000.00, 50000.00, 'Rác 50k', 'chua_thanh_toan', '2025-12-07 17:09:48', '2025-12-31', NULL, NULL, NULL),
(120, 20, 15, 12, 2025, 200, 288, 3000.00, 15, 22, 7000.00, 1700000.00, 50000.00, 'Rác 50k', 'chua_thanh_toan', '2025-12-07 17:09:48', '2025-12-31', NULL, NULL, NULL),
(121, 22, 13, 12, 2025, 150, 245, 3000.00, 12, 28, 7000.00, 2000000.00, 50000.00, 'Rác 50k', 'chua_thanh_toan', '2025-12-07 17:09:48', '2025-12-31', NULL, NULL, NULL),
(122, 23, 18, 12, 2025, 150, 255, 3000.00, 12, 23, 7000.00, 2000000.00, 50000.00, 'Rác 50k', 'chua_thanh_toan', '2025-12-07 17:09:48', '2025-12-31', NULL, NULL, NULL),
(123, 25, 16, 12, 2025, 150, 259, 3000.00, 12, 33, 7000.00, 2000000.00, 50000.00, 'Rác 50k', 'chua_thanh_toan', '2025-12-07 17:09:48', '2025-12-31', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lich_su_thue`
--

CREATE TABLE `lich_su_thue` (
  `id` int(11) NOT NULL,
  `id_nguoi_thue` int(11) NOT NULL,
  `id_phong` int(11) NOT NULL,
  `ngay_bat_dau` date NOT NULL,
  `ngay_ket_thuc` date DEFAULT NULL,
  `gia_thue` decimal(10,2) NOT NULL,
  `tien_coc` decimal(10,2) DEFAULT 0.00,
  `ly_do_ket_thuc` text DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `ho_ten_khach_thue` varchar(255) DEFAULT NULL,
  `sdt_khach_thue` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lich_su_thue`
--

INSERT INTO `lich_su_thue` (`id`, `id_nguoi_thue`, `id_phong`, `ngay_bat_dau`, `ngay_ket_thuc`, `gia_thue`, `tien_coc`, `ly_do_ket_thuc`, `ghi_chu`, `ho_ten_khach_thue`, `sdt_khach_thue`) VALUES
(48, 17, 12, '2025-12-05', NULL, 1500000.00, 300000.00, NULL, NULL, 'Nguyễn Hoàng Khoa', '0987678976'),
(49, 23, 14, '2025-12-05', NULL, 1500000.00, 300000.00, NULL, NULL, 'Nguyễn Tiến Linh', '0987473829'),
(50, 22, 15, '2025-12-05', NULL, 1500000.00, 300000.00, NULL, NULL, 'Nguyễn Quang Hải', '0039929838'),
(51, 21, 16, '2025-12-05', NULL, 1700000.00, 340000.00, NULL, NULL, 'Đỗ Duy Mạnh', '0209348377'),
(52, 19, 17, '2025-12-05', NULL, 1700000.00, 340000.00, NULL, NULL, 'Phan Mạnh Quỳnh', '0937473822'),
(53, 20, 18, '2025-12-05', NULL, 1700000.00, 340000.00, NULL, NULL, 'Lương Xuân Trường', '0098736437'),
(54, 12, 19, '2025-12-04', NULL, 1700000.00, 340000.00, NULL, NULL, 'Ngô Nguyễn Thúy Vy', '0999999999'),
(55, 15, 20, '2025-12-05', NULL, 1700000.00, 340000.00, NULL, NULL, 'Đặng Thành Hiếu', '0234884734'),
(56, 13, 22, '2025-12-04', NULL, 2000000.00, 400000.00, NULL, NULL, 'Nguyễn Đăng Khôi', '0349801407'),
(57, 18, 23, '2025-12-05', NULL, 2000000.00, 400000.00, NULL, NULL, 'Lâm Vỹ Dạ', '0667733773'),
(58, 16, 25, '2025-12-05', NULL, 2000000.00, 400000.00, NULL, NULL, 'Huỳnh Minh Khánh', '0233322222'),
(103, 2, 12, '2023-07-01', '2023-12-31', 1400000.00, 280000.00, 'Chuyển công tác, chuyển chỗ ở mới', NULL, 'Trần Văn An', '0901111111'),
(104, 3, 14, '2023-08-01', '2024-01-31', 1400000.00, 280000.00, 'Tốt nghiệp, lên thành phố tìm việc làm', NULL, 'Lê Thị Bích', '0902222222'),
(105, 4, 16, '2023-07-15', '2024-06-30', 1600000.00, 320000.00, 'Mua nhà mặt tiền, trúng đặc biệt', NULL, 'Phạm Minh Tuấn', '0903333333'),
(106, 5, 18, '2023-09-01', '2024-08-31', 1600000.00, 320000.00, 'Về quê lập gia đình', NULL, 'Nguyễn Thị Hoa', '0904444444'),
(107, 6, 22, '2023-10-01', '2024-09-30', 1900000.00, 380000.00, 'Kết hôn, dọn về ở với gia đình', NULL, 'Hoàng Văn Nam', '0905555555'),
(108, 7, 12, '2024-01-05', '2024-06-30', 1450000.00, 290000.00, 'Chuyển về quê ở với mẹ', NULL, 'Đặng Thị Thu', '0906666666'),
(109, 8, 12, '2024-07-10', '2025-11-30', 1500000.00, 300000.00, 'Nợ không trả', NULL, 'Vũ Minh Đức', '0907777777'),
(110, 9, 14, '2024-02-10', '2024-12-31', 1450000.00, 290000.00, 'Chuyển sang phòng lớn hơn', NULL, 'Bùi Văn Hải', '0908888888'),
(111, 10, 15, '2024-03-01', '2025-02-28', 1450000.00, 290000.00, 'Chỗ làm xa chuyển đến gần chỗ làm việc', NULL, 'Trần Văn Long', '0909999999'),
(112, 11, 16, '2024-07-05', '2025-11-30', 1650000.00, 330000.00, 'Hết hợp đồng', NULL, 'Lê Thị Mai', '0911111112'),
(113, 50, 17, '2024-01-15', '2024-07-31', 1650000.00, 330000.00, 'Chuyển công tác lên Sài Gòn', NULL, 'Nguyễn Thị Lan', '0911111113'),
(114, 51, 17, '2024-08-10', '2025-11-30', 1700000.00, 340000.00, 'Đi nghĩa vụ quân sự', NULL, 'Phạm Văn Tùng', '0911111114'),
(115, 52, 19, '2024-05-01', '2025-11-30', 1650000.00, 330000.00, 'Hết hợp đồng', NULL, 'Hoàng Thị Ngọc', '0911111115'),
(116, 53, 20, '2024-06-01', '2025-11-30', 1650000.00, 330000.00, 'Trả trọ đi công tác', NULL, 'Trần Văn Phong', '0911111116'),
(117, 54, 22, '2024-10-05', '2025-11-30', 1950000.00, 390000.00, 'Hết hợp đồng', NULL, 'Đỗ Văn Hùng', '0911111117'),
(118, 54, 23, '2024-04-01', '2024-09-30', 1900000.00, 380000.00, 'Tốt nghiệp', NULL, 'Lê Văn Tâm', '0911111118'),
(119, 55, 23, '2024-10-10', '2025-11-30', 1950000.00, 390000.00, 'Về nhà chồng', NULL, 'Nguyễn Thị Hương', '0911111119'),
(120, 56, 25, '2024-02-01', '2025-11-30', 1900000.00, 380000.00, 'Hết hợp đồng', NULL, 'Phạm Thị Linh', '0911111120'),
(121, 57, 14, '2025-01-05', '2025-11-30', 1500000.00, 300000.00, 'Hết hợp đồng', NULL, 'Bùi Thị Thảo', '0911111121'),
(122, 58, 15, '2025-03-05', '2025-11-30', 1500000.00, 300000.00, 'Trúng vietlott', NULL, 'Hoàng Văn Dũng', '0911111122');

-- --------------------------------------------------------

--
-- Table structure for table `lien_he`
--

CREATE TABLE `lien_he` (
  `id` int(11) NOT NULL,
  `ten` varchar(100) DEFAULT NULL,
  `sdt` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phong` varchar(100) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `ngay_gui` timestamp NOT NULL DEFAULT current_timestamp(),
  `trang_thai` enum('cho_duyet','da_lien_he','da_xu_ly','tu_choi') DEFAULT 'cho_duyet',
  `nguoi_xu_ly` int(11) DEFAULT NULL,
  `ngay_xu_ly` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lien_he`
--

INSERT INTO `lien_he` (`id`, `ten`, `sdt`, `email`, `phong`, `ghi_chu`, `ngay_gui`, `trang_thai`, `nguoi_xu_ly`, `ngay_xu_ly`) VALUES
(12, 'Nguyễn Hoàng Khoa', '0987678976', 'hoangkhoa@gmail.com', 'Phòng 01', '', '2025-12-04 16:53:51', 'da_xu_ly', 1, '2025-12-05 00:22:39'),
(13, 'Nguyễn Đăng Khôi', '0349801407', 'khoinguyen.080305@gmail.com', 'Phòng 11', 'Dự kiến tuần sau tôi sẽ dọn vào ở', '2025-12-04 16:55:48', 'da_xu_ly', 1, '2025-12-04 23:59:07'),
(14, 'Ngô Nguyễn Thúy Vy', '0999999999', 'thuyvy@gmail.com', 'Phòng 08', '', '2025-12-04 16:56:24', 'da_xu_ly', 1, '2025-12-04 23:59:01'),
(15, 'Huỳnh Minh Khánh', '0233322222', 'khanhhuynh@gmail.com', 'Phòng 14', '', '2025-12-04 16:57:04', 'da_xu_ly', 1, '2025-12-05 00:22:36'),
(16, 'Đặng Thành Hiếu', '0234884734', 'thanhhieu@gmail.com', 'Phòng 09', '', '2025-12-04 16:57:33', 'da_xu_ly', 1, '2025-12-05 00:21:41'),
(18, 'Bùi Tiến Dũng', '0927364378', 'tiendung@gmail.com', 'Phòng 13', '', '2025-12-04 17:23:21', 'cho_duyet', NULL, NULL),
(19, 'Đoàn Văn Hậu', '0283847382', 'vanhau@gmail.com', 'Phòng 02', '', '2025-12-04 17:23:47', 'cho_duyet', NULL, NULL),
(20, 'Nguyễn Tiến Linh', '0987473829', 'tienlinh@gmail.com', 'Phòng 03', '', '2025-12-04 17:24:11', 'da_xu_ly', 1, '2025-12-05 00:30:28'),
(21, 'Nguyễn Quang Hải', '0039929838', 'quanghai@gmail.com', 'Phòng 04', '', '2025-12-04 17:24:35', 'da_xu_ly', 1, '2025-12-05 00:30:26'),
(22, 'Đỗ Duy Mạnh', '0209348377', 'duymanh@gmail.com', 'Phòng 05', '', '2025-12-04 17:25:22', 'da_xu_ly', 1, '2025-12-05 00:30:24'),
(23, 'Lương Xuân Trường', '0098736437', 'xuantruong@gmail.com', 'Phòng 07', 'Khi đến ở tôi muốn lắp thêm các vật dụng cá nhân, nhà bếp có được không?', '2025-12-04 17:26:47', 'da_xu_ly', 1, '2025-12-05 00:30:23'),
(24, 'Phan Mạnh Quỳnh', '0937473822', 'manhquynh@gmail.com', 'Phòng 06', '', '2025-12-04 17:27:38', 'da_xu_ly', 1, '2025-12-05 00:30:21'),
(25, 'Lâm Vỹ Dạ', '0667733773', 'vyda@gmail.com', 'Phòng 12', '', '2025-12-04 17:28:14', 'da_xu_ly', 1, '2025-12-05 00:30:19');

-- --------------------------------------------------------

--
-- Table structure for table `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id` int(11) NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `vai_tro` enum('admin','nguoithue') DEFAULT 'nguoithue',
  `sdt` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `trang_thai` enum('hoat_dong','khong_hoat_dong') DEFAULT 'hoat_dong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id`, `ten_dang_nhap`, `mat_khau`, `ho_ten`, `vai_tro`, `sdt`, `email`, `ngay_tao`, `ngay_cap_nhat`, `trang_thai`) VALUES
(1, 'admin', '$2y$10$hvx03iaEsfVaCIhMgI8y9O3K6cvFSHA8Xqf3Ehp/abAALnv85GXpa', 'Chủ nhà trọ', 'admin', NULL, NULL, '2025-12-03 18:40:37', '2025-12-03 18:40:37', 'hoat_dong'),
(2, 'tranvanan2', '$2y$10$demopasswordhash', 'Trần Văn An', 'nguoithue', '0901111111', 'tranvanan@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(3, 'lethibich3', '$2y$10$demopasswordhash', 'Lê Thị Bích', 'nguoithue', '0902222222', 'lethibich@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(4, 'phamminhtuan4', '$2y$10$demopasswordhash', 'Phạm Minh Tuấn', 'nguoithue', '0903333333', 'phamminhtuan@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(5, 'nguyenthihoa5', '$2y$10$demopasswordhash', 'Nguyễn Thị Hoa', 'nguoithue', '0904444444', 'nguyenthihoa@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(6, 'hoangvannam6', '$2y$10$demopasswordhash', 'Hoàng Văn Nam', 'nguoithue', '0905555555', 'hoangvannam@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(7, 'dangthithu7', '$2y$10$demopasswordhash', 'Đặng Thị Thu', 'nguoithue', '0906666666', 'dangthithu@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(8, 'vuminhduc8', '$2y$10$demopasswordhash', 'Vũ Minh Đức', 'nguoithue', '0907777777', 'vuminhduc@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(9, 'buivanhai9', '$2y$10$demopasswordhash', 'Bùi Văn Hải', 'nguoithue', '0908888888', 'buivanhai@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(10, 'tranvanlong10', '$2y$10$demopasswordhash', 'Trần Văn Long', 'nguoithue', '0909999999', 'tranvanlong@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(11, 'lethimai11', '$2y$10$demopasswordhash', 'Lê Thị Mai', 'nguoithue', '0911111112', 'lethimai@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(12, 'ngonguyenthuyvy12', '$2y$10$kEw2KIpsZaaRG.kEdTuUnuSgKFCqdXt9B7oY3QVt7Fs4wAKH3fT6S', 'Ngô Nguyễn Thúy Vy', 'nguoithue', '0999999999', 'thuyvy@gmail.com', '2025-12-04 16:59:01', '2025-12-04 16:59:01', 'hoat_dong'),
(13, 'nguyendangkhoi13', '$2y$10$lFoEyGWWlpJMK33lDVvsfuTSzSOGY4Qg.PokuWWb9CEVXEHHUdxaC', 'Nguyễn Đăng Khôi', 'nguoithue', '0349801407', 'khoinguyen.080305@gmail.com', '2025-12-04 16:59:07', '2025-12-07 16:09:13', 'hoat_dong'),
(15, 'dangthanhhieu15', '$2y$10$YK6NqvLxXAnYoRrn1LCtheDqzSNXh4LOc/hjN2VleGMpNDHQ90VQi', 'Đặng Thành Hiếu', 'nguoithue', '0234884734', 'thanhhieu@gmail.com', '2025-12-04 17:21:41', '2025-12-04 17:21:41', 'hoat_dong'),
(16, 'huynhminhkhanh16', '$2y$10$1XpgzV9UPZDsUzpLD0iLi.h2tIIFkYghmz2bSf9.B0F3yqoa5pw0K', 'Huỳnh Minh Khánh', 'nguoithue', '0233322222', 'khanhhuynh@gmail.com', '2025-12-04 17:22:36', '2025-12-04 17:22:36', 'hoat_dong'),
(17, 'nguyenhoangkhoa17', '$2y$10$nvncbMtjyMSIInomp/uX1uBJejcCUWyv0PeGhL2ovbOP2pwnZY3ie', 'Nguyễn Hoàng Khoa', 'nguoithue', '0987678976', 'hoangkhoa@gmail.com', '2025-12-04 17:22:39', '2025-12-04 17:22:39', 'hoat_dong'),
(18, 'lamvyda18', '$2y$10$n0Pca18M81Xpjf0yjWFTP.wv5nVgv/fCvaOZdKDPk4I6pXaEph5X6', 'Lâm Vỹ Dạ', 'nguoithue', '0667733773', 'vyda@gmail.com', '2025-12-04 17:30:19', '2025-12-04 17:30:19', 'hoat_dong'),
(19, 'phanmanhquynh19', '$2y$10$8ggKZvMvgRAqYp6u1huSOexVwzAz4zqS7OPbnpuxTF0.XGYszJMaC', 'Phan Mạnh Quỳnh', 'nguoithue', '0937473822', 'manhquynh@gmail.com', '2025-12-04 17:30:21', '2025-12-04 17:30:21', 'hoat_dong'),
(20, 'luongxuantruong20', '$2y$10$PvIVlAn6UGa544W904qo2.DTB34iIr30HXv4Rwcjr5WYdABwusV5W', 'Lương Xuân Trường', 'nguoithue', '0098736437', 'xuantruong@gmail.com', '2025-12-04 17:30:23', '2025-12-04 17:30:23', 'hoat_dong'),
(21, 'doduymanh21', '$2y$10$.yh3e5x2Nhek0zRYeyyvq.3JFJxIE.Xg.RFaWUXYT8QJs08BlsGmC', 'Đỗ Duy Mạnh', 'nguoithue', '0209348377', 'duymanh@gmail.com', '2025-12-04 17:30:24', '2025-12-04 17:30:24', 'hoat_dong'),
(22, 'nguyenquanghai22', '$2y$10$va.G6MD9is.VenIvwy6YRO9o/jsDVGluBMYUsYnFPSnC5iLjgd/3i', 'Nguyễn Quang Hải', 'nguoithue', '0039929838', 'quanghai@gmail.com', '2025-12-04 17:30:26', '2025-12-04 17:30:26', 'hoat_dong'),
(23, 'nguyentienlinh23', '$2y$10$I4qjiGabebeC5/aW.B/g3uA.NHNPDkb42o1VcSI0/uv9QXOB8v45a', 'Nguyễn Tiến Linh', 'nguoithue', '0987473829', 'tienlinh@gmail.com', '2025-12-04 17:30:28', '2025-12-04 17:30:28', 'hoat_dong'),
(50, 'nguyenthilan50', '$2y$10$demopasswordhash', 'Nguyễn Thị Lan', 'nguoithue', '0911111113', 'nguyenthilan@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(51, 'phamvantung51', '$2y$10$demopasswordhash', 'Phạm Văn Tùng', 'nguoithue', '0911111114', 'phamvantung@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(52, 'hoangthingoc52', '$2y$10$demopasswordhash', 'Hoàng Thị Ngọc', 'nguoithue', '0911111115', 'hoangthingoc@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(53, 'tranvanphong53', '$2y$10$demopasswordhash', 'Trần Văn Phong', 'nguoithue', '0911111116', 'tranvanphong@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(54, 'dovanhung54', '$2y$10$demopasswordhash', 'Đỗ Văn Hùng', 'nguoithue', '0911111117', 'dovanhung@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(55, 'nguyenthihuong55', '$2y$10$demopasswordhash', 'Nguyễn Thị Hương', 'nguoithue', '0911111119', 'nguyenthihuong@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(56, 'phamthilinh56', '$2y$10$demopasswordhash', 'Phạm Thị Linh', 'nguoithue', '0911111120', 'phamthilinh@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(57, 'buithithao57', '$2y$10$demopasswordhash', 'Bùi Thị Thảo', 'nguoithue', '0911111121', 'buithithao@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong'),
(58, 'hoangvandung58', '$2y$10$demopasswordhash', 'Hoàng Văn Dũng', 'nguoithue', '0911111122', 'hoangvandung@gmail.com', '2025-12-07 17:30:55', '2025-12-07 17:30:55', 'khong_hoat_dong');

-- --------------------------------------------------------

--
-- Table structure for table `phong_tro`
--

CREATE TABLE `phong_tro` (
  `id` int(11) NOT NULL,
  `ten_phong` varchar(50) NOT NULL,
  `gia_thue` decimal(10,2) NOT NULL,
  `tien_coc` decimal(10,2) DEFAULT 0.00,
  `dien_tich` decimal(5,2) DEFAULT NULL,
  `so_nguoi_o_toi_da` tinyint(4) DEFAULT 2,
  `trang_thai` enum('trong','cho_duyet','da_thue','bao_tri') DEFAULT 'trong',
  `id_nguoi_thue` int(11) DEFAULT NULL,
  `ngay_bat_dau` date DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `anh_phong` varchar(250) DEFAULT NULL,
  `co_dieu_hoa` tinyint(1) DEFAULT 0,
  `co_nong_lanh` tinyint(1) DEFAULT 0,
  `co_tu_lanh` tinyint(1) DEFAULT 0,
  `co_giu_xe` tinyint(1) DEFAULT 0,
  `co_ban_cong` tinyint(1) DEFAULT 0,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `phong_tro`
--

INSERT INTO `phong_tro` (`id`, `ten_phong`, `gia_thue`, `tien_coc`, `dien_tich`, `so_nguoi_o_toi_da`, `trang_thai`, `id_nguoi_thue`, `ngay_bat_dau`, `ghi_chu`, `anh_phong`, `co_dieu_hoa`, `co_nong_lanh`, `co_tu_lanh`, `co_giu_xe`, `co_ban_cong`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(12, 'Phòng 01', 1500000.00, 300000.00, 10.00, 2, 'da_thue', 17, '2025-12-05', 'Phòng tốt', '1764866081_bt.jpg', 0, 0, 0, 1, 1, '2025-12-04 16:34:41', '2025-12-04 17:22:39'),
(13, 'Phòng 02', 1500000.00, 300000.00, 10.00, 2, 'cho_duyet', NULL, NULL, 'Phòng tốt', '1764866189_bt1.jpg', 0, 0, 0, 1, 1, '2025-12-04 16:35:50', '2025-12-04 17:23:47'),
(14, 'Phòng 03', 1500000.00, 300000.00, 10.00, 2, 'da_thue', 23, '2025-12-05', 'Phòng tốt', '1764866288_p2.jpg', 0, 0, 0, 1, 1, '2025-12-04 16:37:23', '2025-12-04 17:30:28'),
(15, 'Phòng 04', 1500000.00, 300000.00, 10.00, 2, 'da_thue', 22, '2025-12-05', 'Phòng tốt', '1764866324_p4.jpg', 0, 0, 0, 1, 1, '2025-12-04 16:38:44', '2025-12-04 17:30:26'),
(16, 'Phòng 05', 1700000.00, 340000.00, 12.00, 2, 'da_thue', 21, '2025-12-05', 'Phòng có điều hòa, diện tích rộng hơn', '1764866448_p1.jpg', 1, 0, 0, 1, 1, '2025-12-04 16:40:48', '2025-12-04 17:30:24'),
(17, 'Phòng 06', 1700000.00, 340000.00, 12.00, 2, 'da_thue', 19, '2025-12-05', 'Phòng có điều hòa, diện tích rộng hơn', '1764866474_p3.jpg', 1, 0, 0, 1, 1, '2025-12-04 16:41:14', '2025-12-04 17:30:21'),
(18, 'Phòng 07', 1700000.00, 340000.00, 12.00, 2, 'da_thue', 20, '2025-12-05', 'Phòng có máy nước nóng, lạnh. Diện tích rộng hơn', '1764866564_vip.jpg', 0, 1, 0, 1, 1, '2025-12-04 16:42:44', '2025-12-04 17:30:23'),
(19, 'Phòng 08', 1700000.00, 340000.00, 12.00, 2, 'da_thue', 12, '2025-12-04', 'Phòng có điều hòa, diện tích rộng hơn', '1764866589_vip1.jpg', 1, 0, 0, 1, 1, '2025-12-04 16:43:09', '2025-12-04 16:59:01'),
(20, 'Phòng 09', 1700000.00, 340000.00, 12.00, 2, 'da_thue', 15, '2025-12-05', 'Phòng có điều hòa, diện tích rộng hơn', '1764866609_vip2.jpg', 1, 0, 0, 1, 1, '2025-12-04 16:43:29', '2025-12-04 17:21:41'),
(21, 'Phòng 10', 1700000.00, 340000.00, 12.00, 2, 'trong', NULL, NULL, 'Phòng có điều hòa, diện tích rộng hơn', '1764866663_vip3.jpg', 1, 0, 0, 1, 1, '2025-12-04 16:44:23', '2025-12-04 16:44:23'),
(22, 'Phòng 11', 2000000.00, 400000.00, 15.00, 3, 'da_thue', 13, '2025-12-04', 'Phòng vip, đầy đủ tiện nghi', '1764866725_vvip.jpg', 1, 1, 1, 1, 1, '2025-12-04 16:45:25', '2025-12-04 16:59:07'),
(23, 'Phòng 12', 2000000.00, 400000.00, 15.00, 3, 'da_thue', 18, '2025-12-05', 'Phòng vip, đầy đủ tiện nghi', '1764866755_vvip1.jpg', 1, 1, 1, 1, 1, '2025-12-04 16:45:55', '2025-12-04 17:30:19'),
(24, 'Phòng 13', 2000000.00, 400000.00, 15.00, 3, 'cho_duyet', NULL, NULL, 'Phòng vip, đầy đủ tiện nghi', '1764866788_vvip2.jpg', 1, 1, 1, 1, 1, '2025-12-04 16:46:28', '2025-12-04 17:23:21'),
(25, 'Phòng 14', 2000000.00, 400000.00, 15.00, 3, 'da_thue', 16, '2025-12-05', 'Phòng vip, đầy đủ tiện nghi', '1764866830_vvip3.jpg', 1, 1, 1, 1, 1, '2025-12-04 16:47:10', '2025-12-04 17:22:36'),
(26, 'Phòng 15', 2200000.00, 440000.00, 20.00, 4, 'trong', NULL, NULL, 'Phòng rộng rãi phù hợp cho gia đình', '1764871771_vvv1.jpg', 1, 1, 1, 1, 1, '2025-12-04 18:09:31', '2025-12-04 18:09:31'),
(27, 'Phòng 16', 2200000.00, 440000.00, 20.00, 4, 'trong', NULL, NULL, '', '1764871813_vvv2.jpg', 1, 1, 1, 1, 1, '2025-12-04 18:10:13', '2025-12-04 18:10:13'),
(28, 'Phòng 17', 2200000.00, 440000.00, 20.00, 4, 'trong', NULL, NULL, '', '1764871850_vvv3.jpg', 1, 1, 1, 1, 1, '2025-12-04 18:10:50', '2025-12-04 18:10:50'),
(29, 'Phòng 18', 2200000.00, 440000.00, 20.00, 4, 'trong', NULL, NULL, '', '1764871891_vvv4.jpg', 1, 1, 1, 1, 1, '2025-12-04 18:11:31', '2025-12-04 18:11:31'),
(30, 'Phòng 19', 2200000.00, 440000.00, 20.00, 4, 'trong', NULL, NULL, '', '1764871929_vvv5.jpg', 1, 1, 1, 1, 1, '2025-12-04 18:12:09', '2025-12-04 18:12:09'),
(31, 'Phòng 20', 2200000.00, 440000.00, 20.00, 4, 'trong', NULL, NULL, '', '1764871981_vvv6.jpg', 1, 1, 1, 1, 1, '2025-12-04 18:13:01', '2025-12-04 18:13:01'),
(32, 'Phòng 21', 2200000.00, 440000.00, 20.00, 4, 'trong', NULL, NULL, '', '1764872012_vvv7.jpg', 1, 1, 1, 1, 1, '2025-12-04 18:13:32', '2025-12-04 18:13:32'),
(33, 'Phòng 22', 2200000.00, 440000.00, 20.00, 4, 'trong', NULL, NULL, '', '1764872044_vvv8.jpg', 1, 1, 1, 1, 1, '2025-12-04 18:14:04', '2025-12-04 18:14:04');

-- --------------------------------------------------------

--
-- Table structure for table `thanh_toan`
--

CREATE TABLE `thanh_toan` (
  `id` int(11) NOT NULL,
  `id_hoa_don` int(11) NOT NULL,
  `so_tien` decimal(10,2) NOT NULL,
  `phuong_thuc` enum('tien_mat','chuyen_khoan') NOT NULL,
  `ngay_thanh_toan` date NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_doanh_thu_thang`
-- (See below for the actual view)
--
CREATE TABLE `v_doanh_thu_thang` (
`nam` smallint(6)
,`thang` tinyint(4)
,`so_hoa_don` bigint(21)
,`tong_doanh_thu` decimal(32,2)
,`da_thu` decimal(32,2)
,`chua_thu` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_hoa_don_chua_thanh_toan`
-- (See below for the actual view)
--
CREATE TABLE `v_hoa_don_chua_thanh_toan` (
`id` int(11)
,`id_phong` int(11)
,`id_nguoi_thue` int(11)
,`thang` tinyint(4)
,`nam` smallint(6)
,`chi_so_dien_cu` int(11)
,`chi_so_dien_moi` int(11)
,`so_dien_tieu_thu` int(11)
,`don_gia_dien` decimal(10,2)
,`tien_dien` decimal(10,2)
,`chi_so_nuoc_cu` int(11)
,`chi_so_nuoc_moi` int(11)
,`so_nuoc_tieu_thu` int(11)
,`don_gia_nuoc` decimal(10,2)
,`tien_nuoc` decimal(10,2)
,`tien_phong` decimal(10,2)
,`phi_khac` decimal(10,2)
,`mo_ta_phi_khac` text
,`tong_tien` decimal(10,2)
,`trang_thai` enum('chua_thanh_toan','da_thanh_toan')
,`ngay_tao` timestamp
,`han_thanh_toan` date
,`ngay_thanh_toan` date
,`phuong_thuc_thanh_toan` enum('tien_mat','chuyen_khoan')
,`ghi_chu` text
,`ten_phong` varchar(50)
,`ho_ten` varchar(100)
,`sdt` varchar(15)
,`so_ngay_qua_han` int(7)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_thong_ke_phong`
-- (See below for the actual view)
--
CREATE TABLE `v_thong_ke_phong` (
`tong_phong` bigint(21)
,`phong_trong` decimal(22,0)
,`phong_da_thue` decimal(22,0)
,`phong_cho_duyet` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Structure for view `v_doanh_thu_thang`
--
DROP TABLE IF EXISTS `v_doanh_thu_thang`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_doanh_thu_thang`  AS SELECT `hoa_don`.`nam` AS `nam`, `hoa_don`.`thang` AS `thang`, count(0) AS `so_hoa_don`, sum(`hoa_don`.`tong_tien`) AS `tong_doanh_thu`, sum(if(`hoa_don`.`trang_thai` = 'da_thanh_toan',`hoa_don`.`tong_tien`,0)) AS `da_thu`, sum(if(`hoa_don`.`trang_thai` = 'chua_thanh_toan',`hoa_don`.`tong_tien`,0)) AS `chua_thu` FROM `hoa_don` GROUP BY `hoa_don`.`nam`, `hoa_don`.`thang` ORDER BY `hoa_don`.`nam` DESC, `hoa_don`.`thang` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_hoa_don_chua_thanh_toan`
--
DROP TABLE IF EXISTS `v_hoa_don_chua_thanh_toan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_hoa_don_chua_thanh_toan`  AS SELECT `hd`.`id` AS `id`, `hd`.`id_phong` AS `id_phong`, `hd`.`id_nguoi_thue` AS `id_nguoi_thue`, `hd`.`thang` AS `thang`, `hd`.`nam` AS `nam`, `hd`.`chi_so_dien_cu` AS `chi_so_dien_cu`, `hd`.`chi_so_dien_moi` AS `chi_so_dien_moi`, `hd`.`so_dien_tieu_thu` AS `so_dien_tieu_thu`, `hd`.`don_gia_dien` AS `don_gia_dien`, `hd`.`tien_dien` AS `tien_dien`, `hd`.`chi_so_nuoc_cu` AS `chi_so_nuoc_cu`, `hd`.`chi_so_nuoc_moi` AS `chi_so_nuoc_moi`, `hd`.`so_nuoc_tieu_thu` AS `so_nuoc_tieu_thu`, `hd`.`don_gia_nuoc` AS `don_gia_nuoc`, `hd`.`tien_nuoc` AS `tien_nuoc`, `hd`.`tien_phong` AS `tien_phong`, `hd`.`phi_khac` AS `phi_khac`, `hd`.`mo_ta_phi_khac` AS `mo_ta_phi_khac`, `hd`.`tong_tien` AS `tong_tien`, `hd`.`trang_thai` AS `trang_thai`, `hd`.`ngay_tao` AS `ngay_tao`, `hd`.`han_thanh_toan` AS `han_thanh_toan`, `hd`.`ngay_thanh_toan` AS `ngay_thanh_toan`, `hd`.`phuong_thuc_thanh_toan` AS `phuong_thuc_thanh_toan`, `hd`.`ghi_chu` AS `ghi_chu`, `pt`.`ten_phong` AS `ten_phong`, `nd`.`ho_ten` AS `ho_ten`, `nd`.`sdt` AS `sdt`, to_days(curdate()) - to_days(`hd`.`han_thanh_toan`) AS `so_ngay_qua_han` FROM ((`hoa_don` `hd` join `phong_tro` `pt` on(`hd`.`id_phong` = `pt`.`id`)) join `nguoi_dung` `nd` on(`hd`.`id_nguoi_thue` = `nd`.`id`)) WHERE `hd`.`trang_thai` = 'chua_thanh_toan' ;

-- --------------------------------------------------------

--
-- Structure for view `v_thong_ke_phong`
--
DROP TABLE IF EXISTS `v_thong_ke_phong`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_thong_ke_phong`  AS SELECT count(0) AS `tong_phong`, sum(case when `phong_tro`.`trang_thai` = 'trong' then 1 else 0 end) AS `phong_trong`, sum(case when `phong_tro`.`trang_thai` = 'da_thue' then 1 else 0 end) AS `phong_da_thue`, sum(case when `phong_tro`.`trang_thai` = 'cho_duyet' then 1 else 0 end) AS `phong_cho_duyet` FROM `phong_tro` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_thang_nam` (`id_phong`,`thang`,`nam`),
  ADD KEY `fk_hd_nguoi_thue` (`id_nguoi_thue`);

--
-- Indexes for table `lich_su_thue`
--
ALTER TABLE `lich_su_thue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ls_nguoi_thue` (`id_nguoi_thue`),
  ADD KEY `fk_ls_phong` (`id_phong`);

--
-- Indexes for table `lien_he`
--
ALTER TABLE `lien_he`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lh_nguoi_xu_ly` (`nguoi_xu_ly`);

--
-- Indexes for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ten_dang_nhap` (`ten_dang_nhap`),
  ADD UNIQUE KEY `uq_email` (`email`);

--
-- Indexes for table `phong_tro`
--
ALTER TABLE `phong_tro`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ten_phong` (`ten_phong`),
  ADD KEY `fk_phong_nguoi_thue` (`id_nguoi_thue`);

--
-- Indexes for table `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tt_hd` (`id_hoa_don`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hoa_don`
--
ALTER TABLE `hoa_don`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `lich_su_thue`
--
ALTER TABLE `lich_su_thue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `lien_he`
--
ALTER TABLE `lien_he`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `phong_tro`
--
ALTER TABLE `phong_tro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `thanh_toan`
--
ALTER TABLE `thanh_toan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD CONSTRAINT `fk_hd_nguoi_thue` FOREIGN KEY (`id_nguoi_thue`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hd_phong` FOREIGN KEY (`id_phong`) REFERENCES `phong_tro` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lich_su_thue`
--
ALTER TABLE `lich_su_thue`
  ADD CONSTRAINT `fk_ls_nguoi_thue` FOREIGN KEY (`id_nguoi_thue`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ls_phong` FOREIGN KEY (`id_phong`) REFERENCES `phong_tro` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lien_he`
--
ALTER TABLE `lien_he`
  ADD CONSTRAINT `fk_lh_nguoi_xu_ly` FOREIGN KEY (`nguoi_xu_ly`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `phong_tro`
--
ALTER TABLE `phong_tro`
  ADD CONSTRAINT `fk_phong_nguoi_thue` FOREIGN KEY (`id_nguoi_thue`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD CONSTRAINT `fk_tt_hd` FOREIGN KEY (`id_hoa_don`) REFERENCES `hoa_don` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
