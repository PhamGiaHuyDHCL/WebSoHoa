<?php
include './config/db.php';
include './views/layouts/header.php';

// ===== Thống kê =====
$total_pdf = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM scan_hoso"));
$total_box = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT hop_ho_so FROM scan_hoso"));
$total_record = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT folder_name FROM scan_hoso"));

$status_q = mysqli_query($conn, "SELECT dataentry_status, COUNT(*) AS count FROM scan_hoso GROUP BY dataentry_status");
$status_counts = [0 => 0, 1 => 0, 2 => 0];
while ($row = mysqli_fetch_assoc($status_q)) {
    $status_counts[$row['dataentry_status']] = $row['count'];
}

// 👉 Đếm số dòng trong bảng session_nhaplieu
$session_q = mysqli_query($conn, "SELECT COUNT(*) AS count FROM session_nhaplieu");
$session_data = mysqli_fetch_assoc($session_q);
$total_session = $session_data['count'] ?? 0;

// 👉 Biểu đồ nhập liệu từ bảng ds_vanban
$chart_q = mysqli_query($conn, "
    SELECT DATE(ngay_tao) AS ngay, COUNT(*) AS soluong
    FROM ds_vanban
    GROUP BY DATE(ngay_tao)
    ORDER BY ngay
");
$chart_labels = [];
$chart_data = [];
while ($row = mysqli_fetch_assoc($chart_q)) {
    $chart_labels[] = $row['ngay'];
    $chart_data[] = $row['soluong'];
}
?>

<div class="container-fluid px-4 py-2">

  <!-- Stats Cards -->
  <div class="row mb-3 g-2">
    <?php
    $stats = [
        ["Tổng PDF", $total_pdf, "primary", "file-earmark-pdf"],
        ["Đã nhập", $status_counts[2], "success", "check-circle"],
        ["Đang nhập", $total_session, "warning", "hourglass-split"],
        ["Chưa nhập", $status_counts[0], "danger", "x-circle"],
        ["Số hộp", $total_box, "info", "box"],
        ["Số hồ sơ", $total_record, "dark", "file-earmark-text"],
    ];
    foreach ($stats as [$label, $count, $color, $icon]) {
        echo <<<HTML
        <div class="col-sm-6 col-md-4 col-lg-2 mb-2">
            <div class="card card-stat bg-{$color} text-white shadow-sm h-100">
                <div class="card-body text-center p-2">
                    <i class="bi bi-{$icon} fs-4"></i>
                    <h6 class="mt-1 small">{$label}</h6>
                    <div class="fw-bold fs-5">{$count}</div>
                </div>
            </div>
        </div>
        HTML;
    }
    ?>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <div class="card shadow-sm mb-3">
        <div class="card-header fw-bold">Biểu đồ nhập liệu theo ngày</div>
        <div class="card-body p-3">
          <canvas id="chartCanvas" height="150"></canvas>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm mb-3">
        <div class="card-header fw-bold">Danh sách file PDF import</div>
        <div class="card-body tree" style="max-height: 300px; overflow-y: auto; font-size: 0.9rem;">
                        <ul class="list-unstyled">
                        <?php
                        $q = mysqli_query($conn, "
                            SELECT khoi, khoa, hop_ho_so, folder_name, ngay_nhap
                            FROM scan_hoso
                            ORDER BY khoi, khoa, hop_ho_so, ngay_nhap DESC
                        ");

                        $data = [];
                        while ($row = mysqli_fetch_assoc($q)) {
                            $khoi = $row['khoi'];
                            $khoa = $row['khoa'];
                            $hop = $row['hop_ho_so'];
                            $file = $row['folder_name'];
                            $ngay = date('d-m-Y', strtotime($row['ngay_nhap']));

                            $data[$khoi][$khoa][$hop][] = ['file' => $file, 'ngay' => $ngay];
                        }

                        foreach ($data as $khoi => $khoas) {
                            echo "<li class='mb-2'>
                                <span class='badge bg-light text-dark border'>Khối: $khoi</span>
                                <ul class='list-unstyled ms-3'>";
                            foreach ($khoas as $khoa => $hops) {
                                echo "<li class='mb-2'>
                                    <span class='badge bg-light text-dark border'>Khoa: $khoa</span>
                                    <ul class='list-unstyled ms-3'>";
                                foreach ($hops as $hop => $files) {
                                    $collapseId = "collapse_{$khoi}_{$khoa}_{$hop}";
                                    echo "<li class='mb-2'>
                                        <div class='toggle-btn collapsed' data-bs-toggle='collapse' data-bs-target='#$collapseId' style='cursor:pointer;'>
                                            <i class='bi bi-chevron-right me-1'></i> <span class='badge bg-light text-dark border'>Hộp: $hop</span>
                                        </div>
                                        <ul class='list-unstyled ms-3 collapse' id='$collapseId'>";
                                    foreach ($files as $f) {
                                        echo "<li><i class='bi bi-file-earmark-pdf text-danger me-1'></i> {$f['file']} <small class='text-muted'>(nhập: {$f['ngay']})</small></li>";
                                    }
                                    echo "</ul></li>";
                                }
                                echo "</ul></li>";
                            }
                            echo "</ul></li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ChartJS v4 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chartCanvas').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($chart_labels) ?>,
    datasets: [{
      label: 'Số văn bản đã nhập',
      data: <?= json_encode($chart_data) ?>,
      backgroundColor: 'rgba(54, 162, 235, 0.6)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
        title: {
          display: true,
          text: 'Số lượng'
        }
      },
      x: {
        title: {
          display: true,
          text: 'Ngày nhập liệu'
        }
      }
    },
    plugins: {
      legend: {
        display: false
      }
    }
  }
});
</script>

<?php include './views/layouts/footer.php'; ?>
