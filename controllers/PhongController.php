<?php
require_once './config/db.php';
require_once './models/PhongModel.php';

class PhongController {
    private $model;

    public function __construct($db) {
        $this->model = new PhongModel($db); // Sửa 'Phong' thành 'PhongModel'
    }

    // Lấy tất cả phòng
    public function index() {
        return $this->model->getAll();
    }

    // Thêm phòng mới
    public function store($maPhong, $tenPhong) {
        // Có thể kiểm tra trùng mã ở đây nếu cần
        if ($this->model->isDuplicateMaPhong($maPhong)) {
            return ['status' => false, 'message' => 'Mã phông đã tồn tại.'];
        }

        $success = $this->model->create($maPhong, $tenPhong);
        return ['status' => $success, 'message' => $success ? 'Đã thêm thành công.' : 'Lỗi khi thêm.'];
    }

    // Xoá phòng
    public function destroy($id) {
        $success = $this->model->delete($id);
        return ['status' => $success, 'message' => $success ? 'Đã xoá thành công.' : 'Lỗi khi xoá.'];
    }

    // Cập nhật phòng
    public function update($id, $maPhong, $tenPhong) {
        if ($this->model->isDuplicateMaPhongExceptId($maPhong, $id)) {
            return ['status' => false, 'message' => 'Mã phông đã tồn tại.'];
        }

        $success = $this->model->update($id, $maPhong, $tenPhong);
        return ['status' => $success, 'message' => $success ? 'Đã cập nhật thành công.' : 'Lỗi khi cập nhật.'];
    }

    // Lấy phòng theo ID (tùy chọn nếu cần)
    public function show($id) {
        return $this->model->findById($id);
    }
}
