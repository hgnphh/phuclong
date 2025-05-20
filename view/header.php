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
    }

    input.search-input {
      background-color: #f3f3f3;
      border: none;
      padding: 8px 20px;
      border-radius: 9999px;
      width: 100%;
      font-size: 14px;
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
  </style>
</head>

<body class="font-sans">

  <!-- HEADER -->
  <header class="bg-white shadow-sm">
    <nav class="flex items-center justify-between px-6 py-3 max-w-screen-xl mx-auto">
      <!-- Logo -->
      <div class="flex items-center space-x-4">
        <img src="https://phuclong.com.vn/_next/static/images/logo_2-fdd0b762f4686e31e1101d029a664bc9.png" alt="Phúc Long Logo" class="h-10">
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
        <a href="login.html"><i class="fa-regular fa-circle-user fa-xl" style="color: #006633;"></i></a>
      </div>
    </nav>
  </header>

  <!-- MENU -->
  <div class="menu-index text-sm tracking-wide">
    <a href="index.php">TRANG CHỦ</a>
    <a href="view/products.php">MENU</a>
    <a href="#">SẢN PHẨM ĐÓNG GÓI</a>
    <a href="#">VỀ CHÚNG TÔI</a>
    <a href="#">KHUYẾN MÃI</a>
    <a href="#">HỘI VIÊN</a>
  </div>
