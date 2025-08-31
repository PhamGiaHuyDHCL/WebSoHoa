<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../models/KhoiDangModel.php';
include_once '../../controllers/KhoiDangController.php';

$model = new KhoiDangModel();

// === Auto lấy file chưa nhập theo thư mục ===
if (!isset($_GET['file'])) {
    if (isset($_SESSION['current_file_path']) && file_exists($_SERVER['DOCUMENT_ROOT'] . "/websohoa1/" . $_SESSION['current_file_path'])) {
        header("Location: ?controller=khoidang&file=" . urlencode($_SESSION['current_file_path']));
        exit;
    }

    $userId = $_SESSION['taikhoan_id'] ?? null;
    $lastPath = $_SESSION['last_path'] ?? null;

    if (!$lastPath || !$model->isFolderAvailable($lastPath)) {
        $lastPath = $model->getAvailableFolderPath();
        $_SESSION['last_path'] = $lastPath;
    }

    if ($lastPath) {
        $file = $model->getNextUnprocessedFileInFolder($lastPath);
        if ($file) {
            $_SESSION['current_file_path'] = $file['path'];
            header("Location: ?controller=khoidang&file=" . urlencode($file['path']));
            exit;
        }
    }
}


include_once '../layouts/header.php';

$scan = $model->getScanHoSoList();
$phong = $model->getPhong();
$mucluc = $model->getMucLuc();
$dvbq = $model->getDonViBaoQuan();
$doMat = $model->getDoMat();
$theLoai = $model->getTheLoai();

$selectedFilePath = $_GET['file'] ?? '';
$selectedScanId = null;
$mucLucInfo = null;
if ($selectedFilePath) {
    $_SESSION['current_file_path'] = $selectedFilePath;

    foreach ($scan as $file) {
        if ($file['path'] === $selectedFilePath) {
            if ($file['dataentry_status'] != 2) {
                $model->markDataentryStatusAsEditing($file['id'], $_SESSION['taikhoan_id']);
            }
            // ➕ THÊM LẠI 2 DÒNG NÀY
            $selectedScanId = $file['id'];
            $mucLucInfo = $file;
            break;
        }
    }
}

$webPath = "/websohoa1/" . $selectedFilePath;
$filePath = $_SERVER['DOCUMENT_ROOT'] . $webPath;
$ext = strtolower(pathinfo($selectedFilePath, PATHINFO_EXTENSION));
?>

<style>
  html, body {
    height: 100%;
    margin: 0;
    overflow: hidden;
  }
  .container-fluid {
    height: calc(100vh - 80px);
    display: flex;
    flex-direction: column;
  }
  .main-row {
    flex: 1;
    overflow: hidden;
  }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://unpkg.com/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>

<div class="container-fluid">
  <div class="d-flex justify-content-between p-3 border-bottom bg-light">
    <div>
      <form method="get">
        <input type="hidden" name="controller" value="khoidang">
        <select name="file" class="form-select d-inline w-auto" onchange="this.form.submit()">
          <?php foreach ($scan as $file): ?>
            <?php 
              if ($file['khoi'] != '2') continue;

              // Nếu là file đang được chọn, cố gắng đánh dấu là đang nhập (hàm tự kiểm tra tránh chiếm của người khác)
              if ($file['path'] === $selectedFilePath) {
                  $model->markDataentryStatusAsEditing($file['id'], $_SESSION['taikhoan_id']);
              }
            ?>
            <option value="<?= htmlspecialchars($file['path']) ?>"
                    <?= ($file['path'] === $selectedFilePath ? 'selected' : '') ?>
                    <?= ($file['dataentry_status'] == 2 && $file['path'] !== $selectedFilePath ? 'disabled' : '') ?>>
              <?= $file['dataentry_status'] == 2 ? '✅' : '' ?> 
              <?= htmlspecialchars(basename($file['path'])) ?>
            </option>
          <?php endforeach; ?>

        </select>
      </form>
    </div>
    <div>
<?php if (!empty($selectedFilePath) && file_exists($filePath)): ?>
  <a href="<?= $webPath ?>" target="_blank" class="btn btn-outline-secondary btn-sm">📂 Mở mục lục</a>
