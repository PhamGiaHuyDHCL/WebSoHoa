<?php
require_once '../../config/dbadmin.php';
checkAdmin();

// Kết nối CSDL
$conn = new mysqli('localhost', 'root', '', 'qlsohoa');
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die("Lỗi kết nối: " . $conn->connect_error);
}

// Lấy ID từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Lấy ID nhân viên liên kết
    $stmt = $conn->prepare("SELECT IDNhanVien FROM taikhoan WHERE ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($idNhanVien);
    $stmt->fetch();
    $stmt->close();

    if ($idNhanVien) {
        $conn->begin_transaction();
        try {
            // Xóa tài khoản
            $stmtDelTK = $conn->prepare("DELETE FROM taikhoan WHERE ID = ?");
            $stmtDelTK->bind_param("i", $id);
            $stmtDelTK->execute();
            $stmtDelTK->close();

            // Xóa nhân viên
            $stmtDelNV = $conn->prepare("DELETE FROM nhanvien WHERE ID = ?");
            $stmtDelNV->bind_param("i", $idNhanVien);
            $stmtDelNV->execute();
            $stmtDelNV->close();

            $conn->commit();
            header("Location: dstaikhoan.php?msg=delete_success");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            header("Location: dstaikhoan.php?msg=delete_fail");
            exit();
        }
    } else {
        // Không tìm thấy tài khoản
        header("Location: dstaikhoan.php?msg=invalid");
        exit();
    }
} else {
    header("Location: dstaikhoan.php?msg=invalid");
    exit();
}
?>
