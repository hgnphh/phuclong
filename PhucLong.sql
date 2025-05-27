CREATE TABLE `khachhang` (
  `MaKH` int(11) NOT NULL,
  `TenKH` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `SDT` bigint(12) NOT NULL,
  `DiaChi` text NOT NULL,
  `MatKhau` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

CREATE TABLE `nhanvien` (
  `MaNV` int(11) NOT NULL,
  `TenNV` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `SDT` int(12) NOT NULL,
  `DiaChi` text NOT NULL,
  `MatKhau` varchar(50) NOT NULL,
  `Quyen` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

CREATE TABLE `sanpham` (
  `MaSP` int(11) NOT NULL,
  `TenSP` varchar(255) NOT NULL,
  `MaDM` int(11) DEFAULT NULL,
  `MaNCC` int(11) NOT NULL,
  `SoLuong` int(11) DEFAULT 0,
  `MoTa` text DEFAULT NULL,
  `DonGia` decimal(10,0) NOT NULL,
  `AnhNen` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- Tạo bảng donhang
CREATE TABLE donhang (
    MaDH INT PRIMARY KEY AUTO_INCREMENT,
    MaKH INT NOT NULL,
    NgayDat DATETIME NOT NULL,
    DiaChiGiaoHang VARCHAR(255) NOT NULL,
    GhiChu TEXT,
    PhuongThucThanhToan VARCHAR(50) NOT NULL,
    TrangThai VARCHAR(50) NOT NULL,
    TongTien DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (MaKH) REFERENCES khachhang(MaKH)
);

-- Tạo bảng chitietdonhang
CREATE TABLE chitietdonhang (
    MaCTDH INT PRIMARY KEY AUTO_INCREMENT,
    MaDH INT NOT NULL,
    MaSP INT NOT NULL,
    SoLuong INT NOT NULL,
    Gia DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (MaDH) REFERENCES donhang(MaDH),
    FOREIGN KEY (MaSP) REFERENCES sanpham(MaSP)
);

CREATE TABLE `quyen` (
  `id` int(11) NOT NULL,
  `Ten` varchar(100) NOT NULL,
  `MoTa` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;


INSERT INTO `quyen` (`id`, `Ten`, `MoTa`) VALUES
(1, 'Manager', 'chủ cửa hàng'),
(2, 'Project Manager', 'quản trị viên'),
(3, 'Quản lý Kho', ''),
(4, 'Nhân viên Bán Hàng', ''),
(5, 'Nhân viên giao hàng', '');

CREATE TABLE `size` (
  `MaSize` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;


INSERT INTO `size` (`MaSize`) VALUES
('L'),
('M');

ALTER TABLE khachhang
MODIFY MaKH INT(11) NOT NULL AUTO_INCREMENT,
ADD PRIMARY KEY (MaKH);
  ALTER TABLE `nhanvien`
  MODIFY `MaNV` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

  ALTER TABLE `sanpham`
  MODIFY `MaSP` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `hoadon`
  MODIFY `MaHD` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

  ALTER TABLE `nhanvien`
  ADD PRIMARY KEY (`Email`),
  ADD UNIQUE KEY `MaNV` (`MaNV`),
  ADD KEY `Quyen` (`Quyen`);