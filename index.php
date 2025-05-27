<?php 
session_start();
include_once('view/header.php'); 
?>

<!-- Thanh chào mừng và đăng nhập/đăng xuất -->
<div style="background-color: #f4f4f4; padding: 15px; text-align: right; font-family: 'Segoe UI', sans-serif;">
  <?php if (isset($_SESSION['TenKH'])): ?>
    <span style="font-size: 16px;">👋 Xin chào, <strong><?= htmlspecialchars($_SESSION['TenKH']) ?></strong></span>
  <?php else: ?>
  <?php endif; ?>
</div>

<!-- Nội dung banner -->
<section class="relative">
  <div class="banner">
    <img src="https://hcm.fstorage.vn/images/2025/05/resize-tang-1-ts-olong_banner-web-20250507083840.jpg" class="w-full">
  </div>
</section>

<?php include_once('view/footer.php'); ?>
</body>
</html>
