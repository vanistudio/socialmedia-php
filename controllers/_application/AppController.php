<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';

header('Content-Type: application/json; charset=utf-8');

function json_error($msg)
{
    die(json_encode(["status" => "error", "message" => $msg]));
}

function json_success($msg, $extra = [])
{
    die(json_encode(array_merge(["status" => "success", "message" => $msg], $extra)));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Yêu cầu không hợp lệ');
}

$type = $_POST['type'] ?? '';

$authRequiredTypes = [
    'UPDATE_PROFILE',
    'CHANGE_PASSWORD',
    'CREATE_POST',
    'TOGGLE_LIKE',
    'ADD_COMMENT',
    'SAVE_POST',
    'REPORT_ENTITY',
    'TOGGLE_COMMENT_LIKE',
    'DELETE_COMMENT',
];

if (in_array($type, $authRequiredTypes, true)) {
    if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
        json_error('Bạn cần đăng nhập');
    }
}

$currentUser = null;
if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
    $currentEmail = addslashes($_SESSION['email']);
    $currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '$currentEmail'");
}

if (in_array($type, $authRequiredTypes, true) && !$currentUser) {
    json_error('Tài khoản không tồn tại');
}
if ($type === 'UPDATE_PROFILE') {
    $updateData = [];

    if (isset($_POST['full_name'])) {
        $updateData['full_name'] = check_string2($_POST['full_name']);
        if (empty($updateData['full_name'])) json_error('Vui lòng nhập họ tên');
    }

    if (isset($_POST['username'])) {
        $updateData['username'] = check_string($_POST['username']);
        if (empty($updateData['username'])) json_error('Vui lòng nhập username');
        if (strlen($updateData['username']) < 3) json_error('Username phải tối thiểu 3 ký tự');
        if (!preg_match('/^[A-Za-z0-9_]+$/', $updateData['username'])) json_error('Username chỉ được chứa chữ, số và dấu gạch dưới');

        $u = addslashes($updateData['username']);
        $exists = $Vani->get_row("SELECT * FROM `users` WHERE `username` = '$u' AND `id` != '" . intval($currentUser['id']) . "'");
        if ($exists) json_error('Username đã tồn tại');
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

    if (empty($updateData)) json_error('Không có thông tin để cập nhật');

    $Vani->update('users', $updateData, "`id` = '" . intval($currentUser['id']) . "'");
    json_success('Cập nhật thành công');
}

if ($type === 'CHANGE_PASSWORD') {
    $current_password = check_string2($_POST['current_password'] ?? '');
    $new_password = check_string2($_POST['new_password'] ?? '');
    $confirm_password = check_string2($_POST['confirm_password'] ?? '');

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) json_error('Vui lòng nhập đầy đủ mật khẩu');
    if (!password_verify($current_password, $currentUser['password'])) json_error('Mật khẩu hiện tại không đúng');
    if ($new_password !== $confirm_password) json_error('Mật khẩu xác nhận không khớp');

    if (strlen($new_password) < 8) json_error('Mật khẩu phải tối thiểu 8 ký tự');
    if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[^A-Za-z0-9]/', $new_password)) {
        json_error('Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt');
    }

    $encoded = password_hash($new_password, PASSWORD_BCRYPT);
    $Vani->update('users', ['password' => $encoded], "`id` = '" . intval($currentUser['id']) . "'");
    json_success('Đổi mật khẩu thành công');
}
if ($type === 'CREATE_POST') {
    $content = check_string2($_POST['content'] ?? '');
    $visibility = check_string2($_POST['visibility'] ?? 'public');

    $media = $_POST['media'] ?? [];
    if (!is_array($media)) $media = [];

    $mediaClean = [];
    foreach ($media as $m) {
        $m = trim($m);
        if ($m !== '') $mediaClean[] = $m;
    }

    if ($content === '' && count($mediaClean) === 0) {
        json_error('Bài viết không được để trống');
    }

    $allowedVisibility = ['public', 'followers', 'private'];
    if (!in_array($visibility, $allowedVisibility, true)) {
        $visibility = 'public';
    }

    $postId = $Vani->insert('posts', [
        'user_id' => intval($currentUser['id']),
        'content' => $content,
        'visibility' => $visibility,
    ]);

    if (!$postId) {
        json_error('Không thể tạo bài viết');
    }

    $sort = 0;
    foreach ($mediaClean as $url) {
        $sort++;
        $mediaType = 'image';
        $lower = strtolower($url);
        if (preg_match('/\.(mp4|webm|mov)(\?|$)/', $lower)) {
            $mediaType = 'video';
        }

        $Vani->insert('post_media', [
            'post_id' => intval($postId),
            'media_url' => $url,
            'media_type' => $mediaType,
            'sort_order' => $sort,
        ]);
    }

    json_success('Đăng bài thành công', ['post_id' => intval($postId)]);
}

if ($type === 'TOGGLE_LIKE') {
    $post_id = intval($_POST['post_id'] ?? 0);
    if ($post_id <= 0) json_error('post_id không hợp lệ');

    $uid = intval($currentUser['id']);
    $liked = $Vani->get_row("SELECT * FROM `post_likes` WHERE `post_id` = '$post_id' AND `user_id` = '$uid'");

    if ($liked) {
        $Vani->remove('post_likes', "`post_id` = '$post_id' AND `user_id` = '$uid'");
        json_success('Đã bỏ thích', ['liked' => false]);
    } else {
        $Vani->insert('post_likes', [
            'post_id' => $post_id,
            'user_id' => $uid,
        ]);
        json_success('Đã thích', ['liked' => true]);
    }
}

