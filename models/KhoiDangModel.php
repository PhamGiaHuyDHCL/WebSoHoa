<?php

class KhoiDangModel {
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=localhost;dbname=qlsohoa;charset=utf8", "root", "");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Kết nối CSDL thất bại: " . $e->getMessage());
        }
    }

public function getOrInsertId($table, $codeColumn, $nameColumn, $codeValue, $nameValue = null) {
    // Nếu chưa có tên => mặc định tên = mã
    $nameValue = $nameValue ?? $codeValue;

    // 1. Kiểm tra tồn tại
    $sql = "SELECT ID FROM `$table` WHERE `$codeColumn` = :code LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(':code', $codeValue);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        return $row['ID'];
    }

    // 2. Nếu chưa có => thêm mới
    $insert = $this->conn->prepare("INSERT INTO `$table` (`$codeColumn`, `$nameColumn`) VALUES (:code, :name)");
    $insert->bindValue(':code', $codeValue);
    $insert->bindValue(':name', $nameValue);
    $insert->execute();

    return $this->conn->lastInsertId();
}

 public function saveVanBan() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_SESSION['taikhoan_id'] ?? 1; // fallback
        $now = date('Y-m-d H:i:s');

        // Lấy mã người dùng nhập
        $maPhong  = trim($_POST['ma_phong'] ?? '');
        $maMucLuc = trim($_POST['ma_mucluc'] ?? '');
        $maDVBQ   = trim($_POST['ma_dvbq'] ?? '');
        $maHoSo   = trim($_POST['ma_hoso'] ?? '');
        $scanId   = intval($_POST['scan_vanban_Id'] ?? 0);

        if (!$maPhong || !$maMucLuc || !$maDVBQ || !$maHoSo) {
            echo "<script>alert('Bạn cần nhập đầy đủ các trường bắt buộc'); history.back();</script>";
            exit;
        }

        // Lấy ID từ bảng liên kết hoặc thêm mới
        $id_phong   = $this->model->getOrInsertId('phong', 'MaPhong', 'TenPhong', $maPhong);
        $id_mucluc  = $this->model->getOrInsertId('mucluc', 'MaMucLuc', 'TenMucLuc', $maMucLuc);
        $id_dvbq    = $this->model->getOrInsertId('donvibaoquan', 'MaDVBQ', 'TenDVBQ', $maDVBQ);

        // Gói dữ liệu (KHÔNG có dấu :)
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

        // Gọi xử lý
        $result = $this->model->upsertVanBan($data);

        if ($result) {
            echo "<script>alert('✅ Lưu thành công!'); window.location.href='?controller=KhoiDang';</script>";
        } else {
            echo "<script>alert('❌ Lỗi khi lưu dữ liệu'); history.back();</script>";
        }
        exit;
    }
}


    // ======= Cập nhật trạng thái scan_hoso sau khi nhập liệu =======
    public function updateScanHoSoStatus($scanId, $status = 2) {
        try {
            $stmt = $this->conn->prepare("UPDATE scan_hoso SET dataentry_status = :status WHERE id = :id");
            $stmt->bindValue(':status', $status, PDO::PARAM_INT);
            $stmt->bindValue(':id', $scanId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update Error: " . $e->getMessage());
            return false;
        }
    }

    // ======= Lấy danh sách file scan =======
    public function getScanHoSoList() {
        return $this->fetchAll("SELECT id, folder_name, path, dataentry_status FROM scan_hoso ORDER BY folder_name ASC");
    }

    // ======= Lấy danh sách phòng =======
    public function getPhong() {
        return $this->fetchAll("SELECT * FROM phong");
    }

    public function getMucLuc() {
        return $this->fetchAll("SELECT * FROM mucluc");
    }

    public function getDonViBaoQuan() {
        return $this->fetchAll("SELECT * FROM donvibaoquan");
    }

    public function getDoMat() {
        return $this->fetchAll("SELECT * FROM domat");
    }

    public function getTheLoai() {
        return $this->fetchAll("SELECT * FROM theloaivanban");
    }

    // ======= Hàm tiện ích =======
    private function fetchAll($sql) {
        try {
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("fetchAll Error: " . $e->getMessage());
            return [];
        }
    }

    // Hàm gộp: nếu chưa có mã thì thêm mới, trả về ID


    public function upsertVanBan($data) {
        if (!isset($data['scan_vanban_Id'])) {
            throw new Exception("Thiếu dữ liệu 'scan_vanban_Id'");
        }

        $checkSql = "SELECT id FROM ds_vanban WHERE scan_vanban_Id = :scan_vanban_Id LIMIT 1";
        $stmt = $this->conn->prepare($checkSql);
        $stmt->bindValue(':scan_vanban_Id', $data['scan_vanban_Id']);
        $stmt->execute();

        if (!isset($data['id_do_tincay'])) {
            $data['id_do_tincay'] = null;
        }

        if ($stmt->fetch()) {
            // UPDATE
            $sql = "UPDATE ds_vanban SET
                id_phong = :id_phong,
                id_mucluc = :id_mucluc,
                id_dvbq = :id_dvbq,
                ma_hoso = :ma_hoso,
                so_vanban = :so_vanban,
                trich_yeu = :trich_yeu,
                trang_so = :trang_so,
                id_do_mat = :id_do_mat,
                tacgia_vanban = :tacgia_vanban,
                nguoi_ky = :nguoi_ky,
                ngay_thang_nam_vanban = :ngay_thang_nam_vanban,
                id_theloaivanban_fk = :id_theloaivanban_fk,
                sotrang_vanban = :sotrang_vanban,
                id_do_tincay = :id_do_tincay,
                so_thutu = :so_thutu,
                dataentry_status = :dataentry_status,
                ten_taptin = :ten_taptin,
                id_nguoisua = :id_nguoisua,
                ngay_sua = :ngay_sua
            WHERE scan_vanban_Id = :scan_vanban_Id";
        } else {
            // INSERT
            $sql = "INSERT INTO ds_vanban (
                id_phong, id_mucluc, id_dvbq, ma_hoso, so_vanban, trich_yeu,
                trang_so, id_do_mat, tacgia_vanban, nguoi_ky, ngay_thang_nam_vanban,
                id_theloaivanban_fk, sotrang_vanban, id_do_tincay, so_thutu,
                dataentry_status, ten_taptin, scan_vanban_Id, ngay_tao, id_nguoisua, ngay_sua
            ) VALUES (
                :id_phong, :id_mucluc, :id_dvbq, :ma_hoso, :so_vanban, :trich_yeu,
                :trang_so, :id_do_mat, :tacgia_vanban, :nguoi_ky, :ngay_thang_nam_vanban,
                :id_theloaivanban_fk, :sotrang_vanban, :id_do_tincay, :so_thutu,
                :dataentry_status, :ten_taptin, :scan_vanban_Id, :ngay_tao, :id_nguoisua, :ngay_sua
            )";
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }
    
}
