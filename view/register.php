<?php
require 'connect.php';

$sodienthoai = $_POST['sodienthoai'];
$matkhau = $_POST['matkhau'];
$email = $_POST['email'];
$diachi = $_POST['diachi'];
$hashedPassword = password_hash($matkhau, PASSWORD_DEFAULT);

$sql = "INSERT INTO khachhang (sodienthoai, matkhau, email, diachi) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $sodienthoai, $hashedPassword, $email, $diachi);
if ($stmt->execute()) {
    echo "Đăng ký thành công! <a href='login.html'>Đăng nhập</a>";
} else {
    echo "Lỗi: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>