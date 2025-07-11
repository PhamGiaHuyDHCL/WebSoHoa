<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['taikhoan_id'])) {
    header("Location: http://localhost/websohoa1/views/login/dangnhap.php");
    exit();
}

// Kết nối CSDL
$conn = new mysqli("localhost", "root", "", "qlsohoa");
$conn->set_charset("utf8mb4");

$role_name = "Người dùng";
$taikhoan = "";
$id_phanquyen = 0;


$hoten = ""; // Khởi tạo trước để tránh lỗi

if (!empty($_SESSION['taikhoan_id'])) {
    $id = $_SESSION['taikhoan_id'];
    
    // Truy vấn tài khoản và quyền
    $sql = "SELECT tk.TaiKhoan, tk.IDPhanQuyen, pq.role_name 
            FROM taikhoan tk 
            JOIN phanquyen pq ON tk.IDPhanQuyen = pq.ID 
            WHERE tk.ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($taikhoan, $id_phanquyen, $role_name);
    $stmt->fetch();
    $stmt->close();

    // 👉 Truy vấn thêm họ tên nhân viên từ bảng nhanvien
    $sql_nv = "SELECT HoTen FROM nhanvien WHERE ID = ?";
    $stmt_nv = $conn->prepare($sql_nv);
    $stmt_nv->bind_param("i", $id);
    $stmt_nv->execute();
    $result_nv = $stmt_nv->get_result();
    if ($row_nv = $result_nv->fetch_assoc()) {
        $hoten = $row_nv['HoTen'];
    }
    $stmt_nv->close();
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>NHẬP LIỆU SỐ HÓA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    html, body {
      height: 100%;
      margin: 0;
      overflow: hidden;
    }
    .sidebar {
      width: 220px;
      min-height: 100vh;
      background-color: #343a40;
      color: white;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
    }
    .sidebar .logo {
      padding: 20px;
      font-size: 1.25rem;
      font-weight: bold;
      border-bottom: 1px solid #495057;
    }
    .sidebar a {
      color: white;
      text-decoration: none;
      padding: 12px 20px;
      display: block;
    }
    .sidebar a:hover,
    .sidebar a.active {
      background-color: #495057;
    }

    .main-content {
      margin-left: 220px;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .top-header {
      padding: 10px 20px;
      background-color: #fff;
      border-bottom: 1px solid #ddd;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 999;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <i class="bi bi-file-earmark-text me-2"></i> NHẬP LIỆU SỐ HÓA
    </div>
    <a href="http://localhost/websohoa1/" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
      <i class="bi bi-house-door me-2"></i> Home
    </a>
    <a href="http://localhost/websohoa1/views/import/dsimport.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dsimport.php' ? 'active' : '' ?>">
      <i class="bi bi-file-earmark-arrow-up me-2"></i> Import PDF
    </a>
    <a href="#"><i class="bi bi-building me-2"></i> Nhập liệu khối Chính</a>
    <a href="http://localhost/websohoa1/views/Nhaplieu/khoidang.php" class="<?= basename($_SERVER['PHP_SELF']) == 'khoidang.php' ? 'active' : '' ?>">
      <i class="bi bi-building me-2"></i> Nhập liệu khối Đảng
    </a>

    <?php if ($id_phanquyen == 1): ?>
      <a href="http://localhost/websohoa1/views/phong/dsphong.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dsphong.php' ? 'active' : '' ?>">
        <i class="bi bi-list-ul me-2"></i> Quản lí danh mục
      </a>
      <a href="http://localhost/websohoa1/views/taikhoan/dstaikhoan.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dstaikhoan.php' ? 'active' : '' ?>">
        <i class="bi bi-people me-2"></i> Quản lí tài khoản
      </a>
    <?php endif; ?>
  </div>

  <!-- Nội dung -->
  <div class="main-content">
    <div class="top-header">
     <h5 class="text-primary mb-0">Xin chào, <?= htmlspecialchars($hoten) ?> 👋</h5>

      <div>
        <i class="bi bi-person-circle me-1"></i>
        <?= htmlspecialchars($taikhoan) ?>,
        <a href="http://localhost/websohoa1/views/login/dangxuat.php" class="text-decoration-none">Đăng xuất</a>
      </div>
    </div>
