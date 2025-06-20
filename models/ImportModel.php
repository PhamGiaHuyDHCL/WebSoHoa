<?php
class ImportModel {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli('localhost', 'root', '', 'qlsohoa');
        if ($this->conn->connect_error) {
            die("Kết nối thất bại: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8");
    }

    public function getAllPhong() {
        $sql = "SELECT id, tenphong FROM phong ORDER BY tenphong";
        $result = $this->conn->query($sql);
        $phongs = [];
        while ($row = $result->fetch_assoc()) {
            $phongs[] = $row;
        }
        return $phongs;
    }

    public function countFiltered($search = '', $hop = '', $khoa = '', $khoi = '', $phong = '', $ma_muc_luc = '') {
        $where = "WHERE 1=1";

        if ($search !== '') {
            $s = $this->conn->real_escape_string($search);
            $where .= " AND (folder_name LIKE '%$s%' OR path LIKE '%$s%')";
        }
        if ($hop !== '') $where .= " AND hop_ho_so = '" . $this->conn->real_escape_string($hop) . "'";
        if ($khoa !== '') $where .= " AND khoa = '" . $this->conn->real_escape_string($khoa) . "'";
        if ($khoi !== '') $where .= " AND khoi = " . (intval($khoi) === 2 ? 2 : 1);
        if ($phong !== '') $where .= " AND id_phong = " . intval($phong);
        if ($ma_muc_luc !== '') $where .= " AND id_mucluc = '" . $this->conn->real_escape_string($ma_muc_luc) . "'";

        $sql = "SELECT COUNT(*) as total FROM scan_hoso $where";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return (int)$row['total'];
    }

    public function getFilteredData($limit, $offset, $search = '', $hop = '', $khoa = '', $khoi = '', $phong = '', $ma_muc_luc = '', $sortBy = 'id', $sortDir = 'DESC') {
        $where = "WHERE 1=1";

        if ($search !== '') {
            $s = $this->conn->real_escape_string($search);
            $where .= " AND (s.folder_name LIKE '%$s%' OR s.path LIKE '%$s%')";
        }
        if ($hop !== '') $where .= " AND s.hop_ho_so = '" . $this->conn->real_escape_string($hop) . "'";
        if ($khoa !== '') $where .= " AND s.khoa = '" . $this->conn->real_escape_string($khoa) . "'";
        if ($khoi !== '') $where .= " AND s.khoi = " . (intval($khoi) === 2 ? 2 : 1);
        if ($phong !== '') $where .= " AND s.id_phong = " . intval($phong);
        if ($ma_muc_luc !== '') $where .= " AND s.id_mucluc = '" . $this->conn->real_escape_string($ma_muc_luc) . "'";

        $allowedCols = ['id', 'khoi', 'tenphong', 'id_mucluc', 'hop_ho_so', 'khoa', 'folder_name', 'HoTen', 'ngay_nhap'];
        $sortBy = in_array($sortBy, $allowedCols) ? $sortBy : 'id';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';
        $sortColumn = ($sortBy === 'tenphong') ? 'p.tenphong' : "s.$sortBy";

        $sql = "SELECT s.*, p.tenphong, nv.HoTen AS TenNguoiScan
                FROM scan_hoso s 
                LEFT JOIN phong p ON s.id_phong = p.id 
                LEFT JOIN taikhoan tk ON s.scan_user = tk.ID
                LEFT JOIN nhanvien nv ON tk.IDNhanVien = nv.ID
                $where 
                ORDER BY $sortColumn $sortDir 
                LIMIT ?, ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function insertImportData($data) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO scan_hoso (
                    khoi, id_phong, id_mucluc, folder_name,
                    dataentry_status, dataentry_user, scan_user,
                    path, hop_ho_so, khoa, id_nguoisua,
                    ngay_sua, ngay_nhap
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $stmt->bind_param(
                "iiisiiisssi",
                $data['khoi'],               // int
                $data['id_phong'],          // int
                $data['ma_muc_luc'],        // int
                $data['folder_name'],       // string
                $data['dataentry_status'],  // int
                $data['dataentry_user'],    // int
                $data['scan_user'],         // int
                $data['path'],              // string
                $data['hop_ho_so'],         // string
                $data['khoa'],              // string
                $data['id_nguoisua']        // int
            );

            if (!$stmt->execute()) {
                error_log("MySQL Error: " . $stmt->error);
                return false;
            }

            return true;
        } catch (Exception $e) {
            error_log("Lỗi chèn dữ liệu: " . $e->getMessage());
            return false;
        }
    }
    public function deleteByIds(array $ids) {
        if (empty($ids)) return;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        // 1. Xoá bản ghi liên quan trong ds_vanban
        $sql1 = "DELETE FROM ds_vanban WHERE scan_vanban_Id IN ($placeholders)";
        $stmt1 = $this->conn->prepare($sql1);
        $stmt1->bind_param($types, ...$ids);
        $stmt1->execute();

        // 2. Xoá bản ghi liên quan trong ds_hoso
        $sql2 = "DELETE FROM ds_hoso WHERE scan_hoso_id IN ($placeholders)";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param($types, ...$ids);
        $stmt2->execute();

        // 3. Cuối cùng xoá bản ghi trong scan_hoso
        $sql3 = "DELETE FROM scan_hoso WHERE id IN ($placeholders)";
        $stmt3 = $this->conn->prepare($sql3);
        $stmt3->bind_param($types, ...$ids);
        $stmt3->execute();
    }

}
