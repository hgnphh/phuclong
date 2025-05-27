<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "phuclong";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>