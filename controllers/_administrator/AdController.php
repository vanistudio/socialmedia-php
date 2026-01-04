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

if (!isset($_SESSION['csrf_token'])) {
    generate_csrf_token();
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrfToken)) {
    json_error('CSRF token không hợp lệ. Vui lòng tải lại trang.');
}

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    json_error('Bạn cần đăng nhập');
}

$currentEmail = addslashes($_SESSION['email']);
$currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '$currentEmail'");

if (!$currentUser) {
    json_error('Tài khoản không tồn tại');
}

function isAdmin($user) {
    $level = $user['level'] ?? '';
    return $level === 'admin' || $level === 'administrator';
}

if (!isAdmin($currentUser)) {
    json_error('Bạn không có quyền truy cập');
}

$type = $_POST['type'] ?? '';

if ($type === 'REVIEW_MODERATION') {
    $logId = intval($_POST['log_id'] ?? 0);
    $reviewStatus = $_POST['review_status'] ?? '';
    
    if ($logId <= 0) json_error('log_id không hợp lệ');
    if (!in_array($reviewStatus, ['approved', 'rejected'])) json_error('review_status không hợp lệ');
    
    $log = $Vani->get_row("SELECT * FROM content_moderation_logs WHERE id = '$logId'");
    if (!$log) json_error('Log không tồn tại');
    
    $Vani->update('content_moderation_logs', [
        'review_status' => $reviewStatus,
        'reviewed_by' => intval($currentUser['id']),
        'reviewed_at' => date('Y-m-d H:i:s'),
    ], "`id` = '$logId'");
    
    if ($reviewStatus === 'approved' && !empty($log['related_id'])) {
        $relatedId = intval($log['related_id']);
        $contentType = $log['content_type'];
        
        if ($contentType === 'post') {
            $Vani->remove('post_media', "`post_id` = '$relatedId'");
            $Vani->remove('post_likes', "`post_id` = '$relatedId'");
            $Vani->remove('post_bookmarks', "`post_id` = '$relatedId'");
            $Vani->remove('post_comments', "`post_id` = '$relatedId'");
            $Vani->remove('posts', "`id` = '$relatedId'");
        } elseif ($contentType === 'comment') {
            $Vani->remove('comment_likes', "`comment_id` = '$relatedId'");
            $Vani->remove('post_comments', "`id` = '$relatedId'");
        }
    }
    
    json_success('Đã cập nhật trạng thái review');
}

if ($type === 'GET_BLACKLIST_KEYWORDS') {
    $keywords = $Vani->get_list("SELECT * FROM blacklist_keywords ORDER BY created_at DESC");
    json_success('Lấy danh sách từ khóa thành công', ['keywords' => $keywords]);
}

if ($type === 'ADD_BLACKLIST_KEYWORD') {
    $keyword = trim($_POST['keyword'] ?? '');
    
    if (empty($keyword)) json_error('Vui lòng nhập từ khóa');
    if (strlen($keyword) > 255) json_error('Từ khóa quá dài (tối đa 255 ký tự)');
    
    $keywordEsc = addslashes($keyword);
    $exists = $Vani->get_row("SELECT * FROM blacklist_keywords WHERE keyword = '$keywordEsc'");
    if ($exists) json_error('Từ khóa đã tồn tại');
    
    $Vani->insert('blacklist_keywords', [
        'keyword' => $keyword,
        'active' => 1,
    ]);
    
    json_success('Đã thêm từ khóa thành công');
}

if ($type === 'UPDATE_BLACKLIST_KEYWORD') {
    $id = intval($_POST['id'] ?? 0);
    $keyword = trim($_POST['keyword'] ?? '');
    $active = intval($_POST['active'] ?? 1);
    
    if ($id <= 0) json_error('id không hợp lệ');
    if (empty($keyword)) json_error('Vui lòng nhập từ khóa');
    
    $exists = $Vani->get_row("SELECT * FROM blacklist_keywords WHERE id = '$id'");
    if (!$exists) json_error('Từ khóa không tồn tại');
    
    $keywordEsc = addslashes($keyword);
    $duplicate = $Vani->get_row("SELECT * FROM blacklist_keywords WHERE keyword = '$keywordEsc' AND id != '$id'");
    if ($duplicate) json_error('Từ khóa đã tồn tại');
    
    $Vani->update('blacklist_keywords', [
        'keyword' => $keyword,
        'active' => $active,
    ], "`id` = '$id'");
    
    json_success('Đã cập nhật từ khóa thành công');
}

