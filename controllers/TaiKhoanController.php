<?php
require_once '../models/TaiKhoanModel.php';

$model = new TaiKhoanModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // RESET
    if (isset($_POST['reset_id'])) {
        $id = $_POST['reset_id'];
        $success = $model->resetMatKhau($id);
        header("Location: ../views/dstaikhoan.php?msg=" . ($success ? "reset_success" : "reset_fail"));
        exit;
    }

    // ADD
    if (isset($_POST['submit_add'])) {
        $tennv = $_POST['tennv'];
        $sdt = $_POST['sdt'];
        $cccd = $_POST['cccd'];
        $taikhoan = $_POST['taikhoan'];
        $mota = $_POST['mota'];
        $quyen = $_POST['quyen'];
        $matkhau = password_hash("123456", PASSWORD_DEFAULT);

        if ($model->checkExistCCCD($cccd)) {
            header("Location: ../views/dstaikhoan.php?msg=cccd_exist");
            exit;
        }

        if ($model->checkExistTaiKhoan($taikhoan)) {
            header("Location: ../views/dstaikhoan.php?msg=taikhoan_exist");
            exit;
        }

        $nvid = $model->insertNhanVien($tennv, $sdt, $cccd, $mota);
        if ($nvid && $model->insertTaiKhoan($nvid, $taikhoan, $matkhau, $quyen)) {
            header("Location: ../views/dstaikhoan.php?msg=add_success");
        } else {
            header("Location: ../views/dstaikhoan.php?msg=add_fail");
        }
        exit;
    }

    // UPDATE
    if (isset($_POST['submit_update'])) {
        $id = $_POST['update_id'];
        $tennv = $_POST['tennv'];
        $sdt = $_POST['sdt'];
        $cccd = $_POST['cccd'];
        $taikhoan = $_POST['taikhoan'];
        $mota = $_POST['mota'];
        $quyen = $_POST['quyen'];

        if ($model->updateTaiKhoan($id, $tennv, $sdt, $cccd, $taikhoan, $mota, $quyen)) {
            header("Location: ../views/dstaikhoan.php?msg=update_success");
        } else {
            header("Location: ../views/dstaikhoan.php?msg=update_fail");
        }
        exit;
    }
}
?>
