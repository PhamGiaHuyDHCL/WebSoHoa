<?php
include_once '../layouts/header.php';
require_once __DIR__ . '/../../models/KhoiDangModel.php';
include_once '../../controllers/KhoiDangController.php';

$model = new KhoiDangModel();
$scan = $model->getScanHoSoList();
$phong = $model->getPhong();
$mucluc = $model->getMucLuc();
$dvbq = $model->getDonViBaoQuan();
$doMat = $model->getDoMat();
$theLoai = $model->getTheLoai();

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

$webPath = "/websohoa1/" . $selectedFilePath;
$filePath = $_SERVER['DOCUMENT_ROOT'] . $webPath;
$ext = strtolower(pathinfo($selectedFilePath, PATHINFO_EXTENSION));
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://unpkg.com/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>

<div class="container-fluid">
  <div class="d-flex justify-content-between p-3 border-bottom bg-light">
    <div>
      <form method="get">
        <input type="hidden" name="controller" value="khoidang">
        <label class="form-label fw-bold mb-0">Chọn file:</label>
        <select name="file" class="form-select d-inline w-auto" onchange="this.form.submit()">
          <option value="">-- Chọn file --</option>
          <?php foreach ($scan as $file): ?>
            <option value="<?= htmlspecialchars($file['path']) ?>" <?= ($file['path'] === $selectedFilePath ? 'selected' : '') ?>>
              <?= htmlspecialchars($file['folder_name']) ?> <?= $file['dataentry_status'] == 2 ? '✅' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
    <div>
      <button class="btn btn-outline-secondary btn-sm" disabled>📂 Mở mục lục</button>
    </div>
  </div>

  <div class="row g-0">
    <div class="col-md-8 border-end p-0 position-relative" style="height: 100vh; overflow: hidden;">
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
    <!-- Bên phải: Nhập liệu -->
    <div class="col-md-4 p-3 d-flex flex-column">
      <div class="card shadow-sm flex-grow-1">
        <div class="card-header bg-light"><strong>Nhập liệu</strong></div>
        <div class="card-body overflow-auto">
          <?php if ($selectedFilePath && $selectedScanId): ?>
          <form method="post" action="../../index.php?controller=khoidang&action=saveVanBan">

            <input type="hidden" name="ten_taptin" value="<?= basename($selectedFilePath) ?>">
            <input type="hidden" name="scan_vanban_Id" value="<?= $selectedScanId ?>">

            <div class="row mb-2">
              <div class="col"><label>Mã phông *</label><input name="ma_phong" class="form-control" required></div>
              <div class="col"><label>Mã mục lục *</label><input name="ma_mucluc" class="form-control" required></div>
              <div class="col"><label>Mã ĐVBQ *</label><input name="ma_dvbq" class="form-control" required></div>
              <div class="col"><label>Mã hồ sơ *</label><input name="ma_hoso" class="form-control" required></div>
            </div>

            <div class="mb-2"><label>Số văn bản</label><input type="text" name="so_vanban" class="form-control"></div>
            <div class="mb-2"><label>Trích yếu</label><textarea name="trich_yeu" class="form-control" rows="2"></textarea></div>

            <div class="row mb-2">
              <div class="col"><label>Ngày văn bản</label><input type="date" name="ngay_thang_nam_vanban" class="form-control"></div>
              <div class="col">
                <label>Độ mật</label>
                <select name="id_do_mat" class="form-select">
                  <option value="">-- Độ mật --</option>
                  <?php foreach ($doMat as $dm): ?>
                    <option value="<?= $dm['ID'] ?>" <?= ($dm['ID'] == ($_POST['id_do_mat'] ?? '') ? 'selected' : '') ?>>
                      <?= htmlspecialchars($dm['MaDoMat'] . ' - ' . $dm['TenDoMat']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="mb-2"><label>Tác giả</label><input type="text" name="tacgia_vanban" class="form-control"></div>

            <div class="row mb-2">
              <div class="col">
                <label>Thể loại</label>
                <select name="id_theloaivanban_fk" class="form-select">
                  <option value="">-- Thể loại --</option>
                  <?php foreach ($theLoai as $tl): ?>
                    <option value="<?= $tl['ID'] ?>" <?= ($tl['ID'] == ($_POST['id_theloaivanban_fk'] ?? '') ? 'selected' : '') ?>>
                      <?= htmlspecialchars($tl['MaTheLoai'] . ' - ' . $tl['TenTheLoai']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col"><label>Số trang</label><input type="number" name="sotrang_vanban" class="form-control" value="1"></div>
              <div class="col"><label>STT</label><input type="text" name="so_thutu" class="form-control"></div>
            </div>

            <div class="row mb-2">
              <div class="col"><label>Người ký</label><input type="text" name="nguoi_ky" class="form-control"></div>
              <div class="col"><label>Trang số</label><input type="text" name="trang_so" class="form-control"></div>
            </div>

            <div class="text-end mt-3">
              <button type="submit" class="btn btn-success">💾 Lưu</button>
            </div>
          </form>
          <?php else: ?>
            <div class="alert alert-warning">Vui lòng chọn file để nhập liệu.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../layouts/footer.php'; ?>
