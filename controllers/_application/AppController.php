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
    'UPDATE_POST',
    'DELETE_POST',
    'GET_POST',
    'TOGGLE_LIKE',
    'ADD_COMMENT',
    'SAVE_POST',
    'REPORT_ENTITY',
    'TOGGLE_COMMENT_LIKE',
    'DELETE_COMMENT',
    'GET_CONVERSATIONS',
    'CREATE_CONVERSATION',
    'GET_MESSAGES',
    'SEND_MESSAGE',
    'MARK_READ',
    'GET_UNREAD_COUNT',
    'SEARCH_USERS',
    'TOGGLE_FOLLOW',
    'GET_NOTIFICATIONS',
    'MARK_NOTIFICATION_READ',
    'TOGGLE_BLOCK',
    'SEARCH_POSTS',
    'SEARCH_ALL',
    'GET_FOLLOWERS',
    'GET_FOLLOWING',
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

if ($type === 'UPDATE_POST') {
    $post_id = intval($_POST['post_id'] ?? 0);
    $content = check_string2($_POST['content'] ?? '');
    $visibility = check_string2($_POST['visibility'] ?? 'public');
    
    if ($post_id <= 0) json_error('post_id không hợp lệ');
    
    $post = $Vani->get_row("SELECT * FROM `posts` WHERE `id` = '$post_id'");
    if (!$post) json_error('Bài viết không tồn tại');
    
    $uid = intval($currentUser['id']);
    if (intval($post['user_id']) !== $uid) {
        json_error('Bạn không có quyền chỉnh sửa bài viết này');
    }
    
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
    
    $Vani->update('posts', [
        'content' => $content,
        'visibility' => $visibility,
    ], "`id` = '$post_id'");
    
    $Vani->remove('post_media', "`post_id` = '$post_id'");
    
    $sort = 0;
    foreach ($mediaClean as $url) {
        $sort++;
        $mediaType = 'image';
        $lower = strtolower($url);
        if (preg_match('/\.(mp4|webm|mov)(\?|$)/', $lower)) {
            $mediaType = 'video';
        }
        
        $Vani->insert('post_media', [
            'post_id' => intval($post_id),
            'media_url' => $url,
            'media_type' => $mediaType,
            'sort_order' => $sort,
        ]);
    }
    
    json_success('Cập nhật bài viết thành công', ['post_id' => intval($post_id)]);
}

if ($type === 'DELETE_POST') {
    $post_id = intval($_POST['post_id'] ?? 0);
    if ($post_id <= 0) json_error('post_id không hợp lệ');
    
    $post = $Vani->get_row("SELECT * FROM `posts` WHERE `id` = '$post_id'");
    if (!$post) json_error('Bài viết không tồn tại');
    
    $uid = intval($currentUser['id']);
    if (intval($post['user_id']) !== $uid) {
        json_error('Bạn không có quyền xóa bài viết này');
    }
    
    $Vani->remove('posts', "`id` = '$post_id'");
    
    json_success('Đã xóa bài viết');
}

