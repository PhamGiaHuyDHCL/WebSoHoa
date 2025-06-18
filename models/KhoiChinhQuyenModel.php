<?php
require_once './config/db.php';

class KhoiChinhQuyenModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function insertKhoiChinhQuyen($data) {
        $sql = "INSERT INTO khoi_chinh_quyen (
                    ma_phong, ma_muc_luc, ma_dvbq, ma_ho_so,
                    so_van_ban, trich_yeu, ngay_vb, do_mat,
                    tac_gia, the_loai, so_trang, so_thu_tu,
                    nguoi_ky, trang_so
                ) VALUES (
                    :ma_phong, :ma_muc_luc, :ma_dvbq, :ma_ho_so,
                    :so_van_ban, :trich_yeu, :ngay_vb, :do_mat,
                    :tac_gia, :the_loai, :so_trang, :so_thu_tu,
                    :nguoi_ky, :trang_so
                )";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }
}