<?php else: ?>
  <button class="btn btn-outline-secondary btn-sm" disabled>📂 Mở mục lục</button>
<?php endif; ?>
    </div>
  </div>

  <div class="row g-0 main-row">
    <div class="col-md-8 border-end p-0 position-relative" style="height: 100%;">
      <?php if ($selectedFilePath && file_exists($filePath)): ?>
        <?php if ($ext === 'pdf'): ?>
        <div id="file-viewer" style="width: 100%; height: 100%; overflow: hidden; position: relative;">
          <div style="position: absolute; top: 60px; left: 10px; z-index: 999; display: flex; flex-direction: column; gap: 8px;">
            <button onclick="pdfZoomIn()" class="btn btn-sm btn-dark">➕</button>
            <button onclick="pdfZoomOut()" class="btn btn-sm btn-dark">➖</button>
            <button onclick="pdfReset()" class="btn btn-sm btn-dark">🔄</button>
            <button onclick="pdfPrevPage()" class="btn btn-sm btn-dark">⬆</button>
            <button onclick="pdfNextPage()" class="btn btn-sm btn-dark">⬇</button>
          </div>
          <div style="width: 100%; height: 100%; overflow: hidden;">
            <canvas id="pdf-canvas" style="display: block; cursor: grab;"></canvas>
          </div>
        </div>
        <script>
          let pdfDoc = null, pageNum = 1, pdfScale = 1.25;
          const canvas = document.getElementById('pdf-canvas');
          const ctx = canvas.getContext('2d');

          function renderPage(num) {
            pdfDoc.getPage(num).then(page => {
              const viewport = page.getViewport({ scale: pdfScale });
              canvas.width = viewport.width;
              canvas.height = viewport.height;
              page.render({ canvasContext: ctx, viewport: viewport });
            });
          }

          function pdfZoomIn() { pdfScale += 0.25; renderPage(pageNum); }
          function pdfZoomOut() { pdfScale = Math.max(0.5, pdfScale - 0.25); renderPage(pageNum); }
          function pdfReset() { pdfScale = 1.25; renderPage(pageNum); }
          function pdfPrevPage() { if (pageNum > 1) { pageNum--; renderPage(pageNum); } }
          function pdfNextPage() { if (pageNum < pdfDoc.numPages) { pageNum++; renderPage(pageNum); } }

          const panzoom = Panzoom(canvas, { maxScale: 5, minScale: 0.5, contain: 'outside' });
          canvas.parentElement.addEventListener('wheel', panzoom.zoomWithWheel);
          canvas.addEventListener('mousedown', () => canvas.style.cursor = 'grabbing');
          canvas.addEventListener('mouseup', () => canvas.style.cursor = 'grab');

          pdfjsLib.getDocument("<?= $webPath ?>").promise.then(pdf => {
            pdfDoc = pdf;
            renderPage(pageNum);
          });
        </script>
        <?php else: ?>
          <div class="alert alert-warning m-3">File không hỗ trợ: <?= htmlspecialchars($ext) ?></div>
        <?php endif; ?>
      <?php else: ?>
        <div class="alert alert-info m-3">Vui lòng chọn file để hiển thị nội dung.</div>
      <?php endif; ?>
    </div>

