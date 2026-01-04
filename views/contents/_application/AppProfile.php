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

// Build visibility filter for viewing other users' posts
$visibilityFilter = '';
if (!$isOwnProfile && $currentUserId > 0) {
    $visibilityFilter = "AND (
        p.visibility = 'public' 
        OR (p.visibility = 'followers' AND EXISTS (
            SELECT 1 FROM follows WHERE follower_id = '$currentUserId' AND following_id = p.user_id
        ))
    )";
} elseif (!$isOwnProfile) {
    $visibilityFilter = "AND p.visibility = 'public'";
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
        $mediaVisibility = $isOwnProfile ? "" : ($currentUserId > 0 ? "AND (p.visibility = 'public' OR (p.visibility = 'followers' AND EXISTS (SELECT 1 FROM follows WHERE follower_id = '$currentUserId' AND following_id = p.user_id)))" : "AND p.visibility = 'public'");
        $tabContent = $Vani->get_list("SELECT pm.media_url, pm.post_id FROM `post_media` pm JOIN `posts` p ON pm.post_id = p.id WHERE p.user_id = '$profileUserId' $mediaVisibility ORDER BY p.created_at DESC");
        break;
    case 'saved':
        if ($isOwnProfile) {
            $tabContent = $Vani->get_list(str_replace("FROM `posts` p", "FROM `post_bookmarks` pb JOIN `posts` p ON pb.post_id = p.id", $basePostQuery) . " WHERE pb.user_id = '$currentUserId' ORDER BY pb.created_at DESC");
        }
        break;
    case 'posts':
    default:
        $tabContent = $Vani->get_list($basePostQuery . " WHERE p.user_id = '$profileUserId' $visibilityFilter ORDER BY p.created_at DESC");
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
            <?php if (!empty($profileUser['bio'])): ?>
            <p class="text-sm text-muted-foreground max-w-2xl text-center sm:text-left"><?php echo htmlspecialchars($profileUser['bio']); ?></p>
            <?php endif; ?>
            
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-sm text-muted-foreground">
                <?php if (!empty($profileUser['location'])): ?>
                <div class="flex items-center gap-1">
                    <iconify-icon icon="solar:map-point-linear" width="16"></iconify-icon>
                    <span><?php echo htmlspecialchars($profileUser['location']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($profileUser['website'])): ?>
                <div class="flex items-center gap-1">
                    <iconify-icon icon="solar:link-linear" width="16"></iconify-icon>
                    <a href="<?php echo htmlspecialchars($profileUser['website']); ?>" target="_blank" rel="noopener noreferrer" class="text-vanixjnk hover:underline"><?php echo htmlspecialchars(preg_replace('/^https?:\/\//', '', $profileUser['website'])); ?></a>
                </div>
                <?php endif; ?>
                <?php if (!empty($profileUser['birthday'])): ?>
                <div class="flex items-center gap-1">
                    <iconify-icon icon="solar:cake-linear" width="16"></iconify-icon>
                    <span><?php echo date('d/m/Y', strtotime($profileUser['birthday'])); ?></span>
                </div>
                <?php endif; ?>
                <div class="flex items-center gap-1">
                    <iconify-icon icon="solar:calendar-linear" width="16"></iconify-icon>
                    <span>Tham gia <?php echo date('m/Y', strtotime($profileUser['created_at'])); ?></span>
                </div>
            </div>

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
<div id="follow-modal" class="dialog hidden fixed inset-0 z-50 flex items-center justify-center p-4" data-dialog data-state="closed">
    <div class="absolute inset-0 bg-background/80 backdrop-blur-sm" data-dialog-backdrop></div>
    <div class="relative w-full max-w-md mx-auto" data-dialog-content>
        <div class="bg-card border border-border rounded-2xl shadow-lg" data-dialog-inner>
            <div class="p-4 border-b border-border flex items-center justify-between">
                <h2 class="text-lg font-semibold text-foreground" id="follow-modal-title">Người theo dõi</h2>
                <button type="button" data-dialog-close class="h-8 w-8 rounded-lg hover:bg-accent transition flex items-center justify-center">
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

<script>
let followModal = null;

function openFollowModal(type, userId) {
    const $title = $('#follow-modal-title');
    const $content = $('#follow-modal-content');
    
    $title.text(type === 'followers' ? 'Người theo dõi' : 'Đang theo dõi');
    $content.html('<div class="text-center py-8"><p class="text-muted-foreground">Đang tải...</p></div>');
    
    if (!followModal && window.initDialog) {
        followModal = window.initDialog('follow-modal');
    }
    
    if (followModal) {
        followModal.open();
    } else {
        const $modal = $('#follow-modal');
        $modal.removeClass('hidden').addClass('flex');
        setTimeout(() => {
            $modal.attr('data-state', 'open');
        }, 10);
    }
    
    $.post('/api/controller/app', { 
        type: type === 'followers' ? 'GET_FOLLOWERS' : 'GET_FOLLOWING', 
        user_id: userId, 
        csrf_token: window.CSRF_TOKEN || '' 
    }, function(data) {
        if (data.status === 'success') {
            const users = data.users || [];
            if (users.length === 0) {
                $content.html('<div class="text-center py-8"><p class="text-muted-foreground">Chưa có ai</p></div>');
                return;
            }
            
            let html = '<div class="space-y-2">';
            users.forEach(function(user) {
                const avatar = user.avatar || 'https://placehold.co/200x200/png';
                const fullName = user.full_name || user.username || 'User';
                const username = user.username || '';
                
                html += `
                    <a href="/u/${username}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-accent transition">
                        <img src="${avatar}" alt="Avatar" class="h-12 w-12 rounded-full object-cover">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-foreground truncate">${fullName}</p>
                            <p class="text-xs text-muted-foreground">@${username}</p>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
            $content.html(html);
        } else {
            $content.html('<div class="text-center py-8"><p class="text-red-500">Có lỗi xảy ra</p></div>');
        }
    }, 'json').fail(function() {
        $content.html('<div class="text-center py-8"><p class="text-red-500">Không thể kết nối tới máy chủ</p></div>');
    });
}

function closeFollowModal() {
    if (followModal) {
        followModal.close();
    } else {
        const $modal = $('#follow-modal');
        $modal.attr('data-state', 'closed');
        setTimeout(() => {
            $modal.addClass('hidden').removeClass('flex');
        }, 200);
    }
}

$(document).ready(function() {
    setTimeout(function() {
        if (window.initDialog) {
            followModal = window.initDialog('follow-modal');
        }
    }, 100);
    
    $(document).on('click', '[data-action="view-followers"], [data-action="view-following"]', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        const userId = $(this).data('user-id');
        const type = action === 'view-followers' ? 'followers' : 'following';
        openFollowModal(type, userId);
    });
});
</script>

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
    const isFollowing = <?php echo $isFollowing ? 'true' : 'false'; ?>;
    
    setTimeout(function() {
        if (window.initDialog) {
            reportDialog = window.initDialog('report-dialog');
        }
    }, 100);
    
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
        
        $.post('/api/controller/app', { type: 'TOGGLE_BLOCK', user_id: userId, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
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
    
    $(document).on('click', '[data-action="report-user"]', function() {
        if (!currentUserId) {
            toast.error('Vui lòng đăng nhập để thực hiện');
            return;
        }
        
        const userId = $(this).data('user-id');
        openReportDialog('user', userId);
    });
    
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
        
        $.post('/api/controller/app', { type: 'TOGGLE_FOLLOW', user_id: userId, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
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

        if (!currentUserId && ['toggle-like','save-post','report-post','reply-comment','toggle-comment-like','delete-comment','edit-post','delete-post'].includes(action)) {
            toast.error('Vui lòng đăng nhập để thực hiện');
            return;
        }

        switch (action) {
            case 'edit-post':
                // Load post data and open edit dialog
                $.post('/api/controller/app', { type: 'GET_POST', post_id: postId, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
                    if (data.status === 'success' && data.post) {
                        const post = data.post;
                        $('#edit-post-id').val(postId);
                        $('#edit-post-content').val(post.content || '');
                        editPostMediaUrls = post.media || [];
                        renderEditPostMedia();
                        openEditPostDialog();
                    } else {
                        toast.error(data.message || 'Không thể tải bài viết');
                    }
                }, 'json').fail(() => toast.error('Không thể kết nối'));
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

// Edit post functionality
let editPostDialog = null;
let editPostMediaUrls = [];

function renderEditPostMedia() {
    const $container = $('#edit-post-media-previews');
    $container.empty();
    editPostMediaUrls.forEach((url, index) => {
        $container.append(`
            <div class="relative group">
                <img src="${url}" class="h-24 w-full object-cover rounded-lg">
                <button type="button" onclick="removeEditPostMedia(${index})" class="absolute top-1 right-1 h-6 w-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                    <iconify-icon icon="solar:close-circle-linear" width="14"></iconify-icon>
                </button>
            </div>
        `);
    });
}

function removeEditPostMedia(index) {
    editPostMediaUrls.splice(index, 1);
    renderEditPostMedia();
}

function openEditPostDialog() {
    if (editPostDialog) {
        editPostDialog.open();
    } else {
        $('#edit-post-dialog').removeClass('hidden').addClass('flex');
    }
}

function closeEditPostDialog() {
    if (editPostDialog) {
        editPostDialog.close();
    } else {
        $('#edit-post-dialog').addClass('hidden').removeClass('flex');
    }
}

$(document).ready(function() {
    if (window.initDialog) {
        editPostDialog = window.initDialog('edit-post-dialog');
    }

    $('#edit-post-media-upload').on('change', function(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('csrf_token', window.CSRF_TOKEN || '');
            $.ajax({
                url: '/api/controller/upload',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success' && res.url) {
                        editPostMediaUrls.push(res.url);
                        renderEditPostMedia();
                    } else {
                        toast.error(res.message || 'Upload thất bại');
                    }
                },
                error: function() {
                    toast.error('Upload thất bại');
                }
            });
        });
        $(this).val('');
    });

    $('#edit-post-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type=submit]');
        const originalBtnText = $btn.text();
        
        let postData = {};
        $form.serializeArray().forEach(item => {
            postData[item.name] = item.value;
        });
        
        if (editPostMediaUrls.length > 0) {
            postData.media = editPostMediaUrls;
        }
        
        postData.csrf_token = window.CSRF_TOKEN || '';
        
        $btn.prop('disabled', true).addClass('opacity-70').text('Đang lưu...');
        
        $.post('/api/controller/app', postData, function(data) {
            if (data.status === 'success') {
                toast.success(data.message);
                closeEditPostDialog();
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
});
</script>

<!-- Edit Post Dialog -->
<?php if ($isLoggedIn): ?>
<div id="edit-post-dialog" class="hidden fixed inset-0 z-50 items-center justify-center" data-dialog data-state="closed">
    <div class="absolute inset-0 bg-black/50" data-dialog-backdrop></div>
    <div class="relative w-full max-w-xl mx-auto p-4" data-dialog-content>
        <div class="bg-card border border-border rounded-2xl shadow-lg" data-dialog-inner>
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h3 class="text-lg font-semibold text-foreground">Chỉnh sửa bài viết</h3>
                <button type="button" class="h-9 w-9 rounded-lg hover:bg-accent transition flex items-center justify-center" onclick="closeEditPostDialog()">
                    <iconify-icon icon="solar:close-circle-linear" width="22"></iconify-icon>
                </button>
            </div>
            <form id="edit-post-form" class="p-4">
                <input type="hidden" name="type" value="UPDATE_POST">
                <input type="hidden" name="post_id" id="edit-post-id">
                <div class="space-y-4">
                    <div>
                        <textarea name="content" id="edit-post-content" placeholder="Nội dung bài viết..." class="w-full bg-transparent text-foreground placeholder:text-muted-foreground outline-none resize-none text-lg min-h-[120px] border border-input rounded-lg p-3"></textarea>
                    </div>
                    <div id="edit-post-media-previews" class="grid grid-cols-2 md:grid-cols-4 gap-2"></div>
                    <div class="flex items-center justify-between pt-4 border-t border-border">
                        <div class="flex items-center gap-2">
                            <button type="button" class="h-9 w-9 rounded-lg hover:bg-accent hover:text-vanixjnk transition flex items-center justify-center" onclick="$('#edit-post-media-upload').click()">
                                <iconify-icon icon="solar:gallery-wide-linear" width="20"></iconify-icon>
                            </button>
                            <input type="file" id="edit-post-media-upload" accept="image/*" class="hidden" multiple>
                        </div>
                        <button type="submit" class="h-9 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium">Lưu thay đổi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php'; ?>