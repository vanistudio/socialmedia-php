<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php';

$isLoggedIn = isset($_SESSION['email']) && !empty($_SESSION['email']);
$currentUser = null;
$currentUserId = 0;
if ($isLoggedIn) {
    $currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '" . addslashes($_SESSION['email']) . "'");
    $currentUserId = intval($currentUser['id'] ?? 0);
}
$blockFilter = '';
if ($isLoggedIn && $currentUserId > 0) {
    $blockFilter = "AND p.user_id NOT IN (
        SELECT blocked_id FROM user_blocks WHERE blocker_id = '$currentUserId'
        UNION
        SELECT blocker_id FROM user_blocks WHERE blocked_id = '$currentUserId'
    )";
}

$visibilityFilter = '';
if ($isLoggedIn && $currentUserId > 0) {
    $visibilityFilter = "AND (
        p.visibility = 'public' 
        OR p.user_id = '$currentUserId'
        OR (p.visibility = 'followers' AND EXISTS (
            SELECT 1 FROM follows WHERE follower_id = '$currentUserId' AND following_id = p.user_id
        ))
    )";
} else {
    $visibilityFilter = "AND p.visibility = 'public'";
}
$suggestedUsers = [];
if ($isLoggedIn && $currentUserId > 0) {
    $suggestedUsers = $Vani->get_list("
        SELECT u.*, 
            (SELECT COUNT(*) FROM `follows` WHERE `following_id` = u.id) as followers_count,
            (SELECT COUNT(*) FROM `posts` WHERE `user_id` = u.id) as posts_count
        FROM `users` u
        WHERE u.id != '$currentUserId'
        AND u.id NOT IN (
            SELECT following_id FROM `follows` WHERE follower_id = '$currentUserId'
        )
        AND u.id NOT IN (
            SELECT blocked_id FROM user_blocks WHERE blocker_id = '$currentUserId'
            UNION
            SELECT blocker_id FROM user_blocks WHERE blocked_id = '$currentUserId'
        )
        ORDER BY followers_count DESC, u.created_at DESC
        LIMIT 5
    ");
}

$posts = $Vani->get_list("SELECT 
    p.id, p.user_id, p.content, p.visibility, p.created_at,
    u.full_name, u.username, u.avatar,
    (SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id) as like_count,
    (SELECT COUNT(*) FROM `post_comments` WHERE `post_id` = p.id) as comment_count,
    " . ($isLoggedIn ? "(SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id AND `user_id` = '$currentUserId') as has_liked,
    (SELECT COUNT(*) FROM `post_bookmarks` WHERE `post_id` = p.id AND `user_id` = '$currentUserId') as has_saved" : "0 as has_liked, 0 as has_saved") . "
    FROM `posts` p 
    JOIN `users` u ON p.user_id = u.id
    WHERE 1=1 $visibilityFilter $blockFilter
    ORDER BY p.created_at DESC
    LIMIT 20");
?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <div class="lg:col-span-3 space-y-6">

        <?php if ($isLoggedIn): ?>
            <div class="bg-card border border-border rounded-2xl p-4 shadow-sm">
                <div class="flex items-start gap-4">
                    <img src="<?php echo htmlspecialchars($currentUser['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                    <div class="flex-1">
                        <form id="create-post-form">
                            <input type="hidden" name="type" value="CREATE_POST">
                            <textarea name="content" placeholder="Bạn đang nghĩ gì, <?php echo htmlspecialchars($currentUser['full_name'] ?? ''); ?>?" class="w-full bg-transparent text-foreground placeholder:text-muted-foreground outline-none resize-none text-lg"></textarea>
                            <div id="post-media-previews" class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2"></div>

                            <div class="flex justify-between items-center mt-2 pt-2 border-t border-border">
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <button type="button" class="h-9 w-9 rounded-lg hover:bg-accent hover:text-vanixjnk transition flex items-center justify-center" aria-label="Add media" onclick="$('#post-media-upload').click()">
                                        <iconify-icon icon="solar:gallery-wide-linear" width="20"></iconify-icon>
                                    </button>
                                    <input type="file" id="post-media-upload" accept="image/*,video/*" class="hidden" multiple>
                                    
                                    <div class="relative" id="visibility-dropdown-container">
                                        <input type="hidden" name="visibility" id="post-visibility" value="public">
                                        <button type="button" id="visibility-trigger" class="h-9 px-3 rounded-lg border border-input bg-background hover:bg-accent transition flex items-center gap-2 text-sm">
                                            <iconify-icon id="visibility-icon" icon="solar:earth-linear" width="16"></iconify-icon>
                                            <span id="visibility-text">Công khai</span>
                                            <iconify-icon icon="solar:alt-arrow-down-linear" width="14" class="text-muted-foreground"></iconify-icon>
                                        </button>
                                        <div id="visibility-dropdown" class="hidden absolute bottom-full left-0 mb-2 w-48 bg-card border border-border rounded-xl shadow-lg z-50">
                                            <ul class="py-1">
                                                <li>
                                                    <button type="button" data-visibility="public" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm hover:bg-accent transition">
                                                        <iconify-icon icon="solar:earth-linear" width="18"></iconify-icon>
                                                        <div>
                                                            <span class="font-medium">Công khai</span>
                                                            <p class="text-xs text-muted-foreground">Mọi người có thể xem</p>
                                                        </div>
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" data-visibility="followers" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm hover:bg-accent transition">
                                                        <iconify-icon icon="solar:users-group-rounded-linear" width="18"></iconify-icon>
                                                        <div>
                                                            <span class="font-medium">Người theo dõi</span>
                                                            <p class="text-xs text-muted-foreground">Chỉ người theo dõi</p>
                                                        </div>
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" data-visibility="private" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm hover:bg-accent transition">
                                                        <iconify-icon icon="solar:lock-linear" width="18"></iconify-icon>
                                                        <div>
                                                            <span class="font-medium">Riêng tư</span>
                                                            <p class="text-xs text-muted-foreground">Chỉ mình tôi</p>
                                                        </div>
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="h-9 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium">Đăng bài</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-card border border-border rounded-2xl p-6 shadow-sm text-center">
                <h2 class="text-lg font-semibold text-foreground">Chào mừng đến với Vani Social!</h2>
                <p class="text-muted-foreground mt-1">Đăng nhập hoặc đăng ký để chia sẻ khoảnh khắc của bạn.</p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <a href="/login" class="h-10 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium flex items-center gap-2">
                        <iconify-icon icon="solar:login-3-linear" width="18"></iconify-icon>
                        <span>Đăng nhập</span>
                    </a>
                    <a href="/register" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium flex items-center gap-2">
                        <iconify-icon icon="solar:user-plus-rounded-linear" width="18"></iconify-icon>
                        <span>Đăng ký</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div id="feed-posts" class="space-y-6">
            <?php foreach ($posts as $post): 
                $post['user_id'] = intval($post['user_id']);
                $currentUserId = $isLoggedIn ? intval($currentUser['id']) : 0;
            ?>
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/views/components/_post_card.php'; ?>
                
                <div class="hidden p-4 border-t border-border space-y-4" id="comments-<?php echo $post['id']; ?>">
                    <?php if ($isLoggedIn): ?>
                        <div class="flex items-start gap-3">
                            <img src="<?php echo htmlspecialchars($currentUser['avatar']); ?>" alt="My Avatar" class="h-8 w-8 rounded-full object-cover">
                            <form class="flex-1 relative" data-form="add-comment" data-post-id="<?php echo $post['id']; ?>">
                                <input type="hidden" name="type" value="ADD_COMMENT">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <input type="text" name="content" placeholder="Viết bình luận..." class="w-full h-9 rounded-lg border border-input bg-background px-3 pr-10 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30">
                                <button type="submit" class="absolute top-1/2 right-2 -translate-y-1/2 h-7 w-7 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 flex items-center justify-center transition">
                                    <iconify-icon icon="solar:arrow-right-linear" width="18"></iconify-icon>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-3 comment-list">
                        <?php
                        $commentBlockFilter = '';
                        if ($isLoggedIn && $currentUserId > 0) {
                            $commentBlockFilter = "AND c.user_id NOT IN (
                                SELECT blocked_id FROM user_blocks WHERE blocker_id = '$currentUserId'
                                UNION
                                SELECT blocker_id FROM user_blocks WHERE blocked_id = '$currentUserId'
                            )";
                        }
                        $comments = $Vani->get_list("SELECT c.*, u.full_name, u.username, u.avatar,
                            (SELECT COUNT(*) FROM `comment_likes` cl WHERE cl.comment_id = c.id) AS like_count,
                            " . ($isLoggedIn ? "(SELECT COUNT(*) FROM `comment_likes` cl WHERE cl.comment_id = c.id AND cl.user_id = '$currentUserId') AS has_liked" : "0 AS has_liked") . "
                            FROM `post_comments` c 
                            JOIN `users` u ON c.user_id = u.id
                            WHERE c.post_id = '{$post['id']}' AND c.parent_id IS NULL $commentBlockFilter
                            ORDER BY c.created_at ASC");

                        foreach ($comments as $comment):
                            $replies = $Vani->get_list("SELECT c.*, u.full_name, u.username, u.avatar,
                                (SELECT COUNT(*) FROM `comment_likes` cl WHERE cl.comment_id = c.id) AS like_count,
                                " . ($isLoggedIn ? "(SELECT COUNT(*) FROM `comment_likes` cl WHERE cl.comment_id = c.id AND cl.user_id = '$currentUserId') AS has_liked" : "0 AS has_liked") . "
                                FROM `post_comments` c 
                                JOIN `users` u ON c.user_id = u.id
                                WHERE c.post_id = '{$post['id']}' AND c.parent_id = '{$comment['id']}' $commentBlockFilter
                                ORDER BY c.created_at ASC");
                        ?>
                            <div class="space-y-2">
                                <div class="flex items-start gap-3">
                                    <a href="/u/<?php echo htmlspecialchars($comment['username']); ?>">
                                        <img src="<?php echo htmlspecialchars($comment['avatar']); ?>" alt="Avatar" class="h-8 w-8 rounded-full object-cover">
                                    </a>
                                    <div class="flex-1">
                                        <div class="bg-accent/50 rounded-xl px-3 py-2">
                                            <a href="/u/<?php echo htmlspecialchars($comment['username']); ?>" class="font-semibold text-foreground text-sm hover:underline"><?php echo htmlspecialchars($comment['full_name']); ?></a>
                                            <p class="text-sm text-foreground"><?php echo htmlspecialchars($comment['content']); ?></p>
                                        </div>
                                        <div class="flex items-center gap-3 text-xs text-muted-foreground mt-1 px-2">
                                            <button type="button" data-action="toggle-comment-like" data-comment-id="<?php echo $comment['id']; ?>" class="hover:underline <?php echo ($comment['has_liked'] > 0) ? 'text-vanixjnk' : ''; ?>">
                                                Thích <span class="comment-like-count"><?php echo intval($comment['like_count']); ?></span>
                                            </button>
                                            <button type="button" class="hover:underline" data-action="reply-comment" data-post-id="<?php echo $post['id']; ?>" data-parent-id="<?php echo $comment['id']; ?>" data-username="<?php echo htmlspecialchars($comment['username']); ?>">Trả lời</button>
                                            <?php if ($currentUserId && intval($comment['user_id']) === $currentUserId): ?>
                                                <button type="button" class="hover:underline text-red-500" data-action="delete-comment" data-comment-id="<?php echo $comment['id']; ?>">Xóa</button>
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($comment['created_at']); ?></span>
                                        </div>

                                        <?php if (!empty($replies)): ?>
                                            <div class="mt-2 space-y-2 pl-6 border-l border-border">
                                                <?php foreach ($replies as $reply): ?>
                                                    <div class="flex items-start gap-3">
                                                        <a href="/u/<?php echo htmlspecialchars($reply['username']); ?>">
                                                            <img src="<?php echo htmlspecialchars($reply['avatar']); ?>" alt="Avatar" class="h-7 w-7 rounded-full object-cover">
                                                        </a>
                                                        <div class="flex-1">
                                                            <div class="bg-accent/40 rounded-xl px-3 py-2">
                                                                <a href="/u/<?php echo htmlspecialchars($reply['username']); ?>" class="font-semibold text-foreground text-sm hover:underline"><?php echo htmlspecialchars($reply['full_name']); ?></a>
                                                                <p class="text-sm text-foreground"><?php echo htmlspecialchars($reply['content']); ?></p>
                                                            </div>
                                                            <div class="flex items-center gap-3 text-xs text-muted-foreground mt-1 px-2">
                                                                <button type="button" data-action="toggle-comment-like" data-comment-id="<?php echo $reply['id']; ?>" class="hover:underline <?php echo ($reply['has_liked'] > 0) ? 'text-vanixjnk' : ''; ?>">
                                                                    Thích <span class="comment-like-count"><?php echo intval($reply['like_count']); ?></span>
                                                                </button>
                                                                <button type="button" class="hover:underline" data-action="reply-comment" data-post-id="<?php echo $post['id']; ?>" data-parent-id="<?php echo $reply['id']; ?>" data-username="<?php echo htmlspecialchars($reply['username']); ?>">Trả lời</button>
                                                                <?php if ($currentUserId && intval($reply['user_id']) === $currentUserId): ?>
                                                                    <button type="button" class="hover:underline text-red-500" data-action="delete-comment" data-comment-id="<?php echo $reply['id']; ?>">Xóa</button>
                                                                <?php endif; ?>
                                                                <span><?php echo htmlspecialchars($reply['created_at']); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="lg:col-span-1 space-y-6">
        <?php if ($isLoggedIn && !empty($suggestedUsers)): ?>
        <div class="bg-card border border-border rounded-2xl p-4 shadow-sm">
            <h3 class="font-semibold text-foreground mb-4">Gợi ý cho bạn</h3>
            <div class="space-y-3">
                <?php foreach ($suggestedUsers as $sugUser): ?>
                <div class="flex items-center gap-3 group">
                    <a href="/u/<?php echo htmlspecialchars($sugUser['username']); ?>" class="shrink-0">
                        <img src="<?php echo htmlspecialchars(!empty($sugUser['avatar']) ? $sugUser['avatar'] : 'https://placehold.co/200x200/png'); ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                    </a>
                    <div class="flex-1 min-w-0">
                        <a href="/u/<?php echo htmlspecialchars($sugUser['username']); ?>" class="font-medium text-foreground hover:underline truncate block text-sm"><?php echo htmlspecialchars($sugUser['full_name']); ?></a>
                        <p class="text-xs text-muted-foreground"><?php echo intval($sugUser['followers_count']); ?> người theo dõi</p>
                    </div>
                    <button type="button" data-action="quick-follow" data-user-id="<?php echo $sugUser['id']; ?>" class="h-8 px-3 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-xs font-medium shrink-0">
                        Theo dõi
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="/explore" class="block mt-4 text-center text-sm text-vanixjnk hover:underline">Xem thêm</a>
        </div>
        <?php endif; ?>

        <?php if (!$isLoggedIn): ?>
        <div class="bg-card border border-border rounded-2xl p-4 shadow-sm">
            <h3 class="font-semibold text-foreground mb-2">Tham gia Vani Social</h3>
            <p class="text-sm text-muted-foreground mb-4">Đăng ký để kết nối với mọi người và chia sẻ khoảnh khắc.</p>
            <div class="space-y-2">
                <a href="/register" class="w-full h-10 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="solar:user-plus-rounded-linear" width="18"></iconify-icon>
                    <span>Đăng ký ngay</span>
                </a>
                <a href="/login" class="w-full h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium flex items-center justify-center gap-2">
                    <iconify-icon icon="solar:login-3-linear" width="18"></iconify-icon>
                    <span>Đã có tài khoản? Đăng nhập</span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-card border border-border rounded-2xl p-4 shadow-sm">
            <h3 class="font-semibold text-foreground mb-3">Liên kết nhanh</h3>
            <div class="flex flex-wrap gap-2 text-xs text-muted-foreground">
                <a href="/about" class="hover:text-vanixjnk transition">Giới thiệu</a>
                <span>·</span>
                <a href="/terms" class="hover:text-vanixjnk transition">Điều khoản</a>
                <span>·</span>
                <a href="/privacy" class="hover:text-vanixjnk transition">Quyền riêng tư</a>
            </div>
            <p class="text-xs text-muted-foreground mt-3">© <?php echo date('Y'); ?> Vani Social</p>
        </div>
    </div>
</div>

<div id="edit-post-dialog" class="hidden fixed inset-0 z-50 items-center justify-center" data-dialog data-state="closed">
    <div class="absolute inset-0 bg-black/50" data-dialog-backdrop></div>
    <div class="relative w-full max-w-xl mx-auto" data-dialog-content>
        <div class="bg-card border border-border rounded-2xl shadow-lg" data-dialog-inner>
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h3 class="text-lg font-semibold text-foreground">Chỉnh sửa bài viết</h3>
                <button type="button" class="h-9 w-9 rounded-lg hover:bg-accent transition flex items-center justify-center" data-dialog-close>
                    <iconify-icon icon="solar:close-circle-linear" width="22"></iconify-icon>
                </button>
            </div>
            <form id="edit-post-form" class="p-4">
                <input type="hidden" name="type" value="UPDATE_POST">
                <input type="hidden" name="post_id" id="edit-post-id">
                <input type="hidden" name="visibility" id="edit-post-visibility" value="public">
                <div class="space-y-4">
                    <div>
                        <textarea name="content" id="edit-post-content" placeholder="Bạn đang nghĩ gì?" class="w-full bg-transparent text-foreground placeholder:text-muted-foreground outline-none resize-none text-lg min-h-[120px] border border-input rounded-lg p-3"></textarea>
                        <div id="edit-post-media-previews" class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2"></div>
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t border-border">
                        <div class="flex items-center gap-2 text-muted-foreground">
                            <button type="button" class="h-9 w-9 rounded-lg hover:bg-accent hover:text-vanixjnk transition flex items-center justify-center" aria-label="Add media" onclick="$('#edit-post-media-upload').click()">
                                <iconify-icon icon="solar:gallery-wide-linear" width="20"></iconify-icon>
                            </button>
                            <input type="file" id="edit-post-media-upload" accept="image/*,video/*" class="hidden" multiple>
                            
                            <div class="relative" id="edit-visibility-dropdown-container">
                                <button type="button" id="edit-visibility-trigger" class="h-9 px-3 rounded-lg border border-input bg-background hover:bg-accent transition flex items-center gap-2 text-sm">
                                    <iconify-icon id="edit-visibility-icon" icon="solar:earth-linear" width="16"></iconify-icon>
                                    <span id="edit-visibility-text">Công khai</span>
                                    <iconify-icon icon="solar:alt-arrow-down-linear" width="14" class="text-muted-foreground"></iconify-icon>
                                </button>
                                <div id="edit-visibility-dropdown" class="hidden absolute bottom-full left-0 mb-2 w-48 bg-card border border-border rounded-xl shadow-lg z-50">
                                    <ul class="py-1">
                                        <li>
                                            <button type="button" data-edit-visibility="public" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm hover:bg-accent transition">
                                                <iconify-icon icon="solar:earth-linear" width="18"></iconify-icon>
                                                <div>
                                                    <span class="font-medium">Công khai</span>
                                                    <p class="text-xs text-muted-foreground">Mọi người có thể xem</p>
                                                </div>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" data-edit-visibility="followers" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm hover:bg-accent transition">
                                                <iconify-icon icon="solar:users-group-rounded-linear" width="18"></iconify-icon>
                                                <div>
                                                    <span class="font-medium">Người theo dõi</span>
                                                    <p class="text-xs text-muted-foreground">Chỉ người theo dõi</p>
                                                </div>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" data-edit-visibility="private" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm hover:bg-accent transition">
                                                <iconify-icon icon="solar:lock-linear" width="18"></iconify-icon>
                                                <div>
                                                    <span class="font-medium">Riêng tư</span>
                                                    <p class="text-xs text-muted-foreground">Chỉ mình tôi</p>
                                                </div>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="h-9 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium">Cập nhật</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let reportDialog = null;

function openReportDialog(targetType, targetId) {
    $('#report-target-type').val(targetType);
    $('#report-target-id').val(targetId);
    $('#report-reason').val('Spam');
    $('#report-custom-reason').val('').closest('.mt-3').addClass('hidden');
    
    const $selectContainer = $('#report-reason').closest('.custom-select-container');
    const $selectedText = $selectContainer.find('.selected-text');
    const $items = $selectContainer.find('.custom-select-item');
    $items.attr('data-state', 'unchecked');
    $items.filter('[data-value="Spam"]').attr('data-state', 'checked');
    $selectedText.text('Spam');
    
    if (reportDialog) {
        reportDialog.open();
    } else {
        const $dialog = $('#report-dialog');
        $dialog.removeClass('hidden').addClass('flex');
        setTimeout(() => {
            $dialog.attr('data-state', 'open');
        }, 10);
    }
}

function closeReportDialog() {
    if (reportDialog) {
        reportDialog.close();
    } else {
        const $dialog = $('#report-dialog');
        $dialog.attr('data-state', 'closed');
        setTimeout(() => {
            $dialog.addClass('hidden').removeClass('flex');
        }, 200);
    }
}

window.openReportDialog = openReportDialog;
window.closeReportDialog = closeReportDialog;

$(document).ready(function() {
    const currentUserId = <?php echo $currentUserId; ?>;
    let postMediaFiles = [];
    let editPostMediaFiles = [];
    let editPostMediaUrls = [];
    let editPostDialog = null;
    $('#visibility-trigger').on('click', function(e) {
        e.stopPropagation();
        $('#visibility-dropdown').toggleClass('hidden');
    });

    $('[data-visibility]').on('click', function() {
        const visibility = $(this).data('visibility');
        $('#post-visibility').val(visibility);
        
        const icons = {
            'public': 'solar:earth-linear',
            'followers': 'solar:users-group-rounded-linear',
            'private': 'solar:lock-linear'
        };
        const texts = {
            'public': 'Công khai',
            'followers': 'Người theo dõi',
            'private': 'Riêng tư'
        };
        
        $('#visibility-icon').attr('icon', icons[visibility]);
        $('#visibility-text').text(texts[visibility]);
        $('#visibility-dropdown').addClass('hidden');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#visibility-dropdown-container').length) {
            $('#visibility-dropdown').addClass('hidden');
        }
    });
    $('#edit-visibility-trigger').on('click', function(e) {
        e.stopPropagation();
        $('#edit-visibility-dropdown').toggleClass('hidden');
    });

    $('[data-edit-visibility]').on('click', function() {
        const visibility = $(this).data('edit-visibility');
        $('#edit-post-visibility').val(visibility);
        
        const icons = {
            'public': 'solar:earth-linear',
            'followers': 'solar:users-group-rounded-linear',
            'private': 'solar:lock-linear'
        };
        const texts = {
            'public': 'Công khai',
            'followers': 'Người theo dõi',
            'private': 'Riêng tư'
        };
        
        $('#edit-visibility-icon').attr('icon', icons[visibility]);
        $('#edit-visibility-text').text(texts[visibility]);
        $('#edit-visibility-dropdown').addClass('hidden');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#edit-visibility-dropdown-container').length) {
            $('#edit-visibility-dropdown').addClass('hidden');
        }
    });
    
    setTimeout(function() {
        if (window.initDialog) {
            editPostDialog = window.initDialog('edit-post-dialog');
            reportDialog = window.initDialog('report-dialog');
        }
    }, 100);
    
    $('#report-reason').on('change', function() {
        const value = $(this).val();
        if (value === 'Other') {
            $('#report-custom-reason').closest('.mt-3').removeClass('hidden');
        } else {
            $('#report-custom-reason').closest('.mt-3').addClass('hidden');
        }
    });
    
    setTimeout(function() {
        if (window.initCustomSelects) {
            window.initCustomSelects();
        }
    }, 200);
    
    $('#report-form').on('submit', function(e) {
        e.preventDefault();
        
        const targetType = $('#report-target-type').val();
        const targetId = $('#report-target-id').val();
        const reason = $('#report-reason').val();
        const customReason = $('#report-custom-reason').val().trim();
        
        if (reason === 'Other' && !customReason) {
            toast.error('Vui lòng nhập lý do báo cáo');
            return;
        }
        
        const finalReason = reason === 'Other' ? customReason : reason;
        
        const $btn = $('#btn-submit-report');
        const original = $btn.html();
        $btn.prop('disabled', true).html('<span>Đang gửi...</span>');
        
        $.post('/api/controller/app', {
            type: 'REPORT_ENTITY',
            target_type: targetType,
            target_id: targetId,
            reason: finalReason,
            csrf_token: window.CSRF_TOKEN || ''
        }, function(data) {
            $btn.prop('disabled', false).html(original);
            
            if (data.status === 'success') {
                toast.success(data.message);
                closeReportDialog();
            } else {
                toast.error(data.message || 'Có lỗi xảy ra');
            }
        }, 'json').fail(function() {
            $btn.prop('disabled', false).html(original);
            toast.error('Không thể kết nối tới máy chủ');
        });
    });
    
    $('#post-media-upload').on('change', function(e) {
        postMediaFiles = Array.from(e.target.files);
        const previews = $('#post-media-previews');
        previews.empty();
        postMediaFiles.forEach(file => {
            const url = URL.createObjectURL(file);
            previews.append(`<div class="relative"><img src="${url}" class="h-24 w-full object-cover rounded-lg"></div>`);
        });
    });
    
    $('#edit-post-media-upload').on('change', function(e) {
        editPostMediaFiles = Array.from(e.target.files);
        editPostMediaFiles.forEach(file => {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('csrf_token', window.CSRF_TOKEN || '');
            $.ajax({
                url: '/api/controller/upload',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data && data.status === 'success' && data.url) {
                        editPostMediaUrls.push(data.url);
                        renderEditPostMedia();
                    }
                }
            });
        });
    });
    
    function renderEditPostMedia() {
        const previews = $('#edit-post-media-previews');
        previews.empty();
        editPostMediaUrls.forEach((url, index) => {
            previews.append(`
                <div class="relative">
                    <img src="${url}" class="h-24 w-full object-cover rounded-lg">
                    <button type="button" class="absolute top-1 right-1 h-6 w-6 rounded-full bg-destructive text-white flex items-center justify-center" onclick="removeEditMedia(${index})">
                        <iconify-icon icon="solar:close-circle-linear" width="14"></iconify-icon>
                    </button>
                </div>
            `);
        });
    }
    
    window.removeEditMedia = function(index) {
        editPostMediaUrls.splice(index, 1);
        renderEditPostMedia();
    };

    function copyToClipboardFallback(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            toast.success('Đã copy link bài viết');
        } catch (err) {
            toast.error('Không thể copy link');
        }
        document.body.removeChild(textarea);
    }
    
    $('#edit-post-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type=submit]');
        const originalBtnText = $btn.text();
        const content = $form.find('textarea[name=content]').val();
        
        if (content.trim() === '' && editPostMediaUrls.length === 0) {
            toast.error('Bài viết không được để trống');
            return;
        }
        
        $btn.prop('disabled', true).addClass('opacity-70').text('Đang cập nhật...');
        
        let postDataArray = $form.serializeArray();
        
        let postData = {};
        postDataArray.forEach(item => {
            if (item.name.endsWith('[]')) {
                const key = item.name.replace('[]', '');
                if (!postData[key]) postData[key] = [];
                postData[key].push(item.value);
            } else {
                postData[item.name] = item.value;
            }
        });
        
        if (editPostMediaUrls.length > 0) {
            postData.media = editPostMediaUrls;
        }
        
        const csrfToken = window.CSRF_TOKEN || '';
        if (!csrfToken) {
            toast.error('CSRF token không tồn tại. Vui lòng tải lại trang.');
            $btn.prop('disabled', false).removeClass('opacity-70').text(originalBtnText);
            return;
        }
        
        postData.csrf_token = csrfToken;
        
        $.post('/api/controller/app', postData, function(data) {
            if (data.status === 'success') {
                toast.success(data.message);
                if (editPostDialog) {
                    editPostDialog.close();
                } else {
                    $('#edit-post-dialog').addClass('hidden').removeClass('flex');
                }
                setTimeout(() => window.location.reload(), 800);
            } else {
                toast.error(data.message || 'Có lỗi xảy ra');
            }
        }, 'json').fail(function() {
            toast.error('Không thể kết nối');
        }).always(function() {
            $btn.prop('disabled', false).removeClass('opacity-70').text(originalBtnText);
        });
    });
    $('#create-post-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type=submit]');
        const originalBtnText = $btn.text();
        const content = $form.find('textarea[name=content]').val();

        if (content.trim() === '' && postMediaFiles.length === 0) {
            toast.error('Bài viết không được để trống');
            return;
        }

        $btn.prop('disabled', true).addClass('opacity-70').text('Đang đăng...');

        let mediaUploadPromises = postMediaFiles.map(file => {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('csrf_token', window.CSRF_TOKEN || '');
            return $.ajax({
                url: '/api/controller/upload',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            });
        });

        Promise.all(mediaUploadPromises).then(results => {
            let mediaUrls = results.map(res => res.url);
            let postDataArray = $form.serializeArray();
            
            let postData = {};
            postDataArray.forEach(item => {
                if (item.name.endsWith('[]')) {
                    const key = item.name.replace('[]', '');
                    if (!postData[key]) postData[key] = [];
                    postData[key].push(item.value);
                } else {
                    postData[item.name] = item.value;
                }
            });
            
            if (mediaUrls.length > 0) {
                postData.media = mediaUrls;
            }
            
            const csrfToken = window.CSRF_TOKEN || '';
            if (!csrfToken) {
                toast.error('CSRF token không tồn tại. Vui lòng tải lại trang.');
                $btn.prop('disabled', false).removeClass('opacity-70').text(originalBtnText);
                return;
            }
            
            postData.csrf_token = csrfToken;

            $.post('/api/controller/app', postData, function(data) {
                if (data.status === 'success') {
                    toast.success(data.message);
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    toast.error(data.message || 'Có lỗi xảy ra');
                }
            }, 'json').fail(() => {
                toast.error('Không thể kết nối');
            }).always(() => {
                $btn.prop('disabled', false).removeClass('opacity-70').text(originalBtnText);
            });

        }).catch(() => {
            toast.error('Upload media thất bại');
            $btn.prop('disabled', false).removeClass('opacity-70').text(originalBtnText);
        });
    });
    $(document).on('click', '[data-action]', function() {
        const action = $(this).data('action');
        const postId = $(this).data('post-id');
        const commentId = $(this).data('comment-id');
        const $self = $(this);

        if (!currentUserId && ['toggle-like','save-post','report-post','reply-comment','toggle-comment-like','delete-comment','edit-post','delete-post'].includes(action)) {
            toast.error('Vui lòng đăng nhập để thực hiện');
            return;
        }

        switch (action) {
            case 'edit-post':
                $.post('/api/controller/app', { 
                    type: 'GET_POST', 
                    post_id: postId, 
                    csrf_token: window.CSRF_TOKEN || '' 
                }, function(data) {
                    if (data.status === 'success' && data.post) {
                        const post = data.post;
                        $('#edit-post-id').val(postId);
                        $('#edit-post-content').val(post.content || '');
                        editPostMediaUrls = post.media || [];
                        editPostMediaFiles = [];
                        renderEditPostMedia();
                        const visibility = post.visibility || 'public';
                        $('#edit-post-visibility').val(visibility);
                        const icons = {
                            'public': 'solar:earth-linear',
                            'followers': 'solar:users-group-rounded-linear',
                            'private': 'solar:lock-linear'
                        };
                        const texts = {
                            'public': 'Công khai',
                            'followers': 'Người theo dõi',
                            'private': 'Riêng tư'
                        };
                        $('#edit-visibility-icon').attr('icon', icons[visibility]);
                        $('#edit-visibility-text').text(texts[visibility]);
                        
                        if (editPostDialog) {
                            editPostDialog.open();
                        } else {
                            $('#edit-post-dialog').removeClass('hidden').addClass('flex');
                        }
                    } else {
                        toast.error(data.message || 'Không thể tải bài viết');
                    }
                }, 'json').fail(function() {
                    toast.error('Không thể kết nối tới máy chủ');
                });
                break;

            case 'delete-post':
                if (!confirm('Bạn có chắc chắn muốn xóa bài viết này?')) return;
                $.post('/api/controller/app', { type: 'DELETE_POST', post_id: postId, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
                    if (data.status === 'success') {
                        toast.success(data.message);
                        $(`#post-${postId}`).fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        toast.error(data.message || 'Có lỗi xảy ra');
                    }
                }, 'json').fail(function() {
                    toast.error('Không thể kết nối tới máy chủ');
                });
                break;
            case 'toggle-like':
                $.post('/api/controller/app', { type: 'TOGGLE_LIKE', post_id: postId, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
                    if (data.status === 'success') {
                        const $icon = $self.find('iconify-icon');
                        const $count = $self.find('.like-count');
                        let currentCount = parseInt($count.text());
                        if (data.liked) {
                            $self.addClass('text-vanixjnk').removeClass('text-muted-foreground');
                            $icon.attr('icon', 'solar:heart-bold');
                            $count.text(currentCount + 1);
                        } else {
                            $self.removeClass('text-vanixjnk').addClass('text-muted-foreground');
                            $icon.attr('icon', 'solar:heart-linear');
                            $count.text(currentCount - 1);
                        }
                    }
                }, 'json');
                break;

            case 'toggle-comments':
                $(`#comments-${postId}`).slideToggle(200);
                break;

            case 'copy-link':
                const postUrl = `${window.location.origin}/post/${postId}`;
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(postUrl).then(() => {
                        toast.success('Đã copy link bài viết');
                    }).catch(() => {
                        copyToClipboardFallback(postUrl);
                    });
                } else {
                    copyToClipboardFallback(postUrl);
                }
                break;

            case 'save-post':
                $.post('/api/controller/app', { type: 'SAVE_POST', post_id: postId, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
                    if (data.status === 'success') {
                        toast.success(data.message);
                        $self.find('span').text(data.saved ? 'Bỏ lưu' : 'Lưu bài viết');
                    }
                }, 'json');
                break;

            case 'report-post':
                if (!currentUserId) {
                    toast.error('Vui lòng đăng nhập để thực hiện');
                    return;
                }
                openReportDialog('post', postId);
                break;

            case 'reply-comment':
                const parentId = $self.data('parent-id');
                const username = $self.data('username');
                const $commentForm = $(`#comments-${postId} form[data-form='add-comment']`);
                $commentForm.find('input[name=content]').val(`@${username} `).focus();
                if ($commentForm.find('input[name=parent_id]').length === 0) {
                    $commentForm.append(`<input type="hidden" name="parent_id" value="${parentId}">`);
                } else {
                    $commentForm.find('input[name=parent_id]').val(parentId);
                }
                break;

            case 'toggle-comment-like':
                $.post('/api/controller/app', { type: 'TOGGLE_COMMENT_LIKE', comment_id: commentId, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
                    if (data.status === 'success') {
                        $self.toggleClass('text-vanixjnk', data.liked);
                        $self.find('.comment-like-count').text(data.like_count);
                    }
                }, 'json');
                break;

            case 'delete-comment':
                if (!confirm('Xóa bình luận này?')) return;
                $.post('/api/controller/app', { type: 'DELETE_COMMENT', comment_id: commentId, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
                    if (data.status === 'success') {
                        toast.success(data.message);
                        setTimeout(() => window.location.reload(), 400);
                    } else {
                        toast.error(data.message || 'Có lỗi xảy ra');
                    }
                }, 'json').fail(() => toast.error('Không thể kết nối tới máy chủ'));
                break;

            case 'quick-follow':
                const userId = $self.data('user-id');
                $.post('/api/controller/app', { type: 'TOGGLE_FOLLOW', user_id: userId, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
                    if (data.status === 'success') {
                        if (data.is_following) {
                            $self.text('Đang theo dõi').removeClass('bg-vanixjnk text-white').addClass('border border-input bg-card');
                        } else {
                            $self.text('Theo dõi').removeClass('border border-input bg-card').addClass('bg-vanixjnk text-white');
                        }
                        toast.success(data.message);
                    } else {
                        toast.error(data.message || 'Có lỗi xảy ra');
                    }
                }, 'json').fail(() => toast.error('Không thể kết nối tới máy chủ'));
                break;
        }
    });
    $(document).on('submit', 'form[data-form="add-comment"]', function(e) {
        e.preventDefault();
        if (!currentUserId) {
            toast.error('Vui lòng đăng nhập để thực hiện');
            return false;
        }

        const $form = $(this);
        const content = $form.find('input[name=content]').val();
        if (content.trim() === '') return;

        const $btn = $form.find('button[type=submit]');
        const original = $btn.html();
        $btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

        const $formForSerialize = $form;
        if ($formForSerialize.find('input[name="csrf_token"]').length === 0) {
            $formForSerialize.append('<input type="hidden" name="csrf_token" value="' + (window.CSRF_TOKEN || '') + '">');
        }
        $.post('/api/controller/app', $formForSerialize.serialize(), function(data) {
            if (data && data.status === 'success') {
                toast.success(data.message);
                setTimeout(() => window.location.reload(), 600);
            } else {
                toast.error(data?.message || 'Có lỗi xảy ra');
            }
        }, 'json').fail(function () {
            toast.error('Không thể kết nối tới máy chủ');
        }).always(function () {
            $btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            $btn.html(original);
        });
    });
});
</script>

