<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Yêu cầu không hợp lệ"]);
    exit;
}
if (!isset($_SESSION['email'])) {
    echo json_encode(["status" => "error", "message" => "Bạn cần đăng nhập"]);
    exit;
}
$type = $_POST['type'] ?? '';
$currentEmail = $_SESSION['email'];
$currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '" . addslashes($currentEmail) . "'");
if (!$currentUser) {
    echo json_encode(["status" => "error", "message" => "Tài khoản không tồn tại"]);
    exit;
}

if ($type === 'UPDATE_PROFILE') {
    $full_name = check_string2($_POST['full_name'] ?? '');
    $username = check_string($_POST['username'] ?? '');
    $bio = check_string2($_POST['bio'] ?? '');
    $avatar = check_string2($_POST['avatar'] ?? '');
    $banner = check_string2($_POST['banner'] ?? '');

    if (empty($full_name) || empty($username)) {
        echo json_encode(["status" => "error", "message" => "Vui lòng nhập họ tên và username"]);
        exit;
    }
    if (strlen($username) < 3) {
        echo json_encode(["status" => "error", "message" => "Username phải tối thiểu 3 ký tự"]);
        exit;
    }
    if (!preg_match('/^[A-Za-z]+$/', $username)) {
        echo json_encode(["status" => "error", "message" => "Username chỉ được chứa chữ (A-Z, a-z)"]);
        exit;
    }
    $exists = $Vani->get_row("SELECT * FROM `users` WHERE `username` = '$username' AND `email` != '" . addslashes($currentEmail) . "'");
    if ($exists) {
        echo json_encode(["status" => "error", "message" => "Username đã tồn tại"]);
        exit;
    }

    $Vani->update('users', [
        'full_name' => $full_name,
        'username' => $username,
        'bio' => $bio,
        'avatar' => $avatar,
        'banner' => $banner,
    ], "`email` = '" . addslashes($currentEmail) . "'");

    echo json_encode(["status" => "success", "message" => "Cập nhật profile thành công"]);
    exit;
}
if ($type === 'CHANGE_PASSWORD') {
    $current_password = check_string2($_POST['current_password'] ?? '');
    $new_password = check_string2($_POST['new_password'] ?? '');
    $confirm_password = check_string2($_POST['confirm_password'] ?? '');

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(["status" => "error", "message" => "Vui lòng nhập đầy đủ mật khẩu"]);
        exit;
    }

    if (!password_verify($current_password, $currentUser['password'])) {
        echo json_encode(["status" => "error", "message" => "Mật khẩu hiện tại không đúng"]);
        exit;
    }

    if ($new_password !== $confirm_password) {
        echo json_encode(["status" => "error", "message" => "Mật khẩu xác nhận không khớp"]);
        exit;
    }

    if (strlen($new_password) < 8) {
        echo json_encode(["status" => "error", "message" => "Mật khẩu phải tối thiểu 8 ký tự"]);
        exit;
    }
    if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[^A-Za-z0-9]/', $new_password)) {
        echo json_encode(["status" => "error", "message" => "Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt"]);
        exit;
    }

    $encoded = password_hash($new_password, PASSWORD_BCRYPT);
    $Vani->update('users', ['password' => $encoded], "`email` = '" . addslashes($currentEmail) . "'");

    echo json_encode(["status" => "success", "message" => "Đổi mật khẩu thành công"]);
    exit;
}

echo json_encode(["status" => "error", "message" => "type không hợp lệ"]);

