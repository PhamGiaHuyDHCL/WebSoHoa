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

// Lấy danh sách quyền
$roles = [];
$res_roles = $conn->query("SELECT ID, role_name FROM phanquyen");
if ($res_roles) {
    while ($r = $res_roles->fetch_assoc()) {
        $roles[] = $r;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoten = trim($_POST['hoten']);
    $sdt = trim($_POST['sdt']);
    $cccd = trim($_POST['cccd']);
    $taikhoan = trim($_POST['taikhoan']);
    $matkhau = $_POST['matkhau'];
    $idphanquyen = (int)$_POST['idphanquyen'];

    // Kiểm tra các trường không được để trống
    if (!$hoten || !$sdt || !$cccd || !$taikhoan || !$matkhau || !$idphanquyen) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    }
    // Kiểm tra CCCD đúng 12 số
    elseif (!preg_match('/^\d{12}$/', $cccd)) {
        $error = 'Số CCCD phải gồm đúng 12 chữ số.';
    }
    // Kiểm tra SĐT đúng 10 số
    elseif (!preg_match('/^\d{10}$/', $sdt)) {
        $error = 'Số điện thoại phải gồm đúng 10 chữ số.';
    }
    else {
        // Kiểm tra trùng CCCD
        $stmt_check = $conn->prepare("SELECT ID FROM nhanvien WHERE CCCD = ?");
        $stmt_check->bind_param("s", $cccd);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "CCCD '$cccd' đã tồn tại trong hệ thống.";
            $stmt_check->close();
        } else {
            $stmt_check->close();

            $matkhau_hash = password_hash($matkhau, PASSWORD_DEFAULT);

            // Thêm nhân viên
            $stmt_nv = $conn->prepare("INSERT INTO nhanvien (HoTen, SoDienThoai, CCCD) VALUES (?, ?, ?)");
            $stmt_nv->bind_param("sss", $hoten, $sdt, $cccd);
            if ($stmt_nv->execute()) {
                $idnhanvien = $conn->insert_id;
                $stmt_nv->close();

                // Thêm tài khoản
                $stmt_tk = $conn->prepare("INSERT INTO taikhoan (TaiKhoan, MatKhau, IDPhanQuyen, IDNhanVien) VALUES (?, ?, ?, ?)");
                $stmt_tk->bind_param("ssii", $taikhoan, $matkhau_hash, $idphanquyen, $idnhanvien);
                if ($stmt_tk->execute()) {
                    header("Location: dstaikhoan.php?msg=Thêm tài khoản thành công");
                    exit();
                } else {
                    $error = "Lỗi khi thêm tài khoản: " . $stmt_tk->error;
                }
                $stmt_tk->close();
            } else {
                $error = "Lỗi khi thêm nhân viên: " . $stmt_nv->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>Thêm tài khoản</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4">
  <h4>Thêm tài khoản mới</h4>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <!-- Hàng 1: Họ tên, SĐT, CCCD -->
    <div class="row mb-3">
      <div class="col-md-4">
        <label>Họ tên *</label>
        <input type="text" name="hoten" class="form-control" required 
               value="<?= isset($_POST['hoten']) ? htmlspecialchars($_POST['hoten']) : '' ?>">
      </div>
      <div class="col-md-4">
        <label>Số điện thoại *</label>
        <input type="text" name="sdt" class="form-control" required
               pattern="\d{10}" title="Số điện thoại phải gồm đúng 10 chữ số"
               value="<?= isset($_POST['sdt']) ? htmlspecialchars($_POST['sdt']) : '' ?>">
      </div>
      <div class="col-md-4">
        <label>Số CCCD *</label>
        <input type="text" name="cccd" class="form-control" required
               pattern="\d{12}" title="CCCD phải gồm đúng 12 chữ số"
               value="<?= isset($_POST['cccd']) ? htmlspecialchars($_POST['cccd']) : '' ?>">
      </div>
    </div>

    <!-- Hàng 2: Tài khoản, Mật khẩu, Quyền -->
    <div class="row mb-3">
      <div class="col-md-4">
        <label>Tài khoản *</label>
        <input type="text" name="taikhoan" class="form-control" required 
               value="<?= isset($_POST['taikhoan']) ? htmlspecialchars($_POST['taikhoan']) : '' ?>">
      </div>
      <div class="col-md-4">
        <label>Mật khẩu *</label>
        <input type="password" name="matkhau" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label>Quyền *</label>
        <select name="idphanquyen" class="form-select" required>
          <option value="">-- Chọn quyền --</option>
          <?php foreach ($roles as $role): ?>
            <option value="<?= $role['ID'] ?>" 
                    <?= (isset($_POST['idphanquyen']) && $_POST['idphanquyen'] == $role['ID']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($role['role_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Nút hành động -->
    <div class="mt-3">
      <a href="dstaikhoan.php" class="btn btn-secondary">Quay lại</a>
      <button type="submit" class="btn btn-primary">Lưu</button>
    </div>
  </form>
</div>
</body>
</html>

<?php $conn->close(); ?>
