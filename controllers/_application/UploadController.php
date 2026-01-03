<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Yêu cầu không hợp lệ"]);
    exit;
}

// CSRF Protection
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrfToken)) {
    echo json_encode(["status" => "error", "message" => "CSRF token không hợp lệ"]);
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

// File size validation
$maxSize = 10 * 1024 * 1024; // 10MB for videos, 5MB for images
if ($file['size'] > $maxSize) {
    echo json_encode(["status" => "error", "message" => "File quá lớn (tối đa 10MB)"]);
    exit;
}

if ($file['size'] === 0) {
    echo json_encode(["status" => "error", "message" => "File rỗng"]);
    exit;
}

// Validate file extension
$fileName = $file['name'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm', 'mov'];
if (!in_array($fileExt, $allowedExts)) {
    echo json_encode(["status" => "error", "message" => "Định dạng file không được phép"]);
    exit;
}

// Validate MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
    'video/mp4' => 'mp4',
    'video/webm' => 'webm',
    'video/quicktime' => 'mov',
];

if (!isset($allowedMimes[$mime])) {
    echo json_encode(["status" => "error", "message" => "Định dạng MIME không được phép"]);
    exit;
}

// Verify extension matches MIME type
$expectedExt = $allowedMimes[$mime];
if ($fileExt !== $expectedExt && !($fileExt === 'jpeg' && $expectedExt === 'jpg')) {
    echo json_encode(["status" => "error", "message" => "Định dạng file không khớp với nội dung"]);
    exit;
}

// Additional validation: Check if file is actually an image/video
$isImage = strpos($mime, 'image/') === 0;
$isVideo = strpos($mime, 'video/') === 0;

if ($isImage) {
    // Validate image by trying to open it
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        echo json_encode(["status" => "error", "message" => "File không phải là ảnh hợp lệ"]);
        exit;
    }
    
    // Check image dimensions (optional - prevent extremely large images)
    $maxWidth = 5000;
    $maxHeight = 5000;
    if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
        echo json_encode(["status" => "error", "message" => "Kích thước ảnh quá lớn (tối đa {$maxWidth}x{$maxHeight})"]);
        exit;
    }
    
    // Limit image file size to 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(["status" => "error", "message" => "Ảnh quá lớn (tối đa 5MB)"]);
        exit;
    }
} elseif ($isVideo) {
    // Limit video file size to 10MB
    if ($file['size'] > 10 * 1024 * 1024) {
        echo json_encode(["status" => "error", "message" => "Video quá lớn (tối đa 10MB)"]);
        exit;
    }
} else {
    echo json_encode(["status" => "error", "message" => "File không phải là ảnh hoặc video"]);
    exit;
}

$ext = $expectedExt;

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