<?php
require_once './models/KhoiDangModel.php';
session_start();

class KhoiDangController {
    private $model;

    public function __construct() {
        $this->model = new KhoiDangModel();
    }

    public function index() {
        require '../views/NhapLieu/khoidang.php';
    }

public function saveVanBan() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_SESSION['taikhoan_id'] ?? 1;
        $now = date('Y-m-d H:i:s');
        $tenFile = $_POST['ten_taptin'] ?? '';

        // Lấy mã nhập
        $maPhong = trim($_POST['ma_phong'] ?? '');
        $maMucLuc = trim($_POST['ma_mucluc'] ?? '');
        $maDVBQ = trim($_POST['ma_dvbq'] ?? '');
        $maHoSo = trim($_POST['ma_hoso'] ?? '');

        // Kiểm tra bắt buộc
        if (!$maPhong || !$maMucLuc || !$maDVBQ || !$maHoSo) {
            echo "<script>alert('⚠️ Bạn cần nhập đầy đủ: Mã phông, mục lục, ĐVBQ, hồ sơ'); history.back();</script>";
            exit;
        }

        // Trả ID từ mã hoặc thêm mới
        $id_phong = $this->model->getOrInsertId('phong', 'MaPhong', 'TenPhong', $maPhong);
        $id_mucluc = $this->model->getOrInsertId('mucluc', 'MaMucLuc', 'TenMucLuc', $maMucLuc);
        $id_dvbq = $this->model->getOrInsertId('donvibaoquan', 'MaDVBQ', 'TenDVBQ', $maDVBQ);

        // Lấy scanId nếu có
        $scanId = $_POST['scan_vanban_Id'] ?? null;

        // Chuẩn bị dữ liệu lưu
        $data = [
            ':ten_taptin' => $tenFile,
            ':scan_vanban_Id' => $scanId,
            ':id_phong' => $id_phong,
            ':id_mucluc' => $id_mucluc,
            ':id_dvbq' => $id_dvbq,
            ':ma_hoso' => $_POST['ma_hoso'] ?? '',
            ':so_vanban' => $_POST['so_vanban'] ?? '',
            ':trich_yeu' => $_POST['trich_yeu'] ?? '',
            ':ngay_thang_nam_vanban' => $_POST['ngay_thang_nam_vanban'] ?? null,
            ':id_do_mat' => $_POST['id_do_mat'] ?? null,
            ':tacgia_vanban' => $_POST['tacgia_vanban'] ?? '',
            ':id_theloaivanban_fk' => $_POST['id_theloaivanban_fk'] ?? null,
            ':sotrang_vanban' => $_POST['sotrang_vanban'] ?? 1,
            ':so_thutu' => $_POST['so_thutu'] ?? '',
            ':nguoi_ky' => $_POST['nguoi_ky'] ?? '',
            ':trang_so' => $_POST['trang_so'] ?? '',
            ':dataentry_status' => 2,
            ':id_nguoisua' => $userId,
            ':ngay_sua' => $now,
            ':ngay_tao' => $now,
        ];

        $result = $this->model->upsertVanBan($data);

        if ($result) {
            echo "<script>alert('✅ Dữ liệu đã được lưu thành công!');</script>";
            header('Location: khoidang.php?controller=khoidang&action=index&file=' . urlencode($tenFile));
            exit;
        } else {
            echo "<script>alert('❌ Lỗi khi lưu dữ liệu');</script>";
        }
    }
}
    
}