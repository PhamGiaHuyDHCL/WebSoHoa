<?php
require_once './models/KhoiDangModel.php';

class KhoiDangController {
    private $model;

    public function __construct() {
        $this->model = new KhoiDangModel();
    }

    // Hiển thị giao diện nhập liệu
    public function index() {
        require './views/NhapLieu/khoidang.php';
    }

    // Hiển thị mục lục của một file
    public function viewMucLuc() {
        $file = $_GET['file'] ?? '';
        if ($file) {
            // Xử lý hiển thị mục lục nếu cần
            echo "<h3>Hiển thị mục lục cho file: $file</h3>";
        } else {
            echo "Không có file được chọn.";
        }
    }

    // Lưu dữ liệu văn bản nhập liệu
    public function saveVanBan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                ':ten_taptin'           => $_POST['ten_taptin'] ?? '',
                ':scan_vanban_Id'      => $_POST['scan_vanban_Id'] ?? null,
                ':id_phong'            => $_POST['id_phong'] ?? null,
                ':id_mucluc'           => $_POST['id_mucluc'] ?? null,
                ':id_dvbq'             => $_POST['id_dvbq'] ?? null,
                ':ma_hoso'             => $_POST['ma_hoso'] ?? '',
                ':so_vanban'           => $_POST['so_vanban'] ?? '',
                ':trich_yeu'           => $_POST['trich_yeu'] ?? '',
                ':ngay_thang_nam'      => $_POST['ngay_thang_nam_vanban'] ?? null,
                ':id_do_mat'           => $_POST['id_do_mat'] ?? null,
                ':tacgia_vanban'       => $_POST['tacgia_vanban'] ?? '',
                ':id_theloaivanban_fk' => $_POST['id_theloaivanban_fk'] ?? null,
                ':sotrang_vanban'      => $_POST['sotrang_vanban'] ?? 1,
                ':so_thutu'            => $_POST['so_thutu'] ?? '',
                ':nguoi_ky'            => $_POST['nguoi_ky'] ?? '',
                ':trang_so'            => $_POST['trang_so'] ?? '',
                ':dataentry_status'    => 2,
                ':dataentry_user'      => $_SESSION['user_id'] ?? 1,
                ':id_nguoisua'         => $_SESSION['user_id'] ?? 1,
                ':ngay_sua'            => date('Y-m-d H:i:s'),
            ];

            $result = $this->model->insertVanBan($data);

            if ($result) {
                header('Location: index.php?controller=khoidang&action=index&file=' . urlencode($_POST['ten_taptin']));
                exit;
            } else {
                echo "<script>alert('❌ Lỗi khi lưu dữ liệu');</script>";
            }
        }
    }
}
