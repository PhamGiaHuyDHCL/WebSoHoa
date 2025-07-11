<?php
// KhoiDangController.php
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
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['taikhoan_id'] ?? 1;
            $now = date('Y-m-d H:i:s');

            $scanId = intval($_POST['scan_vanban_Id'] ?? 0);
            $scanRecord = $this->model->getScanById($scanId);
            $currentPath = $scanRecord['path'] ?? null;
            if (!$currentPath) {
                echo "<script>alert('Không tìm thấy file!'); history.back();</script>";
                exit;
            }

            // Tách thông tin từ path
            $parts = explode('/', $currentPath); // uploads/PHONG/KHOA/HOP/file.pdf
            $maPhong = $parts[1] ?? null;
            $khoa = $parts[2] ?? null;
            $hopHoSo = $parts[3] ?? null;

            // Validate
            $maPhongInput = trim($_POST['ma_phong'] ?? '');
            $maMucLuc = trim($_POST['ma_mucluc'] ?? '');
            $maDVBQ = trim($_POST['ma_dvbq'] ?? '');
            $maHoSo = trim($_POST['ma_hoso'] ?? '');

            if (!$maPhongInput || !$maMucLuc || !$maDVBQ || !$maHoSo) {
                echo "<script>alert('Bạn cần nhập đầy đủ các trường bắt buộc'); history.back();</script>";
                exit;
            }

            $id_phong = $this->model->getOrInsertId('phong', 'MaPhong', 'TenPhong', $maPhongInput);
            $id_mucluc = $this->model->getOrInsertId('mucluc', 'MaMucLuc', 'TenMucLuc', $maMucLuc);
            $id_dvbq = $this->model->getOrInsertId('donvibaoquan', 'MaDVBQ', 'TenDVBQ', $maDVBQ);

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

            $result = $this->model->upsertVanBan($data);
            if ($result) {
                $this->model->updateScanStatus($scanId, 2);

                // Tìm file kế tiếp trong thư mục
                $scanList = $this->model->getScanHoSoList();
                $nextPath = null;
                $foundCurrent = false;

                foreach ($scanList as $item) {
                    if (!str_starts_with($item['path'], "uploads/$maPhong/$khoa/$hopHoSo")) continue;
                    if ($item['path'] === $currentPath) {
                        $foundCurrent = true;
                        continue;
                    }
                    if ($foundCurrent && $item['dataentry_status'] != 2) {
                        $nextPath = $item['path'];
                        break;
                    }
                }

                if ($nextPath) {
                    header("Location: ?controller=khoidang&file=" . urlencode($nextPath));
                } else {
                    $_SESSION['success'] = '✅ Lưu thành công! Đã hết file trong thư mục.';
                    header("Location: ?controller=khoidang");
                }
                exit;
            } else {
                echo "<script>alert('❌ Lỗi khi lưu dữ liệu'); history.back();</script>";
                exit;
            }
        }
    }
}
