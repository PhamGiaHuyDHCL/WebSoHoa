<?php
// Khởi động session nếu cần đăng nhập
session_start();

// Kết nối CSDL
require_once './config/db.php';

// Lấy controller và action từ URL, mặc định là HomeController@index
$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// Tạo đường dẫn tới file controller
$controllerFile = './controllers/' . ucfirst($controller) . 'Controller.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;

    // Tạo tên class controller
    $controllerClass = ucfirst($controller) . 'Controller';

    // Kiểm tra class có tồn tại không
    if (class_exists($controllerClass)) {
        $ctrl = new $controllerClass();

        // Gọi phương thức (action) nếu tồn tại
        if (method_exists($ctrl, $action)) {
            $ctrl->$action();
        } else {
            echo "⚠️ Không tìm thấy phương thức `$action()` trong controller `$controllerClass`.";
        }
    } else {
        echo "❌ Không tìm thấy class `$controllerClass` trong file `$controllerFile`.";
    }
} else {
    echo "❌ Không tìm thấy controller file `$controllerFile`.";
}
