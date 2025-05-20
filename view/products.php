<?php
include 'model/connect.php';
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
</style>

<h2>Sản phẩm nổi bật</h2>

<div class="grid-container">
<?php
$sql = "SELECT * FROM sanpham";
$result = $conn->query($sql);

if ($result->num_rows > 0):
    while($row = $result->fetch_assoc()):
?>
    <div class="card">
        <img src="uploads/<?= htmlspecialchars($row['anhSP']) ?>" alt="<?= htmlspecialchars($row['tenSP']) ?>">
        <h3><?= htmlspecialchars($row['tenSP']) ?></h3>
        <div class="price"><?= number_format($row['donGia']) ?> đ</div>
        <button class="btn">🛒 Đặt mua</button>
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
