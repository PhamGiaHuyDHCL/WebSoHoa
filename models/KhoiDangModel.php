<?php

class KhoiDangModel {
    private $conn;
    public function getConnection() {
    return $this->conn;
    }
    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=localhost;dbname=qlsohoa;charset=utf8", "root", "");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Kết nối CSDL thất bại: " . $e->getMessage());
        }
    }
	public function markDataentryStatusAsEditing($id, $userId) {
	    // Bước 1: Hủy đánh dấu các file khác đang được user này nhập liệu (nếu có)
	    $sqlClear = "UPDATE scan_hoso 
	                 SET dataentry_status = NULL, dataentry_user = NULL 
	                 WHERE dataentry_user = ? AND dataentry_status = 1 AND id != ?";
	    $stmtClear = $this->conn->prepare($sqlClear);
	    $stmtClear->execute([$userId, $id]);

	    // Bước 2: Đánh dấu file mới là đang được nhập
	    $sqlUpdate = "UPDATE scan_hoso 
	                  SET dataentry_status = 1, dataentry_user = ? 
	                  WHERE id = ? AND (dataentry_status IS NULL OR dataentry_status != 2)";
	    $stmtUpdate = $this->conn->prepare($sqlUpdate);
	    $stmtUpdate->execute([$userId, $id]);
	}


    public function getOrInsertId($table, $codeColumn, $nameColumn, $codeValue, $nameValue = null) {
        $nameValue = $nameValue ?? $codeValue;
        $sql = "SELECT ID FROM `$table` WHERE `$codeColumn` = :code LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':code', $codeValue);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return $row['ID'];
        $insert = $this->conn->prepare("INSERT INTO `$table` (`$codeColumn`, `$nameColumn`) VALUES (:code, :name)");
        $insert->bindValue(':code', $codeValue);
        $insert->bindValue(':name', $nameValue);
        $insert->execute();
        return $this->conn->lastInsertId();
    }

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

    public function getScanHoSoList() {
        return $this->fetchAll("SELECT id, folder_name, path, dataentry_status, khoi FROM scan_hoso ORDER BY folder_name ASC");
    }

    public function getPhong() {
        return $this->fetchAll("SELECT * FROM phong");
    }
    public function getScanById($id) {
    $stmt = $this->conn->prepare("SELECT * FROM scan_hoso WHERE id = :id LIMIT 2");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
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

    private function fetchAll($sql) {
        try {
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("fetchAll Error: " . $e->getMessage());
            return [];
        }
    }

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

        // Tự động lọc biến đúng với placeholder
        preg_match_all('/:([a-zA-Z0-9_]+)/', $sql, $matches);
        $placeholders = array_unique($matches[1]);
        $params = array_intersect_key($data, array_flip($placeholders));

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }


    public function updateScanStatus($scanId, $status = 2) {
        $stmt = $this->conn->prepare("UPDATE scan_hoso SET dataentry_status = ? WHERE id = ?");
        $stmt->execute([$status, $scanId]);
    }

    public function updateUserNhapLieuSession($userId, $maPhong, $khoa, $hopHoSo, $filePath) {
        $stmt = $this->conn->prepare("
            INSERT INTO session_nhaplieu (taikhoan_id, ma_phong, khoa, hop_ho_so, current_file)
            VALUES (:uid, :phong, :khoa, :hop, :file)
            ON DUPLICATE KEY UPDATE 
                ma_phong = VALUES(ma_phong),
                khoa = VALUES(khoa),
                hop_ho_so = VALUES(hop_ho_so),
                current_file = VALUES(current_file)
        ");
        $stmt->execute([
            ':uid' => $userId,
            ':phong' => $maPhong,
            ':khoa' => $khoa,
            ':hop' => $hopHoSo,
            ':file' => $filePath
        ]);
    }


    public function getSessionNhapLieu($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM session_nhaplieu WHERE taikhoan_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function clearSessionNhapLieu($userId) {
        $stmt = $this->conn->prepare("DELETE FROM session_nhaplieu WHERE taikhoan_id = :uid");
        $stmt->execute([':uid' => $userId]);
    }
    public function getAvailableFolderPath() {
    $stmt = $this->conn->prepare("
        SELECT path 
        FROM scan_hoso 
        WHERE dataentry_status = 0 
        ORDER BY folder_name ASC, path ASC 
        LIMIT 1
    ");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && isset($row['path'])) {
        // Tách ra phần thư mục: uploads/ma_phong/khoa/hop_ho_so/file.pdf → chỉ lấy uploads/ma_phong/khoa/hop_ho_so
        $parts = explode('/', $row['path']);
        if (count($parts) >= 4) {
            return implode('/', array_slice($parts, 0, 4));
        }
    }

    return null;
    }
    public function isFolderAvailable($folderPath) {
    $stmt = $this->conn->prepare("
        SELECT COUNT(*) FROM scan_hoso 
        WHERE path LIKE :path AND dataentry_status = 0
    ");
    $stmt->execute([':path' => "$folderPath/%"]);
    return $stmt->fetchColumn() > 0;
    }

    public function getNextUnprocessedFileInFolder($folderPath) {
    $stmt = $this->conn->prepare("
        SELECT * FROM scan_hoso 
        WHERE path LIKE :folderPath 
          AND dataentry_status = 0 
        ORDER BY path ASC 
        LIMIT 1
    ");
    $stmt->execute([':folderPath' => "$folderPath/%"]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getEditingUserByPath($path) {
    $stmt = $this->conn->prepare("SELECT * FROM session_nhaplieu WHERE current_path = ? LIMIT 1");
    $stmt->execute([$path]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getNextFileInFolder($folderName, $currentPath) {
        $stmt = $this->conn->prepare("SELECT path FROM scan_hoso WHERE folder_name = ? AND dataentry_status = 0 ORDER BY path ASC");
        $stmt->execute([$folderName]);
        $files = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($files as $file) {
            if ($file > $currentPath) return $file;
        }
        return null;
    }
}