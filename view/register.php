<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../model/connect.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $TenKH = trim($_POST['TenKH'] ?? '');
    $Email = trim($_POST['Email'] ?? '');
    $SDT = trim($_POST['SDT'] ?? '');
    $DiaChi = trim($_POST['DiaChi'] ?? '');
    $MatKhau = $_POST['MatKhau'] ?? '';
    $ConfirmMatKhau = $_POST['confirmPassword'] ?? '';

    // Validation
    if (empty($TenKH)) {
        $errors[] = "Vui lòng nhập họ tên";
    }

    if (empty($Email)) {
        $errors[] = "Vui lòng nhập email";
    } elseif (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    } else {
        // Kiểm tra email đã tồn tại
        $check_email = $conn->prepare("SELECT MaKH FROM khachhang WHERE Email = ?");
        $check_email->bind_param("s", $Email);
        $check_email->execute();
        $result = $check_email->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email này đã được sử dụng";
        }
        $check_email->close();
    }

    if (empty($SDT)) {
        $errors[] = "Vui lòng nhập số điện thoại";
    } elseif (!preg_match('/^[0-9]{10}$/', $SDT)) {
        $errors[] = "Số điện thoại không hợp lệ";
    }

    if (empty($DiaChi)) {
        $errors[] = "Vui lòng nhập địa chỉ";
    }

    if (empty($MatKhau)) {
        $errors[] = "Vui lòng nhập mật khẩu";
    } elseif (strlen($MatKhau) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
    }

    if ($MatKhau !== $ConfirmMatKhau) {
        $errors[] = "Mật khẩu và xác nhận mật khẩu không khớp";
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($MatKhau, PASSWORD_DEFAULT);
        $sql = "INSERT INTO khachhang (TenKH, Email, SDT, DiaChi, MatKhau) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $errors[] = "Lỗi hệ thống: " . $conn->error;
        } else {
            $stmt->bind_param("sssss", $TenKH, $Email, $SDT, $DiaChi, $hashedPassword);

            if ($stmt->execute()) {
                $success = true;
                $_SESSION['message'] = "Đăng ký thành công! Vui lòng đăng nhập.";
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Lỗi: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Phúc Long</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header h2 {
            color: #007a33;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
            color: #333;
        }
        .btn-register {
            background-color: #007a33;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            width: 100%;
        }
        .btn-register:hover {
            background-color: #005f27;
            color: white;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #007a33;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <h2>Đăng ký tài khoản</h2>
                <p class="text-muted">Vui lòng điền thông tin để đăng ký</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <div class="mb-3">
                    <label for="TenKH" class="form-label">Họ và tên</label>
                    <input type="text" class="form-control" id="TenKH" name="TenKH" 
                           value="<?php echo isset($_POST['TenKH']) ? htmlspecialchars($_POST['TenKH']) : ''; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="Email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="Email" name="Email" 
                           value="<?php echo isset($_POST['Email']) ? htmlspecialchars($_POST['Email']) : ''; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="SDT" class="form-label">Số điện thoại</label>
                    <input type="tel" class="form-control" id="SDT" name="SDT" 
                           value="<?php echo isset($_POST['SDT']) ? htmlspecialchars($_POST['SDT']) : ''; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="DiaChi" class="form-label">Địa chỉ</label>
                    <input type="text" class="form-control" id="DiaChi" name="DiaChi" 
                           value="<?php echo isset($_POST['DiaChi']) ? htmlspecialchars($_POST['DiaChi']) : ''; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="MatKhau" class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" id="MatKhau" name="MatKhau" required>
                </div>

                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Xác nhận mật khẩu</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                </div>

                <button type="submit" class="btn btn-register">Đăng ký</button>
            </form>

            <div class="login-link">
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
