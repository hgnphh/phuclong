<?php
session_start();
require_once '../model/connect.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../view/login.php');
    exit();
}

// Thêm chi nhánh
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['TenCN'])) {
    $sql = "INSERT INTO chinhanh (TenCN, DiaChi) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $_POST['TenCN'], $_POST['DiaChi']);
    $stmt->execute();
    $_SESSION['message'] = "Thêm chi nhánh thành công!";
    header('Location: branches.php');
    exit();
}

// Xóa chi nhánh
if (isset($_GET['delete'])) {
    $sql = "DELETE FROM chinhanh WHERE MaCN = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    $_SESSION['message'] = "Xóa chi nhánh thành công!";
    header('Location: branches.php');
    exit();
}

// Lấy danh sách chi nhánh
$result = $conn->query("SELECT * FROM chinhanh");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý chi nhánh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Quản lý chi nhánh</h2>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="TenCN" class="form-control" placeholder="Tên chi nhánh" required>
        </div>
        <div class="col-md-6">
            <input type="text" name="DiaChi" class="form-control" placeholder="Địa chỉ" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Thêm</button>
        </div>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Mã CN</th>
                <th>Tên chi nhánh</th>
                <th>Địa chỉ</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['MaCN']; ?></td>
                <td><?php echo htmlspecialchars($row['TenCN']); ?></td>
                <td><?php echo htmlspecialchars($row['DiaChi']); ?></td>
                <td>
                    <a href="?delete=<?php echo $row['MaCN']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa chi nhánh này?');">Xóa</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html> 