<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';

$isLoggedIn = isset($_SESSION['email']) && !empty($_SESSION['email']);
$currentUser = null;
$currentUserId = 0;
if ($isLoggedIn) {
    $currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '" . addslashes($_SESSION['email']) . "'");
    $currentUserId = intval($currentUser['id'] ?? 0);
}

$visibilityFilter = '';
$blockFilter = '';
if ($currentUserId > 0) {
    $visibilityFilter = "AND (
        p.visibility = 'public' 
        OR p.user_id = '$currentUserId'
        OR (p.visibility = 'followers' AND EXISTS (
            SELECT 1 FROM follows WHERE follower_id = '$currentUserId' AND following_id = p.user_id
        ))
    )";
    $blockFilter = "AND NOT EXISTS (
        SELECT 1 FROM user_blocks ub 
        WHERE (ub.blocker_id = '$currentUserId' AND ub.blocked_id = p.user_id) 
           OR (ub.blocker_id = p.user_id AND ub.blocked_id = '$currentUserId')
    )";
} else {
    $visibilityFilter = "AND p.visibility = 'public'";
}

$trendingPosts = $Vani->get_list("
    SELECT 
        p.*, 
        u.full_name, u.username, u.avatar,
        (SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id) as like_count,
        (SELECT COUNT(*) FROM `post_comments` WHERE `post_id` = p.id) as comment_count,
        " . ($currentUserId > 0 ? "(SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id AND `user_id` = '$currentUserId') as has_liked," : "0 as has_liked,") . "
        " . ($currentUserId > 0 ? "(SELECT COUNT(*) FROM `post_bookmarks` WHERE `post_id` = p.id AND `user_id` = '$currentUserId') as has_saved" : "0 as has_saved") . "
    FROM `posts` p 
    JOIN `users` u ON p.user_id = u.id
    WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) $visibilityFilter $blockFilter
    ORDER BY like_count DESC, comment_count DESC
    LIMIT 20
");

$userBlockFilter = '';
if ($currentUserId > 0) {
    $userBlockFilter = "AND NOT EXISTS (
        SELECT 1 FROM user_blocks ub 
        WHERE (ub.blocker_id = '$currentUserId' AND ub.blocked_id = u.id) 
           OR (ub.blocker_id = u.id AND ub.blocked_id = '$currentUserId')
    )";
}

$popularUsers = $Vani->get_list("
    SELECT 
        u.*,
        (SELECT COUNT(*) FROM `follows` WHERE `following_id` = u.id) as followers_count,
        (SELECT COUNT(*) FROM `posts` WHERE `user_id` = u.id) as posts_count
    FROM `users` u
    WHERE u.id != " . ($currentUserId > 0 ? $currentUserId : 0) . " $userBlockFilter
    ORDER BY followers_count DESC, posts_count DESC
    LIMIT 20
");

require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php';
?>

<div class="w-full max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-foreground">Khám phá</h1>
        <p class="text-sm text-muted-foreground">Khám phá những bài viết và người dùng phổ biến</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-foreground mb-4">Bài viết nổi bật</h2>
                <?php if (empty($trendingPosts)): ?>
                    <div class="text-center py-12 bg-card border border-border rounded-2xl">
                        <p class="text-muted-foreground">Chưa có bài viết nào</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($trendingPosts as $post): ?>
                            <?php 
                            $post['user_id'] = $post['user_id'] ?? 0;
                            include $_SERVER['DOCUMENT_ROOT'] . '/views/components/_post_card.php'; 
                            ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-card border border-border rounded-2xl p-4">
                <h2 class="text-lg font-semibold text-foreground mb-4">Người dùng phổ biến</h2>
                <?php if (empty($popularUsers)): ?>
                    <p class="text-sm text-muted-foreground text-center py-8">Chưa có người dùng nào</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($popularUsers as $user): ?>
                            <a href="/u/<?php echo htmlspecialchars($user['username']); ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-accent transition">
                                <img src="<?php echo htmlspecialchars(!empty($user['avatar']) ? $user['avatar'] : 'https://placehold.co/200x200/png'); ?>" alt="Avatar" class="h-12 w-12 rounded-full object-cover">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-foreground truncate"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                    <p class="text-xs text-muted-foreground">@<?php echo htmlspecialchars($user['username']); ?></p>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        <?php echo $user['followers_count']; ?> người theo dõi &middot; <?php echo $user['posts_count']; ?> bài viết
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const currentUserId = <?php echo $currentUserId; ?>;
let editPostDialog = null;
let editPostMediaUrls = [];
let reportDialog = null;

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

function openReportDialog(type, id) {
    $('#report-target-type').val(type);
    $('#report-target-id').val(id);
    if (reportDialog) {
        reportDialog.open();
    } else {
        $('#report-dialog').removeClass('hidden').addClass('flex');
    }
}

function closeReportDialog() {
    if (reportDialog) {
        reportDialog.close();
    } else {
        $('#report-dialog').addClass('hidden').removeClass('flex');
    }
}

$(document).ready(function() {
    if (window.initDialog) {
        editPostDialog = window.initDialog('edit-post-dialog');
        reportDialog = window.initDialog('report-dialog');
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

    $(document).on('click', '[data-action]', function() {
        const action = $(this).data('action');
        const postId = $(this).data('post-id');
        const $self = $(this);

        if (!currentUserId && ['toggle-like','save-post','report-post','edit-post','delete-post'].includes(action)) {
            toast.error('Vui lòng đăng nhập để thực hiện');
            return;
        }

        switch (action) {
            case 'edit-post':
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
                openReportDialog('post', postId);
                break;
        }
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

<!-- Report Dialog -->
<div id="report-dialog" class="dialog hidden fixed inset-0 z-50 flex items-center justify-center p-4" data-dialog data-state="closed">
    <div class="absolute inset-0 bg-background/80 backdrop-blur-sm" data-dialog-backdrop></div>
    <div class="relative w-full max-w-md mx-auto" data-dialog-content>
        <div class="bg-card border border-border rounded-2xl shadow-lg" data-dialog-inner>
            <div class="p-4 border-b border-border flex items-center justify-between">
                <h3 class="text-lg font-semibold text-foreground">Báo cáo</h3>
                <button type="button" onclick="closeReportDialog()" class="h-8 w-8 rounded-lg hover:bg-accent transition flex items-center justify-center" data-dialog-close>
                    <iconify-icon icon="solar:close-circle-linear" width="20"></iconify-icon>
                </button>
            </div>
            <form id="report-form" class="p-4 space-y-4">
                <input type="hidden" id="report-target-type" name="target_type" value="">
                <input type="hidden" id="report-target-id" name="target_id" value="">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-foreground">Lý do báo cáo</label>
                    <select id="report-reason" name="reason" class="w-full h-10 rounded-lg border border-input bg-background px-3 text-sm">
                        <option value="Spam">Spam</option>
                        <option value="Harassment">Quấy rối</option>
                        <option value="Inappropriate">Nội dung không phù hợp</option>
                        <option value="Violence">Bạo lực</option>
                        <option value="Other">Khác</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-foreground">Chi tiết (tùy chọn)</label>
                    <textarea id="report-detail" name="detail" rows="3" placeholder="Mô tả thêm về vấn đề..." class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"></textarea>
                </div>
                <button type="submit" id="btn-submit-report" class="w-full h-10 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium">Gửi báo cáo</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php'; ?>

