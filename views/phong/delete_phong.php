<?php
// Kết nối cơ sở dữ liệu
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "qlsohoa";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM phong WHERE ID = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        echo "success";
    } else {
        echo "Lỗi: ID không hợp lệ";
    }
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>