if ($type === 'GET_POST') {
    $post_id = intval($_POST['post_id'] ?? 0);
    if ($post_id <= 0) json_error('post_id không hợp lệ');
    
    $post = $Vani->get_row("SELECT * FROM `posts` WHERE `id` = '$post_id'");
    if (!$post) json_error('Bài viết không tồn tại');
    
    $uid = isset($currentUser) ? intval($currentUser['id']) : 0;
    $postOwnerId = intval($post['user_id']);
    $visibility = $post['visibility'] ?? 'public';
    
    // Check permission: owner can always view, others can view public posts
    if ($uid !== $postOwnerId) {
        if ($visibility !== 'public') {
            // Check if user is following (for followers visibility)
            if ($visibility === 'followers' && $uid > 0) {
                $isFollowing = $Vani->get_row("SELECT id FROM `follows` WHERE `follower_id` = '$uid' AND `following_id` = '$postOwnerId'");
                if (!$isFollowing) {
                    json_error('Bạn không có quyền xem bài viết này');
                }
            } else {
                json_error('Bạn không có quyền xem bài viết này');
            }
        }
        
        // Check if user is blocked
        if ($uid > 0) {
            $isBlocked = $Vani->get_row("SELECT id FROM `user_blocks` WHERE (`blocker_id` = '$uid' AND `blocked_id` = '$postOwnerId') OR (`blocker_id` = '$postOwnerId' AND `blocked_id` = '$uid')");
            if ($isBlocked) {
                json_error('Bạn không có quyền xem bài viết này');
            }
        }
    }
    
    $media = $Vani->get_list("SELECT media_url FROM `post_media` WHERE `post_id` = '$post_id' ORDER BY `sort_order` ASC");
    $mediaUrls = array_column($media, 'media_url');
    
    json_success('Lấy bài viết thành công', [
        'post' => [
            'id' => intval($post['id']),
            'content' => $post['content'],
            'visibility' => $visibility,
            'media' => $mediaUrls,
        ]
    ]);
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
        
        // Tạo notification cho chủ bài viết
        $post = $Vani->get_row("SELECT user_id FROM `posts` WHERE `id` = '$post_id'");
        if ($post && intval($post['user_id']) !== $uid) {
            $Vani->insert("notifications", [
                'user_id' => intval($post['user_id']),
                'actor_id' => $uid,
                'type' => 'like',
                'entity_type' => 'post',
                'entity_id' => $post_id
            ]);
        }
        
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

    // Tạo notification
    $postOwnerId = intval($post['user_id']);
    $commenterId = intval($currentUser['id']);
    
    if ($parent_id > 0) {
        // Reply to comment - notify comment owner
        $parentComment = $Vani->get_row("SELECT user_id FROM `post_comments` WHERE `id` = '$parent_id'");
        if ($parentComment && intval($parentComment['user_id']) !== $commenterId) {
            $Vani->insert("notifications", [
                'user_id' => intval($parentComment['user_id']),
                'actor_id' => $commenterId,
                'type' => 'reply',
                'entity_type' => 'comment',
                'entity_id' => intval($commentId)
            ]);
        }
    }
    
    // Notify post owner (if not the commenter and not already notified for reply)
    if ($postOwnerId !== $commenterId && ($parent_id <= 0 || intval($parentComment['user_id']) !== $postOwnerId)) {
        $Vani->insert("notifications", [
            'user_id' => $postOwnerId,
            'actor_id' => $commenterId,
            'type' => 'comment',
            'entity_type' => 'post',
            'entity_id' => $post_id
        ]);
    }

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
if ($type === 'GET_CONVERSATIONS') {
    $uid = intval($currentUser['id']);
    
    $conversations = $Vani->get_list("
        SELECT 
            c.id,
            c.type,
            c.title,
            c.created_at,
            (SELECT content FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
            (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time,
            (SELECT sender_id FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_sender_id,
            (SELECT COUNT(*) FROM messages m 
             LEFT JOIN message_reads mr ON m.id = mr.message_id AND mr.user_id = '$uid'
             WHERE m.conversation_id = c.id AND m.sender_id != '$uid' AND mr.id IS NULL) as unread_count
        FROM conversations c
        INNER JOIN conversation_members cm ON c.id = cm.conversation_id
        WHERE cm.user_id = '$uid'
        ORDER BY last_message_time DESC, c.created_at DESC
    ");
    
    $result = [];
    foreach ($conversations as $conv) {
        $convId = intval($conv['id']);
        $otherMembers = $Vani->get_list("
            SELECT u.id, u.username, u.full_name, u.avatar
            FROM conversation_members cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.conversation_id = '$convId' AND cm.user_id != '$uid'
        ");
        
        $result[] = [
            'id' => $convId,
            'type' => $conv['type'],
            'title' => $conv['title'],
            'last_message' => $conv['last_message'],
            'last_message_time' => $conv['last_message_time'],
            'last_sender_id' => intval($conv['last_sender_id'] ?? 0),
            'unread_count' => intval($conv['unread_count'] ?? 0),
            'members' => $otherMembers,
        ];
    }
    
    json_success('Lấy danh sách thành công', ['conversations' => $result]);
}

if ($type === 'CREATE_CONVERSATION') {
    $target_user_id = intval($_POST['target_user_id'] ?? 0);
    if ($target_user_id <= 0) json_error('target_user_id không hợp lệ');
    if ($target_user_id == $currentUser['id']) json_error('Không thể tạo cuộc trò chuyện với chính mình');
    
    $targetUser = $Vani->get_row("SELECT * FROM `users` WHERE `id` = '$target_user_id'");
    if (!$targetUser) json_error('Người dùng không tồn tại');
    
    $uid = intval($currentUser['id']);
    $existing = $Vani->get_row("
        SELECT c.id
        FROM conversations c
        INNER JOIN conversation_members cm1 ON c.id = cm1.conversation_id
        INNER JOIN conversation_members cm2 ON c.id = cm2.conversation_id
        WHERE c.type = 'direct'
        AND cm1.user_id = '$uid'
        AND cm2.user_id = '$target_user_id'
        AND (SELECT COUNT(*) FROM conversation_members WHERE conversation_id = c.id) = 2
    ");
    
    if ($existing) {
        json_success('Cuộc trò chuyện đã tồn tại', ['conversation_id' => intval($existing['id'])]);
    }
    $convId = $Vani->insert('conversations', [
        'type' => 'direct',
        'title' => null,
        'created_by' => $uid,
    ]);
    
    if (!$convId) json_error('Không thể tạo cuộc trò chuyện');
    
    $Vani->insert('conversation_members', [
        'conversation_id' => $convId,
        'user_id' => $uid,
        'role' => 'member',
    ]);
    
    $Vani->insert('conversation_members', [
        'conversation_id' => $convId,
        'user_id' => $target_user_id,
        'role' => 'member',
    ]);
    
    json_success('Tạo cuộc trò chuyện thành công', ['conversation_id' => intval($convId)]);
}

if ($type === 'GET_MESSAGES') {
    $conversation_id = intval($_POST['conversation_id'] ?? 0);
    if ($conversation_id <= 0) json_error('conversation_id không hợp lệ');
    
    $uid = intval($currentUser['id']);
    $member = $Vani->get_row("SELECT * FROM conversation_members WHERE conversation_id = '$conversation_id' AND user_id = '$uid'");
    if (!$member) json_error('Bạn không có quyền xem cuộc trò chuyện này');
    
    $limit = intval($_POST['limit'] ?? 50);
    $offset = intval($_POST['offset'] ?? 0);
    if ($limit > 100) $limit = 100;
    
    $messages = $Vani->get_list("
        SELECT 
            m.id,
            m.sender_id,
            m.content,
            m.media_url,
            m.created_at,
            u.username,
            u.full_name,
            u.avatar,
            (SELECT COUNT(*) FROM message_reads WHERE message_id = m.id AND user_id = '$uid') as is_read
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = '$conversation_id'
        ORDER BY m.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    
    $result = array_reverse($messages);
    
    json_success('Lấy tin nhắn thành công', ['messages' => $result]);
}

if ($type === 'SEND_MESSAGE') {
    $conversation_id = intval($_POST['conversation_id'] ?? 0);
    $content = check_string2($_POST['content'] ?? '');
    $media_url = check_string2($_POST['media_url'] ?? '');
    
    if ($conversation_id <= 0) json_error('conversation_id không hợp lệ');
    if ($content === '' && $media_url === '') json_error('Nội dung hoặc media không được để trống');
    
    $uid = intval($currentUser['id']);
    $member = $Vani->get_row("SELECT * FROM conversation_members WHERE conversation_id = '$conversation_id' AND user_id = '$uid'");
    if (!$member) json_error('Bạn không có quyền gửi tin nhắn trong cuộc trò chuyện này');
    
    $messageData = [
        'conversation_id' => $conversation_id,
        'sender_id' => $uid,
        'content' => $content ?: null,
        'media_url' => $media_url ?: null,
    ];
    
    $messageId = $Vani->insert('messages', $messageData);
    if (!$messageId) json_error('Không thể gửi tin nhắn');
    $message = $Vani->get_row("
        SELECT 
            m.id,
            m.sender_id,
            m.content,
            m.media_url,
            m.created_at,
            u.username,
            u.full_name,
            u.avatar
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.id = '$messageId'
    ");
    
    json_success('Gửi tin nhắn thành công', ['message' => $message]);
}

if ($type === 'MARK_READ') {
    $conversation_id = intval($_POST['conversation_id'] ?? 0);
    if ($conversation_id <= 0) json_error('conversation_id không hợp lệ');
    
    $uid = intval($currentUser['id']);
    $messages = $Vani->get_list("
        SELECT m.id
        FROM messages m
        WHERE m.conversation_id = '$conversation_id'
        AND m.sender_id != '$uid'
        AND m.id NOT IN (SELECT message_id FROM message_reads WHERE user_id = '$uid')
    ");
    
    foreach ($messages as $msg) {
        $msgId = intval($msg['id']);
        $existing = $Vani->get_row("SELECT * FROM message_reads WHERE message_id = '$msgId' AND user_id = '$uid'");
        if (!$existing) {
            $Vani->insert('message_reads', [
                'message_id' => $msgId,
                'user_id' => $uid,
            ]);
        }
    }
    
    json_success('Đã đánh dấu đã đọc');
}

if ($type === 'GET_UNREAD_COUNT') {
    $uid = intval($currentUser['id']);
    
    $unreadCount = $Vani->num_rows("
        SELECT m.id
        FROM messages m
        LEFT JOIN message_reads mr ON m.id = mr.message_id AND mr.user_id = '$uid'
        INNER JOIN conversation_members cm ON m.conversation_id = cm.conversation_id
        WHERE cm.user_id = '$uid'
        AND m.sender_id != '$uid'
        AND mr.id IS NULL
    ") ?: 0;
    
    json_success('Lấy số tin nhắn chưa đọc thành công', ['unread_count' => intval($unreadCount)]);
}

if ($type === 'SEARCH_USERS') {
    $query = check_string($_POST['query'] ?? '');
    if (strlen($query) < 2) json_error('Query phải có ít nhất 2 ký tự');
    
    $uid = intval($currentUser['id']);
    $queryEscaped = addslashes($query);
    
    $users = $Vani->get_list("
        SELECT id, username, full_name, avatar
        FROM users
        WHERE id != '$uid'
        AND (username LIKE '%$queryEscaped%' OR full_name LIKE '%$queryEscaped%')
        ORDER BY 
            CASE 
                WHEN username LIKE '$queryEscaped%' THEN 1
                WHEN full_name LIKE '$queryEscaped%' THEN 2
                ELSE 3
            END,
            username ASC
        LIMIT 20
    ");
    
    json_success('Tìm kiếm thành công', ['users' => $users]);
}

if ($type === 'TOGGLE_FOLLOW') {
    $targetUserId = intval($_POST['user_id'] ?? 0);
    if ($targetUserId <= 0) json_error('User ID không hợp lệ');
    
    $uid = intval($currentUser['id']);
    if ($uid === $targetUserId) json_error('Không thể theo dõi chính mình');
    
    $existing = $Vani->get_row("SELECT id FROM `follows` WHERE `follower_id` = '$uid' AND `following_id` = '$targetUserId'");
    
    if ($existing) {
        $Vani->remove("follows", "`follower_id` = '$uid' AND `following_id` = '$targetUserId'");
        $isFollowing = false;
    } else {
        $Vani->insert("follows", [
            'follower_id' => $uid,
            'following_id' => $targetUserId
        ]);
        $isFollowing = true;
        
        // Tạo notification
        $Vani->insert("notifications", [
            'user_id' => $targetUserId,
            'actor_id' => $uid,
            'type' => 'follow',
            'entity_type' => 'user',
            'entity_id' => $uid
        ]);
    }
    
    $followersCount = $Vani->num_rows("SELECT id FROM `follows` WHERE `following_id` = '$targetUserId'") ?: 0;
    
    json_success($isFollowing ? 'Đã theo dõi' : 'Đã bỏ theo dõi', [
        'is_following' => $isFollowing,
        'followers_count' => intval($followersCount)
    ]);
}

if ($type === 'GET_NOTIFICATIONS') {
    $uid = intval($currentUser['id']);
    $limit = intval($_POST['limit'] ?? 20);
    $offset = intval($_POST['offset'] ?? 0);
    
    $notifications = $Vani->get_list("
        SELECT n.*, 
            u.username, u.full_name, u.avatar,
            CASE 
                WHEN n.type = 'follow' THEN CONCAT(u.full_name, ' đã theo dõi bạn')
                WHEN n.type = 'like' THEN CONCAT(u.full_name, ' đã thích bài viết của bạn')
                WHEN n.type = 'comment' THEN CONCAT(u.full_name, ' đã bình luận bài viết của bạn')
                WHEN n.type = 'reply' THEN CONCAT(u.full_name, ' đã trả lời bình luận của bạn')
                ELSE 'Có thông báo mới'
            END as message
        FROM `notifications` n
        LEFT JOIN `users` u ON n.actor_id = u.id
        WHERE n.user_id = '$uid'
        ORDER BY n.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    
    $unreadCount = $Vani->num_rows("SELECT id FROM `notifications` WHERE `user_id` = '$uid' AND `is_read` = 0") ?: 0;
    
    json_success('Lấy thông báo thành công', [
        'notifications' => $notifications,
        'unread_count' => intval($unreadCount)
    ]);
}

if ($type === 'MARK_NOTIFICATION_READ') {
    $notificationId = intval($_POST['notification_id'] ?? 0);
    $uid = intval($currentUser['id']);
    
    if ($notificationId > 0) {
        $Vani->update("notifications", ['is_read' => 1], "`id` = '$notificationId' AND `user_id` = '$uid'");
    } else {
        $Vani->update("notifications", ['is_read' => 1], "`user_id` = '$uid'");
    }
    
    json_success('Đã đánh dấu đã đọc');
}

if ($type === 'TOGGLE_BLOCK') {
    $targetUserId = intval($_POST['user_id'] ?? 0);
    if ($targetUserId <= 0) json_error('User ID không hợp lệ');
    
    $uid = intval($currentUser['id']);
    if ($uid === $targetUserId) json_error('Không thể chặn chính mình');
    
    $existing = $Vani->get_row("SELECT id FROM `user_blocks` WHERE `blocker_id` = '$uid' AND `blocked_id` = '$targetUserId'");
    
    if ($existing) {
        $Vani->remove("user_blocks", "`blocker_id` = '$uid' AND `blocked_id` = '$targetUserId'");
        $isBlocked = false;
    } else {
        $Vani->insert("user_blocks", [
            'blocker_id' => $uid,
            'blocked_id' => $targetUserId
        ]);
        $isBlocked = true;
        
        // Xóa follow nếu có
        $Vani->remove("follows", "`follower_id` = '$uid' AND `following_id` = '$targetUserId'");
        $Vani->remove("follows", "`follower_id` = '$targetUserId' AND `following_id` = '$uid'");
    }
    
    json_success($isBlocked ? 'Đã chặn người dùng' : 'Đã bỏ chặn người dùng', ['is_blocked' => $isBlocked]);
}

if ($type === 'SEARCH_POSTS') {
    $query = check_string($_POST['query'] ?? '');
    if (strlen($query) < 2) json_error('Query phải có ít nhất 2 ký tự');
    
    $uid = isset($currentUser) ? intval($currentUser['id']) : 0;
    $queryEscaped = addslashes($query);
    
    $posts = $Vani->get_list("
        SELECT p.*, 
            u.full_name, u.username, u.avatar,
            (SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id) as like_count,
            (SELECT COUNT(*) FROM `post_comments` WHERE `post_id` = p.id) as comment_count,
            " . ($uid > 0 ? "(SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id AND `user_id` = '$uid') as has_liked," : "0 as has_liked,") . "
            " . ($uid > 0 ? "(SELECT COUNT(*) FROM `post_bookmarks` WHERE `post_id` = p.id AND `user_id` = '$uid') as has_saved" : "0 as has_saved") . "
        FROM `posts` p
        JOIN `users` u ON p.user_id = u.id
        WHERE p.content LIKE '%$queryEscaped%'
        ORDER BY p.created_at DESC
        LIMIT 20
    ");
    
    json_success('Tìm kiếm thành công', ['posts' => $posts]);
}

if ($type === 'SEARCH_ALL') {
    $query = check_string($_POST['query'] ?? '');
    if (strlen($query) < 2) json_error('Query phải có ít nhất 2 ký tự');
    
    $uid = isset($currentUser) ? intval($currentUser['id']) : 0;
    $queryEscaped = addslashes($query);
    
    $users = $Vani->get_list("
        SELECT id, username, full_name, avatar
        FROM users
        WHERE " . ($uid > 0 ? "id != '$uid' AND " : "") . "(username LIKE '%$queryEscaped%' OR full_name LIKE '%$queryEscaped%')
        ORDER BY 
            CASE 
                WHEN username LIKE '$queryEscaped%' THEN 1
                WHEN full_name LIKE '$queryEscaped%' THEN 2
                ELSE 3
            END,
            username ASC
        LIMIT 10
    ");
    
    $posts = $Vani->get_list("
        SELECT p.*, 
            u.full_name, u.username, u.avatar,
            (SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id) as like_count,
            (SELECT COUNT(*) FROM `post_comments` WHERE `post_id` = p.id) as comment_count
        FROM `posts` p
        JOIN `users` u ON p.user_id = u.id
        WHERE p.content LIKE '%$queryEscaped%'
        ORDER BY p.created_at DESC
        LIMIT 10
    ");
    
    json_success('Tìm kiếm thành công', ['users' => $users, 'posts' => $posts]);
}

if ($type === 'GET_FOLLOWERS') {
    $targetUserId = intval($_POST['user_id'] ?? 0);
    if ($targetUserId <= 0) json_error('User ID không hợp lệ');
    
    $users = $Vani->get_list("
        SELECT u.id, u.username, u.full_name, u.avatar
        FROM `follows` f
        JOIN `users` u ON f.follower_id = u.id
        WHERE f.following_id = '$targetUserId'
        ORDER BY f.created_at DESC
        LIMIT 100
    ");
    
    json_success('Lấy danh sách người theo dõi thành công', ['users' => $users]);
}

if ($type === 'GET_FOLLOWING') {
    $targetUserId = intval($_POST['user_id'] ?? 0);
    if ($targetUserId <= 0) json_error('User ID không hợp lệ');
    
    $users = $Vani->get_list("
        SELECT u.id, u.username, u.full_name, u.avatar
        FROM `follows` f
        JOIN `users` u ON f.following_id = u.id
        WHERE f.follower_id = '$targetUserId'
        ORDER BY f.created_at DESC
        LIMIT 100
    ");
    
    json_success('Lấy danh sách đang theo dõi thành công', ['users' => $users]);
}

json_error('type không hợp lệ');