if ($type === 'DELETE_BLACKLIST_KEYWORD') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) json_error('id không hợp lệ');
    
    $exists = $Vani->get_row("SELECT * FROM blacklist_keywords WHERE id = '$id'");
    if (!$exists) json_error('Từ khóa không tồn tại');
    
    $Vani->remove('blacklist_keywords', "`id` = '$id'");
    
    json_success('Đã xóa từ khóa thành công');
}

if ($type === 'RESOLVE_REPORT') {
    $reportId = intval($_POST['report_id'] ?? 0);
    $reportStatus = $_POST['report_status'] ?? '';
    
    if ($reportId <= 0) json_error('report_id không hợp lệ');
    if (!in_array($reportStatus, ['resolved', 'dismissed'])) json_error('report_status không hợp lệ');
    
    $exists = $Vani->get_row("SELECT * FROM reports WHERE id = '$reportId'");
    if (!$exists) json_error('Report không tồn tại');
    
    $Vani->update('reports', [
        'status' => $reportStatus,
    ], "`id` = '$reportId'");
    
    json_success('Đã cập nhật trạng thái báo cáo');
}

if ($type === 'DELETE_REPORTED_CONTENT') {
    $reportId = intval($_POST['report_id'] ?? 0);
    $targetType = $_POST['entity_type'] ?? $_POST['target_type'] ?? '';
    $targetId = intval($_POST['entity_id'] ?? $_POST['target_id'] ?? 0);
    
    if ($reportId <= 0) json_error('report_id không hợp lệ');
    if ($targetId <= 0) json_error('target_id không hợp lệ');
    
    if ($targetType === 'post') {
        $Vani->remove('post_media', "`post_id` = '$targetId'");
        $Vani->remove('post_likes', "`post_id` = '$targetId'");
        $Vani->remove('post_bookmarks', "`post_id` = '$targetId'");
        $Vani->remove('post_comments', "`post_id` = '$targetId'");
        $Vani->remove('posts', "`id` = '$targetId'");
    } elseif ($targetType === 'comment') {
        $Vani->remove('comment_likes', "`comment_id` = '$targetId'");
        $Vani->remove('post_comments', "`id` = '$targetId'");
    } elseif ($targetType === 'user') {
    }
    
    $Vani->update('reports', [
        'status' => 'resolved',
    ], "`id` = '$reportId'");
    
    json_success('Đã xóa nội dung và giải quyết báo cáo');
}

if ($type === 'ADMIN_CHANGE_USER_LEVEL') {
    $userId = intval($_POST['user_id'] ?? 0);
    $level = $_POST['level'] ?? '';
    
    if ($userId <= 0) json_error('user_id không hợp lệ');
    if (!in_array($level, ['member', 'admin', 'administrator'])) json_error('level không hợp lệ');
    
    if ($userId == $currentUser['id'] && $level !== 'administrator') {
        json_error('Bạn không thể tự hạ cấp chính mình');
    }
    
    $targetUser = $Vani->get_row("SELECT * FROM users WHERE id = '$userId'");
    if (!$targetUser) json_error('User không tồn tại');
    
    $Vani->update('users', [
        'level' => $level,
    ], "`id` = '$userId'");
    
    json_success('Đã cập nhật level thành công');
}

