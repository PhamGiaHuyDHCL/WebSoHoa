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

    // ======= Thêm mới văn bản =======
    public function insertVanBan($data) {
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
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Insert Error: " . $e->getMessage());
            return false;
        }
    }

    // ======= Cập nhật trạng thái sau nhập liệu =======
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

    // ======= Lấy danh sách file đã quét =======
    public function getScanHoSoList() {
        return $this->fetchAll("SELECT id, folder_name, path, dataentry_status FROM scan_hoso ORDER BY folder_name ASC");
    }

    public function getScanHoSoById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM scan_hoso WHERE id = :id LIMIT 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getScanHoSoById Error: " . $e->getMessage());
            return null;
        }
    }

    // ======= Danh mục hỗ trợ nhập liệu =======
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

    public function getDoTinCay() {
        return $this->fetchAll("SELECT * FROM dotincay");
    }

    // ======= Hàm tiện ích dùng chung =======
    private function fetchAll($sql) {
        try {
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("fetchAll Error: " . $e->getMessage());
            return [];
        }
    }
}
