<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'qlsohoa';

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Lỗi kết nối CSDL: " . $conn->connect_error);
}

$thongBao = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taiKhoan = trim($_POST['TaiKhoan']);
    $matKhau = $_POST['MatKhau'];

    $sql = "SELECT ID, TaiKhoan, MatKhau, IDPhanQuyen FROM taikhoan WHERE TaiKhoan = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $taiKhoan);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($matKhau, $row['MatKhau'])) {
            $_SESSION['taikhoan_id'] = $row['ID'];
            $_SESSION['username'] = $row['TaiKhoan'];
            $_SESSION['phanquyen_id'] = $row['IDPhanQuyen'];

            header("Location: ../../index.php");
            exit;
        } else {
            $thongBao = "❌ Mật khẩu không đúng!";
        }
    } else {
        $thongBao = "❌ Tài khoản không tồn tại!";
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: #f0f2f5;
        }
        .login-box {
            max-width: 400px;
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
    <h3 class="text-center mb-4">🔐 Đăng nhập hệ thống</h3>

    <?php if ($thongBao): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($thongBao) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="TaiKhoan" class="form-label">Tài khoản</label>
            <input type="text" name="TaiKhoan" class="form-control"
                   pattern="^[a-zA-Z0-9]+$"
                   title="Chỉ nhập chữ cái và số, không dấu cách hoặc ký tự đặc biệt"
                   required>
        </div>
        <div class="mb-3">
            <label for="MatKhau" class="form-label">Mật khẩu</label>
            <input type="password" name="MatKhau" class="form-control"
                   pattern="^[a-zA-Z0-9]{6,}$"
                   title="Mật khẩu phải có ít nhất 6 ký tự, chỉ gồm chữ cái và số"
                   required>
        </div>


        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>

        <!-- ✅ Nút Quên mật khẩu -->
        <div class="text-center mt-3">
            <a href="quenmatkhau.php">Quên mật khẩu?</a>
        </div>
    </form>
</div>
</body>
</html>
