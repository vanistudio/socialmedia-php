<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';

$username = isset($_GET['username']) ? check_string($_GET['username']) : '';
if (empty($username)) {
    header('Location: /');
    exit;
}

$profileUser = $Vani->get_row("SELECT * FROM `users` WHERE `username` = '$username'");
if (!$profileUser) {
    header('Location: /'); 
    exit;
}
$profileUserId = intval($profileUser['id']);

$isLoggedIn = isset($_SESSION['email']) && !empty($_SESSION['email']);
$currentUser = null;
$currentUserId = 0;
if ($isLoggedIn) {
    $currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '" . addslashes($_SESSION['email']) . "'");
    $currentUserId = intval($currentUser['id'] ?? 0);
}

$isOwnProfile = ($isLoggedIn && $currentUserId === $profileUserId);

$isFollowing = false;
$isBlocked = false;
if ($isLoggedIn && !$isOwnProfile) {
    $isFollowing = $Vani->num_rows("SELECT id FROM `follows` WHERE `follower_id` = '$currentUserId' AND `following_id` = '$profileUserId'") > 0;
    $isBlocked = $Vani->num_rows("SELECT id FROM `user_blocks` WHERE `blocker_id` = '$currentUserId' AND `blocked_id` = '$profileUserId'") > 0;
}

$stats = [
    'posts' => $Vani->num_rows("SELECT id FROM `posts` WHERE `user_id` = '$profileUserId'") ?: 0,
    'followers' => $Vani->num_rows("SELECT id FROM `follows` WHERE `following_id` = '$profileUserId'") ?: 0,
    'following' => $Vani->num_rows("SELECT id FROM `follows` WHERE `follower_id` = '$profileUserId'") ?: 0,
];

$tabs = ['posts', 'media'];
if ($isOwnProfile) {
    $tabs[] = 'saved';
}
$activeTab = $_GET['tab'] ?? 'posts';
if (!in_array($activeTab, $tabs)) {
    $activeTab = 'posts';
}

$tabContent = [];
$basePostQuery = "SELECT 
    p.*, 
    u.full_name, u.username, u.avatar,
    (SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id) as like_count,
    (SELECT COUNT(*) FROM `post_comments` WHERE `post_id` = p.id) as comment_count,
    (SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id AND `user_id` = '$currentUserId') as has_liked,
    (SELECT COUNT(*) FROM `post_bookmarks` WHERE `post_id` = p.id AND `user_id` = '$currentUserId') as has_saved
    FROM `posts` p 
    JOIN `users` u ON p.user_id = u.id";

switch ($activeTab) {
    case 'media':
        $tabContent = $Vani->get_list("SELECT pm.media_url, pm.post_id FROM `post_media` pm JOIN `posts` p ON pm.post_id = p.id WHERE p.user_id = '$profileUserId' ORDER BY p.created_at DESC");
        break;
    case 'saved':
        if ($isOwnProfile) {
            $tabContent = $Vani->get_list(str_replace("FROM `posts` p", "FROM `post_bookmarks` pb JOIN `posts` p ON pb.post_id = p.id", $basePostQuery) . " WHERE pb.user_id = '$currentUserId' ORDER BY pb.created_at DESC");
        }
        break;
    case 'posts':
    default:
        $tabContent = $Vani->get_list($basePostQuery . " WHERE p.user_id = '$profileUserId' ORDER BY p.created_at DESC");
        break;
}

require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php';
?>

