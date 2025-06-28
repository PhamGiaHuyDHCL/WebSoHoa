

<?php
require_once __DIR__ . '/../models/KhoiDangModel.php';

class KhoiDangController {
    private $model;

    public function __construct() {
        $this->model = new KhoiDangModel();
    }

    public function index() {
        require '../views/NhapLieu/khoidang.php';
    }

public function saveVanBan() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_SESSION['taikhoan_id'] ?? 1;
        $now = date('Y-m-d H:i:s');

        // Lấy mã từ người dùng nhập
        $maPhong  = trim($_POST['ma_phong'] ?? '');
        $maMucLuc = trim($_POST['ma_mucluc'] ?? '');
        $maDVBQ   = trim($_POST['ma_dvbq'] ?? '');
        $maHoSo   = trim($_POST['ma_hoso'] ?? '');
        $scanId   = intval($_POST['scan_vanban_Id'] ?? 0);

        if (!$maPhong || !$maMucLuc || !$maDVBQ || !$maHoSo) {
            echo "<script>alert('Bạn cần nhập đầy đủ các trường bắt buộc'); history.back();</script>";
            exit;
        }

        // Lấy ID hoặc thêm mới
        $id_phong   = $this->model->getOrInsertId('phong', 'MaPhong', 'TenPhong', $maPhong);
        $id_mucluc  = $this->model->getOrInsertId('mucluc', 'MaMucLuc', 'TenMucLuc', $maMucLuc);
        $id_dvbq    = $this->model->getOrInsertId('donvibaoquan', 'MaDVBQ', 'TenDVBQ', $maDVBQ);

        // Gói dữ liệu KHÔNG DẤU :
        $data = [
            'id_phong' => $id_phong,
            'id_mucluc' => $id_mucluc,
            'id_dvbq' => $id_dvbq,
            'ma_hoso' => $maHoSo,
            'so_vanban' => trim($_POST['so_vanban'] ?? '') ?: null,
            'trich_yeu' => trim($_POST['trich_yeu'] ?? '') ?: null,
            'trang_so' => trim($_POST['trang_so'] ?? '') ?: null,
            'id_do_mat' => intval($_POST['id_do_mat'] ?? 0) ?: null,
            'tacgia_vanban' => trim($_POST['tacgia_vanban'] ?? '') ?: null,
            'nguoi_ky' => trim($_POST['nguoi_ky'] ?? '') ?: null,
            'ngay_thang_nam_vanban' => $_POST['ngay_thang_nam_vanban'] ?? null,
            'id_theloaivanban_fk' => intval($_POST['id_theloaivanban_fk'] ?? 0) ?: null,
            'sotrang_vanban' => intval($_POST['sotrang_vanban'] ?? 1),
            'so_thutu' => trim($_POST['so_thutu'] ?? '') ?: null,
            'dataentry_status' => 2,
            'ten_taptin' => trim($_POST['ten_taptin'] ?? '') ?: null,
            'scan_vanban_Id' => $scanId,
            'ngay_tao' => $now,
            'id_nguoisua' => $userId,
            'ngay_sua' => $now
        ];

        // Gọi model xử lý
        $result = $this->model->upsertVanBan($data);

            if ($result) {
                $_SESSION['success'] = '✅ Lưu thành công!';
                header("Location: http://localhost/websohoa1/views/Nhaplieu/khoidang.php");
                exit;
            } else {
                echo "<script>alert('❌ Lỗi khi lưu dữ liệu'); history.back();</script>";
                exit;
            }
    }
}


}


