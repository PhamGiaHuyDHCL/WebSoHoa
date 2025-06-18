<?php
require_once './models/DangNhapModel.php';

class DangNhapController {
    private $model;

    public function __construct() {
        $this->model = new DangNhapModel();
    }

    public function dangNhap() {
        session_start();
        $thongBao = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taiKhoan = trim($_POST['TaiKhoan']);
            $matKhau = $_POST['MatKhau'];

            $user = $this->model->kiemTraTaiKhoan($taiKhoan);

            if ($user) {
                if (password_verify($matKhau, $user['MatKhau'])) {
                    $_SESSION['user_id'] = $user['ID'];
                    $_SESSION['username'] = $user['TaiKhoan'];
                    header("Location: http://localhost/websohoa1/");
                    exit;
                } else {
                    $thongBao = "❌ Mật khẩu không đúng!";
                }
            } else {
                $thongBao = "❌ Tài khoản không tồn tại!";
            }
        }

        include 'http://localhost/websohoa1/views/login/dangnhap.php'; // Giao diện form đăng nhập
       
    }
}
