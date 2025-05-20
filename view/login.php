<?php
require 'connect.php';

$sodienthoai = $_POST['sodienthoai'];
$matkhau = $_POST['matkhau'];

$sql = "SELECT matkhau FROM khachhang WHERE sodienthoai = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $sodienthoai);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($matkhau, hash: $row['matkhau'])) {
        echo "Đăng nhập thành công!";
    } else {
        echo "Sai mật khẩu!";
    }
} else {
    echo "Người dùng không tồn tại!";
}

$stmt->close();
$conn->close();
?>