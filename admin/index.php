<?php
session_start();
require_once '../model/connect.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../view/login.php');
    exit();
}

// Lấy thống kê
$stats = [
    'total_orders' => 0,
    'pending_orders' => 0,
    'total_products' => 0,
    'total_employees' => 0
];

// Tổng số đơn hàng
$sql = "SELECT COUNT(*) as total FROM donhang";
$result = $conn->query($sql);
if ($result) {
    $stats['total_orders'] = $result->fetch_assoc()['total'];
}

// Đơn hàng đang chờ xử lý
$sql = "SELECT COUNT(*) as pending FROM donhang WHERE TrangThai = 'Chờ xác nhận'";
$result = $conn->query($sql);
if ($result) {
    $stats['pending_orders'] = $result->fetch_assoc()['pending'];
}

// Tổng số sản phẩm
$sql = "SELECT COUNT(*) as total FROM sanpham";
$result = $conn->query($sql);
if ($result) {
    $stats['total_products'] = $result->fetch_assoc()['total'];
}

// Tổng số nhân viên
$sql = "SELECT COUNT(*) as total FROM nhanvien";
$result = $conn->query($sql);
if ($result) {
    $stats['total_employees'] = $result->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Phúc Long</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 1rem;
        }
        .sidebar .nav-link:hover {
            color: rgba(255,255,255,1);
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h4>Admin Panel</h4>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">
                                <i class="fas fa-box me-2"></i> Quản lý sản phẩm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="employees.php">
                                <i class="fas fa-users me-2"></i> Quản lý nhân viên
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-cart me-2"></i> Quản lý đơn hàng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../view/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="revenue.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Quản lý doanh thu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="branches.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Quản lý chi nhánh
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Dashboard</h2>
                
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card bg-primary text-white">
                            <i class="fas fa-shopping-cart"></i>
                            <h3><?php echo $stats['total_orders']; ?></h3>
                            <p class="mb-0">Tổng đơn hàng</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-warning text-white">
                            <i class="fas fa-clock"></i>
                            <h3><?php echo $stats['pending_orders']; ?></h3>
                            <p class="mb-0">Đơn hàng chờ xử lý</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-success text-white">
                            <i class="fas fa-box"></i>
                            <h3><?php echo $stats['total_products']; ?></h3>
                            <p class="mb-0">Sản phẩm</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-info text-white">
                            <i class="fas fa-users"></i>
                            <h3><?php echo $stats['total_employees']; ?></h3>
                            <p class="mb-0">Nhân viên</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Đơn hàng gần đây</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT d.*, k.TenKH 
                                           FROM donhang d 
                                           JOIN khachhang k ON d.MaKH = k.MaKH 
                                           ORDER BY d.NgayDat DESC LIMIT 5";
                                    $result = $conn->query($sql);
                                    while ($order = $result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo $order['MaDH']; ?></td>
                                        <td><?php echo htmlspecialchars($order['TenKH']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($order['NgayDat'])); ?></td>
                                        <td><?php echo number_format($order['TongTien']); ?>đ</td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $order['TrangThai'] == 'pending' ? 'warning' : 
                                                    ($order['TrangThai'] == 'completed' ? 'success' : 'secondary'); 
                                            ?>">
                                                <?php echo $order['TrangThai']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="order-detail.php?id=<?php echo $order['MaDH']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 