<?php
session_start();
require_once '../model/connect.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../view/login.php');
    exit();
}

$employee = [
    'MaNV' => '',
    'TenNV' => '',
    'Email' => '',
    'MatKhau' => '',
    'SDT' => '',
    'DiaChi' => '',
    'Quyen' => 'staff',
    'AnhDaiDien' => ''
];

// Lấy thông tin nhân viên nếu đang sửa
if (isset($_GET['id'])) {
    $sql = "SELECT * FROM nhanvien WHERE MaNV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $employee = $row;
    }
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee['TenNV'] = $_POST['TenNV'];
    $employee['Email'] = $_POST['Email'];
    $employee['SDT'] = $_POST['SDT'];
    $employee['DiaChi'] = $_POST['DiaChi'];
    $employee['Quyen'] = $_POST['Quyen'];
    
    // Xử lý upload ảnh
    if (isset($_FILES['AnhDaiDien']) && $_FILES['AnhDaiDien']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['AnhDaiDien'];
        $file_name = time() . '_' . $file['name'];
        $target_path = "../uploads/employees/" . $file_name;
        
        // Kiểm tra và tạo thư mục nếu chưa tồn tại
        if (!file_exists("../uploads/employees/")) {
            mkdir("../uploads/employees/", 0777, true);
        }
        
        // Upload file
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Xóa ảnh cũ nếu đang sửa nhân viên
            if (!empty($employee['AnhDaiDien'])) {
                $old_image_path = "../uploads/employees/" . $employee['AnhDaiDien'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            $employee['AnhDaiDien'] = $file_name;
        }
    }
    
    // Thêm hoặc cập nhật nhân viên
    if (empty($employee['MaNV'])) {
        // Thêm mới
        $employee['MatKhau'] = password_hash($_POST['MatKhau'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO nhanvien (TenNV, Email, MatKhau, SDT, DiaChi, Quyen, AnhDaiDien) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", 
            $employee['TenNV'], 
            $employee['Email'], 
            $employee['MatKhau'], 
            $employee['SDT'], 
            $employee['DiaChi'], 
            $employee['Quyen'], 
            $employee['AnhDaiDien']
        );
    } else {
        // Cập nhật
        $sql = "UPDATE nhanvien 
                SET TenNV = ?, Email = ?, SDT = ?, DiaChi = ?, Quyen = ?";
        $params = [$employee['TenNV'], $employee['Email'], $employee['SDT'], 
                  $employee['DiaChi'], $employee['Quyen']];
        $types = "sssss";
        
        // Cập nhật mật khẩu nếu có
        if (!empty($_POST['MatKhau'])) {
            $employee['MatKhau'] = password_hash($_POST['MatKhau'], PASSWORD_DEFAULT);
            $sql .= ", MatKhau = ?";
            $params[] = $employee['MatKhau'];
            $types .= "s";
        }
        
        if (!empty($employee['AnhDaiDien'])) {
            $sql .= ", AnhDaiDien = ?";
            $params[] = $employee['AnhDaiDien'];
            $types .= "s";
        }
        
        $sql .= " WHERE MaNV = ?";
        $params[] = $employee['MaNV'];
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = empty($employee['MaNV']) ? 
            "Thêm nhân viên thành công!" : "Cập nhật nhân viên thành công!";
        header('Location: employees.php');
        exit();
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo empty($employee['MaNV']) ? 'Thêm' : 'Sửa'; ?> nhân viên - Admin Dashboard</title>
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
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 50%;
            margin-top: 10px;
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
                    <h2><?php echo empty($employee['MaNV']) ? 'Thêm' : 'Sửa'; ?> nhân viên</h2>
                    <a href="employees.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>

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
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="TenNV" class="form-label">Họ tên</label>
                                        <input type="text" class="form-control" id="TenNV" name="TenNV" 
                                               value="<?php echo htmlspecialchars($employee['TenNV']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="Email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="Email" name="Email" 
                                               value="<?php echo htmlspecialchars($employee['Email']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="MatKhau" class="form-label">
                                            Mật khẩu <?php echo empty($employee['MaNV']) ? '' : '(để trống nếu không muốn thay đổi)'; ?>
                                        </label>
                                        <input type="password" class="form-control" id="MatKhau" name="MatKhau" 
                                               <?php echo empty($employee['MaNV']) ? 'required' : ''; ?>>
                                    </div>

                                    <div class="mb-3">
                                        <label for="SDT" class="form-label">Số điện thoại</label>
                                        <input type="tel" class="form-control" id="SDT" name="SDT" 
                                               value="<?php echo htmlspecialchars($employee['SDT']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="DiaChi" class="form-label">Địa chỉ</label>
                                        <textarea class="form-control" id="DiaChi" name="DiaChi" rows="3" required><?php 
                                            echo htmlspecialchars($employee['DiaChi']); 
                                        ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="Quyen" class="form-label">Vai trò</label>
                                        <select class="form-select" id="Quyen" name="Quyen" required>
                                            <option value="1" <?php echo $employee['Quyen'] === '1' ? 'selected' : ''; ?>>
                                                Nhân viên
                                            </option>
                                            <option value="2" <?php echo $employee['Quyen'] === '0' ? 'selected' : ''; ?>>
                                                Quản trị viên
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="AnhDaiDien" class="form-label">Ảnh đại diện</label>
                                        <input type="file" class="form-control" id="AnhDaiDien" name="AnhDaiDien" 
                                               accept="image/*" <?php echo empty($employee['MaNV']) ? 'required' : ''; ?>>
                                        <?php if (!empty($employee['AnhDaiDien'])): ?>
                                            <img src="../uploads/employees/<?php echo $employee['AnhDaiDien']; ?>" 
                                                 alt="Preview" class="preview-image">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Lưu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview ảnh trước khi upload
        document.getElementById('AnhDaiDien').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.preview-image');
                    if (preview) {
                        preview.src = e.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'preview-image';
                        document.querySelector('.mb-3').appendChild(img);
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 