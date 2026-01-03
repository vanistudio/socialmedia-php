<?php
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php';

$isLoggedIn = isset($_SESSION['email']) && !empty($_SESSION['email']);
$currentUser = null;
$currentUserId = 0;
if ($isLoggedIn) {
    $currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '" . addslashes($_SESSION['email']) . "'");
    $currentUserId = intval($currentUser['id'] ?? 0);
}

$postId = intval($_GET['id'] ?? 0);
if ($postId <= 0) {
    header('Location: /');
    exit;
}

$post = $Vani->get_row("SELECT 
    p.*, 
    u.full_name, u.username, u.avatar,
    (SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id) as like_count,
    (SELECT COUNT(*) FROM `post_comments` WHERE `post_id` = p.id) as comment_count,
    (SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id AND `user_id` = '$currentUserId') as has_liked,
    (SELECT COUNT(*) FROM `post_bookmarks` WHERE `post_id` = p.id AND `user_id` = '$currentUserId') as has_saved
    FROM `posts` p 
    JOIN `users` u ON p.user_id = u.id
    WHERE p.id = '$postId'");

if (!$post) {
    echo '<div class="max-w-3xl mx-auto bg-card border border-border rounded-2xl p-6 text-center">';
    echo '<h1 class="text-xl font-semibold">Bài viết không tồn tại</h1>';
    echo '<p class="text-muted-foreground mt-1">Có thể bài viết đã bị xóa hoặc bạn nhập sai link.</p>';
    echo '<a href="/" class="inline-flex mt-4 h-10 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition items-center">Về trang chủ</a>';
    echo '</div>';
    require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php';
    exit;
}

$media = $Vani->get_list("SELECT * FROM `post_media` WHERE `post_id` = '$postId' ORDER BY `sort_order` ASC");

?>

<div class="w-full max-w-3xl mx-auto space-y-6">

    <div class="bg-card border border-border rounded-2xl shadow-sm" id="post-<?php echo $post['id']; ?>">
        <div class="p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/u/<?php echo htmlspecialchars($post['username']); ?>">
                    <img src="<?php echo htmlspecialchars($post['avatar']); ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                </a>
                <div>
                    <a href="/u/<?php echo htmlspecialchars($post['username']); ?>" class="font-semibold text-foreground hover:underline"><?php echo htmlspecialchars($post['full_name']); ?></a>
                    <p class="text-xs text-muted-foreground">@<?php echo htmlspecialchars($post['username']); ?> · <?php echo htmlspecialchars($post['created_at']); ?></p>
                </div>
            </div>

            <div class="relative dropdown-container">
                <button type="button" onclick="toggleDropdown('post-menu-<?php echo $post['id']; ?>', this)" class="h-8 w-8 rounded-lg hover:bg-accent transition flex items-center justify-center text-muted-foreground" aria-label="More options">
                    <iconify-icon icon="solar:menu-dots-bold" width="18"></iconify-icon>
                </button>
                <div id="post-menu-<?php echo $post['id']; ?>" class="dropdown-menu hidden fixed w-56 bg-card border border-border rounded-xl shadow-lg z-50" data-state="closed">
                    <ul class="py-1">
                        <li>
                            <button type="button" data-action="copy-link" data-post-id="<?php echo $post['id']; ?>" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-foreground hover:bg-accent">
                                <iconify-icon icon="solar:link-linear" width="16"></iconify-icon><span>Copy link</span>
                            </button>
                        </li>
                        <li>
                            <button type="button" data-action="save-post" data-post-id="<?php echo $post['id']; ?>" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-foreground hover:bg-accent">
                                <iconify-icon icon="solar:bookmark-linear" width="16"></iconify-icon><span><?php echo $post['has_saved'] > 0 ? 'Bỏ lưu' : 'Lưu bài viết'; ?></span>
                            </button>
                        </li>
                        <hr class="my-1 border-border">
                        <li>
                            <button type="button" data-action="report-post" data-post-id="<?php echo $post['id']; ?>" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-red-500 hover:bg-red-500/10">
                                <iconify-icon icon="solar:danger-triangle-linear" width="16"></iconify-icon><span>Báo cáo</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if (!empty($post['content'])): ?>
            <div class="px-4 pb-4">
                <p class="text-foreground whitespace-pre-wrap"><?php echo htmlspecialchars($post['content']); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($media)): ?>
            <div class="grid grid-cols-<?php echo count($media) > 1 ? '2' : '1'; ?> gap-0.5 border-y border-border bg-border">
                <?php foreach ($media as $item): ?>
                    <a href="<?php echo htmlspecialchars($item['media_url']); ?>" target="_blank" class="bg-background">
                        <img src="<?php echo htmlspecialchars($item['media_url']); ?>" alt="Post media" class="w-full h-auto max-h-[700px] object-cover">
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="p-2 flex justify-around">
            <button type="button" data-action="toggle-like" data-post-id="<?php echo $post['id']; ?>" class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-accent transition <?php echo $post['has_liked'] > 0 ? 'text-vanixjnk' : 'text-muted-foreground'; ?>">
                <iconify-icon icon="<?php echo $post['has_liked'] > 0 ? 'solar:heart-bold' : 'solar:heart-linear'; ?>" width="20"></iconify-icon>
                <span class="text-sm font-medium like-count"><?php echo $post['like_count']; ?></span>
            </button>
            <button type="button" data-action="toggle-comments" data-post-id="<?php echo $post['id']; ?>" class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-accent transition text-muted-foreground">
                <iconify-icon icon="solar:chat-dots-linear" width="20"></iconify-icon>
                <span class="text-sm font-medium comment-count"><?php echo $post['comment_count']; ?></span>
            </button>
            <button type="button" class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-accent transition text-muted-foreground" onclick="toast.info('Đang phát triển')">
                <iconify-icon icon="solar:share-linear" width="20"></iconify-icon>
                <span class="text-sm font-medium">Chia sẻ</span>
            </button>
        </div>

        <div class="p-4 border-t border-border space-y-4" id="comments-<?php echo $post['id']; ?>">
            <?php if ($isLoggedIn): ?>
                <div class="flex items-start gap-3">
                    <img src="<?php echo htmlspecialchars($currentUser['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="My Avatar" class="h-8 w-8 rounded-full object-cover">
                    <form class="flex-1 relative" data-form="add-comment" data-post-id="<?php echo $post['id']; ?>">
                        <input type="hidden" name="type" value="ADD_COMMENT">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="text" name="content" placeholder="Viết bình luận..." class="w-full h-9 rounded-lg border border-input bg-background px-3 pr-10 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30">
                        <button type="submit" class="absolute top-1/2 right-2 -translate-y-1/2 h-7 w-7 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 flex items-center justify-center transition">
                            <iconify-icon icon="solar:arrow-right-linear" width="18"></iconify-icon>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-accent/40 border border-border rounded-xl p-4 text-sm">
                    <a href="/login" class="text-vanixjnk font-medium hover:underline">Đăng nhập</a> để bình luận.
                </div>
            <?php endif; ?>

            <div class="space-y-3 comment-list">
                <?php
                $comments = $Vani->get_list("SELECT c.*, u.full_name, u.username, u.avatar,
                    (SELECT COUNT(*) FROM `comment_likes` cl WHERE cl.comment_id = c.id) AS like_count,
                    (SELECT COUNT(*) FROM `comment_likes` cl WHERE cl.comment_id = c.id AND cl.user_id = '$currentUserId') AS has_liked
                    FROM `post_comments` c 
                    JOIN `users` u ON c.user_id = u.id
                    WHERE c.post_id = '{$post['id']}' AND c.parent_id IS NULL
                    ORDER BY c.created_at ASC");

                foreach ($comments as $comment):
                    $replies = $Vani->get_list("SELECT c.*, u.full_name, u.username, u.avatar,
                        (SELECT COUNT(*) FROM `comment_likes` cl WHERE cl.comment_id = c.id) AS like_count,
                        (SELECT COUNT(*) FROM `comment_likes` cl WHERE cl.comment_id = c.id AND cl.user_id = '$currentUserId') AS has_liked
                        FROM `post_comments` c 
                        JOIN `users` u ON c.user_id = u.id
                        WHERE c.post_id = '{$post['id']}' AND c.parent_id = '{$comment['id']}'
                        ORDER BY c.created_at ASC");
                ?>
                    <div class="space-y-2">
                        <div class="flex items-start gap-3">
                            <a href="/u/<?php echo htmlspecialchars($comment['username']); ?>"><img src="<?php echo htmlspecialchars($comment['avatar']); ?>" alt="Avatar" class="h-8 w-8 rounded-full object-cover"></a>
                            <div class="flex-1">
                                <div class="bg-accent/50 rounded-xl px-3 py-2">
                                    <a href="/u/<?php echo htmlspecialchars($comment['username']); ?>" class="font-semibold text-foreground text-sm hover:underline"><?php echo htmlspecialchars($comment['full_name']); ?></a>
                                    <p class="text-sm text-foreground"><?php echo htmlspecialchars($comment['content']); ?></p>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-muted-foreground mt-1 px-2">
                                    <button type="button" data-action="toggle-comment-like" data-comment-id="<?php echo $comment['id']; ?>" class="hover:underline <?php echo ($comment['has_liked'] > 0) ? 'text-vanixjnk' : ''; ?>">Thích <span class="comment-like-count"><?php echo intval($comment['like_count']); ?></span></button>
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
                                                <a href="/u/<?php echo htmlspecialchars($reply['username']); ?>"><img src="<?php echo htmlspecialchars($reply['avatar']); ?>" alt="Avatar" class="h-7 w-7 rounded-full object-cover"></a>
                                                <div class="flex-1">
                                                    <div class="bg-accent/40 rounded-xl px-3 py-2">
                                                        <a href="/u/<?php echo htmlspecialchars($reply['username']); ?>" class="font-semibold text-foreground text-sm hover:underline"><?php echo htmlspecialchars($reply['full_name']); ?></a>
                                                        <p class="text-sm text-foreground"><?php echo htmlspecialchars($reply['content']); ?></p>
                                                    </div>
                                                    <div class="flex items-center gap-3 text-xs text-muted-foreground mt-1 px-2">
                                                        <button type="button" data-action="toggle-comment-like" data-comment-id="<?php echo $reply['id']; ?>" class="hover:underline <?php echo ($reply['has_liked'] > 0) ? 'text-vanixjnk' : ''; ?>">Thích <span class="comment-like-count"><?php echo intval($reply['like_count']); ?></span></button>
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
    </div>
</div>

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
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center opacity-0 data-[state=checked]:opacity-100 check-icon">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2 pl-3 pr-8 text-sm outline-none hover:bg-vanixjnk/10 hover:text-vanixjnk data-[state=checked]:font-bold data-[state=checked]:text-vanixjnk transition-colors" data-value="Nội dung không phù hợp" data-label="Nội dung không phù hợp" data-state="unchecked">
                                    <span class="truncate">Nội dung không phù hợp</span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center opacity-0 data-[state=checked]:opacity-100 check-icon">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2 pl-3 pr-8 text-sm outline-none hover:bg-vanixjnk/10 hover:text-vanixjnk data-[state=checked]:font-bold data-[state=checked]:text-vanixjnk transition-colors" data-value="Quấy rối" data-label="Quấy rối" data-state="unchecked">
                                    <span class="truncate">Quấy rối</span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center opacity-0 data-[state=checked]:opacity-100 check-icon">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2 pl-3 pr-8 text-sm outline-none hover:bg-vanixjnk/10 hover:text-vanixjnk data-[state=checked]:font-bold data-[state=checked]:text-vanixjnk transition-colors" data-value="Lừa đảo" data-label="Lừa đảo" data-state="unchecked">
                                    <span class="truncate">Lừa đảo</span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center opacity-0 data-[state=checked]:opacity-100 check-icon">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2 pl-3 pr-8 text-sm outline-none hover:bg-vanixjnk/10 hover:text-vanixjnk data-[state=checked]:font-bold data-[state=checked]:text-vanixjnk transition-colors" data-value="Bạo lực" data-label="Bạo lực" data-state="unchecked">
                                    <span class="truncate">Bạo lực</span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center opacity-0 data-[state=checked]:opacity-100 check-icon">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2 pl-3 pr-8 text-sm outline-none hover:bg-vanixjnk/10 hover:text-vanixjnk data-[state=checked]:font-bold data-[state=checked]:text-vanixjnk transition-colors" data-value="Other" data-label="Khác" data-state="unchecked">
                                    <span class="truncate">Khác</span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center opacity-0 data-[state=checked]:opacity-100 check-icon">
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
    
    setTimeout(function() {
        if (window.initDialog) {
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

    $(document).on('click', '[data-action]', function() {
        const action = $(this).data('action');
        const postId = $(this).data('post-id');
        const commentId = $(this).data('comment-id');
        const $self = $(this);

        if (!currentUserId && ['toggle-like','save-post','report-post','reply-comment','toggle-comment-like','delete-comment'].includes(action)) {
            toast.error('Vui lòng đăng nhập để thực hiện');
            return;
        }

        switch (action) {
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

            case 'copy-link':
                const url = `${window.location.origin}/post/${postId}`;
                navigator.clipboard.writeText(url).then(() => toast.success('Đã copy link bài viết')).catch(() => toast.error('Không thể copy link'));
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

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php'; ?>
