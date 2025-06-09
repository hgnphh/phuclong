<?php
session_start();
require_once '../model/connect.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../view/login.php');
    exit();
}

// Xử lý filter ngày tháng năm
$filter = '';
$params = [];
$types = '';
if (!empty($_GET['from_date'])) {
    $filter .= ' AND d.NgayDat >= ?';
    $params[] = $_GET['from_date'] . ' 00:00:00';
    $types .= 's';
}
if (!empty($_GET['to_date'])) {
    $filter .= ' AND d.NgayDat <= ?';
    $params[] = $_GET['to_date'] . ' 23:59:59';
    $types .= 's';
}

// Lấy danh sách chi nhánh
$chinhanh = $conn->query("SELECT * FROM chinhanh");

// Thống kê doanh thu theo chi nhánh
$sql = "SELECT cn.MaCN, cn.TenCN, SUM(d.TongTien) AS DoanhThu
        FROM donhang d
        JOIN chinhanh cn ON d.MaCN = cn.MaCN
        WHERE d.TrangThai = 'completed' $filter
        GROUP BY cn.MaCN, cn.TenCN
        ORDER BY DoanhThu DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Tổng doanh thu toàn hệ thống
$filter_total = str_replace('d.NgayDat', 'NgayDat', $filter);
$sql_total = "SELECT SUM(TongTien) AS TongDoanhThu FROM donhang WHERE TrangThai = 'completed' $filter_total";
$stmt_total = $conn->prepare($sql_total);
if (!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total = $stmt_total->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê doanh thu - Admin Dashboard</title>
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
                            <a class="nav-link" href="index.php">
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
                            <a class="nav-link active" href="revenue.php">
                                <i class="fas fa-chart-line me-2"></i> Thống kê doanh thu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../view/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Thống kê doanh thu theo chi nhánh</h2>
                <form class="row g-3 mb-4" method="get">
                    <div class="col-md-3">
                        <label for="from_date" class="form-label">Từ ngày</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="to_date" class="form-label">Đến ngày</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3 align-self-end">
                        <button type="submit" class="btn btn-primary">Lọc</button>
                    </div>
                </form>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Tổng doanh thu toàn hệ thống: <span class="text-success"><?php echo number_format($total['TongDoanhThu'] ?? 0); ?>đ</span></h5>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Chi nhánh</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['TenCN']); ?></td>
                                        <td><?php echo number_format($row['DoanhThu'] ?? 0); ?>đ</td>
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