<?php include '../../config/dbadmin.php'; 
checkAdmin();?>
<?php
// Kết nối CSDL
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'qlsohoa';

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("❌ Lỗi kết nối CSDL: " . $conn->connect_error);
}

// Lấy ID tài khoản từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Lấy ID nhân viên liên quan để xóa sau
    $stmt_nv = $conn->prepare("SELECT IDNhanVien FROM taikhoan WHERE ID = ?");
    $stmt_nv->bind_param("i", $id);
    $stmt_nv->execute();
    $stmt_nv->bind_result($idNhanVien);
    $stmt_nv->fetch();
    $stmt_nv->close();

    if ($idNhanVien) {
        // Xóa tài khoản
        $stmt_del_tk = $conn->prepare("DELETE FROM taikhoan WHERE ID = ?");
        $stmt_del_tk->bind_param("i", $id);
        $stmt_del_tk->execute();
        $stmt_del_tk->close();

        // Xóa nhân viên (vì mỗi nhân viên gắn với đúng 1 tài khoản)
        $stmt_del_nv = $conn->prepare("DELETE FROM nhanvien WHERE ID = ?");
        $stmt_del_nv->bind_param("i", $idNhanVien);
        $stmt_del_nv->execute();
        $stmt_del_nv->close();

        header("Location: dstaikhoan.php?msg=Xóa tài khoản thành công");
        exit();
    } else {
        $error = "Không tìm thấy tài khoản hoặc nhân viên tương ứng.";
    }
} else {
    $error = "Thiếu ID tài khoản cần xóa.";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xóa tài khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4">
    <h4>Lỗi xóa tài khoản</h4>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <a href="dstaikhoan.php" class="btn btn-secondary">Quay lại danh sách</a>
</div>
</body>
</html>

<?php $conn->close(); ?>
