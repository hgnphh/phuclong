<?php
session_start();
require_once '../model/connect.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../view/login.php');
    exit();
}

$product = [
    'MaSP' => '',
    'TenSP' => '',
    'MaDM' => '',
    'MaNCC' => '',
    'SoLuong' => '',
    'MoTa' => '',
    'DonGia' => '',
    'AnhNen' => ''
];

// Lấy thông tin sản phẩm nếu đang sửa
if (isset($_GET['id'])) {
    $sql = "SELECT * FROM sanpham WHERE MaSP = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $product = $row;
    }
}

// Lấy danh sách danh mục
$sql = "SELECT * FROM danhmuc WHERE TrangThai = 1";
$danhmuc = $conn->query($sql);

// Lấy danh sách nhà cung cấp
$sql = "SELECT * FROM nhacungcap";
$nhacungcap = $conn->query($sql);

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product['TenSP'] = $_POST['TenSP'];
    $product['MaDM'] = $_POST['MaDM'];
    $product['MaNCC'] = $_POST['MaNCC'];
    $product['SoLuong'] = $_POST['SoLuong'];
    $product['MoTa'] = $_POST['MoTa'];
    $product['DonGia'] = $_POST['DonGia'];
    
    // Xử lý upload ảnh
    if (isset($_FILES['AnhNen']) && $_FILES['AnhNen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['AnhNen'];
        $file_name = time() . '_' . $file['name'];
        $target_path = "../uploads/products/" . $file_name;
        
        // Kiểm tra và tạo thư mục nếu chưa tồn tại
        if (!file_exists("../uploads/products/")) {
            mkdir("../uploads/products/", 0777, true);
        }
        
        // Upload file
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Xóa ảnh cũ nếu đang sửa sản phẩm
            if (!empty($product['AnhNen'])) {
                $old_image_path = "../uploads/products/" . $product['AnhNen'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            $product['AnhNen'] = $file_name;
        }
    }
    
    // Thêm hoặc cập nhật sản phẩm
    if (empty($product['MaSP'])) {
        // Thêm mới
        $sql = "INSERT INTO sanpham (TenSP, MaDM, MaNCC, SoLuong, MoTa, DonGia, AnhNen) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiisds", 
            $product['TenSP'], 
            $product['MaDM'], 
            $product['MaNCC'], 
            $product['SoLuong'], 
            $product['MoTa'], 
            $product['DonGia'], 
            $product['AnhNen']
        );
    } else {
        // Cập nhật
        $sql = "UPDATE sanpham 
                SET TenSP = ?, MaDM = ?, MaNCC = ?, SoLuong = ?, MoTa = ?, DonGia = ?";
        $params = [$product['TenSP'], $product['MaDM'], $product['MaNCC'], 
                  $product['SoLuong'], $product['MoTa'], $product['DonGia']];
        $types = "siiisd";
        
        if (!empty($product['AnhNen'])) {
            $sql .= ", AnhNen = ?";
            $params[] = $product['AnhNen'];
            $types .= "s";
        }
        
        $sql .= " WHERE MaSP = ?";
        $params[] = $product['MaSP'];
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = empty($product['MaSP']) ? 
            "Thêm sản phẩm thành công!" : "Cập nhật sản phẩm thành công!";
        header('Location: products.php');
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
    <title><?php echo empty($product['MaSP']) ? 'Thêm' : 'Sửa'; ?> sản phẩm - Admin Dashboard</title>
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
            border-radius: 4px;
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
                            <a class="nav-link active" href="products.php">
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
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><?php echo empty($product['MaSP']) ? 'Thêm' : 'Sửa'; ?> sản phẩm</h2>
                    <a href="products.php" class="btn btn-secondary">
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
                                        <label for="TenSP" class="form-label">Tên sản phẩm</label>
                                        <input type="text" class="form-control" id="TenSP" name="TenSP" 
                                               value="<?php echo htmlspecialchars($product['TenSP']); ?>" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="MaDM" class="form-label">Danh mục</label>
                                                <select class="form-select" id="MaDM" name="MaDM" required>
                                                    <option value="">Chọn danh mục</option>
                                                    <?php while ($dm = $danhmuc->fetch_assoc()): ?>
                                                        <option value="<?php echo $dm['MaDM']; ?>" 
                                                                <?php echo $dm['MaDM'] == $product['MaDM'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($dm['TenDM']); ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="MaNCC" class="form-label">Nhà cung cấp</label>
                                                <select class="form-select" id="MaNCC" name="MaNCC" required>
                                                    <option value="">Chọn nhà cung cấp</option>
                                                    <?php while ($ncc = $nhacungcap->fetch_assoc()): ?>
                                                        <option value="<?php echo $ncc['MaNCC']; ?>"
                                                                <?php echo $ncc['MaNCC'] == $product['MaNCC'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($ncc['TenNCC']); ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="DonGia" class="form-label">Giá</label>
                                                <input type="number" class="form-control" id="DonGia" name="DonGia" 
                                                       value="<?php echo $product['DonGia']; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="SoLuong" class="form-label">Số lượng</label>
                                                <input type="number" class="form-control" id="SoLuong" name="SoLuong" 
                                                       value="<?php echo $product['SoLuong']; ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="MoTa" class="form-label">Mô tả</label>
                                        <textarea class="form-control" id="MoTa" name="MoTa" rows="4"><?php 
                                            echo htmlspecialchars($product['MoTa']); 
                                        ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="AnhNen" class="form-label">Ảnh sản phẩm</label>
                                        <input type="file" class="form-control" id="AnhNen" name="AnhNen" 
                                               accept="image/*" <?php echo empty($product['MaSP']) ? 'required' : ''; ?>>
                                        <?php if (!empty($product['AnhNen'])): ?>
                                            <img src="../uploads/products/<?php echo $product['AnhNen']; ?>" 
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
        document.getElementById('AnhNen').addEventListener('change', function(e) {
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