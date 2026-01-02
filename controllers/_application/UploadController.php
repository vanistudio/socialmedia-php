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

if (!isset($_FILES['file'])) {
    echo json_encode(["status" => "error", "message" => "Không có file"]);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "Upload lỗi (code: {$file['error']})"]);
    exit;
}

$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    echo json_encode(["status" => "error", "message" => "File quá lớn (tối đa 5MB)"]);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
];

if (!isset($allowed[$mime])) {
    echo json_encode(["status" => "error", "message" => "Định dạng không hỗ trợ"]);
    exit;
}

$ext = $allowed[$mime];

$mediaDir = $_SERVER['DOCUMENT_ROOT'] . '/public/media';
if (!is_dir($mediaDir)) {
    @mkdir($mediaDir, 0755, true);
}

$rand = bin2hex(random_bytes(10));
$filename = 'media_' . date('Ymd_His') . '_' . $rand . '.' . $ext;
$destPath = $mediaDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(["status" => "error", "message" => "Không thể lưu file"]);
    exit;
}
$path = '/public/media/' . $filename;
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$origin = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME']);
$url = $origin . $path;

echo json_encode([
    "status" => "success",
    "message" => "Upload thành công",
    "url" => $url,
    "path" => $path
]);