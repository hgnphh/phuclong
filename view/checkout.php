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
        // Find all cart entries for this product
        foreach ($_SESSION['cart'] as $key => $quantity) {
            $parts = explode('_', $key);
            if ($parts[0] == $product['MaSP']) {
                // Add 5000 VND for size L
                $price = $product['DonGia'];
                if ($parts[1] === 'L') {
                    $price += 5000;
                }
                
                $subtotal = $price * $quantity;
        $total += $subtotal;
        
        $cart_items[] = [
            'MaSP' => $product['MaSP'],
            'TenSP' => $product['TenSP'],
                    'DonGia' => $price,
            'SoLuong' => $quantity,
                    'ThanhTien' => $subtotal,
                    'Size' => $parts[1]
        ];
    }
}
    }
}

// Xử lý mã giảm giá
$discount = 0;
$coupon_message = '';
if (isset($_POST['apply_coupon'])) {
    $coupon_code = trim($_POST['coupon']);
    $sql_coupon = "SELECT * FROM magiamgia WHERE MaGiamGia = ? AND TrangThai = 1 LIMIT 1";
    $stmt = $conn->prepare($sql_coupon);
    $stmt->bind_param("s", $coupon_code);
    $stmt->execute();
    $result_coupon = $stmt->get_result();
    if ($row = $result_coupon->fetch_assoc()) {
        if ($row['Loai'] == 'percent') {
            $discount = $total * ($row['GiaTri'] / 100);
            $coupon_message = 'Áp dụng thành công mã giảm giá ' . $row['GiaTri'] . '%!';
        } else {
            $discount = $row['GiaTri'];
            $coupon_message = 'Áp dụng thành công mã giảm giá ' . number_format($row['GiaTri'], 0, ',', '.') . 'đ!';
        }
        $_SESSION['applied_coupon'] = $coupon_code;
        $_SESSION['discount'] = $discount;
        $_SESSION['coupon_message'] = $coupon_message;
    } else {
        $coupon_message = 'Mã giảm giá không hợp lệ hoặc đã hết hạn!';
        unset($_SESSION['applied_coupon'], $_SESSION['discount'], $_SESSION['coupon_message']);
    }
} elseif (isset($_SESSION['discount'])) {
    $discount = $_SESSION['discount'];
    $coupon_message = $_SESSION['coupon_message'] ?? '';
}

$total_after_discount = max(0, $total - $discount);

// Xử lý đặt hàng
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['DiaChiGiaoHang'])
    && isset($_POST['PhuongThucThanhToan'])
    && isset($_POST['MaCN'])
) {
    $DiaChiGiaoHang = trim($_POST['DiaChiGiaoHang']);
    $GhiChu = trim($_POST['GhiChu'] ?? '');
    $PhuongThucThanhToan = $_POST['PhuongThucThanhToan'];
    $MaCN = (int)$_POST['MaCN'];
    // Bắt đầu transaction
    $conn->begin_transaction();
    
    try {
        // Tạo đơn hàng mới (thêm MaCN)
        $sql = "INSERT INTO donhang (MaKH, NgayDat, DiaChiGiaoHang, GhiChu, PhuongThucThanhToan, TrangThai, TongTien, MaCN) 
                VALUES (?, NOW(), ?, ?, ?, 'Chờ xác nhận', ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssdi", $user_id, $DiaChiGiaoHang, $GhiChu, $PhuongThucThanhToan, $total_after_discount, $MaCN);
        $stmt->execute();
        
        $donhang_id = $conn->insert_id;
        
        // Thêm chi tiết đơn hàng
        $sql = "INSERT INTO chitietdonhang (MaDH, MaSP, SoLuong, Gia, Size) VALUES (?, ?, ?, ?, ?)";
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
            
            // Thêm chi tiết đơn hàng với giá đã điều chỉnh theo size
            $stmt->bind_param("iiids", $donhang_id, $item['MaSP'], $item['SoLuong'], $item['DonGia'], $item['Size']);
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

        <!-- Form nhập mã giảm giá -->
        <form method="post" class="mb-3" action="">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <label for="coupon" class="col-form-label">Mã giảm giá:</label>
                </div>
                <div class="col-auto">
                    <input type="text" name="coupon" id="coupon" class="form-control" value="<?php echo isset($_POST['coupon']) ? htmlspecialchars($_POST['coupon']) : (isset($_SESSION['applied_coupon']) ? htmlspecialchars($_SESSION['applied_coupon']) : ''); ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" name="apply_coupon" class="btn btn-success">Áp dụng</button>
                </div>
            </div>
            <?php if ($coupon_message): ?>
                <div class="mt-2 alert alert-<?php echo strpos($coupon_message, 'thành công') !== false ? 'success' : 'warning'; ?>">
                    <?php echo $coupon_message; ?>
                </div>
            <?php endif; ?>
        </form>

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
                            <label for="MaCN" class="form-label">Chọn chi nhánh nhận hàng</label>
                            <select name="MaCN" id="MaCN" class="form-select" required>
                                <option value="">-- Chọn chi nhánh --</option>
                                <?php 
                                $branches = $conn->query("SELECT * FROM chinhanh");
                                while ($row = $branches->fetch_assoc()): ?>
                                    <option value="<?php echo $row['MaCN']; ?>"><?php echo htmlspecialchars($row['TenCN']); ?> - <?php echo htmlspecialchars($row['DiaChi']); ?></option>
                                <?php endwhile; ?>
                            </select>
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
                                        Size: <?php echo htmlspecialchars($item['Size']); ?>
                                        <?php if ($item['Size'] === 'L'): ?>
                                            (+5,000đ)
                                        <?php endif; ?>
                                        <br>
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
                            <?php if ($discount > 0): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Giảm giá:</span>
                                    <strong class="text-success">- <?php echo number_format($discount); ?>đ</strong>
                                </div>
                            <?php endif; ?>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Tổng cộng:</span>
                                <strong class="text-danger"><?php echo number_format($total_after_discount); ?>đ</strong>
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