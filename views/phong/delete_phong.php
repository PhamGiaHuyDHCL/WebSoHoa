<?php
// Kết nối cơ sở dữ liệu
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "qlsohoa";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
    exit();
}

// Xử lý xóa phông
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = trim($_POST['id']);
    
    try {
        $stmt = $conn->prepare("DELETE FROM phong WHERE ID = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        echo "success";
    } catch (PDOException $e) {
        echo "Lỗi xóa phòng: " . $e->getMessage();
    }
}
?>
