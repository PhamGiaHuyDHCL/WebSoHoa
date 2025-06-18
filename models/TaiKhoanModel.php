<?php
require_once '../config.php'; // Kết nối CSDL

class TaiKhoanModel {
    public $conn;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }

    public function checkExistCCCD($cccd) {
        $stmt = $this->conn->prepare("SELECT * FROM nhanvien WHERE CCCD=?");
        $stmt->bind_param("s", $cccd);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function checkExistTaiKhoan($taikhoan) {
        $stmt = $this->conn->prepare("SELECT * FROM taikhoan WHERE TaiKhoan=?");
        $stmt->bind_param("s", $taikhoan);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function insertNhanVien($tennv, $sdt, $cccd, $mota) {
        $stmt = $this->conn->prepare("INSERT INTO nhanvien (TenNV, SDT, CCCD, MoTa) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $tennv, $sdt, $cccd, $mota);
        return $stmt->execute() ? $this->conn->insert_id : false;
    }

    public function insertTaiKhoan($nvid, $taikhoan, $matkhau, $quyen) {
        $stmt = $this->conn->prepare("INSERT INTO taikhoan (NVID, TaiKhoan, MatKhau, Quyen) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $nvid, $taikhoan, $matkhau, $quyen);
        return $stmt->execute();
    }

    public function updateTaiKhoan($id, $tennv, $sdt, $cccd, $taikhoan, $mota, $quyen) {
        $stmt1 = $this->conn->prepare("UPDATE nhanvien SET TenNV=?, SDT=?, CCCD=?, MoTa=? WHERE NVID=?");
        $stmt1->bind_param("ssssi", $tennv, $sdt, $cccd, $mota, $id);
        $stmt1->execute();

        $stmt2 = $this->conn->prepare("UPDATE taikhoan SET TaiKhoan=?, Quyen=? WHERE NVID=?");
        $stmt2->bind_param("ssi", $taikhoan, $quyen, $id);
        return $stmt2->execute();
    }

    public function resetMatKhau($id) {
        $matkhau = password_hash("123456", PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE taikhoan SET MatKhau=? WHERE NVID=?");
        $stmt->bind_param("si", $matkhau, $id);
        return $stmt->execute();
    }
}
?>
