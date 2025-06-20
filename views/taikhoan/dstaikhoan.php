<?php
include_once '../../config/dbadmin.php';
include_once '../../models/TaiKhoanModel.php';
include_once '../../controllers/TaiKhoanController.php';
include_once '../layouts/header.php';

$alerts = [
    'success' => ['success', '✔ Tạo tài khoản thành công!'],
    'cccd_exist' => ['warning', '⚠️ CCCD đã tồn tại trong hệ thống!'],
    'invalid_sdt' => ['warning', '⚠️ Số điện thoại phải đúng 10 chữ số!'],
    'invalid_cccd' => ['warning', '⚠️ CCCD phải đúng 12 chữ số!'],
    'missing' => ['danger', '❌ Vui lòng điền đầy đủ thông tin!'],
    'error' => ['danger', '❌ Lỗi xảy ra, vui lòng thử lại.'],
    'edit_success' => ['success', '✔️ Chỉnh sửa tài khoản thành công!'],
    'edit_fail' => ['danger', '❌ Lỗi khi chỉnh sửa tài khoản!'],
    'delete_success' => ['success', '🗑️ Xóa tài khoản thành công!'],
    'delete_fail' => ['danger', '❌ Không thể xóa tài khoản do đang được sử dụng!'],
    'invalid' => ['danger', '❌ Yêu cầu không hợp lệ!']
];

$msg = $_GET['msg'] ?? '';
if (!empty($msg) && isset($alerts[$msg])) {
    [$type, $text] = $alerts[$msg];
    echo "<div class='alert alert-$type'>$text</div>";
}
?>

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Quản lý tài khoản</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAccountModal">Tạo tài khoản</button>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Danh sách tài khoản</strong>
      <div>
        <button class="btn btn-sm btn-outline-secondary me-1" onclick="toggleCardBody(this)" title="Thu gọn">
          <i class="bi bi-dash"></i>
        </button>
        <button class="btn btn-sm btn-outline-danger" onclick="closeCard(this)" title="Đóng">
          <i class="bi bi-x"></i>
        </button>
      </div>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-bordered align-middle table-hover" id="accountTable">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Họ tên</th>
            <th>Số điện thoại</th>
            <th>CCCD</th>
            <th>Tài khoản</th>
            <th>Quyền</th>
            <th>Mô tả</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($accounts && $accounts->num_rows > 0): ?>
            <?php $i = 1; while ($row = $accounts->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['HoTen']) ?></td>
                <td><?= htmlspecialchars($row['SoDienThoai']) ?></td>
                <td><?= htmlspecialchars($row['CCCD']) ?></td>
                <td><?= htmlspecialchars($row['TaiKhoan']) ?></td>
                <td>
                  <?php if ($row['Quyen'] === 'Admin'): ?>
                    <span class='badge bg-success'><i class='bi bi-patch-check-fill me-1'></i><?= $row['Quyen'] ?></span>
                  <?php else: ?>
                    <span class='badge bg-primary'><i class='bi bi-person-workspace me-1'></i><?= $row['Quyen'] ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                    $desc = htmlspecialchars($row['MoTa']);
                    $descBadge = match ($desc) {
                      'Quản trị viên' => "<span class='badge bg-danger'>$desc</span>",
                      'Nhập liệu'    => "<span class='badge bg-warning text-dark'>$desc</span>",
                      'Kiểm tra'     => "<span class='badge bg-info text-dark'>$desc</span>",
                      'Import'       => "<span class='badge bg-secondary'>$desc</span>",
                      default        => "<span class='badge bg-pink'>$desc</span>",
                    };
                    echo $descBadge;
                  ?>
                </td>
                <td class='action-btns'>
                  <a href='#' onclick='editAccount(<?= json_encode($row) ?>)'><i class='bi bi-pencil-fill text-warning'></i></a>
                  <a href='delete_taikhoan.php?id=<?= $row["ID"] ?>' onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này không?')">
                    <i class='bi bi-trash-fill text-danger'></i>
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan='8' class='text-center'>Không có dữ liệu</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include './add_taikhoan.php'; ?>
<?php include './edit_taikhoan.php'; ?>
<?php include '../layouts/scripts.php'; ?>
<?php include '../layouts/footer.php'; ?>
