<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Cấu hình kết nối database
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "qlsohoa";

try {
    // Kết nối PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['taikhoan_id'])) {
    // Chưa đăng nhập, chuyển về trang đăng nhập
    header("Location:http://localhost/websohoa1/views/login/dangnhap.php");
    exit();
}

// Lấy thông tin tài khoản và quyền từ CSDL
try {
    $stmt = $conn->prepare("SELECT tk.TaiKhoan, tk.IDPhanQuyen, pq.role_name 
                            FROM taikhoan tk 
                            JOIN phanquyen pq ON tk.IDPhanQuyen = pq.ID 
                            WHERE tk.ID = ?");
    $stmt->execute([$_SESSION['taikhoan_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Tài khoản không tồn tại => đăng xuất luôn
        session_destroy();
        header("Location:http://localhost/websohoa1/views/login/dangnhap.php");
        exit();
    }
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Lưu thông tin user ra biến toàn cục để dùng tiếp
$taikhoan = $user['TaiKhoan'];
$id_phanquyen = $user['IDPhanQuyen'];
$role_name = $user['role_name'];

// Hàm kiểm tra quyền (ví dụ quyền admin = 1)
function checkAdmin() {
    global $id_phanquyen;
    if ($id_phanquyen != 1) {
        header("Location: ../../index.php");
        exit();
    }
}

