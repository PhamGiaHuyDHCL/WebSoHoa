<?php
session_start();
require_once '../../config/db.php';

$thongBao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taikhoan = $_POST['taikhoan'] ?? '';
    $sdt = $_POST['sdt'] ?? '';
    $matkhaumoi = $_POST['matkhaumoi'] ?? '';
    $nhaplaimatkhau = $_POST['nhaplaimatkhau'] ?? '';

    if ($matkhaumoi !== $nhaplaimatkhau) {
        $thongBao = "❌ Mật khẩu nhập lại không khớp.";
    } else {
        // Kiểm tra tài khoản và số điện thoại khớp qua JOIN
        $sql = "SELECT taikhoan.ID 
                FROM taikhoan 
                JOIN nhanvien ON taikhoan.IDNhanVien = nhanvien.ID 
                WHERE taikhoan.TaiKhoan = ? AND nhanvien.SoDienThoai = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $taikhoan, $sdt);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $id = $row['ID'];
            $hashedPassword = password_hash($matkhaumoi, PASSWORD_DEFAULT);

            $updateSql = "UPDATE taikhoan SET MatKhau = ? WHERE ID = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("si", $hashedPassword, $id);

            if ($updateStmt->execute()) {
                $thongBao = "✅ Đặt lại mật khẩu thành công.";
            } else {
                $thongBao = "❌ Lỗi khi cập nhật mật khẩu.";
            }
        } else {
            $thongBao = "❌ Tài khoản hoặc số điện thoại không đúng.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: #f0f2f5;
        }
        .login-box {
            max-width: 450px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
    </style>
</head>
<body>
<div class="login-box">
    <h3 class="text-center mb-4">🔒 Quên mật khẩu</h3>

    <?php if ($thongBao): ?>
        <div class="alert <?= str_contains($thongBao, '✅') ? 'alert-success' : 'alert-danger' ?>">
            <?= htmlspecialchars($thongBao) ?>
        </div>
    <?php endif; ?>

        <form method="post" id="resetForm">
            <div class="mb-3">
                <label for="taikhoan" class="form-label">Tài khoản</label>
                <input type="text" class="form-control" id="taikhoan" name="taikhoan" 
                       pattern="^[a-zA-Z0-9]+$" required
                       title="Chỉ nhập chữ và số, không có ký tự đặc biệt"
                       value="<?= htmlspecialchars($_POST['taikhoan'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="sdt" class="form-label">Số điện thoại</label>
                <input type="text" class="form-control" id="sdt" name="sdt" 
                       pattern="^0\d{9}$" required
                       title="Số điện thoại hợp lệ gồm 10 chữ số và bắt đầu bằng số 0"
                       value="<?= htmlspecialchars($_POST['sdt'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="matkhaumoi" class="form-label">Mật khẩu mới</label>
                <input type="password" class="form-control" id="matkhaumoi" name="matkhaumoi"
                       pattern="^[a-zA-Z0-9]{6,}$" required
                       title="Mật khẩu phải có ít nhất 6 ký tự, chỉ bao gồm chữ và số">
            </div>

            <div class="mb-3">
                <label for="nhaplaimatkhau" class="form-label">Nhập lại mật khẩu</label>
                <input type="password" class="form-control" id="nhaplaimatkhau" name="nhaplaimatkhau"
                       pattern="^[a-zA-Z0-9]{6,}$" required
                       title="Nhập lại mật khẩu giống mật khẩu mới ở trên">
            </div>

            <button type="submit" class="btn btn-primary w-100">Tiếp tục</button>

            <div class="mt-3 text-center">
                <a href="dangnhap.php">← Quay lại đăng nhập</a>
            </div>
        </form>


</div>

<script>
document.getElementById('resetForm').addEventListener('submit', function (e) {
    const mk1 = document.getElementById("matkhaumoi").value;
    const mk2 = document.getElementById("nhaplaimatkhau").value;

    if (mk1 !== mk2) {
        e.preventDefault();
        alert("❌ Mật khẩu nhập lại không khớp!");
    }
});
</script>
</body>
</html>
