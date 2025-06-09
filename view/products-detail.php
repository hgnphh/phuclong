<?php
session_start();
require_once '../model/connect.php';

// Kiểm tra có ID sản phẩm không
if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = $_GET['id'];

// Lấy thông tin sản phẩm
$sql = "SELECT s.*, d.TenDM 
        FROM sanpham s 
        LEFT JOIN danhmuc d ON s.MaDM = d.MaDM 
        WHERE s.MaSP = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit();
}

// Lấy danh sách size
$sql_size = "SELECT * FROM size";
$result_size = $conn->query($sql_size);

// Xử lý thêm vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = "Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!";
        $_SESSION['redirect_after_login'] = $_SERVER['PHP_SELF'] . '?id=' . $product_id;
        header('Location: login.php');
        exit();
    }

    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size_id = isset($_POST['size']) ? $_POST['size'] : null;

    // Ghi log thông tin
    error_log("POST data: " . print_r($_POST, true));
    error_log("Size ID: " . $size_id);
    error_log("Quantity: " . $quantity);

    if (empty($size_id)) {
        $error = "Vui lòng chọn size!";
    } else {
        // Khởi tạo giỏ hàng nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        // Tạo key cho sản phẩm trong giỏ hàng (kết hợp MaSP và MaSize)
        $cart_key = $product_id . '_' . $size_id;

        // Thêm hoặc cập nhật số lượng sản phẩm
        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key] += $quantity;
        } else {
            $_SESSION['cart'][$cart_key] = $quantity;
        }

        // Lưu thông tin size vào session
        if (!isset($_SESSION['cart_sizes'])) {
            $_SESSION['cart_sizes'] = array();
        }
        $_SESSION['cart_sizes'][$cart_key] = $size_id;

        // Ghi log session
        error_log("Cart after update: " . print_r($_SESSION['cart'], true));
        error_log("Cart sizes after update: " . print_r($_SESSION['cart_sizes'], true));

        $_SESSION['message'] = "Đã thêm sản phẩm vào giỏ hàng!";
        header("Location: cart.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['TenSP']); ?> - Phúc Long</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-image {
            width: 90%;
            height:auto;
            object-fit:cover;
            border-radius: 10px;
        }
        .size-option {
            border: 1px solid #ddd;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .size-option:hover {
            border-color: #007a33;
        }
        .size-option.selected {
            background-color: #007a33;
            color: white;
            border-color: #007a33;
        }
        .quantity-input {
            width: 100px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container my-5">
        <div class="row">
            <!-- Hình ảnh sản phẩm -->
            <div class="col-md-6">
                <img src="../uploads/products/<?php echo htmlspecialchars($product['AnhNen']); ?>" 
                     alt="<?php echo htmlspecialchars($product['TenSP']); ?>" 
                     class="product-image">
            </div>

            <!-- Thông tin sản phẩm -->
            <div class="col-md-6">
                <h1 class="mb-3"><?php echo htmlspecialchars($product['TenSP']); ?></h1>
                <p class="text-muted mb-3">Danh mục: <?php echo htmlspecialchars($product['TenDM']); ?></p>
                <h3 class="text-danger mb-4"><?php echo number_format($product['DonGia']); ?>đ</h3>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="" id="cartForm">
                    <!-- Chọn size -->
                    <div class="mb-4">
                        <h5>Chọn size:</h5>
                        <div class="d-flex flex-wrap">
                            <?php while($size = $result_size->fetch_assoc()): ?>
                                <div class="size-option" onclick="selectSize(this, '<?php echo $size['MaSize']; ?>')">
                                    <?php echo htmlspecialchars($size['MaSize']); ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <input type="hidden" name="size" id="selected_size" required>
                        <div id="size-error" class="text-danger mt-2" style="display: none;">Vui lòng chọn size</div>
                    </div>

                    <!-- Số lượng -->
                    <div class="mb-4">
                        <h5>Số lượng:</h5>
                        <input type="number" name="quantity" value="1" min="1" 
                               class="form-control quantity-input" required>
                    </div>

                    <!-- Nút thêm vào giỏ -->
                    <button type="submit" name="add_to_cart" class="btn btn-success btn-lg" onclick="return validateForm()">
                        Thêm vào giỏ hàng
                    </button>
                </form>

                <!-- Mô tả sản phẩm -->
                <div class="mt-5">
                    <h4>Mô tả sản phẩm</h4>
                    <p><?php echo nl2br(htmlspecialchars($product['MoTa'] ?? '')); ?></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectSize(element, sizeId) {
            // Bỏ chọn tất cả các size
            document.querySelectorAll('.size-option').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Chọn size được click
            element.classList.add('selected');
            
            // Cập nhật giá trị size đã chọn
            document.getElementById('selected_size').value = sizeId;
            
            // Ẩn thông báo lỗi nếu có
            document.getElementById('size-error').style.display = 'none';
            
            // Log để kiểm tra
            console.log('Selected size:', sizeId);
        }

        function validateForm() {
            const sizeInput = document.getElementById('selected_size');
            if (!sizeInput.value) {
                document.getElementById('size-error').style.display = 'block';
                return false;
            }
            return true;
        }
    </script>
</body>
</html> 