<div class="col-md-4 p-3 d-flex flex-column" style="height: 100%; overflow: hidden;">
  <div class="card shadow-sm flex-grow-1 d-flex flex-column" style="overflow: hidden;">
    <div class="card-header bg-light"><strong>Nhập liệu</strong></div>
    <div class="card-body flex-grow-1 overflow-auto">

      <?php
      // Chưa chọn file
      if (!$selectedFilePath || !$selectedScanId): ?>
        <div class="alert alert-warning">Vui lòng chọn file để nhập liệu.</div>

      <?php
      // File đã hoàn tất nhập liệu
      elseif ($mucLucInfo['dataentry_status'] == 2): ?>
        <div class="alert alert-success">✅ Văn bản này đã được nhập liệu. Không thể chỉnh sửa thêm.</div>

      <?php
      // File đang được người khác nhập
      elseif (($editingUser = $model->getEditingUserByPath($mucLucInfo['path']))
              && $editingUser['taikhoan_id'] != ($_SESSION['taikhoan_id'] ?? null)): ?>
        <div class="alert alert-danger">
          ❗ File này đang được người khác nhập liệu (Tài khoản ID: <?= $editingUser['taikhoan_id'] ?>).
          Vui lòng chờ hoặc chọn file khác.
        </div>

      <?php
      // Các trường hợp còn lại – hiển thị form
      else:

        $fileName = basename($selectedFilePath);
        $isMLorKH = stripos($fileName, 'ML') !== false || stripos($fileName, 'KH') !== false;
        $isBHS    = stripos($fileName, 'BHS') !== false;
        ?>

        <form method="post"
              action="../../index.php?controller=khoidang&action=saveVanBan">
          <input type="hidden" name="ten_taptin" value="<?= $fileName ?>">
          <input type="hidden" name="scan_vanban_Id" value="<?= $selectedScanId ?>">

          <?php
          /*-------------------------------------------------
           * 1. HỒ SƠ (BHS)
           *-------------------------------------------------*/
          if ($isBHS): ?>

            <div class="row mb-2">
              <div class="col">
                <label>Mã đơn vị bảo quản *</label>
                <input name="id_dvbq" type="number" class="form-control" required>
              </div>
              <div class="col">
                <label>Mã hồ sơ *</label>
                <input name="ma_hoso" type="text" class="form-control" required>
              </div>
              <div class="col">
                <label>Mã phông *</label>
                <input name="id_phong" type="number" class="form-control" required>
              </div>
              <div class="col">
                <label>Mã mục lục *</label>
                <input name="id_mucluc" type="number" class="form-control" required>
              </div>
            </div>

            <div class="mb-2">
              <label>Tiêu đề hồ sơ</label>
              <textarea name="tieu_de" class="form-control" rows="2"></textarea>
            </div>

            <div class="row mb-2">
              <div class="col">
                <label>Số ký hiệu hồ sơ</label>
                <input type="text" name="so_kyhieu_hoso" class="form-control">
              </div>
              <div class="col">
                <label>Mã độ mật</label>
                <select name="id_do_mat" class="form-select">
                  <option value="">-- Độ mật --</option>
                  <?php foreach ($doMat as $dm): ?>
                    <option value="<?= $dm['ID'] ?>"><?= htmlspecialchars($dm['MaDoMat'].' - '.$dm['TenDoMat']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col">
                <label>Số lượng trang</label>
                <input type="number" name="so_luong_trang" class="form-control" value="1" min="1">
              </div>
            </div>

            <div class="row mb-2">
              <div class="col">
                <label>Thời gian bắt đầu</label>
                <input type="date" name="thoigian_batdau" class="form-control">
              </div>
              <div class="col">
                <label>Thời gian kết thúc</label>
                <input type="date" name="thoigian_ketthuc" class="form-control">
              </div>
              <div class="col">
                <label>Mã thời hạn bảo quản</label>
                <input type="number" name="id_thoihan_baoquan" class="form-control">
              </div>
            </div>

            <div class="row mb-2">
              <div class="col">
                <label>Tình trạng vật lý</label>
                <input type="number" name="id_tinhtrang_vatly" class="form-control">
              </div>
              <div class="col">
                <label>Mã chuyên đề</label>
                <input type="number" name="id_chuyen_de" class="form-control">
              </div>
              <div class="col">
                <label>Mã ngôn ngữ</label>
                <input type="number" name="id_ngon_ngu" class="form-control">
              </div>
            </div>

            <div class="mb-2">
              <label>Từ khóa hồ sơ</label>
              <textarea name="tu_khoa" class="form-control" rows="2"></textarea>
            </div>

            <div class="row mb-2">
              <div class="col">
                <label>Thời gian tài liệu</label>
                <input type="text" name="thoigian_tailieu" class="form-control">
              </div>
              <div class="col">
                <label>Mã hộp hồ sơ</label>
                <input type="text" name="hop_ho_so" class="form-control">
              </div>
              <div class="col">
                <label>Mã hồ sơ scan liên kết</label>
                <input type="number" name="scan_hoso_id" class="form-control">
              </div>
            </div>

          <?php
          /*-------------------------------------------------
           * 2. VĂN BẢN THƯỜNG (không phải ML/KH)
           *-------------------------------------------------*/
          elseif (!$isMLorKH): ?>

            <div class="row mb-2">
              <div class="col">
                <label>Mã phông *</label>
                <input name="ma_phong" class="form-control" required
                       pattern="[A-Za-z0-9]+"
                       oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')"
                       title="Chỉ được nhập chữ và số">
              </div>
              <div class="col">
                <label>Mã mục lục *</label>
                <input name="ma_mucluc" class="form-control" required
                       pattern="[A-Za-z0-9]+"
                       oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')"
                       title="Chỉ được nhập chữ và số">
              </div>
              <div class="col">
                <label>Mã ĐVBQ *</label>
                <input name="ma_dvbq" class="form-control" required
                       pattern="[A-Za-z0-9]+"
                       oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')"
                       title="Chỉ được nhập chữ và số">
              </div>
              <div class="col">
                <label>Mã hồ sơ *</label>
                <input name="ma_hoso" class="form-control" required
                       pattern="[A-Za-z0-9]+"
                       oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')"
                       title="Chỉ được nhập chữ và số">
              </div>
            </div>

            <div class="mb-2"><label>Số văn bản</label>
              <input type="text" name="so_vanban" class="form-control">
            </div>
            <div class="mb-2"><label>Trích yếu</label>
              <textarea name="trich_yeu" class="form-control" rows="2"></textarea>
            </div>

            <div class="row mb-2">
              <div class="col">
                <label>Ngày văn bản</label>
                <input type="date" name="ngay_thang_nam_vanban" class="form-control">
              </div>
              <div class="col">
                <label>Độ mật</label>
                <select name="id_do_mat" class="form-select">
                  <option value="">-- Độ mật --</option>
                  <?php foreach ($doMat as $dm): ?>
                    <option value="<?= $dm['ID'] ?>"><?= htmlspecialchars($dm['MaDoMat'] . ' - ' . $dm['TenDoMat']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="mb-2"><label>Tác giả</label>
              <input type="text" name="tacgia_vanban" class="form-control">
            </div>

            <div class="row mb-2">
              <div class="col">
                <label>Thể loại</label>
                <select name="id_theloaivanban_fk" class="form-select">
                  <option value="">-- Thể loại --</option>
                  <?php foreach ($theLoai as $tl): ?>
                    <option value="<?= $tl['ID'] ?>"><?= htmlspecialchars($tl['MaTheLoai'] . ' - ' . $tl['TenTheLoai']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col">
                <label>Số trang</label>
                <input type="number" name="sotrang_vanban" class="form-control" value="1" min="1">
              </div>
              <div class="col">
                <label>STT</label>
                <input type="text" name="so_thutu" class="form-control"
                       pattern="[A-Za-z0-9]+"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       title="Chỉ được nhập số">
              </div>
            </div>

            <div class="row mb-2">
              <div class="col">
                <label>Người ký</label>
                <input type="text" name="nguoi_ky" class="form-control"
                       pattern="[A-Za-z0-9]+"
                       oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')"
                       title="Chỉ được nhập chữ và số">
              </div>
              <div class="col">
                <label>Trang số</label>
                <input type="text" name="trang_so" class="form-control"
                       pattern="[A-Za-z0-9]+"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       title="Chỉ được nhập số">
              </div>
            </div>

          <?php endif; // $isBHS / !$isMLorKH ?>

          <div class="text-end mt-3">
            <button type="submit" class="btn btn-success">
              <?= $isBHS ? '💾 Lưu hồ sơ' : '💾 Lưu và chuyển tiếp' ?>
            </button>
          </div>

        </form>

      <?php endif; // Các điều kiện trên ?>

    </div>
  </div>
</div>
<?php include '../layouts/footer.php'; ?>
<?php if (!empty($_SESSION['success'])): ?>
  <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>
