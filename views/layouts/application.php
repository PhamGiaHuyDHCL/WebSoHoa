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

$chart_q = mysqli_query($conn, "
    SELECT DATE(ngay_nhap) AS ngay, COUNT(*) AS soluong
    FROM scan_hoso
    WHERE dataentry_status = 2
    GROUP BY DATE(ngay_nhap)
    ORDER BY ngay
");
$chart_labels = [];
$chart_data = [];
while ($row = mysqli_fetch_assoc($chart_q)) {
    $chart_labels[] = $row['ngay'];
    $chart_data[] = $row['soluong'];
}
?>

<div class="container-fluid">
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <?php
        $stats = [
            ["Tổng PDF", $total_pdf, "primary", "file-earmark-pdf"],
            ["Đã nhập", $status_counts[2], "success", "check-circle"],
            ["Đang nhập", $status_counts[1], "warning", "hourglass-split"],
            ["Chưa nhập", $status_counts[0], "danger", "x-circle"],
            ["Số hộp", $total_box, "info", "box"],
            ["Số hồ sơ", $total_record, "dark", "file-earmark-text"],
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

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header fw-bold">Biểu đồ nhập liệu theo ngày</div>
                <div class="card-body">
                    <canvas id="chartCanvas" height="250"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header fw-bold">Danh sách file PDF import</div>
                <div class="card-body tree" style="max-height: 400px; overflow-y: auto;">
                    <ul class="list-unstyled">
                        <?php
                        // Truy vấn tất cả theo khối -> khoa -> hộp
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
<?php include './views/layouts/footer.php'; ?>  
