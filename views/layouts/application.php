<?php include './views/layouts/header.php'; ?>

  <!-- Stats Cards -->
  <div class="row mb-4">
    <?php
    $stats = [
      ["Tổng PDF", 27, "primary", "file-earmark-pdf"],
      ["Đã nhập", 1, "success", "check-circle"],
      ["Đang nhập", 2, "warning", "hourglass-split"],
      ["Chưa nhập", 24, "danger", "x-circle"],
      ["Số hộp", 1, "info", "box"],
      ["Số hồ sơ", 6, "dark", "file-earmark-text"],
    ];
    foreach ($stats as [$label, $count, $color, $icon]) {
      echo <<<HTML
      <div class="col-md-2">
        <div class="card card-stat bg-{$color} text-white shadow-sm">
          <div class="card-body text-center">
            <i class="bi bi-{$icon} fs-4"></i>
            <h6 class="mt-2">{$label}</h6>
            <div class="fw-bold fs-5">{$count}</div>
          </div>
        </div>
      </div>
      HTML;
    }
    ?>
  </div>

  <!-- Chart & Treeview -->
  <div class="row">
    <!-- Chart -->
    <div class="col-md-8">
      <div class="card shadow-sm mb-3">
        <div class="card-header fw-bold">Biểu đồ nhập liệu theo ngày</div>
        <div class="card-body">
          <canvas id="chartCanvas" height="250"></canvas>
        </div>
      </div>
    </div>

    <!-- Treeview -->
    <div class="col-md-4">
      <div class="card shadow-sm mb-3">
        <div class="card-header fw-bold d-flex justify-content-between align-items-center">
          Danh sách file PDF import
          <!-- <button class="btn btn-sm btn-outline-primary" onclick="importSelected()">Nhập hồ sơ</button> -->
        </div>
        <div class="card-body tree" style="max-height: 350px; overflow-y: auto;">
          <ul class="list-unstyled">
            <li class="mb-2">
              <span class="badge bg-primary">028</span>
              <ul class="list-unstyled ms-3">
                <li class="mb-2">
                  <span class="badge bg-secondary">KHOA_1</span>
                  <ul class="list-unstyled ms-3">
                    <li class="mb-2">
                      <span class="badge bg-success">HOP_1</span>
                      <ul class="list-unstyled ms-3">
                        <li><input type="checkbox" id="checkAll" onchange="toggleAll(this)"> <b>Chọn tất cả</b></li>
                        <?php
                        $pdfs = ["028-01-0006", "028-01-0005", "028-01-0004", "028-01-0003", "028-01-0002", "028-01-0001"];
                        foreach ($pdfs as $i => $code) {
                          $checked = ($i == 5) ? "checked" : "";
                          $style = ($i == 5) ? "fw-bold" : "text-muted";
echo "<li class='mb-2'><input type='checkbox' class='file-check' $checked> <span class='{$style}'>{$code}</span></li>";
                        }
                        ?>
                      </ul>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('chartCanvas').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Đã nhập', 'Đang nhập', 'Chưa nhập'],
      datasets: [{
        label: 'Số lượng',
        data: [1, 2, 24],
        backgroundColor: ['#198754', '#ffc107', '#dc3545']
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });

  function toggleAll(source) {
    document.querySelectorAll('.file-check').forEach(cb => cb.checked = source.checked);
  }

  function importSelected() {
    const selected = Array.from(document.querySelectorAll('.file-check:checked'))
      .map(cb => cb.nextElementSibling.textContent.trim());
    if (selected.length === 0) {
      alert("Vui lòng chọn ít nhất một hồ sơ để nhập.");
    } else {
      alert("Nhập các hồ sơ: " + selected.join(", "));
      // TODO: Gửi selected[] về server để xử lý
    }
  }
</script>

<style>
  .card-stat {
    transition: transform 0.2s ease;
  }
  .card-stat:hover {
    transform: scale(1.05);
  }
</style>

<?php include './views/layouts/footer.php'; ?>  