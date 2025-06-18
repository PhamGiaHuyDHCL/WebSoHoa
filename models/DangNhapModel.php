<?php
require_once './config/db.php';

class DangNhapModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function kiemTraTaiKhoan($taiKhoan) {
        $sql = "SELECT * FROM taikhoan WHERE TaiKhoan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $taiKhoan);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function dangXuat() {
    session_start();
    session_unset();     // Xóa toàn bộ biến phiên
    session_destroy();   // Hủy phiên làm việc
    header("http://localhost/websohoa1/views/login/dangnhap.php"); // Chuyển về trang đăng nhập
    exit;
}

}
