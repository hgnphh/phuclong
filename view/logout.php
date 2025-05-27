<?php
session_start(); // Bắt đầu session
session_unset(); // Xóa tất cả biến session
session_destroy(); // Hủy session

// Chuyển hướng về trang login hoặc index
header("Location: ../index.php");
exit();
