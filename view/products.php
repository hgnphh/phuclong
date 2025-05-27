<?php
session_start();
include '../model/connect.php';

// Xử lý thêm vào giỏ hàng — PHẢI đặt trước khi include header.php
if (isset($_POST['add_to_cart'])) {
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = "Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!";
        $_SESSION['redirect_after_login'] = $_SERVER['PHP_SELF'];
        header('Location: login.php');
        exit();
    }

    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // Thêm hoặc cập nhật số lượng sản phẩm
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    // Thông báo thành công
    $_SESSION['message'] = "Đã thêm sản phẩm vào giỏ hàng!";
    header("Location: cart.php");
    exit();
}

// 👉 Sau khi xử lý logic, mới include giao diện
include 'header.php';
?>


<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        margin: 0;
        padding: 20px;
    }
    h2 {
        text-align: center;
        color: #007a33;
        margin: 20px 0;
    }
    .grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        padding: 0 20px;
    }
    .card {
        background: #fff;
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .card img {
        max-width: 100%;
        height: 180px;
        object-fit: contain;
        margin-bottom: 10px;
    }
    .card h3 {
        font-size: 16px;
        margin: 10px 0;
        min-height: 40px;
        color: #333;
    }
    .price {
        font-weight: bold;
        color: #007a33;
        margin-bottom: 10px;
    }
    .btn {
        background-color: #007a33;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn:hover {
        background-color: #005f27;
    }
    .quantity-input {
        width: 60px;
        padding: 5px;
        margin: 0 10px;
        text-align: center;
    }
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    .alert-warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeeba;
    }
    .cart-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #ff4444;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
    }
</style>

<h2>Sản phẩm nổi bật</h2>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert <?php echo strpos($_SESSION['message'], 'Vui lòng đăng nhập') !== false ? 'alert-warning' : 'alert-success'; ?>">
        <?php 
        echo $_SESSION['message'];
        unset($_SESSION['message']);
        ?>
    </div>
<?php endif; ?>

<div class="grid-container">
<?php
$sql = "SELECT * FROM sanpham";
$result = $conn->query($sql);

if ($result->num_rows > 0):
    while($row = $result->fetch_assoc()):
?>
    <div class="card">
        <img src="../uploads/<?= htmlspecialchars($row['AnhNen']) ?>" alt="<?= htmlspecialchars($row['TenSP']) ?>">
        <h3><?= htmlspecialchars($row['TenSP']) ?></h3>
        <div class="price"><?= number_format($row['DonGia']) ?> đ</div>
        <form method="POST" class="d-flex justify-content-center align-items-center">
            <input type="hidden" name="product_id" value="<?= $row['MaSP'] ?>">
            <input type="number" name="quantity" value="1" min="1" class="quantity-input">
            <button type="submit" name="add_to_cart" class="btn">
                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
            </button>
        </form>
    </div>
<?php
    endwhile;
else:
    echo "<p style='text-align:center;'>Không có sản phẩm nào.</p>";
endif;
$conn->close();
?>
</div>

</body>
</html>
