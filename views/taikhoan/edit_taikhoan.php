<?php include '../../config/dbadmin.php'; 
checkAdmin();?>
<?php
session_start();
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

// Kiểm tra ID tài khoản truyền vào
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID tài khoản không hợp lệ.");
}

$id = intval($_GET['id']);

// Lấy dữ liệu tài khoản hiện tại, gồm cả mật khẩu hash để so sánh
$sql = "
    SELECT tk.ID, tk.TaiKhoan, tk.MatKhau, tk.IDPhanQuyen, nv.HoTen, nv.CCCD, nv.SoDienThoai
    FROM taikhoan tk
    JOIN nhanvien nv ON nv.ID = tk.IDNhanVien
    WHERE tk.ID = $id
";
$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) {
    die("Không tìm thấy tài khoản.");
}
$account = $result->fetch_assoc();

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $taiKhoan = trim($conn->real_escape_string($_POST['TaiKhoan']));
    $matKhauCu = $_POST['MatKhauCu']; // mật khẩu cũ nhập để xác nhận
    $matKhauMoi = $_POST['MatKhauMoi']; // mật khẩu mới (có thể để trống nếu không đổi)
    $idPhanQuyen = intval($_POST['IDPhanQuyen']);

    $errors = [];

    // Kiểm tra tên tài khoản mới có trùng với tài khoản khác không (trừ chính tài khoản này)
    $checkSql = "SELECT ID FROM taikhoan WHERE TaiKhoan = '$taiKhoan' AND ID <> $id LIMIT 1";
    $checkResult = $conn->query($checkSql);
    if ($checkResult && $checkResult->num_rows > 0) {
        $errors[] = "Tên tài khoản đã tồn tại, vui lòng chọn tên khác.";
    }

   // Bắt buộc nhập mật khẩu cũ để xác nhận
    if (empty($matKhauCu)) {
    	$errors[] = "Vui lòng nhập mật khẩu cũ để xác nhận.";
    } else {
    // Kiểm tra mật khẩu cũ có đúng không
    	if (!password_verify($matKhauCu, $account['MatKhau'])) {
    		$errors[] = "Mật khẩu cũ không đúng.";
    	} else {
    	// Nếu người dùng có nhập mật khẩu mới, kiểm tra không được giống mật khẩu cũ
    		if (!empty($matKhauMoi) && password_verify($matKhauMoi, $account['MatKhau'])) {
    			$errors[] = "Mật khẩu mới không được giống mật khẩu cũ.";
    		}
    	}
    }


    if (empty($errors)) {
        // Cập nhật dữ liệu
        if (!empty($matKhauMoi)) {
            $matKhauHashed = password_hash($matKhauMoi, PASSWORD_DEFAULT);
            $sqlUpdate = "UPDATE taikhoan SET TaiKhoan='$taiKhoan', MatKhau='$matKhauHashed', IDPhanQuyen=$idPhanQuyen WHERE ID=$id";
        } else {
            $sqlUpdate = "UPDATE taikhoan SET TaiKhoan='$taiKhoan', IDPhanQuyen=$idPhanQuyen WHERE ID=$id";
        }

        if ($conn->query($sqlUpdate) === TRUE) {
            $_SESSION['success'] = "Cập nhật tài khoản thành công!";
            header("Location: dstaikhoan.php");
            exit;
        } else {
            $errors[] = "Lỗi khi cập nhật: " . $conn->error;
        }
    }
}

// Lấy danh sách phân quyền để chọn
$sqlRoles = "SELECT ID, role_name FROM phanquyen";
$rolesResult = $conn->query($sqlRoles);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Sửa tài khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h3>Sửa tài khoản cho: <?= htmlspecialchars($account['HoTen']) ?></h3>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
            <?php foreach($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="mb-3">
            <label for="TaiKhoan" class="form-label">Tài khoản</label>
            <input type="text" class="form-control" id="TaiKhoan" name="TaiKhoan" required value="<?= htmlspecialchars($account['TaiKhoan']) ?>" />
        </div>

        <div class="mb-3">
            <label for="MatKhauCu" class="form-label">Mật khẩu cũ <small></small></label>
            <input type="password" class="form-control" id="MatKhauCu" name="MatKhauCu" />
        </div>

        <div class="mb-3">
            <label for="MatKhauMoi" class="form-label">Mật khẩu mới <small>(để trống nếu không đổi)</small></label>
            <input type="password" class="form-control" id="MatKhauMoi" name="MatKhauMoi" />
        </div>

        <div class="mb-3">
            <label for="IDPhanQuyen" class="form-label">Quyền</label>
            <select class="form-select" id="IDPhanQuyen" name="IDPhanQuyen" required>
                <?php
                if ($rolesResult && $rolesResult->num_rows > 0) {
                    // Reset data pointer to re-fetch
                    $rolesResult->data_seek(0);
                    while ($role = $rolesResult->fetch_assoc()) {
                        $selected = ($role['ID'] == $account['IDPhanQuyen']) ? "selected" : "";
                        echo "<option value='" . $role['ID'] . "' $selected>" . htmlspecialchars($role['role_name']) . "</option>";
                    }
                }
                ?>
            </select>
        </div>

        <a href="dstaikhoan.php" class="btn btn-secondary">Quay lại</a>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
