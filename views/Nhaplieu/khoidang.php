<?php
include '../layouts/header.php';
require_once '../../models/KhoiDangModel.php';

$model = new KhoiDangModel();

// Lấy danh sách file đã scan
$scan = $model->getScanHoSoList();

// Danh mục khác
$phong    = $model->getPhong();
$mucluc   = $model->getMucLuc();
$dvbq     = $model->getDonViBaoQuan();
$doMat    = $model->getDoMat();
$theLoai  = $model->getTheLoai();

// Lấy file được chọn
$selectedFilePath = $_GET['file'] ?? '';
$selectedScanId = null;
$mucLucInfo = null;

foreach ($scan as $item) {
    if ($item['path'] === $selectedFilePath) {
        $selectedScanId = $item['id'];
        $mucLucInfo = $item;
        break;
    }
}
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between p-3 border-bottom">
    <div>
      <form method="get">
        <input type="hidden" name="controller" value="khoidang">
        <select name="file" class="form-select d-inline w-auto" onchange="this.form.submit()">
          <option value="">-- Chọn file PDF --</option>
          <?php foreach ($scan as $file): ?>
            <option value="<?= $file['path'] ?>" <?= ($file['path'] === $selectedFilePath) ? 'selected' : '' ?>>
              <?= $file['folder_name'] ?> <?= $file['dataentry_status'] == 2 ? '✅' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
    <div>
      <button class="btn btn-outline-primary btn-sm" disabled>Mở mục lục</button>
    </div>
  </div>

  <div class="row g-0">
    <!-- PDF Viewer -->
    <div class="col-md-8 border-end p-3" style="height: 90vh; overflow-y: auto;">
      <?php if ($selectedFilePath): ?>
        <iframe src="/websohoa/<?= $selectedFilePath ?>" style="width: 100%; height: 100%;" frameborder="0"></iframe>
      <?php else: ?>
        <div class="alert alert-info">Vui lòng chọn file PDF để nhập liệu.</div>
      <?php endif; ?>
    </div>

    <!-- Form nhập liệu -->
    <div class="col-md-4 p-3">
      <div class="card">
        <div class="card-header"><strong>Nhập liệu</strong></div>
        <div class="card-body">
          <?php if ($selectedFilePath): ?>
          <form method="post">
            <input type="hidden" name="ten_taptin" value="<?= basename($selectedFilePath) ?>">
            <input type="hidden" name="scan_vanban_Id" value="<?= $selectedScanId ?>">

            <div class="row mb-2">
              <div class="col"><label class="form-label">Mã phòng </label><input name="id_phong" class="form-control" required></div>
              <div class="col"><label class="form-label">Mã mục lục *</label><input name="id_mucluc" class="form-control" required></div>
              <div class="col"><label class="form-label">Mã ĐVBQ *</label><input name="id_dvbq" class="form-control" required></div>
              <div class="col"><label class="form-label">Mã hồ sơ *</label><input name="ma_hoso" class="form-control" required></div>
            </div>

            <div class="mb-2">
              <label class="form-label">Số văn bản</label>
              <input type="text" name="so_vanban" class="form-control">
            </div>

            <div class="mb-2">
              <label class="form-label">Trích yếu</label>
              <textarea name="trich_yeu" class="form-control" rows="2"></textarea>
            </div>

            <div class="row mb-2">
              <div class="col">
                <label class="form-label">Ngày tháng năm văn bản</label>
                <input type="date" name="ngay_thang_nam_vanban" class="form-control">
              </div>
              <div class="col">
                <label class="form-label">Độ mật</label>
                <select name="id_do_mat" class="form-select">
                  <option value="">-- Độ mật --</option>
                  <?php foreach ($doMat as $dm): ?>
                    <option value="<?= $dm['id'] ?>"><?= $dm['ten'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label">Tác giả văn bản</label>
              <input type="text" name="tacgia_vanban" class="form-control">
            </div>

            <div class="row mb-2">
              <div class="col">
                <label class="form-label">Thể loại văn bản</label>
                <select name="id_theloaivanban_fk" class="form-select">
                  <option value="">-- Thể loại --</option>
                  <?php foreach ($theLoai as $tl): ?>
                    <option value="<?= $tl['id'] ?>"><?= $tl['ten'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col">
                <label class="form-label">Số trang</label>
                <input type="number" name="sotrang_vanban" class="form-control" value="1">
              </div>
              <div class="col">
                <label class="form-label">Số thứ tự</label>
                <input type="text" name="so_thutu" class="form-control">
              </div>
            </div>

            <div class="row mb-2">
              <div class="col">
                <label class="form-label">Người ký</label>
                <input type="text" name="nguoi_ky" class="form-control">
              </div>
              <div class="col">
                <label class="form-label">Trang số</label>
                <input type="text" name="trang_so" class="form-control">
              </div>
            </div>

            <div class="text-end mt-3">
              <button type="submit" class="btn btn-success">💾 Lưu</button>
            </div>
          </form>
          <?php else: ?>
            <div class="alert alert-warning">Vui lòng chọn file PDF trước khi nhập liệu.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../layouts/footer.php'; ?>
