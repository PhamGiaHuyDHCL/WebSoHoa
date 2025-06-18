
<?php
// config/db.php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'qlsohoa'; // thay bằng tên CSDL của bạn

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
