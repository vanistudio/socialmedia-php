<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Yêu cầu không hợp lệ"]));
}

if (!isset($_SESSION['email'])) {
    die(json_encode(["status" => "error", "message" => "Bạn cần đăng nhập"]));
}

$type = $_POST['type'] ?? '';
$currentEmail = $_SESSION['email'];
$currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '" . addslashes($currentEmail) . "'");

if (!$currentUser) {
    die(json_encode(["status" => "error", "message" => "Tài khoản không tồn tại"]));
}

if ($type === 'UPDATE_PROFILE') {
    $updateData = [];
    if (isset($_POST['full_name'])) {
        $updateData['full_name'] = check_string2($_POST['full_name']);
    }
    if (isset($_POST['username'])) {
        $updateData['username'] = check_string($_POST['username']);
    }
    if (isset($_POST['bio'])) {
        $updateData['bio'] = check_string2($_POST['bio']);
    }
    if (isset($_POST['avatar'])) {
        $updateData['avatar'] = check_string2($_POST['avatar']);
    }
    if (isset($_POST['banner'])) {
        $updateData['banner'] = check_string2($_POST['banner']);
    }
    if (isset($updateData['full_name']) && empty($updateData['full_name'])) {
        die(json_encode(["status" => "error", "message" => "Vui lòng nhập họ tên"]));
    }
    if (isset($updateData['username'])) {
        if (empty($updateData['username'])) {
            die(json_encode(["status" => "error", "message" => "Vui lòng nhập username"]));
        }
        if (strlen($updateData['username']) < 3) {
            die(json_encode(["status" => "error", "message" => "Username phải tối thiểu 3 ký tự"]));
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $updateData['username'])) {
            die(json_encode(["status" => "error", "message" => "Username chỉ được chứa chữ, số và dấu gạch dưới"]));
        }
        $exists = $Vani->get_row("SELECT * FROM `users` WHERE `username` = '{$updateData['username']}' AND `email` != '" . addslashes($currentEmail) . "'");
        if ($exists) {
            die(json_encode(["status" => "error", "message" => "Username đã tồn tại"]));
        }
    }

    if (empty($updateData)) {
        die(json_encode(["status" => "error", "message" => "Không có thông tin để cập nhật"]));
    }

    $Vani->update('users', $updateData, "`email` = '" . addslashes($currentEmail) . "'");
    die(json_encode(["status" => "success", "message" => "Cập nhật thành công"]));
}

if ($type === 'CHANGE_PASSWORD') {
    $current_password = check_string2($_POST['current_password'] ?? '');
    $new_password = check_string2($_POST['new_password'] ?? '');
    $confirm_password = check_string2($_POST['confirm_password'] ?? '');

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        die(json_encode(["status" => "error", "message" => "Vui lòng nhập đầy đủ mật khẩu"]));
    }

    if (!password_verify($current_password, $currentUser['password'])) {
        die(json_encode(["status" => "error", "message" => "Mật khẩu hiện tại không đúng"]));
    }

    if ($new_password !== $confirm_password) {
        die(json_encode(["status" => "error", "message" => "Mật khẩu xác nhận không khớp"]));
    }

    if (strlen($new_password) < 8) {
        die(json_encode(["status" => "error", "message" => "Mật khẩu phải tối thiểu 8 ký tự"]));
    }
    if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[^A-Za-z0-9]/', $new_password)) {
        die(json_encode(["status" => "error", "message" => "Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt"]));
    }

    $encoded = password_hash($new_password, PASSWORD_BCRYPT);
    $Vani->update('users', ['password' => $encoded], "`email` = '" . addslashes($currentEmail) . "'");

    die(json_encode(["status" => "success", "message" => "Đổi mật khẩu thành công"]));
}

die(json_encode(["status" => "error", "message" => "type không hợp lệ"]));
