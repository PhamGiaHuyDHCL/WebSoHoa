<?php
require_once './config/db.php';

class PhongModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy tất cả bản ghi
    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM phong ORDER BY ID ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tạo bản ghi mới
    public function create($maPhong, $tenPhong) {
        $stmt = $this->conn->prepare("INSERT INTO phong (MaPhong, TenPhong) VALUES (?, ?)");
        return $stmt->execute([$maPhong, $tenPhong]);
    }

    // Xoá bản ghi theo ID
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM phong WHERE ID = ?");
        return $stmt->execute([$id]);
    }

    // Tìm phông theo ID
    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM phong WHERE ID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật bản ghi
    public function update($id, $maPhong, $tenPhong) {
        $stmt = $this->conn->prepare("UPDATE phong SET MaPhong = ?, TenPhong = ? WHERE ID = ?");
        return $stmt->execute([$maPhong, $tenPhong, $id]);
    }

    // Kiểm tra mã phông trùng (dùng khi thêm mới)
    public function isDuplicateMaPhong($maPhong) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM phong WHERE MaPhong = ?");
        $stmt->execute([$maPhong]);
        return $stmt->fetchColumn() > 0;
    }

    // Kiểm tra mã phông trùng (dùng khi cập nhật, loại trừ ID hiện tại)
    public function isDuplicateMaPhongExceptId($maPhong, $id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM phong WHERE MaPhong = ? AND ID != ?");
        $stmt->execute([$maPhong, $id]);
        return $stmt->fetchColumn() > 0;
    }
}
