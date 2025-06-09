<?php
session_start();
require_once '../model/connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Lấy thông tin khách hàng theo email
    $sql = "SELECT * FROM khachhang WHERE Email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // So sánh mật khẩu (mã hóa hoặc plain text tuỳ dữ liệu)
        if (password_verify($password, $user['MatKhau']) || $password === $user['MatKhau']) {
            $_SESSION['user_id'] = $user['MaKH'];
            $_SESSION['TenKH'] = $user['TenKH'];
            $_SESSION['SDT'] = $user['SDT'];
            $_SESSION['DiaChi'] = $user['DiaChi'];
            header('Location: ../index.php');
            exit();
        } else {
            $error = 'Mật khẩu không đúng!';
        }
    } else {
        $error = 'Tài khoản không tồn tại!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 80px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 32px 24px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h3 class="mb-4 text-center">Đăng nhập khách hàng</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
