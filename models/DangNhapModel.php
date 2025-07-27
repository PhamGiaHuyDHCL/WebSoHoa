<?php
require_once './config/db.php';

class DangNhapModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // Kiểm tra tài khoản có tồn tại không
    public function kiemTraTaiKhoan($taiKhoan) {
        $sql = "SELECT * FROM taikhoan WHERE TaiKhoan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $taiKhoan);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Kiểm tra tài khoản và số điện thoại có khớp trong hệ thống không
    public function kiemTraTaiKhoanVaSDT($taiKhoan, $soDienThoai) {
        $sql = "SELECT taikhoan.ID
                FROM taikhoan
                JOIN nhanvien ON taikhoan.IDNhanVien = nhanvien.ID
                WHERE taikhoan.TaiKhoan = ? AND nhanvien.SoDienThoai = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $taiKhoan, $soDienThoai);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
    public function resetMatKhau($taikhoan, $sdt, $matkhaumoi) {
        $sql = "SELECT taikhoan.ID 
                FROM taikhoan 
                JOIN nhanvien ON taikhoan.IDNhanVien = nhanvien.ID 
                WHERE taikhoan.TaiKhoan = ? AND nhanvien.SoDienThoai = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $taikhoan, $sdt);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $id = $row['ID'];
            $hashedPassword = password_hash($matkhaumoi, PASSWORD_DEFAULT);

            $updateSql = "UPDATE taikhoan SET MatKhau = ? WHERE ID = ?";
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->bind_param("si", $hashedPassword, $id);

            if ($updateStmt->execute()) {
                return "✅ Đặt lại mật khẩu thành công.";
            } else {
                return "❌ Lỗi khi cập nhật mật khẩu.";
            }
        } else {
            return "❌ Tài khoản hoặc số điện thoại không đúng.";
        }
    }

    // Đăng xuất người dùng
    public function dangXuat() {
        session_start();
        session_unset();     // Xóa toàn bộ biến phiên
        session_destroy();   // Hủy phiên làm việc
        header("Location: ./views/login/dangnhap.php"); // Điều hướng đúng vị trí file
        exit;
    }
}
