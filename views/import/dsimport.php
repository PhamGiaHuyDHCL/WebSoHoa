<?php
include '../layouts/header.php';
require_once '../../models/ImportModel.php';

$model = new ImportModel();
$phongs = $model->getAllPhong();

// Xử lý xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids'])) {
    $deleteIds = array_map('intval', $_POST['delete_ids']);
    if (!empty($deleteIds)) {
        $model->deleteByIds($deleteIds);
        header("Location: dsimport.php?msg=" . urlencode("Đã xóa các bản ghi thành công"));
        exit;
    }
}

// Lọc và phân trang
$filters = [
    'khoi' => $_GET['khoi'] ?? '',
    'phong' => $_GET['phong'] ?? '',
    'ma_muc_luc' => $_GET['ma_muc_luc'] ?? '',
    'khoa' => $_GET['khoa'] ?? '',
    'hop_ho_so' => $_GET['hop_ho_so'] ?? '',
    'search' => $_GET['search'] ?? '',
    'sortBy' => $_GET['sortBy'] ?? 'id',
    'sortDir' => $_GET['sortDir'] ?? 'DESC',
    'page' => max(1, (int)($_GET['page'] ?? 1)),
    'limit' => 10,
];

$offset = ($filters['page'] - 1) * $filters['limit'];
$data = $model->getFilteredData(
    $filters['limit'], $offset, $filters['search'], $filters['hop_ho_so'],
    $filters['khoa'], $filters['khoi'], $filters['phong'], $filters['ma_muc_luc'],
    $filters['sortBy'], $filters['sortDir']
);
$total = $model->countFiltered(
    $filters['search'], $filters['hop_ho_so'], $filters['khoa'],
    $filters['khoi'], $filters['phong'], $filters['ma_muc_luc']
);
$totalPages = ceil($total / $filters['limit']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Danh sách file PDF import</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="p-4">
  <h3>📂 Danh sách file PDF import</h3>

  <?php foreach (['msg', 'upload_success', 'upload_error'] as $type): ?>
    <?php if (!empty($_GET[$type]) || !empty($_SESSION[$type])): ?>
      <div class="alert alert-<?= $type === 'upload_error' ? 'danger' : 'success' ?>">
        <?= htmlspecialchars($_GET[$type] ?? $_SESSION[$type]) ?>
        <?php unset($_SESSION[$type]); ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <!-- Bộ lọc -->
  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-2">
      <label class="form-label">Khối</label>
      <select name="khoi" class="form-select">
        <option value="">Tất cả</option>
        <option value="2" <?= $filters['khoi'] === '2' ? 'selected' : '' ?>>Đảng</option>
        <option value="1" <?= $filters['khoi'] === '1' ? 'selected' : '' ?>>Chính quyền</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Phòng</label>
      <select name="phong" class="form-select">
        <option value="">Tất cả phòng</option>
        <?php foreach ($phongs as $p): ?>
          <option value="<?= (int)$p['id'] ?>" <?= $filters['phong'] == $p['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($p['tenphong']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><label class="form-label">Mã mục lục</label><input name="ma_muc_luc" value="<?= htmlspecialchars($filters['ma_muc_luc']) ?>" class="form-control" /></div>
    <div class="col-md-2"><label class="form-label">Khóa</label><input name="khoa" value="<?= htmlspecialchars($filters['khoa']) ?>" class="form-control" /></div>
    <div class="col-md-2"><label class="form-label">Hộp hồ sơ</label><input name="hop_ho_so" value="<?= htmlspecialchars($filters['hop_ho_so']) ?>" class="form-control" /></div>
    <div class="col-md-2"><label class="form-label">Từ khóa</label><input name="search" value="<?= htmlspecialchars($filters['search']) ?>" class="form-control" /></div>
    <div class="col-md-12 text-end mt-2">
      <button class="btn btn-primary">Lọc</button>
      <a href="dsimport.php" class="btn btn-secondary">Xóa lọc</a>
    </div>
  </form>

  <!-- Import -->
  <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#importModal">📤 Import hộp hồ sơ</button>

  <!-- Danh sách -->
  <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa các mục đã chọn?');">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th><input type="checkbox" id="checkAll" /></th>
          <th>#</th>
          <th>Khối</th>
          <th>Phòng</th>
          <th>Mã mục lục</th>
          <th>Hộp</th>
          <th>Khóa</th>
          <th>Tài liệu</th>
          <th>Người scan</th>
          <th>Người nhập liệu</th>
          <th>Ngày nhập</th>
          <th>Trạng thái</th>
          <th>Người sửa</th>
          <th>Ngày sửa</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($data)): ?>
          <tr><td colspan="14" class="text-center">Không có dữ liệu</td></tr>
        <?php else: ?>
          <?php foreach ($data as $d): ?>
            <tr class="clickable-row" data-path="<?= htmlspecialchars($d['path']) ?>">
              <td><input type="checkbox" name="delete_ids[]" value="<?= (int)$d['id'] ?>" onclick="event.stopPropagation();" /></td>
              <td><?= (int)$d['id'] ?></td>
              <td><?= $d['khoi'] == 1 ? 'Chính quyền' : ($d['khoi'] == 2 ? 'Đảng' : '') ?></td>
              <td><?= htmlspecialchars($d['tenphong']) ?></td>
              <td><?= htmlspecialchars($d['id_mucluc']) ?></td>
              <td><?= htmlspecialchars($d['hop_ho_so']) ?></td>
              <td><?= htmlspecialchars($d['khoa']) ?></td>
              <td><?= htmlspecialchars($d['folder_name']) ?></td>
              <td><?= htmlspecialchars($d['scan_user']) ?></td>
              <td><?= htmlspecialchars($d['dataentry_user']) ?></td>
              <td><?= htmlspecialchars($d['ngay_nhap']) ?></td>
              <td><?= $d['dataentry_status'] == 1 ? 'Đã nhập' : 'Chưa nhập' ?></td>
              <td><?= htmlspecialchars($d['id_nguoisua']) ?></td>
              <td><?= htmlspecialchars($d['ngay_sua']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
    <?php if (!empty($data)): ?>
      <button class="btn btn-danger">🗑 Xóa các mục đã chọn</button>
    <?php endif; ?>
  </form>

  <!-- Phân trang -->
  <?php if ($totalPages > 1): ?>
    <nav class="mt-3">
      <ul class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= $i == $filters['page'] ? 'active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>

  <!-- Modal Import -->
  <div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
      <form action="../../controllers/ImportController.php" method="POST" enctype="multipart/form-data" class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Import hộp hồ sơ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Khối</label>
            <select name="khoi" class="form-select" required>
              <option value="">Chọn khối</option>
              <option value="2">Đảng</option>
              <option value="1">Chính quyền</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Phòng</label>
            <select name="phong" class="form-select" required>
              <option value="">Chọn phòng</option>
              <?php foreach ($phongs as $p): ?>
                <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['tenphong']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Mã mục lục</label><input type="text" name="ma_muc_luc" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Khóa</label><input type="text" name="khoa" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Hộp số</label><input type="text" name="hop_ho_so" class="form-control" required></div>
          <div class="mb-3">
            <label class="form-label">Chọn thư mục PDF</label>
            <input type="file" name="pdf_files[]" class="form-control" multiple webkitdirectory directory required>
            <small class="text-muted">Chọn thư mục chứa các file PDF.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Import</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal xem PDF -->
  <div class="modal fade" id="pdfPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">📄 Xem tài liệu PDF</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <iframe id="pdfViewerModal" src="" width="100%" height="600px" style="border: none;"></iframe>
        </div>
      </div>
    </div>
  </div>

  <!-- Script -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('checkAll').addEventListener('change', function () {
      document.querySelectorAll('input[name="delete_ids[]"]').forEach(cb => cb.checked = this.checked);
    });

    document.querySelectorAll('.clickable-row').forEach(row => {
      row.addEventListener('click', function () {
        const relativePath = this.getAttribute('data-path');
        if (!relativePath || !relativePath.toLowerCase().endsWith(".pdf")) {
          alert("Không phải file PDF hợp lệ.");
          return;
        }
        document.getElementById('pdfViewerModal').src = '../../' + relativePath;
        new bootstrap.Modal(document.getElementById('pdfPreviewModal')).show();
      });
    });

    document.getElementById('pdfPreviewModal').addEventListener('hidden.bs.modal', function () {
      document.getElementById('pdfViewerModal').src = '';
    });
  </script>
</body>
</html>
