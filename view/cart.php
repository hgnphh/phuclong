<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Vui lòng đăng nhập để xe  m giỏ hàng!";
    $_SESSION['redirect_after_login'] = $_SERVER['PHP_SELF'];
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../model/connect.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle cart actions
if (isset($_POST['action'])) {
    $product_id = $_POST['product_id'];
    
    switch ($_POST['action']) {
        case 'update':
            $quantity = (int)$_POST['quantity'];
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = $quantity;
            }
            break;
            
        case 'remove':
            unset($_SESSION['cart'][$product_id]);
            break;
    }
    
    // Redirect to prevent form resubmission
    header('Location: cart.php');
    exit();
}

// Get cart items details
$cart_items = array();
$total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', $product_ids);
    
    $sql = "SELECT * FROM sanpham WHERE MaSP IN ($ids_string)";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $cart_items[] = array(
                'id' => $row['MaSP'],
                'name' => $row['TenSP'],
                'price' => $row['DonGia'],
                'image' => $row['AnhNen'],
                'quantity' => $_SESSION['cart'][$row['MaSP']]
            );
            $total += $row['DonGia'] * $_SESSION['cart'][$row['MaSP']];
        }
    } else {
        echo "Lỗi truy vấn: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Phúc Long</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .cart-item img {
            max-width: 100px;
            height: auto;
        }
        .quantity-input {
            width: 70px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4">Giỏ hàng của bạn</h2>
        
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">
                Giỏ hàng của bạn đang trống. <a href="products.php">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="img-fluid">
                                </div>
                                <div class="col-md-4">
                                    <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="text-muted"><?php echo number_format($item['price']); ?> VNĐ</p>
                                </div>
                                <div class="col-md-3">
                                    <form method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                               class="form-control quantity-input" min="1">
                                        <button type="submit" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-2">
                                    <p class="fw-bold"><?php echo number_format($item['price'] * $item['quantity']); ?> VNĐ</p>
                                </div>
                                <div class="col-md-1">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Tổng đơn hàng</h5>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tạm tính:</span>
                                <span class="fw-bold"><?php echo number_format($total); ?> VNĐ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Phí vận chuyển:</span>
                                <span>Miễn phí</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tổng cộng:</span>
                                <span class="fw-bold text-primary"><?php echo number_format($total); ?> VNĐ</span>
                            </div>
                            <a href="checkout.php" class="btn btn-primary w-100">Tiến hành thanh toán</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
