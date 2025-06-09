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

// Lấy danh sách danh mục
$sql_danhmuc = "SELECT * FROM danhmuc WHERE TrangThai = 1";
$result_danhmuc = $conn->query($sql_danhmuc);

// Xử lý lọc theo danh mục
$selected_danhmuc = isset($_GET['danhmuc']) ? $_GET['danhmuc'] : 'all';

// Lấy danh sách sản phẩm với điều kiện lọc
$sql_sanpham = "SELECT s.*, d.TenDM 
                FROM sanpham s 
                LEFT JOIN danhmuc d ON s.MaDM = d.MaDM";
if ($selected_danhmuc !== 'all') {
    $sql_sanpham .= " WHERE s.MaDM = " . intval($selected_danhmuc);
}
$result_sanpham = $conn->query($sql_sanpham);
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

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Danh Mục -->
        <div class="w-full md:w-64 bg-white rounded-lg shadow-sm p-4">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">Danh Mục Sản Phẩm</h2>
            <ul class="space-y-2">
                <li>
                    <a href="?danhmuc=all" 
                       class="block px-4 py-2 rounded <?php echo $selected_danhmuc === 'all' ? 'bg-green-50 text-green-600' : 'hover:bg-green-50 text-gray-700 hover:text-green-600'; ?>">
                        Tất cả sản phẩm
                    </a>
                </li>
                <?php while($danhmuc = $result_danhmuc->fetch_assoc()): ?>
                <li>
                    <a href="?danhmuc=<?php echo $danhmuc['MaDM']; ?>" 
                       class="block px-4 py-2 rounded <?php echo $selected_danhmuc == $danhmuc['MaDM'] ? 'bg-green-50 text-green-600' : 'hover:bg-green-50 text-gray-700 hover:text-green-600'; ?>">
                        <?php echo $danhmuc['TenDM']; ?>
                    </a>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="flex-1">
            <?php if ($result_sanpham->num_rows > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($sanpham = $result_sanpham->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <a href="products-detail.php?id=<?php echo $sanpham['MaSP']; ?>">
                        <img src="../uploads/products/<?php echo $sanpham['AnhNen']; ?>" alt="<?php echo $sanpham['TenSP']; ?>" class="product-image">
                    </a>
                    <div class="p-4">
                        <a href="products-detail.php?id=<?php echo $sanpham['MaSP']; ?>" class="text-decoration-none">
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo $sanpham['TenSP']; ?></h3>
                        </a>
                        <p class="text-sm text-gray-600 mb-2"><?php echo $sanpham['TenDM']; ?></p>
                        <p class="text-green-600 font-semibold"><?php echo number_format($sanpham['DonGia'], 0, ',', '.'); ?>đ</p>
                        <a href="products-detail.php?id=<?php echo $sanpham['MaSP']; ?>" class="mt-4 block w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition-colors text-center">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8">
                <p class="text-gray-600">Không có sản phẩm nào trong danh mục này.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