if ($type === 'ADMIN_RESET_PASSWORD') {
    $userId = intval($_POST['user_id'] ?? 0);
    $newPassword = $_POST['new_password'] ?? '';
    
    if ($userId <= 0) json_error('user_id không hợp lệ');
    if (strlen($newPassword) < 6) json_error('Mật khẩu phải có ít nhất 6 ký tự');
    
    $targetUser = $Vani->get_row("SELECT * FROM users WHERE id = '$userId'");
    if (!$targetUser) json_error('User không tồn tại');
    
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    $Vani->update('users', [
        'password' => $hashedPassword,
        'session' => null,
    ], "`id` = '$userId'");
    
    json_success('Đã reset mật khẩu thành công');
}

if ($type === 'ADMIN_DELETE_USER') {
    $userId = intval($_POST['user_id'] ?? 0);
    
    if ($userId <= 0) json_error('user_id không hợp lệ');
    
    if ($userId == $currentUser['id']) {
        json_error('Bạn không thể xóa chính mình');
    }
    
    $targetUser = $Vani->get_row("SELECT * FROM users WHERE id = '$userId'");
    if (!$targetUser) json_error('User không tồn tại');
    
    $posts = $Vani->get_list("SELECT id FROM posts WHERE user_id = '$userId'");
    foreach ($posts as $post) {
        $postId = $post['id'];
        $Vani->remove('post_media', "`post_id` = '$postId'");
        $Vani->remove('post_likes', "`post_id` = '$postId'");
        $Vani->remove('post_bookmarks', "`post_id` = '$postId'");
        $Vani->remove('post_comments', "`post_id` = '$postId'");
    }
    $Vani->remove('posts', "`user_id` = '$userId'");
    
    $Vani->remove('post_comments', "`user_id` = '$userId'");
    
    $Vani->remove('post_likes', "`user_id` = '$userId'");
    $Vani->remove('comment_likes', "`user_id` = '$userId'");
    
    $Vani->remove('post_bookmarks', "`user_id` = '$userId'");
    
    $Vani->remove('follows', "`follower_id` = '$userId' OR `following_id` = '$userId'");
    
    $Vani->remove('user_blocks', "`blocker_id` = '$userId' OR `blocked_id` = '$userId'");
    
    $Vani->remove('notifications', "`user_id` = '$userId' OR `actor_id` = '$userId'");
    
    $Vani->remove('messages', "`sender_id` = '$userId'");
    $Vani->remove('message_reads', "`user_id` = '$userId'");
    $Vani->remove('conversation_members', "`user_id` = '$userId'");
    
    $Vani->remove('reports', "`reporter_id` = '$userId'");
    
    $Vani->remove('content_moderation_logs', "`user_id` = '$userId'");
    
    $Vani->remove('users', "`id` = '$userId'");
    
    json_success('Đã xóa user và tất cả dữ liệu liên quan');
}

if ($type === 'ADMIN_UPDATE_SETTINGS') {
    $settings = $_POST['settings'] ?? '';
    $settingsData = json_decode($settings, true);
    
    if (!is_array($settingsData)) {
        json_error('Dữ liệu settings không hợp lệ');
    }
    
    foreach ($settingsData as $key => $value) {
        $keyEsc = addslashes($key);
        
        $exists = $Vani->get_row("SELECT * FROM settings WHERE `key` = '$keyEsc'");
        
        if ($exists) {
            $Vani->update('settings', [
                'value' => $value,
            ], "`key` = '$keyEsc'");
        } else {
            $Vani->insert('settings', [
                'key' => $key,
                'value' => $value,
            ]);
        }
    }
    
    json_success('Đã cập nhật cài đặt thành công');
}

if ($type === 'GET_DASHBOARD_STATS') {
    $stats = [
        'users' => $Vani->num_rows("SELECT id FROM users") ?: 0,
        'posts' => $Vani->num_rows("SELECT id FROM posts") ?: 0,
        'comments' => $Vani->num_rows("SELECT id FROM post_comments") ?: 0,
        'messages' => $Vani->num_rows("SELECT id FROM messages") ?: 0,
        'reports' => $Vani->num_rows("SELECT id FROM reports WHERE status = 'open'") ?: 0,
        'moderation' => $Vani->num_rows("SELECT id FROM content_moderation_logs WHERE review_status IS NULL") ?: 0,
        'today_users' => $Vani->num_rows("SELECT id FROM users WHERE DATE(created_at) = CURDATE()") ?: 0,
        'today_posts' => $Vani->num_rows("SELECT id FROM posts WHERE DATE(created_at) = CURDATE()") ?: 0,
    ];
    
    json_success('Lấy thống kê thành công', ['stats' => $stats]);
}

