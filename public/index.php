<?php
// Định nghĩa một số thông tin cần thiết
define('BASE_URL', '/');

// Điều hướng người dùng đến các trang khác dựa trên tham số
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'login':
        include '../views/login.php';
        break;
    case 'register':
        include '../views/register.php';
        break;
    case 'createPost':
        include '../views/createPost.php';
        break;
    case 'postDetail':
        include '../views/postDetail.php';
        break;
    case 'userProfile':
        include '../views/userProfile.php';
        break;
    default:
        include '../views/index.php';
}
?>