if ($type === 'ADD_COMMENT') {
    $post_id = intval($_POST['post_id'] ?? 0);
    $parent_id = intval($_POST['parent_id'] ?? 0);
    $content = check_string2($_POST['content'] ?? '');

    if ($post_id <= 0) json_error('post_id không hợp lệ');
    if ($content === '') json_error('Nội dung bình luận không được để trống');

    $post = $Vani->get_row("SELECT * FROM `posts` WHERE `id` = '$post_id'");
    if (!$post) json_error('Bài viết không tồn tại');

    if ($parent_id > 0) {
        $parent = $Vani->get_row("SELECT * FROM `post_comments` WHERE `id` = '$parent_id' AND `post_id` = '$post_id'");
        if (!$parent) json_error('Bình luận cha không tồn tại');
    } else {
        $parent_id = null;
    }

    $commentData = [
        'post_id' => $post_id,
        'user_id' => intval($currentUser['id']),
        'content' => $content,
    ];
    if ($parent_id !== null) {
        $commentData['parent_id'] = $parent_id;
    }

    $commentId = $Vani->insert('post_comments', $commentData);
    if (!$commentId) json_error('Không thể bình luận');

    json_success('Bình luận thành công', ['comment_id' => intval($commentId)]);
}
if ($type === 'TOGGLE_COMMENT_LIKE') {
    $comment_id = intval($_POST['comment_id'] ?? 0);
    if ($comment_id <= 0) json_error('comment_id không hợp lệ');

    $uid = intval($currentUser['id']);

    $comment = $Vani->get_row("SELECT * FROM `post_comments` WHERE `id` = '$comment_id'");
    if (!$comment) json_error('Bình luận không tồn tại');

    $liked = $Vani->get_row("SELECT * FROM `comment_likes` WHERE `comment_id` = '$comment_id' AND `user_id` = '$uid'");

    if ($liked) {
        $Vani->remove('comment_likes', "`comment_id` = '$comment_id' AND `user_id` = '$uid'");
        $count = $Vani->num_rows("SELECT * FROM `comment_likes` WHERE `comment_id` = '$comment_id'") ?: 0;
        json_success('Đã bỏ thích', ['liked' => false, 'like_count' => intval($count)]);
    } else {
        $Vani->insert('comment_likes', [
            'comment_id' => $comment_id,
            'user_id' => $uid,
        ]);
        $count = $Vani->num_rows("SELECT * FROM `comment_likes` WHERE `comment_id` = '$comment_id'") ?: 0;
        json_success('Đã thích', ['liked' => true, 'like_count' => intval($count)]);
    }
}
if ($type === 'DELETE_COMMENT') {
    $comment_id = intval($_POST['comment_id'] ?? 0);
    if ($comment_id <= 0) json_error('comment_id không hợp lệ');

    $uid = intval($currentUser['id']);
    $comment = $Vani->get_row("SELECT * FROM `post_comments` WHERE `id` = '$comment_id'");
    if (!$comment) json_error('Bình luận không tồn tại');

    if (intval($comment['user_id']) !== $uid) {
        json_error('Bạn không có quyền xóa bình luận này');
    }

    $Vani->remove('post_comments', "`id` = '$comment_id'");
    json_success('Đã xóa bình luận');
}

if ($type === 'SAVE_POST') {
    $post_id = intval($_POST['post_id'] ?? 0);
    if ($post_id <= 0) json_error('post_id không hợp lệ');

    $uid = intval($currentUser['id']);
    $saved = $Vani->get_row("SELECT * FROM `post_bookmarks` WHERE `post_id` = '$post_id' AND `user_id` = '$uid'");

    if ($saved) {
        $Vani->remove('post_bookmarks', "`post_id` = '$post_id' AND `user_id` = '$uid'");
        json_success('Đã bỏ lưu', ['saved' => false]);
    } else {
        $Vani->insert('post_bookmarks', [
            'post_id' => $post_id,
            'user_id' => $uid,
        ]);
        json_success('Đã lưu bài viết', ['saved' => true]);
    }
}

if ($type === 'REPORT_ENTITY') {
    $target_type = check_string2($_POST['target_type'] ?? '');
    $target_id = intval($_POST['target_id'] ?? 0);
    $reason = check_string2($_POST['reason'] ?? '');
    $detail = check_string2($_POST['detail'] ?? '');

    if (!in_array($target_type, ['user', 'post', 'comment'], true)) json_error('target_type không hợp lệ');
    if ($target_id <= 0) json_error('target_id không hợp lệ');
    if ($reason === '') json_error('Vui lòng chọn lý do');

    $Vani->insert('reports', [
        'reporter_id' => intval($currentUser['id']),
        'target_type' => $target_type,
        'target_id' => $target_id,
        'reason' => $reason,
        'detail' => $detail,
        'status' => 'open',
    ]);

    json_success('Đã gửi báo cáo');
}

json_error('type không hợp lệ');