<div class="w-full max-w-5xl mx-auto">
    <div class="h-48 md:h-64 bg-card border border-border rounded-2xl relative bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars(!empty($profileUser['banner']) ? $profileUser['banner'] : 'https://placehold.co/1200x400/png'); ?>');"></div>
    <div class="relative -mt-16 md:-mt-20 px-4 sm:px-8">
        <div class="flex flex-col sm:flex-row items-center sm:items-end gap-4">
            <img src="<?php echo htmlspecialchars(!empty($profileUser['avatar']) ? $profileUser['avatar'] : 'https://placehold.co/200x200/png'); ?>" alt="Avatar" class="h-32 w-32 md:h-40 md:w-40 rounded-full border-4 border-background bg-card object-cover">
            <div class="flex-1 flex flex-col sm:flex-row items-center justify-between w-full gap-4">
                <div class="text-center sm:text-left mt-2 sm:mt-0">
                    <h1 class="text-2xl md:text-3xl font-bold text-foreground"><?php echo htmlspecialchars($profileUser['full_name']); ?></h1>
                    <p class="text-sm text-muted-foreground">@<?php echo htmlspecialchars($profileUser['username']); ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($isOwnProfile): ?>
                        <a href="/settings" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium flex items-center gap-2">
                            <iconify-icon icon="solar:settings-linear" width="18"></iconify-icon><span>Chỉnh sửa</span>
                        </a>
                    <?php else: ?>
                        <?php if ($isLoggedIn): ?>
                            <button type="button" id="follow-btn" data-action="toggle-follow" data-user-id="<?php echo $profileUserId; ?>" class="h-10 px-4 rounded-lg <?php echo $isFollowing ? 'border border-input bg-card hover:bg-accent' : 'bg-vanixjnk text-white hover:bg-vanixjnk/90'; ?> transition text-sm font-medium flex items-center gap-2">
                                <iconify-icon icon="<?php echo $isFollowing ? 'solar:user-check-rounded-linear' : 'solar:user-plus-rounded-linear'; ?>" width="18"></iconify-icon>
                                <span id="follow-text"><?php echo $isFollowing ? 'Đang theo dõi' : 'Theo dõi'; ?></span>
                            </button>
                            <div class="relative dropdown-container">
                                <button type="button" onclick="toggleDropdown('profile-menu', this)" class="h-10 w-10 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center justify-center text-muted-foreground" aria-label="More options">
                                    <iconify-icon icon="solar:menu-dots-bold" width="18"></iconify-icon>
                                </button>
                                <div id="profile-menu" class="dropdown-menu hidden fixed w-56 bg-card border border-border rounded-xl shadow-lg z-50" data-state="closed">
                                    <ul class="py-1">
                                        <li>
                                            <button type="button" data-action="toggle-block" data-user-id="<?php echo $profileUserId; ?>" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-red-500 hover:bg-red-500/10">
                                                <iconify-icon icon="<?php echo $isBlocked ? 'solar:unlock-linear' : 'solar:lock-linear'; ?>" width="16"></iconify-icon>
                                                <span><?php echo $isBlocked ? 'Bỏ chặn' : 'Chặn người dùng'; ?></span>
                                            </button>
                                        </li>
                                        <hr class="my-1 border-border">
                                        <li>
                                            <button type="button" data-action="report-user" data-user-id="<?php echo $profileUserId; ?>" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-red-500 hover:bg-red-500/10">
                                                <iconify-icon icon="solar:danger-triangle-linear" width="16"></iconify-icon>
                                                <span>Báo cáo</span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="/login" class="h-10 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium flex items-center gap-2">
                                <iconify-icon icon="solar:user-plus-rounded-linear" width="18"></iconify-icon><span>Theo dõi</span>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="mt-6 space-y-4">
            <p class="text-sm text-muted-foreground max-w-2xl text-center sm:text-left"><?php echo htmlspecialchars($profileUser['bio']); ?></p>
            <div class="flex items-center justify-center sm:justify-start gap-6 text-sm">
                <div class="text-center sm:text-left"><span class="font-bold text-foreground"><?php echo $stats['posts']; ?></span><span class="text-muted-foreground"> bài viết</span></div>
                <button type="button" class="text-center sm:text-left hover:underline" data-action="view-followers" data-user-id="<?php echo $profileUserId; ?>">
                    <span class="font-bold text-foreground" id="followers-count"><?php echo $stats['followers']; ?></span><span class="text-muted-foreground"> người theo dõi</span>
                </button>
                <button type="button" class="text-center sm:text-left hover:underline" data-action="view-following" data-user-id="<?php echo $profileUserId; ?>">
                    <span class="font-bold text-foreground" id="following-count"><?php echo $stats['following']; ?></span><span class="text-muted-foreground"> đang theo dõi</span>
                </button>
            </div>
        </div>
    </div>
    <div class="mt-8 border-b border-border">
        <nav class="-mb-px flex gap-6" aria-label="Tabs">
            <a href="/u/<?php echo $profileUser['username']; ?>?tab=posts" class="shrink-0 border-b-2 px-1 pb-3 text-sm font-medium <?php echo $activeTab === 'posts' ? 'border-vanixjnk text-vanixjnk' : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground'; ?>">Bài viết</a>
            <a href="/u/<?php echo $profileUser['username']; ?>?tab=media" class="shrink-0 border-b-2 px-1 pb-3 text-sm font-medium <?php echo $activeTab === 'media' ? 'border-vanixjnk text-vanixjnk' : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground'; ?>">Ảnh</a>
            <?php if ($isOwnProfile): ?>
                <a href="/u/<?php echo $profileUser['username']; ?>?tab=saved" class="shrink-0 border-b-2 px-1 pb-3 text-sm font-medium <?php echo $activeTab === 'saved' ? 'border-vanixjnk text-vanixjnk' : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground'; ?>">Đã lưu</a>
            <?php endif; ?>
        </nav>
    </div>
    <div class="mt-6">
        <?php if (empty($tabContent)): ?>
            <div class="text-center py-12">
                <p class="text-muted-foreground">Chưa có gì ở đây.</p>
            </div>
        <?php else: ?>
            <?php if ($activeTab === 'media'): ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($tabContent as $item): ?>
                        <a href="/post/<?php echo $item['post_id']; ?>" class="block bg-card border border-border rounded-lg overflow-hidden aspect-square">
                            <img src="<?php echo htmlspecialchars($item['media_url']); ?>" alt="Media" class="w-full h-full object-cover">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($tabContent as $post): ?>
                        <?php include $_SERVER['DOCUMENT_ROOT'] . '/views/components/_post_card.php';  ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Followers/Following Modal -->
