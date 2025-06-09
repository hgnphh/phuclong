<?php
session_start();
require_once '../model/connect.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../view/login.php');
    exit();
}

// Xử lý xóa nhân viên
if (isset($_POST['delete_employee'])) {
    $employee_id = $_POST['employee_id'];
    
    // Xóa nhân viên
    $sql = "DELETE FROM nhanvien WHERE MaNV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Xóa nhân viên thành công!";
    } else {
        $_SESSION['error'] = "Lỗi khi xóa nhân viên!";
    }
    
    header('Location: employees.php');
    exit();
}

// Lấy danh sách nhân viên
$sql = "SELECT * FROM nhanvien ORDER BY MaNV DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhân viên - Admin Dashboard</title>
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
        .employee-avatar {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
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
                            <a class="nav-link active" href="employees.php">
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
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Quản lý nhân viên</h2>
                    <a href="employee-form.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Thêm nhân viên mới
                    </a>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ảnh</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>Số điện thoại</th>
                                        <th>Địa chỉ</th>
                                        <th>Vai trò</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($employee = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $employee['MaNV']; ?></td>
                                        <td>
                                            <img src="../uploads/employees/<?php echo $employee['AnhDaiDien']; ?>" 
                                                 alt="<?php echo $employee['TenNV']; ?>" 
                                                 class="employee-avatar">
                                        </td>
                                        <td><?php echo htmlspecialchars($employee['TenNV']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['Email']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['SDT']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['DiaChi']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $employee['Quyen'] === 'admin' ? 'danger' : 'info'; ?>">
                                                <?php echo $employee['Quyen']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="employee-form.php?id=<?php echo $employee['MaNV']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhân viên này?');">
                                                <input type="hidden" name="employee_id" value="<?php echo $employee['MaNV']; ?>">
                                                <button type="submit" name="delete_employee" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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