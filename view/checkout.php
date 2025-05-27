<?php
session_start();
require_once '../model/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Vui lòng đăng nhập để tiếp tục thanh toán";
    header("Location: login.php");
    exit();
}

// Kiểm tra giỏ hàng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['message'] = "Giỏ hàng của bạn đang trống";
    header("Location: cart.php");
    exit();
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM khachhang WHERE MaKH = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Lấy thông tin sản phẩm trong giỏ hàng
$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    $sql = "SELECT * FROM sanpham WHERE MaSP IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($product = $result->fetch_assoc()) {
        $quantity = $_SESSION['cart'][$product['MaSP']];
        $subtotal = $product['DonGia'] * $quantity;
        $total += $subtotal;
        
        $cart_items[] = [
            'MaSP' => $product['MaSP'],
            'TenSP' => $product['TenSP'],
            'DonGia' => $product['DonGia'],
            'SoLuong' => $quantity,
            'ThanhTien' => $subtotal
        ];
    }
}

// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $DiaChiGiaoHang = trim($_POST['DiaChiGiaoHang']);
    $GhiChu = trim($_POST['GhiChu'] ?? '');
    $PhuongThucThanhToan = $_POST['PhuongThucThanhToan'];
    
    // Bắt đầu transaction
    $conn->begin_transaction();
    
    try {
        // Tạo đơn hàng mới
        $sql = "INSERT INTO donhang (MaKH, NgayDat, DiaChiGiaoHang, GhiChu, PhuongThucThanhToan, TrangThai, TongTien) 
                VALUES (?, NOW(), ?, ?, ?, 'Chờ xác nhận', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssd", $user_id, $DiaChiGiaoHang, $GhiChu, $PhuongThucThanhToan, $total);
        $stmt->execute();
        
        $donhang_id = $conn->insert_id;
        
        // Thêm chi tiết đơn hàng
        $sql = "INSERT INTO chitietdonhang (MaDH, MaSP, SoLuong, Gia) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        foreach ($cart_items as $item) {
            // Kiểm tra số lượng sản phẩm trong kho
            $check_sql = "SELECT SoLuong FROM sanpham WHERE MaSP = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $item['MaSP']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $product = $check_result->fetch_assoc();
            
            if ($product['SoLuong'] < $item['SoLuong']) {
                throw new Exception("Sản phẩm " . $item['TenSP'] . " chỉ còn " . $product['SoLuong'] . " sản phẩm trong kho");
            }
            
            // Thêm chi tiết đơn hàng
            $stmt->bind_param("iiid", $donhang_id, $item['MaSP'], $item['SoLuong'], $item['DonGia']);
            $stmt->execute();
            
            // Cập nhật số lượng sản phẩm
            $update_sql = "UPDATE sanpham SET SoLuong = SoLuong - ? WHERE MaSP = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $item['SoLuong'], $item['MaSP']);
            $update_stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Xóa giỏ hàng
        unset($_SESSION['cart']);
        
        $_SESSION['message'] = "Đặt hàng thành công! Cảm ơn bạn đã mua hàng.";
        header("Location: orders.php");
        exit();
        
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $conn->rollback();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Phúc Long</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .checkout-container {
            max-width: 1200px;
            margin: 40px auto;
        }
        .checkout-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .section-title {
            color: #007a33;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007a33;
        }
        .product-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        .btn-checkout {
            background-color: #007a33;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            width: 100%;
        }
        .btn-checkout:hover {
            background-color: #005f27;
            color: white;
        }
        .payment-method {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
        }
        .payment-method:hover {
            border-color: #007a33;
        }
        .payment-method.selected {
            border-color: #007a33;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container checkout-container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="checkoutForm">
            <div class="row">
                <!-- Thông tin giao hàng -->
                <div class="col-md-8">
                    <div class="checkout-section">
                        <h3 class="section-title">Thông tin giao hàng</h3>
                        <div class="mb-3">
                            <label for="TenKH" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="TenKH" value="<?php echo htmlspecialchars($user['TenKH']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="SDT" class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="SDT" value="<?php echo htmlspecialchars($user['SDT']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="DiaChiGiaoHang" class="form-label">Địa chỉ giao hàng</label>
                            <input type="text" class="form-control" id="DiaChiGiaoHang" name="DiaChiGiaoHang" 
                                   value="<?php echo htmlspecialchars($user['DiaChi']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="GhiChu" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="GhiChu" name="GhiChu" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Phương thức thanh toán -->
                    <div class="checkout-section">
                        <h3 class="section-title">Phương thức thanh toán</h3>
                        <div class="payment-method selected" onclick="selectPayment('cod')">
                            <input type="radio" name="PhuongThucThanhToan" value="cod" checked>
                            <label>Thanh toán khi nhận hàng (COD)</label>
                        </div>
                        <div class="payment-method" onclick="selectPayment('banking')">
                            <input type="radio" name="PhuongThucThanhToan" value="banking">
                            <label>Chuyển khoản ngân hàng</label>
                        </div>
                    </div>
                </div>

                <!-- Tổng quan đơn hàng -->
                <div class="col-md-4">
                    <div class="checkout-section">
                        <h3 class="section-title">Đơn hàng của bạn</h3>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="product-item">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['TenSP']); ?></h6>
                                    <p class="mb-0 text-muted">
                                        <?php echo number_format($item['DonGia']); ?>đ x <?php echo $item['SoLuong']; ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <strong><?php echo number_format($item['ThanhTien']); ?>đ</strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính:</span>
                                <strong><?php echo number_format($total); ?>đ</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí vận chuyển:</span>
                                <strong>Miễn phí</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Tổng cộng:</span>
                                <strong class="text-danger"><?php echo number_format($total); ?>đ</strong>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-checkout mt-4">Đặt hàng</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectPayment(method) {
            // Bỏ chọn tất cả
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Chọn phương thức được click
            const selectedMethod = document.querySelector(`.payment-method[onclick="selectPayment('${method}')"]`);
            selectedMethod.classList.add('selected');
            
            // Chọn radio button tương ứng
            document.querySelector(`input[value="${method}"]`).checked = true;
        }
    </script>
</body>
</html> 