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

// Xử lý cập nhật phông
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_phong'])) {
    $id = trim($_POST['id']);
    $maPhong = trim($_POST['maPhong']);
    $tenPhong = trim($_POST['tenPhong']);
    
    // Khởi tạo mảng lưu trữ lỗi
    $errors = [];

    // Kiểm tra ID hợp lệ
    if (!is_numeric($id) || $id <= 0) {
        $errors[] = "ID phòng không hợp lệ!";
    }

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
            // Kiểm tra xem bản ghi có tồn tại không
            $existStmt = $conn->prepare("SELECT COUNT(*) FROM phong WHERE ID = :id");
            $existStmt->bindParam(':id', $id);
            $existStmt->execute();
            if ($existStmt->fetchColumn() == 0) {
                $errors[] = "Phòng không tồn tại!";
            } else {
                // Kiểm tra trùng MaPhong, nhưng bỏ qua bản ghi hiện tại
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM phong WHERE MaPhong = :maPhong AND ID != :id");
                $checkStmt->bindParam(':maPhong', $maPhong);
                $checkStmt->bindParam(':id', $id);
                $checkStmt->execute();
                
                if ($checkStmt->fetchColumn() > 0) {
                    $errors[] = "Mã phòng đã tồn tại!";
                } else {
                    // Cập nhật phòng
                    $stmt = $conn->prepare("UPDATE phong SET MaPhong = :maPhong, TenPhong = :tenPhong WHERE ID = :id");
                    $stmt->bindParam(':id', $id);
                    $stmt->bindParam(':maPhong', $maPhong);
                    $stmt->bindParam(':tenPhong', $tenPhong);
                    $stmt->execute();
                    
                    // Redirect về dsphong.php với thông báo thành công
                    header("Location: dsphong.php?status=edit_success");
                    exit();
                }
                $checkStmt->closeCursor();
            }
            $existStmt->closeCursor();
        } catch (PDOException $e) {
            $errors[] = "Lỗi cập nhật phòng: " . $e->getMessage();
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
