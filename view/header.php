<!-- header.php -->
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Phúc Long Tea & Coffee</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/a8df2d5945.js" crossorigin="anonymous"></script>
  <style>
 
    .menu-index a {
      padding: 10px 16px;
      font-size: 14px;
      font-weight: 500;
      color: #333;
      text-transform: uppercase;
      transition: color 0.2s;
    }

    .menu-index a:hover {
      color: #007f3f;
    }

    .menu-index {
      display: flex;
      justify-content: center;
      background-color: white;
      border-top: 1px solid #eee;
      border-bottom: 1px solid #eee;
    }

    .btn-shipping {
      font-size: 13px;
      display: flex;
      align-items: center;
      background-color: #f5f5f5;
      border-radius: 9999px;
      padding: 2px 8px;
      margin-left: 16px;
      color: #006633;
    }

    .btn-shipping img {
      height: 24px;
      margin-right: 8px;
    }

    .icon--header a {
      margin-left: 16px;
      position: relative;
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

    input.search-input {
      background-color: #f3f3f3;
      border: none;
      padding: 8px 20px;
      border-radius: 9999px;
      width: 100%;
      font-size: 14px;
    }

    .user-dropdown {
      position: relative;
      display: inline-block;
    }

    .user-dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      background-color: white;
      min-width: 200px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      border-radius: 8px;
      z-index: 1000;
    }

    .user-dropdown:hover .user-dropdown-content {
      display: block;
    }

    .user-dropdown-content a {
      color: #333;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
      font-size: 14px;
      transition: background-color 0.2s;
    }

    .user-dropdown-content a:hover {
      background-color: #f5f5f5;
      color: #007a33;
    }

    .user-dropdown-content .divider {
      border-top: 1px solid #eee;
      margin: 4px 0;
    }

    @media (max-width: 768px) {
      .menu-index {
        flex-wrap: wrap;
        padding: 10px;
      }

      .menu-index a {
        padding: 6px 10px;
        font-size: 13px;
      }
    }
    .menu-index a.active {
  color: #007f3f;
  border-bottom: 2px solid #007f3f;
}
  </style>
</head>

<body class="font-sans">
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Tính tổng số lượng sản phẩm trong giỏ hàng
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        $cart_count += $quantity;
    }
}
?>
<div class="wrapper">
  <!-- HEADER -->
  <header class="bg-white shadow-sm">
    <nav class="flex items-center justify-between px-6 py-3 max-w-screen-xl mx-auto">
      <!-- Logo -->
      <div class="flex items-center space-x-4">
        <a href="/PhucLongProject/index.php">
          <img src="https://phuclong.com.vn/_next/static/images/logo_2-fdd0b762f4686e31e1101d029a664bc9.png" alt="Phúc Long Logo" class="h-10">
        </a>
      </div>

      <!-- Thanh tìm kiếm -->
      <div class="flex-1 mx-6">
        <input type="text" placeholder="Bạn muốn mua gì..." class="search-input">
      </div>

      <!-- Giao hàng -->
      <div class="btn-shipping hidden lg:flex">
        <img src="https://phuclong.com.vn/_next/static/images/delivery-686d7142750173aa8bc5f1d11ea195e4.png" alt="Shipping Icon">
        <button>Chọn Phương Thức Nhận Hàng</button>
      </div>

      <!-- Icon -->
      <div class="icon--header flex items-center">
        <a href="#"><i class="fa-solid fa-envelope fa-lg" style="color: #006633;"></i></a>
        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="user-dropdown">
            <a href="#" class="user-icon">
              <i class="fa-regular fa-circle-user fa-xl" style="color: #006633;"></i>
            </a>
            <div class="user-dropdown-content">
              <a href="/PhucLongProject/view/profile.php">
                <i class="fas fa-user me-2"></i> Thông tin cá nhân
              </a>
              <a href="/PhucLongProject/view/orders.php">
                <i class="fas fa-shopping-bag me-2"></i> Đơn hàng của tôi
              </a>
              <div class="divider"></div>
              <a href="/PhucLongProject/view/logout.php">
                <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
              </a>
            </div>
          </div>
        <?php else: ?>
          <a href="/PhucLongProject/view/login.php"><i class="fa-regular fa-circle-user fa-xl" style="color: #006633;"></i></a>
        <?php endif; ?>
        <a href="/PhucLongProject/view/cart.php" class="cart-icon">
          <i class="fa-solid fa-cart-shopping fa-lg" style="color: #006633;"></i>
          <?php if ($cart_count > 0): ?>
            <span class="cart-count"><?php echo $cart_count; ?></span>
          <?php endif; ?>
        </a>
      </div>

    </nav>
  </header>

  <!-- MENU -->
  <div class="menu-index text-sm tracking-wide">
    <a href="/PhucLongProject/index.php">TRANG CHỦ</a>
    <a href="/PhucLongProject/view/products.php">MENU</a>
    <a href="#">SẢN PHẨM ĐÓNG GÓI</a>
    <a href="#">VỀ CHÚNG TÔI</a>
    <a href="#">KHUYẾN MÃI</a>
    <a href="#">HỘI VIÊN</a>
  </div>
  </div>