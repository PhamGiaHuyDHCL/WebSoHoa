<?php
class TaiKhoanModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllRoles() {
        $roles = [];
        $res = $this->conn->query("SELECT ID, role_name FROM phanquyen");
        while ($r = $res->fetch_assoc()) $roles[] = $r;
        return $roles;
    }

    public function getAllAccounts() {
        $sql = "
            SELECT tk.ID, tk.TaiKhoan, nv.HoTen, nv.CCCD, nv.SoDienThoai, pq.role_name AS Quyen, pq.MoTa, pq.ID AS QuyenID
            FROM taikhoan tk
            JOIN phanquyen pq ON pq.ID = tk.IDPhanQuyen
            JOIN nhanvien nv ON nv.ID = tk.IDNhanVien
        ";
        return $this->conn->query($sql);
    }

    public function checkCCCDExist($cccd, $exceptId = null) {
        $sql = $exceptId
            ? "SELECT ID FROM nhanvien WHERE CCCD = ? AND ID != (SELECT IDNhanVien FROM taikhoan WHERE ID = ?)"
            : "SELECT ID FROM nhanvien WHERE CCCD = ?";
        $stmt = $this->conn->prepare($sql);
        if ($exceptId) {
            $stmt->bind_param("si", $cccd, $exceptId);
        } else {
            $stmt->bind_param("s", $cccd);
        }
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    public function addAccount($data) {
        $this->conn->begin_transaction();
        try {
            $stmt_nv = $this->conn->prepare("INSERT INTO nhanvien (HoTen, SoDienThoai, CCCD) VALUES (?, ?, ?)");
            $stmt_nv->bind_param("sss", $data['hoten'], $data['sdt'], $data['cccd']);
            $stmt_nv->execute();
            $idnv = $this->conn->insert_id;
            $stmt_nv->close();

            $hash = password_hash($data['matkhau'], PASSWORD_DEFAULT);
            $stmt_tk = $this->conn->prepare("INSERT INTO taikhoan (TaiKhoan, MatKhau, IDPhanQuyen, IDNhanVien) VALUES (?, ?, ?, ?)");
            $stmt_tk->bind_param("ssii", $data['taikhoan'], $hash, $data['quyen'], $idnv);
            $stmt_tk->execute();
            $stmt_tk->close();

            $this->conn->commit();
            return true;
       } catch (Exception $e) {
            $this->conn->rollback();
            error_log("❌ updateAccount() Lỗi: " . $e->getMessage());
            return false;
        }

    }

    public function updateAccount($id, $data) {
        $this->conn->begin_transaction();
        try {
            $stmt_nv = $this->conn->prepare("
                UPDATE nhanvien 
                SET HoTen = ?, SoDienThoai = ?, CCCD = ? 
                WHERE ID = (SELECT IDNhanVien FROM taikhoan WHERE ID = ?)
            ");
            $stmt_nv->bind_param("sssi", $data['hoten'], $data['sdt'], $data['cccd'], $id);
            $stmt_nv->execute();
            $stmt_nv->close();

            if (!empty($data['new_password'])) {
                $hash = password_hash($data['new_password'], PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare("
                    UPDATE taikhoan 
                    SET TaiKhoan = ?, MatKhau = ?, IDPhanQuyen = ? 
                    WHERE ID = ?
                ");
                $stmt->bind_param("ssii", $data['taikhoan'], $hash, $data['quyen'], $id);
            } else {
                $stmt = $this->conn->prepare("
                    UPDATE taikhoan 
                    SET TaiKhoan = ?, IDPhanQuyen = ? 
                    WHERE ID = ?
                ");
                $stmt->bind_param("sii", $data['taikhoan'], $data['quyen'], $id);
            }
            $stmt->execute();
            $stmt->close();

            $this->conn->commit();
            return true;
       } catch (Exception $e) {
            $this->conn->rollback();
            error_log("❌ updateAccount() Lỗi: " . $e->getMessage());
            return false;
        }

    }

    public function deleteAccount($id) {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("SELECT IDNhanVien FROM taikhoan WHERE ID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($idnv);
            $stmt->fetch();
            $stmt->close();

            if ($idnv) {
                $stmtDelTK = $this->conn->prepare("DELETE FROM taikhoan WHERE ID = ?");
                $stmtDelTK->bind_param("i", $id);
                $stmtDelTK->execute();
                $stmtDelTK->close();

                $stmtDelNV = $this->conn->prepare("DELETE FROM nhanvien WHERE ID = ?");
                $stmtDelNV->bind_param("i", $idnv);
                $stmtDelNV->execute();
                $stmtDelNV->close();

                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}
?>