<div id="follow-modal" class="dialog hidden fixed inset-0 z-50 flex items-center justify-center p-4" data-state="closed">
    <div class="dialog-overlay fixed inset-0 bg-background/80 backdrop-blur-sm" onclick="closeFollowModal()"></div>
    <div class="dialog-content relative bg-card border border-border rounded-2xl shadow-lg w-full max-w-md max-h-[80vh] overflow-hidden">
        <div class="p-4 border-b border-border flex items-center justify-between">
            <h2 class="text-lg font-semibold text-foreground" id="follow-modal-title">Người theo dõi</h2>
            <button type="button" onclick="closeFollowModal()" class="h-8 w-8 rounded-lg hover:bg-accent transition flex items-center justify-center">
                <iconify-icon icon="solar:close-circle-linear" width="20"></iconify-icon>
            </button>
        </div>
        <div id="follow-modal-content" class="p-4 overflow-y-auto max-h-[calc(80vh-80px)]">
            <div class="text-center py-8">
                <p class="text-muted-foreground">Đang tải...</p>
            </div>
        </div>
    </div>
</div>

<script>
function openFollowModal(type, userId) {
    const modal = document.getElementById('follow-modal');
    const title = document.getElementById('follow-modal-title');
    const content = document.getElementById('follow-modal-content');
    
    title.textContent = type === 'followers' ? 'Người theo dõi' : 'Đang theo dõi';
    content.html('<div class="text-center py-8"><p class="text-muted-foreground">Đang tải...</p></div>');
    
    modal.classList.remove('hidden');
    modal.setAttribute('data-state', 'open');
    
    $.post('/api/controller/app', { type: type === 'followers' ? 'GET_FOLLOWERS' : 'GET_FOLLOWING', user_id: userId }, function(data) {
        if (data.status === 'success') {
            const users = data.users || [];
            if (users.length === 0) {
                content.html('<div class="text-center py-8"><p class="text-muted-foreground">Chưa có ai</p></div>');
                return;
            }
            
            let html = '<div class="space-y-2">';
            users.forEach(function(user) {
                html += `
                    <a href="/u/${user.username}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-accent transition">
                        <img src="${user.avatar || 'https://placehold.co/200x200/png'}" alt="Avatar" class="h-12 w-12 rounded-full object-cover">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-foreground truncate">${user.full_name}</p>
                            <p class="text-xs text-muted-foreground">@${user.username}</p>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
            content.html(html);
        } else {
            content.html('<div class="text-center py-8"><p class="text-red-500">Có lỗi xảy ra</p></div>');
        }
    }, 'json').fail(function() {
        content.html('<div class="text-center py-8"><p class="text-red-500">Không thể kết nối tới máy chủ</p></div>');
    });
}

function closeFollowModal() {
    const modal = document.getElementById('follow-modal');
    modal.setAttribute('data-state', 'closed');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

$(document).ready(function() {
    $(document).on('click', '[data-action="view-followers"], [data-action="view-following"]', function() {
        const action = $(this).data('action');
        const userId = $(this).data('user-id');
        const type = action === 'view-followers' ? 'followers' : 'following';
        openFollowModal(type, userId);
    });
});
</script>

<script>
$(document).ready(function() {
    const currentUserId = <?php echo $currentUserId; ?>;
    const isFollowing = <?php echo $isFollowing ? 'true' : 'false'; ?>;
    
    // Block/Unblock handler
    $(document).on('click', '[data-action="toggle-block"]', function() {
        if (!currentUserId) {
            toast.error('Vui lòng đăng nhập để thực hiện');
            return;
        }
        
        const userId = $(this).data('user-id');
        const $btn = $(this);
        const $text = $btn.find('span');
        const $icon = $btn.find('iconify-icon');
        
        if (!confirm('Bạn có chắc chắn muốn ' + ($text.text().includes('Bỏ chặn') ? 'bỏ chặn' : 'chặn') + ' người dùng này?')) {
            return;
        }
        
        $.post('/api/controller/app', { type: 'TOGGLE_BLOCK', user_id: userId }, function(data) {
            if (data.status === 'success') {
                if (data.is_blocked) {
                    $text.text('Bỏ chặn');
                    $icon.attr('icon', 'solar:unlock-linear');
                } else {
                    $text.text('Chặn người dùng');
                    $icon.attr('icon', 'solar:lock-linear');
                }
                toast.success(data.message);
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toast.error(data.message || 'Có lỗi xảy ra');
            }
        }, 'json').fail(() => toast.error('Không thể kết nối tới máy chủ'));
    });
    
    // Report user handler
    $(document).on('click', '[data-action="report-user"]', function() {
        if (!currentUserId) {
            toast.error('Vui lòng đăng nhập để thực hiện');
            return;
        }
        
        const userId = $(this).data('user-id');
        toast.info('Đang gửi báo cáo...');
        $.post('/api/controller/app', { type: 'REPORT_ENTITY', target_type: 'user', target_id: userId, reason: 'Spam' }, function(data) {
            if (data.status === 'success') {
                toast.success(data.message);
            } else {
                toast.error(data.message || 'Có lỗi xảy ra');
            }
        }, 'json').fail(() => toast.error('Không thể kết nối tới máy chủ'));
    });
    
    // Follow/Unfollow handler
    $(document).on('click', '[data-action="toggle-follow"]', function() {
        if (!currentUserId) {
            toast.error('Vui lòng đăng nhập để thực hiện');
            return;
        }
        
        const $btn = $(this);
        const userId = $btn.data('user-id');
        const $text = $('#follow-text');
        const $icon = $btn.find('iconify-icon');
        const $followersCount = $('#followers-count');
        
        $.post('/api/controller/app', { type: 'TOGGLE_FOLLOW', user_id: userId }, function(data) {
            if (data.status === 'success') {
                if (data.is_following) {
                    $btn.removeClass('bg-vanixjnk text-white hover:bg-vanixjnk/90').addClass('border border-input bg-card hover:bg-accent');
                    $text.text('Đang theo dõi');
                    $icon.attr('icon', 'solar:user-check-rounded-linear');
                } else {
                    $btn.removeClass('border border-input bg-card hover:bg-accent').addClass('bg-vanixjnk text-white hover:bg-vanixjnk/90');
                    $text.text('Theo dõi');
                    $icon.attr('icon', 'solar:user-plus-rounded-linear');
                }
                $followersCount.text(data.followers_count);
                toast.success(data.message);
            } else {
                toast.error(data.message || 'Có lỗi xảy ra');
            }
        }, 'json').fail(() => toast.error('Không thể kết nối tới máy chủ'));
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
                $.post('/api/controller/app', { type: 'TOGGLE_LIKE', post_id: postId }, function(data) {
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
                const url = `${window.location.origin}/post/${postId}`;
                navigator.clipboard.writeText(url).then(() => toast.success('Đã copy link bài viết')).catch(() => toast.error('Không thể copy link'));
                break;

            case 'save-post':
                $.post('/api/controller/app', { type: 'SAVE_POST', post_id: postId }, function(data) {
                    if (data.status === 'success') {
                        toast.success(data.message);
                        $self.find('span').text(data.saved ? 'Bỏ lưu' : 'Lưu bài viết');
                    }
                }, 'json');
                break;

            case 'report-post':
                toast.info('Đang gửi báo cáo...');
                $.post('/api/controller/app', { type: 'REPORT_ENTITY', target_type: 'post', target_id: postId, reason: 'Spam' }, function(data) {
                    if (data.status === 'success') toast.success(data.message);
                }, 'json');
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
                $.post('/api/controller/app', { type: 'TOGGLE_COMMENT_LIKE', comment_id: commentId }, function(data) {
                    if (data.status === 'success') {
                        $self.toggleClass('text-vanixjnk', data.liked);
                        $self.find('.comment-like-count').text(data.like_count);
                    }
                }, 'json');
                break;

            case 'delete-comment':
                if (!confirm('Xóa bình luận này?')) return;
                $.post('/api/controller/app', { type: 'DELETE_COMMENT', comment_id: commentId }, function(data) {
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

        $.post('/api/controller/app', $form.serialize(), function(data) {
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