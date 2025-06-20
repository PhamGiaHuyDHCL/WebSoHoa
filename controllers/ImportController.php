<?php
session_start();
require_once '../models/ImportModel.php';

$model = new ImportModel();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
    exit;
}

if (!isset($_FILES['pdf_files'])) {
    $_SESSION['upload_error'] = 'Chưa chọn file PDF.';
    header("Location: views/import/dsimport.php");
    exit;
}

$user = $_SESSION['username'] ?? 'admin';
$user_id = $_SESSION['taikhoan_id'] ?? 0;

$khoi_raw = $_POST['khoi'] ?? '';
$khoi = ($khoi_raw === 'Đảng' || $khoi_raw == '2') ? 2 : 1;

$id_phong = (int)($_POST['phong'] ?? 0);
$ma_phong = str_pad((string)$id_phong, 3, '0', STR_PAD_LEFT);
$khoa = trim($_POST['khoa'] ?? 'KHOA_UNKNOWN');
$hop_ho_so = trim($_POST['hop_ho_so'] ?? 'HOP_UNKNOWN');
$ma_muc_luc = trim($_POST['ma_muc_luc'] ?? '');

$uploadDir = __DIR__ . "/../uploads/$ma_phong/$khoa/$hop_ho_so/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$files = $_FILES['pdf_files'];
$totalFiles = count($files['name']);
$success = 0;
$skipped = 0;
$errors = [];

for ($i = 0; $i < $totalFiles; $i++) {
    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        $errors[] = "Lỗi khi upload file thứ " . ($i + 1);
        continue;
    }

    $originalName = $files['name'][$i];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($ext !== 'pdf') {
        $skipped++;
        continue;
    }

    $safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($originalName, PATHINFO_FILENAME)) . '.pdf';
    $fullPath = $uploadDir . $safeName;
    $relPath = "uploads/$ma_phong/$khoa/$hop_ho_so/$safeName";

    if (move_uploaded_file($files['tmp_name'][$i], $fullPath)) {
        $data = [
            'khoi' => $khoi,
            'id_phong' => $id_phong,
            'ma_muc_luc' => $ma_muc_luc,
            'khoa' => $khoa,
            'hop_ho_so' => $hop_ho_so,
            'folder_name' => $safeName,
            'path' => $relPath,
            'scan_user' => $user_id,          // <-- dùng ID
            'dataentry_status' => 0,
            'dataentry_user' => $user_id,     // <-- dùng ID
            'id_nguoisua' => $user_id,        // <-- dùng ID
            'ngay_sua' => date('Y-m-d H:i:s'),
        ];


        if ($model->insertImportData($data)) {
            $success++;
        } else {
            $errors[] = "Lỗi khi lưu vào CSDL với file: $safeName.";
        }
    } else {
        $errors[] = "Không thể upload file: $safeName.";
    }
}

$_SESSION['upload_success'] = "Đã import $success file. Bỏ qua $skipped file.";
if ($errors) {
    $_SESSION['upload_error'] = implode(" | ", $errors);
}

header("Location: views/import/dsimport.php");
exit;
