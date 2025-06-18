<?php include '../layouts/header.php'; ?>
<?php include '../../config/dbadmin.php'; 
checkAdmin(); ?>

<?php
// Kết nối CSDL
$conn = new mysqli('localhost', 'root', '', 'qlsohoa');
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("❌ Lỗi kết nối CSDL: " . $conn->connect_error);
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ Xử lý CHỈNH SỬA tài khoản
    if (isset($_POST['edit_id'])) {
        $id = (int)$_POST['edit_id'];
        $hoten = trim($_POST['hoten']);
        $sdt = trim($_POST['sdt']);
        $cccd = trim($_POST['cccd']);
        $taikhoan = trim($_POST['taikhoan']);
        $idphanquyen = (int)$_POST['quyen'];
        $new_password = !empty($_POST['new_password']) ? $_POST['new_password'] : null;

        if (!$hoten || !$sdt || !$cccd || !$taikhoan || !$idphanquyen) {
            $msg = 'missing';
        } elseif (!preg_match('/^\d{10}$/', $sdt)) {
            $msg = 'invalid_sdt';
        } elseif (!preg_match('/^\d{12}$/', $cccd)) {
            $msg = 'invalid_cccd';
        } else {
            $conn->begin_transaction();
            try {
                // Kiểm tra CCCD trùng (trừ tài khoản hiện tại)
                $stmt = $conn->prepare("SELECT ID FROM nhanvien WHERE CCCD = ? AND ID != (SELECT IDNhanVien FROM taikhoan WHERE ID = ?)");
                $stmt->bind_param("si", $cccd, $id);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $msg = 'cccd_exist';
                } else {
                    // Cập nhật thông tin nhân viên
                    $stmt_nv = $conn->prepare("UPDATE nhanvien SET HoTen = ?, SoDienThoai = ?, CCCD = ? WHERE ID = (SELECT IDNhanVien FROM taikhoan WHERE ID = ?)");
                    $stmt_nv->bind_param("sssi", $hoten, $sdt, $cccd, $id);
                    $stmt_nv->execute();
                    $stmt_nv->close();

                    // Cập nhật tài khoản
                    if ($new_password) {
                        $hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt_tk = $conn->prepare("UPDATE taikhoan SET TaiKhoan = ?, MatKhau = ?, IDPhanQuyen = ? WHERE ID = ?");
                        $stmt_tk->bind_param("ssii", $taikhoan, $hash, $idphanquyen, $id);
                    } else {
                        $stmt_tk = $conn->prepare("UPDATE taikhoan SET TaiKhoan = ?, IDPhanQuyen = ? WHERE ID = ?");
                        $stmt_tk->bind_param("sii", $taikhoan, $idphanquyen, $id);
                    }
                    $stmt_tk->execute();
                    $stmt_tk->close();

                    $conn->commit();
                    $msg = 'edit_success';
                }
                $stmt->close();
            } catch (Exception $e) {
                $conn->rollback();
                $msg = 'edit_fail';
            }
        }
    // ✅ Xử lý THÊM tài khoản
    } elseif (
        isset($_POST['hoten'], $_POST['sdt'], $_POST['cccd'], $_POST['taikhoan'], $_POST['matkhau'], $_POST['quyen'])
    ) {
        $hoten = trim($_POST['hoten']);
        $sdt = trim($_POST['sdt']);
        $cccd = trim($_POST['cccd']);
        $taikhoan = trim($_POST['taikhoan']);
        $matkhau = $_POST['matkhau'];
        $idphanquyen = (int)$_POST['quyen'];

        if (!$hoten || !$sdt || !$cccd || !$taikhoan || !$matkhau || !$idphanquyen) {
            $msg = 'missing';
        } elseif (!preg_match('/^\d{10}$/', $sdt)) {
            $msg = 'invalid_sdt';
        } elseif (!preg_match('/^\d{12}$/', $cccd)) {
            $msg = 'invalid_cccd';
        } else {
            $stmt = $conn->prepare("SELECT ID FROM nhanvien WHERE CCCD = ?");
            $stmt->bind_param("s", $cccd);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $msg = 'cccd_exist';
            } else {
                $stmt->close();
                $conn->begin_transaction();
                try {
                    $stmt_nv = $conn->prepare("INSERT INTO nhanvien (HoTen, SoDienThoai, CCCD) VALUES (?, ?, ?)");
                    $stmt_nv->bind_param("sss", $hoten, $sdt, $cccd);
                    $stmt_nv->execute();
                    $idnv = $conn->insert_id;
                    $stmt_nv->close();

                    $matkhau_hash = password_hash($matkhau, PASSWORD_DEFAULT);
                    $stmt_tk = $conn->prepare("INSERT INTO taikhoan (TaiKhoan, MatKhau, IDPhanQuyen, IDNhanVien) VALUES (?, ?, ?, ?)");
                    $stmt_tk->bind_param("ssii", $taikhoan, $matkhau_hash, $idphanquyen, $idnv);
                    $stmt_tk->execute();
                    $stmt_tk->close();

                    $conn->commit();
                    $msg = 'success';
                } catch (Exception $e) {
                    $conn->rollback();
                    $msg = 'error';
                }
            }
        }
    }
}

