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
                        <button class="h-10 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium flex items-center gap-2">
                            <iconify-icon icon="solar:user-plus-rounded-linear" width="18"></iconify-icon><span>Theo dõi</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="mt-6 space-y-4">
            <p class="text-sm text-muted-foreground max-w-2xl text-center sm:text-left"><?php echo htmlspecialchars($profileUser['bio']); ?></p>
            <div class="flex items-center justify-center sm:justify-start gap-6 text-sm">
                <div class="text-center sm:text-left"><span class="font-bold text-foreground"><?php echo $stats['posts']; ?></span><span class="text-muted-foreground"> bài viết</span></div>
                <div class="text-center sm:text-left"><span class="font-bold text-foreground"><?php echo $stats['followers']; ?></span><span class="text-muted-foreground"> người theo dõi</span></div>
                <div class="text-center sm:text-left"><span class="font-bold text-foreground"><?php echo $stats['following']; ?></span><span class="text-muted-foreground"> đang theo dõi</span></div>
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
                        <?php include __DIR__ . '/../components/_post_card.php';  ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<script>
$(document).ready(function() {
    const currentUserId = <?php echo $currentUserId; ?>;
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