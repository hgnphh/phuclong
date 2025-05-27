<?php
session_start();
require_once '../model/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Vui lòng đăng nhập để xem đơn hàng";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng của người dùng
$sql = "SELECT d.*, 
        (SELECT SUM(ct.SoLuong * ct.Gia) FROM chitietdonhang ct WHERE ct.MaDH = d.MaDH) as TongTien
        FROM donhang d 
        WHERE d.MaKH = ? 
        ORDER BY d.NgayDat DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Lấy chi tiết đơn hàng khi người dùng click vào
$order_details = [];
if (isset($_GET['id'])) {
    $order_id = $_GET['id'];
    $sql = "SELECT ct.*, sp.TenSP, sp.HinhAnh 
            FROM chitietdonhang ct 
            JOIN sanpham sp ON ct.MaSP = sp.MaSP 
            WHERE ct.MaDH = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order_details = $result->fetch_all(MYSQLI_ASSOC);
}

// Hàm để hiển thị trạng thái đơn hàng
function getStatusBadge($status) {
    $badges = [
        'Chờ xác nhận' => 'bg-warning',
        'Đang giao' => 'bg-info',
        'Đã giao' => 'bg-success',
        'Đã hủy' => 'bg-danger'
    ];
    $badge_class = $badges[$status] ?? 'bg-secondary';
    return "<span class='badge $badge_class'>$status</span>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - Phúc Long</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .orders-container {
            max-width: 1200px;
            margin: 40px auto;
        }
        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .order-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }
        .order-body {
            padding: 20px;
        }
        .order-footer {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-top: 1px solid #eee;
        }
        .product-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        .badge {
            padding: 8px 12px;
            font-weight: 500;
        }
        .order-details {
            display: none;
        }
        .order-details.show {
            display: block;
        }
        .btn-view-details {
            color: #007a33;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-view-details:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container orders-container">
        <h2 class="mb-4">Đơn hàng của tôi</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                Bạn chưa có đơn hàng nào.
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Đơn hàng #<?php echo $order['MaDH']; ?></h5>
                            <small class="text-muted">
                                Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['NgayDat'])); ?>
                            </small>
                        </div>
                        <div>
                            <?php echo getStatusBadge($order['TrangThai']); ?>
                        </div>
                    </div>

                    <div class="order-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Địa chỉ giao hàng:</strong><br>
                                <?php echo htmlspecialchars($order['DiaChiGiaoHang']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phương thức thanh toán:</strong><br>
                                <?php echo $order['PhuongThucThanhToan'] == 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 'Chuyển khoản ngân hàng'; ?></p>
                            </div>
                        </div>

                        <?php if (!empty($order['GhiChu'])): ?>
                            <p><strong>Ghi chú:</strong><br>
                            <?php echo htmlspecialchars($order['GhiChu']); ?></p>
                        <?php endif; ?>

                        <div class="order-details <?php echo isset($_GET['id']) && $_GET['id'] == $order['MaDH'] ? 'show' : ''; ?>">
                            <h6 class="mb-3">Chi tiết sản phẩm:</h6>
                            <?php
                            $sql = "SELECT ct.*, sp.TenSP, sp.HinhAnh 
                                    FROM chitietdonhang ct 
                                    JOIN sanpham sp ON ct.MaSP = sp.MaSP 
                                    WHERE ct.MaDH = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $order['MaDH']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $items = $result->fetch_all(MYSQLI_ASSOC);
                            
                            foreach ($items as $item):
                            ?>
                                <div class="product-item">
                                    <img src="<?php echo htmlspecialchars($item['HinhAnh']); ?>" alt="<?php echo htmlspecialchars($item['TenSP']); ?>" class="product-image">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['TenSP']); ?></h6>
                                        <p class="mb-0 text-muted">
                                            <?php echo number_format($item['Gia']); ?>đ x <?php echo $item['SoLuong']; ?>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <strong><?php echo number_format($item['Gia'] * $item['SoLuong']); ?>đ</strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="text-end mt-3">
                            <a href="?id=<?php echo $order['MaDH']; ?>" class="btn-view-details">
                                <?php echo isset($_GET['id']) && $_GET['id'] == $order['MaDH'] ? 'Ẩn chi tiết' : 'Xem chi tiết'; ?>
                            </a>
                        </div>
                    </div>

                    <div class="order-footer d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Tổng tiền:</strong>
                            <span class="text-danger ms-2"><?php echo number_format($order['TongTien']); ?>đ</span>
                        </div>
                        <?php if ($order['TrangThai'] == 'Chờ xác nhận'): ?>
                            <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(<?php echo $order['MaDH']; ?>)">
                                Hủy đơn hàng
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function cancelOrder(orderId) {
            if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
                window.location.href = `cancel_order.php?id=${orderId}`;
            }
        }

        // Xử lý hiển thị/ẩn chi tiết đơn hàng
        document.querySelectorAll('.btn-view-details').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const orderDetails = this.closest('.order-body').querySelector('.order-details');
                orderDetails.classList.toggle('show');
                this.textContent = orderDetails.classList.contains('show') ? 'Ẩn chi tiết' : 'Xem chi tiết';
            });
        });
    </script>
</body>
</html> 