<div id="report-dialog" class="dialog hidden fixed inset-0 z-50 flex items-center justify-center p-4" data-dialog data-state="closed">
    <div class="absolute inset-0 bg-background/80 backdrop-blur-sm" data-dialog-backdrop></div>
    <div class="relative w-full max-w-md mx-auto" data-dialog-content>
        <div class="bg-card border border-border rounded-2xl shadow-lg" data-dialog-inner>
            <div class="p-4 border-b border-border flex items-center justify-between">
                <h3 class="text-lg font-semibold text-foreground">Báo cáo</h3>
                <button type="button" data-dialog-close onclick="closeReportDialog()" class="h-8 w-8 rounded-lg hover:bg-accent transition flex items-center justify-center">
                    <iconify-icon icon="solar:close-circle-linear" width="20"></iconify-icon>
                </button>
            </div>
            <form id="report-form" class="p-4 space-y-4">
                <input type="hidden" id="report-target-type" value="">
                <input type="hidden" id="report-target-id" value="">
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-foreground">Lý do báo cáo</label>
                    <div class="custom-select-container relative">
                        <input type="hidden" id="report-reason" name="report-reason" value="Spam">
                        <button type="button" class="custom-select-trigger w-full flex items-center justify-between bg-background border border-input hover:border-vanixjnk rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-1 focus:ring-vanixjnk/20 transition-all cursor-pointer">
                            <span class="selected-text truncate mr-2">Spam</span>
                            <iconify-icon icon="solar:alt-arrow-down-linear" class="chevron-icon text-xs text-muted-foreground transition-transform duration-200" width="14"></iconify-icon>
                        </button>
                        <div class="custom-select-content absolute w-full mt-1 bg-popover border border-border rounded-xl shadow-xl z-50 overflow-hidden bg-background hidden" data-state="closed">
                            <div class="max-h-[200px] overflow-y-auto scrollbar-thin p-1">
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2 pl-3 pr-8 text-sm outline-none hover:bg-vanixjnk/10 hover:text-vanixjnk data-[state=checked]:font-bold data-[state=checked]:text-vanixjnk transition-colors" data-value="Spam" data-label="Spam" data-state="checked">
                                    <span class="truncate">Spam</span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center opacity-0 transition-opacity duration-150 check-icon">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2 pl-3 pr-8 text-sm outline-none hover:bg-vanixjnk/10 hover:text-vanixjnk data-[state=checked]:font-bold data-[state=checked]:text-vanixjnk transition-colors" data-value="Nội dung không phù hợp" data-label="Nội dung không phù hợp" data-state="unchecked">
                                    <span class="truncate">Nội dung không phù hợp</span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center opacity-0 transition-opacity duration-150 check-icon">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2 pl-3 pr-8 text-sm outline-none hover:bg-vanixjnk/10 hover:text-vanixjnk data-[state=checked]:font-bold data-[state=checked]:text-vanixjnk transition-colors" data-value="Quấy rối" data-label="Quấy rối" data-state="unchecked">
                                    <span class="truncate">Quấy rối</span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center opacity-0 transition-opacity duration-150 check-icon">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2 pl-3 pr-8 text-sm outline-none hover:bg-vanixjnk/10 hover:text-vanixjnk data-[state=checked]:font-bold data-[state=checked]:text-vanixjnk transition-colors" data-value="Lừa đảo" data-label="Lừa đảo" data-state="unchecked">
                                    <span class="truncate">Lừa đảo</span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center opacity-0 transition-opacity duration-150 check-icon">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2 pl-3 pr-8 text-sm outline-none hover:bg-vanixjnk/10 hover:text-vanixjnk data-[state=checked]:font-bold data-[state=checked]:text-vanixjnk transition-colors" data-value="Bạo lực" data-label="Bạo lực" data-state="unchecked">
                                    <span class="truncate">Bạo lực</span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center opacity-0 transition-opacity duration-150 check-icon">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2 pl-3 pr-8 text-sm outline-none hover:bg-vanixjnk/10 hover:text-vanixjnk data-[state=checked]:font-bold data-[state=checked]:text-vanixjnk transition-colors" data-value="Other" data-label="Khác" data-state="unchecked">
                                    <span class="truncate">Khác</span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center opacity-0 transition-opacity duration-150 check-icon">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 hidden">
                    <label for="report-custom-reason" class="text-sm font-medium text-foreground">Mô tả chi tiết</label>
                    <textarea id="report-custom-reason" rows="3" placeholder="Vui lòng mô tả chi tiết lý do báo cáo..." class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50"></textarea>
                </div>
                
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeReportDialog()" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium">Hủy</button>
                    <button type="submit" id="btn-submit-report" class="h-10 px-4 rounded-lg bg-red-500 text-white hover:bg-red-500/90 transition text-sm font-medium">Gửi báo cáo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php'; ?>