// Lấy danh sách quyền
$roles = [];
$res_roles = $conn->query("SELECT ID, role_name FROM phanquyen");
while ($r = $res_roles->fetch_assoc()) {
    $roles[] = $r;
}

// Lấy danh sách tài khoản
$sql = "
    SELECT tk.ID, tk.TaiKhoan, nv.HoTen, nv.CCCD, nv.SoDienThoai, pq.role_name AS Quyen, pq.MoTa
    FROM taikhoan tk
    JOIN phanquyen pq ON pq.ID = tk.IDPhanQuyen
    JOIN nhanvien nv ON nv.ID = tk.IDNhanVien
";
$result = $conn->query($sql);
?>

<style>
  .action-btns i {
    font-size: 1.2rem;
    cursor: pointer;
    margin-right: 10px;
  }
  .badge.bg-pink {
    background-color: #f78fb3;
    color: #000 !important;
  }
</style>

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Quản lý tài khoản</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAccountModal">Tạo tài khoản</button>
  </div>

  <?php if ($msg): ?>
    <?php
    $alerts = [
      'success' => ['success', ' Tạo tài khoản thành công!'],
      'cccd_exist' => ['warning', '⚠️ CCCD đã tồn tại trong hệ thống!'],
      'invalid_sdt' => ['warning', '⚠️ Số điện thoại phải đúng 10 chữ số!'],
      'invalid_cccd' => ['warning', '⚠️ CCCD phải đúng 12 chữ số!'],
      'missing' => ['danger', '❌ Vui lòng điền đầy đủ thông tin!'],
      'error' => ['danger', '❌ Lỗi xảy ra, vui lòng thử lại.'],
      'edit_success' => ['success', '✔️ Chỉnh sửa tài khoản thành công!'],
      'edit_fail' => ['danger', '❌ Lỗi khi chỉnh sửa tài khoản!']
    ];
    list($type, $text) = $alerts[$msg];
    ?>
    <div class="alert alert-<?= $type ?>"><?= $text ?></div>
  <?php endif; ?>

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
          <?php
          if ($result && $result->num_rows > 0) {
              $index = 1;
              while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . $index++ . "</td>";
                  echo "<td>" . htmlspecialchars($row['HoTen']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['SoDienThoai']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['CCCD']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['TaiKhoan']) . "</td>";

                  $role = htmlspecialchars($row['Quyen']);
                  $badge = ($role === 'Admin')
                      ? "<span class='badge bg-success'><i class='bi bi-patch-check-fill me-1'></i>$role</span>"
                      : "<span class='badge bg-primary'><i class='bi bi-person-workspace me-1'></i>$role</span>";
                  echo "<td>$badge</td>";

                  $desc = htmlspecialchars($row['MoTa']);
                  switch ($desc) {
                      case 'Quản trị viên': $descBadge = "<span class='badge bg-danger'>$desc</span>"; break;
                      case 'Nhập liệu':    $descBadge = "<span class='badge bg-warning text-dark'>$desc</span>"; break;
                      case 'Kiểm tra':     $descBadge = "<span class='badge bg-info text-dark'>$desc</span>"; break;
                      case 'Import':       $descBadge = "<span class='badge bg-secondary'>$desc</span>"; break;
                      default:             $descBadge = "<span class='badge bg-pink'>$desc</span>";
                  }

                  echo "<td>$descBadge</td>";
                  echo "<td class='action-btns'>
                          <a href='#' onclick='editAccount(" . json_encode($row) . ")'><i class='bi bi-pencil-fill text-warning'></i></a>
                          <a href='delete_taikhoan.php?id={$row["ID"]}' onclick=\"return confirm('Bạn có chắc chắn muốn xóa tài khoản này không?')\">
                            <i class='bi bi-trash-fill text-danger'></i>
                          </a>
                        </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='8' class='text-center'>Không có dữ liệu</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal thêm tài khoản -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="addAccountModalLabel">Thêm tài khoản mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-4"><label>Họ tên *</label><input name="hoten" class="form-control" required></div>
            <div class="col-md-4"><label>Số điện thoại *</label><input name="sdt" class="form-control" required pattern="\d{10}"></div>
            <div class="col-md-4"><label>Số CCCD *</label><input name="cccd" class="form-control" required pattern="\d{12}"></div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4"><label>Tài khoản *</label><input name="taikhoan" class="form-control" required></div>
            <div class="col-md-4"><label>Mật khẩu *</label><input name="matkhau" type="password" class="form-control" required></div>
            <div class="col-md-4">
              <label>Quyền *</label>
              <select name="quyen" class="form-select" required>
                <option value="">-- Chọn quyền --</option>
                <?php foreach ($roles as $r): ?>
                  <option value="<?= $r['ID'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Lưu</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal chỉnh sửa tài khoản -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="editAccountForm">
        <div class="modal-header">
          <h5 class="modal-title" id="editAccountModalLabel">Chỉnh sửa tài khoản</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="editUserId">
          <div class="row mb-3">
            <div class="col-md-4">
              <label>Họ tên *</label>
              <input name="hoten" id="editHoTen" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label>Số điện thoại *</label>
              <input name="sdt" id="editSdt" class="form-control" required pattern="\d{10}">
            </div>
            <div class="col-md-4">
              <label>Số CCCD *</label>
              <input name="cccd" id="editCccd" class="form-control" required pattern="\d{12}">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4">
              <label>Tài khoản *</label>
              <input name="taikhoan" id="editTaiKhoan" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label>Mật khẩu mới (để trống nếu không đổi)</label>
              <input name="new_password" type="password" id="editMatKhau" class="form-control">
            </div>
            <div class="col-md-4">
              <label>Quyền *</label>
              <select name="quyen" id="editQuyen" class="form-select" required>
                <option value="">-- Chọn quyền --</option>
                <?php foreach ($roles as $r): ?>
                  <option value="<?= $r['ID'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Cập nhật</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- External Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
function editAccount(account) {
    document.getElementById('editUserId').value = account.ID;
    document.getElementById('editHoTen').value = account.HoTen;
    document.getElementById('editSdt').value = account.SoDienThoai;
    document.getElementById('editCccd').value = account.CCCD;
    document.getElementById('editTaiKhoan').value = account.TaiKhoan;
    document.getElementById('editQuyen').value = <?php echo json_encode(array_column($roles, 'ID', 'role_name')); ?>[account.Quyen];
    document.getElementById('editMatKhau').value = '';
    const modal = new bootstrap.Modal(document.getElementById('editAccountModal'));
    modal.show();
}
</script>

<script>
$(document).ready(function () {
  $('#accountTable').DataTable({
    language: {
      zeroRecords: 'Không tìm thấy dữ liệu',
      info: 'Hiển thị _START_ đến _END_ trong _TOTAL_ dòng',
      infoEmpty: 'Không có dữ liệu',
      infoFiltered: '(lọc từ _MAX_ tổng dòng)',
      paginate: {
        first: "Đầu",
        last: "Cuối",
        next: "›",
        previous: "‹"
      },
    },
    paging: true,
    ordering: true,
    info: true,
    lengthChange: false,
    searching: true
  });
});
</script>

<script>
function toggleCardBody(btn) {
    const card = btn.closest('.card');
    const body = card.querySelector('.card-body');
    const icon = btn.querySelector('i');

    if (body.style.display === 'none') {
      body.style.display = '';
      icon.classList.remove('bi-plus');
      icon.classList.add('bi-dash');
    } else {
      body.style.display = 'none';
      icon.classList.remove('bi-dash');
      icon.classList.add('bi-plus');
    }
}

function closeCard(btn) {
    const card = btn.closest('.card');
    card.style.display = 'none';
}
</script>

<?php $conn->close(); ?>