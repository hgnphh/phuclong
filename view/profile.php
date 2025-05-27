<?php
session_start();
require_once __DIR__ . '/../model/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = $_POST['TenKH'];
    $email = $_POST['Email'];
    $sdt = $_POST['SDT'];
    $diachi = $_POST['DiaChi'];
    
    // Cập nhật thông tin
    $update_sql = "UPDATE khachhang SET TenKH = ?, Email = ?, SDT = ?, DiaChi = ? WHERE MaKH = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $ten, $email, $sdt, $diachi, $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['message'] = "Cập nhật thông tin thành công!";
        $_SESSION['TenKH'] = $ten; // Cập nhật session
        header('Location: profile.php');
        exit();
    } else {
        $error = "Có lỗi xảy ra khi cập nhật thông tin!";
    }
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - Phúc Long</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px;
            background-color: #007a33;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
        }
        .form-label {
            font-weight: 500;
            color: #333;
        }
        .btn-save {
            background-color: #007a33;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
        }
        .btn-save:hover {
            background-color: #005f27;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h2>Thông tin cá nhân</h2>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="TenKH" class="form-label">Họ và tên</label>
                        <input type="text" class="form-control" id="TenKH" name="TenKH" 
                               value="<?php echo htmlspecialchars($user['TenKH']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="Email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="Email" name="Email" 
                               value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="SDT" class="form-label">Số điện thoại</label>
                        <input type="tel" class="form-control" id="SDT" name="SDT" 
                               value="<?php echo htmlspecialchars($user['SDT']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="DiaChi" class="form-label">Địa chỉ</label>
                        <input type="text" class="form-control" id="DiaChi" name="DiaChi" 
                               value="<?php echo htmlspecialchars($user['DiaChi']); ?>" required>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-save">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 