if ($type === 'GET_USERS') {
    $page = intval($_POST['page'] ?? 1);
    $limit = intval($_POST['limit'] ?? 20);
    $search = $_POST['search'] ?? '';
    $level = $_POST['level'] ?? '';
    $offset = ($page - 1) * $limit;
    
    $whereClause = "1=1";
    if (!empty($search)) {
        $searchEsc = addslashes($search);
        $whereClause .= " AND (full_name LIKE '%$searchEsc%' OR username LIKE '%$searchEsc%' OR email LIKE '%$searchEsc%')";
    }
    if (!empty($level)) {
        $levelEsc = addslashes($level);
        $whereClause .= " AND level = '$levelEsc'";
    }
    
    $users = $Vani->get_list("
        SELECT *,
            (SELECT COUNT(*) FROM posts WHERE user_id = users.id) as posts_count,
            (SELECT COUNT(*) FROM follows WHERE follower_id = users.id) as following_count,
            (SELECT COUNT(*) FROM follows WHERE following_id = users.id) as followers_count
        FROM users
        WHERE $whereClause
        ORDER BY created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    
    $total = $Vani->num_rows("SELECT id FROM users WHERE $whereClause") ?: 0;
    
    json_success('Lấy danh sách users thành công', [
        'users' => $users,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
    ]);
}

if ($type === 'GET_REPORTS') {
    $page = intval($_POST['page'] ?? 1);
    $limit = intval($_POST['limit'] ?? 20);
    $status = $_POST['status'] ?? 'open';
    $offset = ($page - 1) * $limit;
    
    $statusFilter = "1=1";
    if ($status === 'open') {
        $statusFilter = "r.status = 'open'";
    } elseif ($status === 'resolved') {
        $statusFilter = "r.status = 'resolved'";
    } elseif ($status === 'dismissed') {
        $statusFilter = "r.status = 'dismissed'";
    }
    
    $reports = $Vani->get_list("
        SELECT 
            r.*,
            u.username as reporter_username,
            u.full_name as reporter_name,
            u.avatar as reporter_avatar
        FROM reports r
        JOIN users u ON r.reporter_id = u.id
        WHERE $statusFilter
        ORDER BY r.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    
    $total = $Vani->num_rows("SELECT id FROM reports WHERE " . str_replace('r.', '', $statusFilter)) ?: 0;
    
    json_success('Lấy danh sách reports thành công', [
        'reports' => $reports,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
    ]);
}

if ($type === 'GET_MODERATION_LOGS') {
    $page = intval($_POST['page'] ?? 1);
    $limit = intval($_POST['limit'] ?? 20);
    $status = $_POST['status'] ?? 'pending';
    $offset = ($page - 1) * $limit;
    
    $statusFilter = "1=1";
    if ($status === 'pending') {
        $statusFilter = "review_status IS NULL";
    } elseif ($status === 'approved') {
        $statusFilter = "review_status = 'approved'";
    } elseif ($status === 'rejected') {
        $statusFilter = "review_status = 'rejected'";
    }
    
    $logs = $Vani->get_list("
        SELECT 
            m.*,
            u.username,
            u.full_name,
            u.avatar,
            r.username as reviewer_username,
            r.full_name as reviewer_full_name
        FROM content_moderation_logs m
        JOIN users u ON m.user_id = u.id
        LEFT JOIN users r ON m.reviewed_by = r.id
        WHERE $statusFilter
        ORDER BY m.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    
    $total = $Vani->num_rows("SELECT id FROM content_moderation_logs WHERE $statusFilter") ?: 0;
    
    json_success('Lấy danh sách moderation logs thành công', [
        'logs' => $logs,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
    ]);
}

json_error('type không hợp lệ');