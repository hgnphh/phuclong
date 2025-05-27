<?php
session_start(); // Bắt đầu session
require __DIR__ . '/../model/connect.php'; // Kết nối CSDL

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['SDT']) && isset($_POST['MatKhau'])) {
    $sodienthoai = $_POST['SDT'];
    $matkhau = $_POST['MatKhau'];

    $sql = "SELECT MaKH, TenKH, MatKhau FROM khachhang WHERE SDT = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $sodienthoai);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($matkhau, $row['MatKhau'])) {
            // Đăng nhập thành công → lưu session
            $_SESSION['user_id'] = $row['MaKH'];
            $_SESSION['TenKH'] = $row['TenKH'];
            $_SESSION['SDT'] = $sodienthoai;

            // Kiểm tra xem có trang cần chuyển hướng sau khi đăng nhập không
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: " . $redirect);
            } else {
                // Nếu không có trang chuyển hướng, về trang chủ
                header("Location: ../index.php");
            }
            exit();
        } else {
            $_SESSION['login_error'] = "❌ Sai mật khẩu!";
        }
    } else {
        $_SESSION['login_error'] = "❌ Người dùng không tồn tại!";
    }

    $stmt->close();
    $conn->close();
} else {
    $_SESSION['login_error'] = "⚠️ Vui lòng nhập thông tin đăng nhập hợp lệ.";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Phúc Long</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo img {
            max-width: 200px;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px;
        }
        .btn-login {
            background-color: #007a33;
            color: white;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn-login:hover {
            background-color: #005f27;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-logo">
                <img src="https://phuclong.com.vn/_next/static/images/logo_2-fdd0b762f4686e31e1101d029a664bc9.png" alt="Phúc Long Logo">
            </div>
            
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['login_error'];
                    unset($_SESSION['login_error']);
                    ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="SDT" class="form-label">Số điện thoại</label>
                    <input type="text" class="form-control" id="SDT" name="SDT" required>
                </div>
                <div class="mb-3">
                    <label for="MatKhau" class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" id="MatKhau" name="MatKhau" required>
                </div>
                <button type="submit" class="btn btn-login">Đăng nhập</button>
            </form>
            
            <div class="register-link">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
