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
        // Kiểm tra xem bản ghi đã tồn tại chưa
        $query = $this->conn->prepare("SELECT id FROM $table WHERE $codeColumn = :code");
        $query->bindValue(':code', $codeValue);
        $query->execute();
        $result = $query->fetch();

        if ($result) {
            // Nếu tồn tại, trả về ID
            return $result['id'];
        } else {
            // Nếu không tồn tại, thêm mới bản ghi
            $nameValue = $nameValue ?? $codeValue; // Nếu không có tên, sử dụng mã làm tên
            $insertQuery = $this->conn->prepare("INSERT INTO $table ($codeColumn, $nameColumn) VALUES (:code, :name)");
            $insertQuery->bindValue(':code', $codeValue);
            $insertQuery->bindValue(':name', $nameValue);
            $insertQuery->execute();
            return $this->conn->lastInsertId(); // Trả về ID mới
        }
    }
    // ======= Thêm hoặc cập nhật văn bản theo scan_vanban_Id =======
    public function upsertVanBan($data) {
        $checkSql = "SELECT id FROM ds_vanban WHERE scan_vanban_Id = :scan_vanban_Id LIMIT 1";
        $stmt = $this->conn->prepare($checkSql);
        $stmt->bindValue(':scan_vanban_Id', $data[':scan_vanban_Id']);
        $stmt->execute();

        if (!isset($data[':id_do_tincay'])) {
            $data[':id_do_tincay'] = null;
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

        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $success = $stmt->execute();

            if ($success && isset($data[':scan_vanban_Id'])) {
                $this->updateScanHoSoStatus($data[':scan_vanban_Id']);
            }

            return $success;
        } catch (PDOException $e) {
            error_log("Upsert Error: " . $e->getMessage());
            return false;
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
}