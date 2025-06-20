<?php
ob_start(); // Bật output buffering để tránh lỗi header

// Kết nối cơ sở dữ liệu
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "qlsohoa";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Xử lý thêm phông mới
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_phong'])) {
    $maPhong = trim($_POST['maPhong']);
    $tenPhong = trim($_POST['tenPhong']);
    
    // Khởi tạo mảng lưu trữ lỗi
    $errors = [];

    // Ràng buộc 1: Kiểm tra trường không được để trống
    if (empty($maPhong)) {
        $errors[] = "Mã phòng không được để trống!";
    }
    
    if (empty($tenPhong)) {
        $errors[] = "Tên phòng không được để trống!";
    }

    // Ràng buộc 2: Kiểm tra độ dài
    if (strlen($maPhong) > 10) {
        $errors[] = "Mã phòng không được vượt quá 10 ký tự!";
    }
    
    if (strlen($tenPhong) > 50) {
        $errors[] = "Tên phòng không được vượt quá 50 ký tự!";
    }

    // Ràng buộc 3: Kiểm tra định dạng mã phòng (chỉ chấp nhận chữ cái, số và gạch dưới)
    if (!empty($maPhong) && !preg_match('/^[a-zA-Z0-9_]+$/', $maPhong)) {
        $errors[] = "Mã phòng chỉ được chứa chữ cái, số và gạch dưới!";
    }

    // Ràng buộc 4: Kiểm tra tên phòng không chứa ký tự đặc biệt nguy hiểm
    if (!empty($tenPhong) && preg_match('/[<>"\']/', $tenPhong)) {
        $errors[] = "Tên phòng không được chứa ký tự đặc biệt như <, >, \", '!";
    }

    // Nếu không có lỗi, tiến hành xử lý database
    if (empty($errors)) {
        try {
            // Kiểm tra xem MaPhong đã tồn tại chưa
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM phong WHERE MaPhong = :maPhong");
            $checkStmt->bindParam(':maPhong', $maPhong);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                $errors[] = "Mã phòng đã tồn tại!";
            } else {
                // Thêm mới phòng
                $stmt = $conn->prepare("INSERT INTO phong (MaPhong, TenPhong) VALUES (:maPhong, :tenPhong)");
                $stmt->bindParam(':maPhong', $maPhong);
                $stmt->bindParam(':tenPhong', $tenPhong);
                $stmt->execute();
                
                // Redirect về dsphong.php với thông báo thành công
                header("Location: dsphong.php?status=add_success");
                exit();
            }
            $checkStmt->closeCursor();
        } catch (PDOException $e) {
            $errors[] = "Lỗi thêm phòng: " . $e->getMessage();
        }
    }

    // Nếu có lỗi, redirect về dsphong.php với thông báo lỗi
    if (!empty($errors)) {
        $errorString = implode('|', $errors);
        header("Location: dsphong.php?status=error&errors=" . urlencode($errorString));
        exit();
    }
}

ob_end_flush();
?>
