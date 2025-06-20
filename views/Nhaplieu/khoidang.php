<?php
include '../layouts/header.php';
require_once '../../models/KhoiDangModel.php';

$model = new KhoiDangModel();

// Lấy danh sách file đã scan từ bảng scan_hoso
$scan = $model->getScanHoSoList();

// Danh mục khác
$phong    = $model->getPhong();
$mucluc   = $model->getMucLuc();
$dvbq     = $model->getDonViBaoQuan();
$doMat    = $model->getDoMat();
$theLoai  = $model->getTheLoai();

// Lấy đường dẫn file được chọn
$selectedFilePath = $_GET['file'] ?? '';
$selectedScanId = null;
$mucLucInfo = null;

// Tìm file được chọn (so sánh theo 'path' trong scan_hoso)
foreach ($scan as $item) {
    if ($item['path'] === $selectedFilePath) {
        $selectedScanId = $item['id'];
        $mucLucInfo = $item;
        break;
    }
}
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<div class="container-fluid">
  <!-- Header -->
  <div class="d-flex justify-content-between p-3 border-bottom bg-light">
    <div>
      <form method="get">
        <input type="hidden" name="controller" value="khoidang">
        <label class="form-label fw-bold mb-0">Chọn file PDF:</label>
        <select name="file" class="form-select d-inline w-auto" onchange="this.form.submit()">
          <option value="">-- Chọn file PDF --</option>
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

      <!-- Nội dung -->
      <div class="col-md-8 border-end p-3" style="height: 90vh; overflow: auto;">
        <?php
          $webPath = "/websohoa1/" . $selectedFilePath;
          $filePath = $_SERVER['DOCUMENT_ROOT'] . $webPath;
          $ext = strtolower(pathinfo($selectedFilePath, PATHINFO_EXTENSION));
        ?>

        <?php if ($selectedFilePath && file_exists($filePath)): ?>
          <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])): ?>
            <!-- Ảnh thì hiển thị kèm zoom -->
            <div style="overflow: hidden;">
              <img id="zoom-img" src="<?= $webPath ?>" style="max-width: 100%; height: auto; cursor: grab;" />
            </div>
            <div class="mt-2 text-center">
              <button onclick="imgPanzoom.zoomIn()">➕</button>
              <button onclick="imgPanzoom.zoomOut()">➖</button>
            </div>

            <script src="https://unpkg.com/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>
            <script>
              const imgPanzoom = Panzoom(document.getElementById('zoom-img'), { maxScale: 5, minScale: 0.5 });
              document.getElementById('zoom-img').parentElement.addEventListener('wheel', imgPanzoom.zoomWithWheel);
            </script>
          <?php elseif ($ext === 'pdf'): ?>
            <!-- PDF thì dùng canvas để hiển thị như ảnh -->
            <canvas id="pdf-canvas" style="border: none;"></canvas>
            <div class="mt-2 text-center">
              <button onclick="pdfZoomIn()">➕</button>
              <button onclick="pdfZoomOut()">➖</button>
            </div>

            <script>
              let pdfDoc = null, pageNum = 1, pdfScale = 1.5;
              const canvas = document.getElementById('pdf-canvas');
              const ctx = canvas.getContext('2d');

              function renderPage(num) {
                pdfDoc.getPage(num).then(page => {
                  const viewport = page.getViewport({ scale: pdfScale });
                  canvas.height = viewport.height;
                  canvas.width = viewport.width;

                  const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                  };
                  page.render(renderContext);
                });
              }

              function pdfZoomIn() {
                pdfScale += 0.25;
                renderPage(pageNum);
              }

              function pdfZoomOut() {
                pdfScale = Math.max(0.5, pdfScale - 0.25);
                renderPage(pageNum);
              }

              pdfjsLib.getDocument("<?= $webPath ?>").promise.then(pdf => {
                pdfDoc = pdf;
                renderPage(pageNum);
              });
            </script>
          <?php else: ?>
            <div class="alert alert-warning">Định dạng file không hỗ trợ hiển thị: <?= htmlspecialchars($ext) ?></div>
          <?php endif; ?>
        <?php else: ?>
          <div class="alert alert-info">Vui lòng chọn file để hiển thị nội dung.</div>
        <?php endif; ?>
      </div>



    <!-- Bên phải: Nhập liệu -->
    <div class="col-md-4 p-3">
      <div class="card shadow-sm">
        <div class="card-header bg-light"><strong>Nhập liệu</strong></div>
        <div class="card-body">
          <?php if ($selectedFilePath && $selectedScanId): ?>
          <form method="post">
            <input type="hidden" name="ten_taptin" value="<?= basename($selectedFilePath) ?>">
            <input type="hidden" name="scan_vanban_Id" value="<?= $selectedScanId ?>">

            <div class="row mb-2">
              <div class="col"><label class="form-label">Mã phòng *</label><input name="id_phong" class="form-control" required></div>
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
            <div class="alert alert-warning">Vui lòng chọn file để nhập liệu.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../layouts/footer.php'; ?>
