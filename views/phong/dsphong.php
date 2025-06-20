<?php
ob_start(); // Bật output buffering để tránh lỗi header
include '../layouts/header.php'; 

// Kết nối cơ sở dữ liệu
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "qlsohoa";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Lấy danh sách phông từ bảng phong
try {
    $stmt = $conn->prepare("SELECT ID, MaPhong, TenPhong FROM phong ORDER BY ID ASC");
    $stmt->execute();
    $phongList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalRecords = count($phongList);
} catch (PDOException $e) {
    die("Truy vấn thất bại: " . $e->getMessage());
}

// Chống cache để đảm bảo danh sách luôn mới
header("Cache-Control: no-cache, must-revalidate");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Danh mục phông</title>
</head>
<body>
<div class="container-fluid px-4">
  <?php
  // Hiển thị thông báo từ add_phong.php hoặc edit_phong.php
  if (isset($_GET['status'])) {
      if ($_GET['status'] === 'add_success') {
          echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                  Thêm phông thành công!
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
      } elseif ($_GET['status'] === 'edit_success') {
          echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                  Sửa phông thành công!
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
      } elseif ($_GET['status'] === 'error' && isset($_GET['errors'])) {
          $errors = explode('|', urldecode($_GET['errors']));
          foreach ($errors as $error) {
              echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                      $error
                      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
          }
      }
  }
  ?>

  <div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Danh mục phông</h5>
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPhongModal">
        <i class="bi bi-plus-circle"></i> Thêm phông mới
      </button>
    </div>

    <div class="card-body">
      <div class="mb-3 row justify-content-end">
        <label for="searchBox" class="col-sm-auto col-form-label">Tìm kiếm:</label>
        <div class="col-sm-4">
          <input type="text" id="searchBox" class="form-control form-control-sm" placeholder="Nhập từ khóa...">
        </div>
      </div>

      <table id="danhmucTable" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Mã phông</th>
            <th>Tên phông</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($totalRecords > 0): ?>
            <?php $index = 1; ?>
            <?php foreach ($phongList as $phong): ?>
              <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo htmlspecialchars($phong['MaPhong']); ?></td>
                <td><?php echo htmlspecialchars($phong['TenPhong']); ?></td>
                <td>
                  <button type="button" class="btn btn-sm btn-primary edit-btn" data-bs-toggle="modal" data-bs-target="#editPhongModal" data-id="<?php echo $phong['ID']; ?>" data-maphong="<?php echo htmlspecialchars($phong['MaPhong']); ?>" data-tenphong="<?php echo htmlspecialchars($phong['TenPhong']); ?>">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $phong['ID']; ?>">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center">Không có dữ liệu</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <p class="mt-3" id="recordInfo">Hiển thị 1 - <?php echo $totalRecords; ?> của <?php echo $totalRecords; ?> phông</p>
    </div>
  </div>
</div>

<!-- Modal for adding new room -->
<div class="modal fade" id="addPhongModal" tabindex="-1" aria-labelledby="addPhongModalLabel" aria-label="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addPhongModalLabel">Thêm phông mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="add_phong.php">
          <div class="mb-3">
            <label for="maPhong" class="form-label">Mã phông *</label>
            <input type="text" class="form-control" id="maPhong" name="maPhong" required>
          </div>
          <div class="mb-3">
            <label for="tenPhong" class="form-label">Tên phông *</label>
            <input type="text" class="form-control" id="tenPhong" name="tenPhong" required>
          </div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-primary" name="add_phong">Thêm</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal for editing room -->
<div class="modal fade" id="editPhongModal" tabindex="-1" aria-labelledby="editPhongModalLabel" aria-label="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editPhongModalLabel">Sửa phông</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="edit_phong.php">
          <input type="hidden" id="editId" name="id">
          <div class="mb-3">
            <label for="editMaPhong" class="form-label">Mã phông *</label>
            <input type="text" class="form-control" id="editMaPhong" name="maPhong" required>
          </div>
          <div class="mb-3">
            <label for="editTenPhong" class="form-label">Tên phông *</label>
            <input type="text" class="form-control" id="editTenPhong" name="tenPhong" required>
          </div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-primary" name="edit_phong">Lưu</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal confirm delete -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-label="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteModalLabel">Xác nhận xóa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Bạn có chắc chắn muốn xóa phông này?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  // Handle search
  const searchInput = document.getElementById('searchBox');
  searchInput.addEventListener('input', function () {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll("#danhmucTable tbody tr");
    let visibleCount = 0;
    rows.forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(value) ? '' : 'none';
      if (text.includes(value)) visibleCount++;
    });
    document.getElementById('recordInfo').innerText = `Hiển thị 1 - ${visibleCount} của ${rows.length} phông`;
  });

  // Handle edit modal
  document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
      const id = this.getAttribute('data-id');
      const maPhong = this.getAttribute('data-maphong');
      const tenPhong = this.getAttribute('data-tenphong');
      document.getElementById('editId').value = id;
      document.getElementById('editMaPhong').value = maPhong;
      document.getElementById('editTenPhong').value = tenPhong;
      console.log('Modal trigger clicked (Edit) with ID: ' + id + ', MaPhong: ' + maPhong + ', TenPhong: ' + tenPhong);
    });
  });

  // Debugging: Check if add modal is triggered
  document.querySelector('[data-bs-target="#addPhongModal"]').addEventListener('click', function() {
    console.log('Modal trigger clicked (Add)');
  });

  // Handle delete button
  document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
      const id = this.getAttribute('data-id');
      $('#confirmDeleteModal').modal('show');
      $('#confirmDeleteBtn').off('click').on('click', function() {
        $.ajax({
          url: 'delete_phong.php',
          type: 'POST',
          data: { id: id },
          success: function(response) {
            if (response === 'success') {
              $('#confirmDeleteModal').modal('hide');
              const row = $(button).closest('tr');
              row.remove();
              $('.container-fluid').prepend(`
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  Xóa phông thành công!
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              `);
              setTimeout(() => $('.alert-success').alert('close'), 3000); // Tự động ẩn sau 3 giây
              // Update record info
              const rows = document.querySelectorAll("#danhmucTable tbody tr");
              const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
              document.getElementById('recordInfo').innerText = `Hiển thị 1 - ${visibleRows.length} của ${rows.length} phông`;
            } else {
              $('.container-fluid').prepend(`
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  Xóa thất bại: ${response}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              `);
              setTimeout(() => $('.alert-danger').alert('close'), 3000);
            }
          },
          error: function(xhr, status, error) {
            console.log('AJAX Error:', xhr.responseText, status, error);
            $('.container-fluid').prepend(`
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Lỗi: ${xhr.responseText || error}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            `);
            setTimeout(() => $('.alert-danger').alert('close'), 3000);
          }
        });
      });
    });
  });
</script>
</body>
</html>
<?php ob_end_flush(); ?>
