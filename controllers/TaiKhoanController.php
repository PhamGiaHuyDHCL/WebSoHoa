<?php
require_once '../../config/dbadmin.php';
require_once '../../models/TaiKhoanModel.php';

// ✅ Kiểm tra quyền truy cập
checkAdmin();

// ✅ Kết nối CSDL
$conn = new mysqli('localhost', 'root', '', 'qlsohoa');
$conn->set_charset("utf8mb4");
if ($conn->connect_error) die("Lỗi kết nối CSDL: " . $conn->connect_error);

// ✅ Khởi tạo Model
$model = new TaiKhoanModel($conn);

// ✅ Xử lý thông điệp
$msg = $_GET['msg'] ?? '';

// ✅ Xử lý thêm/sửa nếu có dữ liệu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CHỈNH SỬA tài khoản
    if (isset($_POST['edit_id'])) {
        $id = (int)$_POST['edit_id'];
        $data = [
            'hoten'        => trim($_POST['hoten']),
            'sdt'          => trim($_POST['sdt']),
            'cccd'         => trim($_POST['cccd']),
            'taikhoan'     => trim($_POST['taikhoan']),
            'quyen'        => (int)$_POST['quyen'],
            'new_password' => $_POST['new_password'] ?? null
        ];

        // Kiểm tra hợp lệ
        if (in_array('', $data, true)) {
            $msg = 'missing';
        } elseif (!preg_match('/^\d{10}$/', $data['sdt'])) {
            $msg = 'invalid_sdt';
        } elseif (!preg_match('/^\d{12}$/', $data['cccd'])) {
            $msg = 'invalid_cccd';
        } elseif ($model->checkCCCDExist($data['cccd'], $id)) {
            $msg = 'cccd_exist';
        } else {
            $msg = $model->updateAccount($id, $data) ? 'edit_success' : 'edit_fail';
        }

    }

    // THÊM tài khoản mới
    elseif (isset($_POST['hoten'], $_POST['sdt'], $_POST['cccd'], $_POST['taikhoan'], $_POST['matkhau'], $_POST['quyen'])) {
        $data = [
            'hoten'    => trim($_POST['hoten']),
            'sdt'      => trim($_POST['sdt']),
            'cccd'     => trim($_POST['cccd']),
            'taikhoan' => trim($_POST['taikhoan']),
            'matkhau'  => $_POST['matkhau'],
            'quyen'    => (int)$_POST['quyen']
        ];

        if (in_array('', $data, true)) {
            $msg = 'missing';
        } elseif (!preg_match('/^\d{10}$/', $data['sdt'])) {
            $msg = 'invalid_sdt';
        } elseif (!preg_match('/^\d{12}$/', $data['cccd'])) {
            $msg = 'invalid_cccd';
        } elseif ($model->checkCCCDExist($data['cccd'])) {
            $msg = 'cccd_exist';
        } else {
            $msg = $model->addAccount($data) ? 'success' : 'error';
        }
    }

    // Redirect lại về view để tránh gửi lại form khi F5
    header("Location: dstaikhoan.php?msg=$msg");
    exit();
}

// ✅ Lấy danh sách quyền và tài khoản
$roles    = $model->getAllRoles();
$accounts = $model->getAllAccounts();
