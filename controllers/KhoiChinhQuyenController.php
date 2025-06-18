<?php
require_once './models/KhoiChinhQuyenModel.php';

class KhoiChinhQuyenController {
    private $model;

    public function __construct() {
        $this->model = new KhoiChinhQuyenModel();
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                ':ma_phong'    => $_POST['ma_phong'] ?? '',
                ':ma_muc_luc'  => $_POST['ma_muc_luc'] ?? '',
                ':ma_dvbq'     => $_POST['ma_dvbq'] ?? '',
                ':ma_ho_so'    => $_POST['ma_ho_so'] ?? '',
                ':so_van_ban'  => $_POST['so_van_ban'] ?? '',
                ':trich_yeu'   => $_POST['trich_yeu'] ?? '',
                ':ngay_vb'     => $_POST['ngay_vb'] ?? null,
                ':do_mat'      => $_POST['do_mat'] ?? 'Thường',
                ':tac_gia'     => $_POST['tac_gia'] ?? '',
                ':the_loai'    => $_POST['the_loai'] ?? '',
                ':so_trang'    => $_POST['so_trang'] ?? 0,
                ':so_thu_tu'   => $_POST['so_thu_tu'] ?? '',
                ':nguoi_ky'    => $_POST['nguoi_ky'] ?? '',
                ':trang_so'    => $_POST['trang_so'] ?? ''
            ];

            $success = $this->model->insertKhoiChinhQuyen($data);

            if ($success) {
                header("Location: index.php?controller=khoichinhquyen&action=index&status=success");
                exit;
            } else {
                header("Location: index.php?controller=khoichinhquyen&action=index&status=error");
                exit;
            }
        }
    }

    public function index() {
        include './views/khoichinhquyen/index.php';
    }
}
