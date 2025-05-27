<?php
session_start();
require_once '../model/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Vui lòng đăng nhập để thực hiện thao tác này";
    header("Location: login.php");
    exit();
}

// Kiểm tra có ID đơn hàng không
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "Không tìm thấy đơn hàng";
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Kiểm tra đơn hàng có thuộc về người dùng không và có thể hủy không
$sql = "SELECT * FROM donhang WHERE MaDH = ? AND MaKH = ? AND TrangThai = 'Chờ xác nhận'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Không thể hủy đơn hàng này";
    header("Location: orders.php");
    exit();
}

// Bắt đầu transaction
$conn->begin_transaction();

try {
    // Cập nhật trạng thái đơn hàng
    $sql = "UPDATE donhang SET TrangThai = 'Đã hủy' WHERE MaDH = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Lấy chi tiết đơn hàng để hoàn trả số lượng sản phẩm
    $sql = "SELECT MaSP, SoLuong FROM chitietdonhang WHERE MaDH = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Hoàn trả số lượng sản phẩm
    while ($item = $result->fetch_assoc()) {
        $update_sql = "UPDATE sanpham SET SoLuong = SoLuong + ? WHERE MaSP = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $item['SoLuong'], $item['MaSP']);
        $update_stmt->execute();
    }

    // Commit transaction
    $conn->commit();
    
    $_SESSION['message'] = "Hủy đơn hàng thành công";
} catch (Exception $e) {
    // Rollback nếu có lỗi
    $conn->rollback();
    $_SESSION['message'] = "Có lỗi xảy ra khi hủy đơn hàng";
}

header("Location: orders.php");
exit(); 