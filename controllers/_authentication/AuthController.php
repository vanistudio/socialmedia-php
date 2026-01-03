<?php require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrfToken)) {
        die(json_encode(["status" => "error", "message" => "CSRF token không hợp lệ"]));
    }
    
    if ($_POST['type'] == "LOGIN") {
        $identifier = check_string($_POST['login_identifier']);
        $password = check_string($_POST['password']);
        if (empty($identifier) || empty($password)) {
            die(json_encode(["status" => "error", "message" => "Vui lòng nhập đầy đủ thông tin"]));
        } else {
            $user_data = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '$identifier' OR `username` = '$identifier'");
            if (!$user_data) {
                die(json_encode(["status" => "error", "message" => "Tài khoản hoặc email không tồn tại"]));
            } elseif (!password_verify($password, $user_data['password'])) {
                die(json_encode(["status" => "error", "message" => "Mật khẩu không chính xác"]));
            } else {
                $user_email = $user_data['email'];
                $Vani->update("users", ['session' => session_id()], "`email` = '$user_email'");
                $_SESSION['email'] = $user_email;
                die(json_encode(["status" => "success", "message" => "Đăng nhập thành công"]));
            }
        }
    }
    if ($_POST['type'] == "REGISTER") {
        $full_name = check_string2($_POST['full_name']);
        $email = check_string($_POST['email']);
        $username = check_string($_POST['username']);
        $password = check_string($_POST['password']);
        $re_password = check_string($_POST['re_password']);
        $terms = isset($_POST['terms']) ? check_string($_POST['terms']) : '';
        $terms_bool = ($terms === '1' || $terms === 1 || $terms === 'true' || $terms === true);
        if (empty($full_name) || empty($email) || empty($username) || empty($password) || empty($re_password)) {
            die(json_encode(["status" => "error", "message" => "Vui lòng nhập đầy đủ thông tin"]));
        } elseif (!$terms_bool) {
            die(json_encode(["status" => "error", "message" => "Bạn phải đồng ý với điều khoản và chính sách"]));
        } elseif (!preg_match('/^[\p{L}\s]+$/u', $full_name)) {
            die(json_encode(["status" => "error", "message" => "Họ và tên không được chứa ký tự đặc biệt"]));
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die(json_encode(["status" => "error", "message" => "Email không hợp lệ"]));
        } elseif (strlen($username) < 6) {
            die(json_encode(["status" => "error", "message" => "Tài khoản phải tối thiểu 6 ký tự"]));
        } elseif (!preg_match('/^[A-Za-z]+$/', $username)) {
            die(json_encode(["status" => "error", "message" => "Tên đăng nhập chỉ được chứa chữ hoa và chữ thường"]));
        } elseif (strlen($password) < 8) {
            die(json_encode(["status" => "error", "message" => "Mật khẩu phải tối thiểu 8 ký tự"]));
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
            die(json_encode(["status" => "error", "message" => "Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt"]));
        } elseif ($password != $re_password) {
            die(json_encode(["status" => "error", "message" => "Nhập lại mật khẩu không đúng"]));
        } else {
            $Check_username_or_email = $Vani->get_row("SELECT * FROM `users` WHERE `username` = '$username' OR `email` = '$email'");
            if ($Check_username_or_email) {
                die(json_encode(["status" => "error", "message" => "Tên đăng nhập hoặc email đã tồn tại"]));
            } else {
                $Encode_password = password_hash($password, PASSWORD_BCRYPT);
                $defaultAvatar = 'https://placehold.co/200x200/png?text=' . urlencode($username);
                $defaultBanner = 'https://placehold.co/1200x400/png?text=' . urlencode($username);

                $Vani->insert("users", [
                    'username' => $username,
                    'password' => $Encode_password,
                    'email' => $email,
                    'full_name' => $full_name,
                    'level' => 'user',
                    'avatar' => $defaultAvatar,
                    'banner' => $defaultBanner,
                    'bio' => ''
                ]);
                die(json_encode(["status" => "success", "message" => "Đăng ký thành công"]));
            }
        }
    }
    if ($_POST['type'] == "FORGOT_PASSWORD") {
        $email = check_string($_POST['email'] ?? '');
        
        if (empty($email)) {
            die(json_encode(["status" => "error", "message" => "Vui lòng nhập email"]));
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die(json_encode(["status" => "error", "message" => "Email không hợp lệ"]));
        }
        
        // Check if email exists (but don't reveal this to user for security)
        $user = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '$email'");
        
        // In a real application, you would send an email with a reset link here
        // For now, we just log this request and return success
        // This prevents email enumeration attacks
        
        if ($user) {
            // Log password reset request for admin review
            error_log("Password reset requested for user: " . $user['username'] . " (" . $email . ")");
        }
        
        // Always return success to prevent email enumeration
        die(json_encode(["status" => "success", "message" => "Nếu email tồn tại, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu."]));
    }
} else {
    die(json_encode(["status" => "error", "message" => "Yêu cầu không hợp lệ"]));
}
