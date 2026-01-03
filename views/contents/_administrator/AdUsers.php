<?php
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_administrator/AdHeader.php';

$page = intval($_GET['page'] ?? 1);
$search = $_GET['search'] ?? '';
$level = $_GET['level'] ?? '';
$limit = 20;
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
$totalPages = ceil($total / $limit);

// Check if viewing specific user
$viewUserId = intval($_GET['id'] ?? 0);
$viewUser = null;
if ($viewUserId > 0) {
    $viewUser = $Vani->get_row("
        SELECT *,
            (SELECT COUNT(*) FROM posts WHERE user_id = users.id) as posts_count,
            (SELECT COUNT(*) FROM follows WHERE follower_id = users.id) as following_count,
            (SELECT COUNT(*) FROM follows WHERE following_id = users.id) as followers_count
        FROM users WHERE id = $viewUserId
    ");
}

$levelOptions = [
    ['value' => 'member', 'label' => 'Member'],
    ['value' => 'admin', 'label' => 'Admin'],
    ['value' => 'administrator', 'label' => 'Administrator'],
];
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Users</h1>
            <p class="text-sm text-muted-foreground">Quản lý người dùng hệ thống</p>
        </div>
        <div class="text-sm text-muted-foreground">
            Tổng: <?php echo number_format($Vani->num_rows("SELECT id FROM users") ?: 0); ?> users
        </div>
    </div>

    <?php if ($viewUser): ?>
    <!-- User Detail View -->
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="h-32 bg-gradient-to-r from-red-500/20 to-purple-500/20 relative">
            <?php if (!empty($viewUser['banner'])): ?>
            <img src="<?php echo htmlspecialchars($viewUser['banner']); ?>" alt="Banner" class="w-full h-full object-cover">
            <?php endif; ?>
            <a href="/admin/users" class="absolute top-4 left-4 h-9 px-3 rounded-lg bg-black/50 backdrop-blur text-white hover:bg-black/70 transition flex items-center gap-2 text-sm">
                <iconify-icon icon="solar:arrow-left-linear" width="18"></iconify-icon>
                <span class="hidden sm:inline">Quay lại</span>
            </a>
        </div>
        <div class="px-4 sm:px-6 pb-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-end gap-4 -mt-12">
                <img src="<?php echo htmlspecialchars($viewUser['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-24 w-24 rounded-full border-4 border-card object-cover shrink-0">
                <div class="flex-1 text-center sm:text-left">
                    <h2 class="text-xl font-bold text-foreground"><?php echo htmlspecialchars($viewUser['full_name']); ?></h2>
                    <p class="text-muted-foreground">@<?php echo htmlspecialchars($viewUser['username']); ?></p>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <span class="px-3 py-1 rounded-lg text-sm font-medium <?php echo $viewUser['level'] === 'admin' || $viewUser['level'] === 'administrator' ? 'bg-red-500/15 text-red-500' : 'bg-blue-500/15 text-blue-500'; ?>">
                        <?php echo ucfirst($viewUser['level']); ?>
                    </span>
                    <a href="/u/<?php echo htmlspecialchars($viewUser['username']); ?>" target="_blank" class="h-9 px-3 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center gap-2 text-sm">
                        <iconify-icon icon="solar:eye-linear" width="16"></iconify-icon>
                        <span class="hidden sm:inline">Xem Profile</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-6">
                <div class="bg-background rounded-xl p-3 sm:p-4 text-center">
                    <p class="text-xl sm:text-2xl font-bold text-foreground"><?php echo number_format($viewUser['posts_count']); ?></p>
                    <p class="text-xs text-muted-foreground">Posts</p>
                </div>
                <div class="bg-background rounded-xl p-3 sm:p-4 text-center">
                    <p class="text-xl sm:text-2xl font-bold text-foreground"><?php echo number_format($viewUser['followers_count']); ?></p>
                    <p class="text-xs text-muted-foreground">Followers</p>
                </div>
                <div class="bg-background rounded-xl p-3 sm:p-4 text-center">
                    <p class="text-xl sm:text-2xl font-bold text-foreground"><?php echo number_format($viewUser['following_count']); ?></p>
                    <p class="text-xs text-muted-foreground">Following</p>
                </div>
                <div class="bg-background rounded-xl p-3 sm:p-4 text-center">
                    <p class="text-sm font-medium text-foreground"><?php echo date('d/m/Y', strtotime($viewUser['created_at'])); ?></p>
                    <p class="text-xs text-muted-foreground">Joined</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-muted-foreground">Email</p>
                        <p class="text-sm text-foreground break-all"><?php echo htmlspecialchars($viewUser['email']); ?></p>
                    </div>
                    <?php if (!empty($viewUser['location'])): ?>
                    <div>
                        <p class="text-xs text-muted-foreground">Location</p>
                        <p class="text-sm text-foreground"><?php echo htmlspecialchars($viewUser['location']); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($viewUser['website'])): ?>
                    <div>
                        <p class="text-xs text-muted-foreground">Website</p>
                        <a href="<?php echo htmlspecialchars($viewUser['website']); ?>" target="_blank" class="text-sm text-red-500 hover:underline break-all"><?php echo htmlspecialchars($viewUser['website']); ?></a>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($viewUser['birthday'])): ?>
                    <div>
                        <p class="text-xs text-muted-foreground">Birthday</p>
                        <p class="text-sm text-foreground"><?php echo date('d/m/Y', strtotime($viewUser['birthday'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if (!empty($viewUser['bio'])): ?>
                    <div>
                        <p class="text-xs text-muted-foreground mb-1">Bio</p>
                        <p class="text-sm text-foreground"><?php echo nl2br(htmlspecialchars($viewUser['bio'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 pt-6 border-t border-border space-y-4">
                <!-- Change Level -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="custom-select-container relative flex-1 sm:max-w-xs" id="level-select-container">
                        <input type="hidden" id="change-level-<?php echo $viewUser['id']; ?>" name="level" value="<?php echo htmlspecialchars($viewUser['level']); ?>">
                        <button type="button" class="custom-select-trigger w-full flex items-center justify-between bg-background border border-input hover:border-red-500/50 rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all cursor-pointer">
                            <span class="selected-text truncate mr-2"><?php echo ucfirst($viewUser['level']); ?></span>
                            <iconify-icon icon="solar:alt-arrow-down-linear" class="chevron-icon text-xs text-muted-foreground transition-transform duration-200" width="14"></iconify-icon>
                        </button>
                        <div class="custom-select-content absolute w-full mt-1 bg-popover border border-border rounded-xl shadow-xl z-50 overflow-hidden bg-card hidden" data-state="closed">
                            <div class="max-h-[200px] overflow-y-auto scrollbar-thin p-1">
                                <?php foreach ($levelOptions as $opt): ?>
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2.5 pl-3 pr-8 text-sm outline-none hover:bg-red-500/10 hover:text-red-500 data-[state=checked]:font-bold data-[state=checked]:text-red-500 transition-colors" data-value="<?php echo $opt['value']; ?>" data-label="<?php echo $opt['label']; ?>" data-state="<?php echo $viewUser['level'] === $opt['value'] ? 'checked' : 'unchecked'; ?>">
                                    <span class="truncate"><?php echo $opt['label']; ?></span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center check-icon transition-opacity duration-150 <?php echo $viewUser['level'] === $opt['value'] ? 'opacity-100' : 'opacity-0'; ?>">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="changeUserLevel(<?php echo $viewUser['id']; ?>)" class="h-11 px-4 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition text-sm font-medium shrink-0">
                        Cập nhật Level
                    </button>
                </div>

                <!-- Other Actions -->
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="resetUserPassword(<?php echo $viewUser['id']; ?>)" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium flex items-center gap-2">
                        <iconify-icon icon="solar:key-linear" width="16"></iconify-icon>
                        <span>Reset Password</span>
                    </button>
                    <button type="button" onclick="deleteUser(<?php echo $viewUser['id']; ?>)" class="h-10 px-4 rounded-lg bg-red-500 text-white hover:bg-red-600 transition text-sm font-medium flex items-center gap-2">
                        <iconify-icon icon="solar:trash-bin-trash-linear" width="16"></iconify-icon>
                        <span>Xóa User</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Search & Filter -->
    <div class="bg-card border border-border rounded-2xl p-4">
        <form method="GET" action="/admin/users" class="flex flex-col gap-3">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="18"></iconify-icon>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Tìm kiếm username, email, tên..." class="w-full h-11 pl-10 pr-4 rounded-xl border border-input bg-background text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30">
                </div>
                <div class="flex gap-2">
                    <!-- Custom Select for Level Filter -->
                    <div class="custom-select-container relative flex-1 sm:w-40" id="filter-level-container">
                        <input type="hidden" name="level" id="filter-level" value="<?php echo htmlspecialchars($level); ?>">
                        <button type="button" class="custom-select-trigger w-full flex items-center justify-between bg-background border border-input hover:border-red-500/50 rounded-xl px-4 h-11 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all cursor-pointer">
                            <span class="selected-text truncate mr-2"><?php echo $level ? ucfirst($level) : 'Tất cả level'; ?></span>
                            <iconify-icon icon="solar:alt-arrow-down-linear" class="chevron-icon text-xs text-muted-foreground transition-transform duration-200" width="14"></iconify-icon>
                        </button>
                        <div class="custom-select-content absolute w-full mt-1 bg-popover border border-border rounded-xl shadow-xl z-50 overflow-hidden bg-card hidden" data-state="closed">
                            <div class="max-h-[200px] overflow-y-auto scrollbar-thin p-1">
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2.5 pl-3 pr-8 text-sm outline-none hover:bg-red-500/10 hover:text-red-500 data-[state=checked]:font-bold data-[state=checked]:text-red-500 transition-colors" data-value="" data-label="Tất cả level" data-state="<?php echo empty($level) ? 'checked' : 'unchecked'; ?>">
                                    <span class="truncate">Tất cả level</span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center check-icon transition-opacity duration-150 <?php echo empty($level) ? 'opacity-100' : 'opacity-0'; ?>">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <?php foreach ($levelOptions as $opt): ?>
                                <div class="custom-select-item relative flex w-full cursor-pointer select-none items-center rounded-lg py-2.5 pl-3 pr-8 text-sm outline-none hover:bg-red-500/10 hover:text-red-500 data-[state=checked]:font-bold data-[state=checked]:text-red-500 transition-colors" data-value="<?php echo $opt['value']; ?>" data-label="<?php echo $opt['label']; ?>" data-state="<?php echo $level === $opt['value'] ? 'checked' : 'unchecked'; ?>">
                                    <span class="truncate"><?php echo $opt['label']; ?></span>
                                    <span class="absolute right-3 flex h-3.5 w-3.5 items-center justify-center check-icon transition-opacity duration-150 <?php echo $level === $opt['value'] ? 'opacity-100' : 'opacity-0'; ?>">
                                        <iconify-icon icon="solar:check-circle-bold" class="text-xs" width="14"></iconify-icon>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="h-11 px-4 rounded-xl bg-red-500 text-white hover:bg-red-600 transition text-sm font-medium flex items-center gap-2 shrink-0">
                        <iconify-icon icon="solar:magnifer-linear" width="16"></iconify-icon>
                        <span class="hidden sm:inline">Tìm kiếm</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Users List -->
    <?php if (empty($users)): ?>
    <div class="bg-card border border-border rounded-2xl p-12 text-center">
        <iconify-icon icon="solar:users-group-two-rounded-linear" width="48" class="text-muted-foreground mx-auto mb-4"></iconify-icon>
        <p class="text-muted-foreground">Không tìm thấy user nào</p>
    </div>
    <?php else: ?>
    
    <!-- Mobile Cards View -->
    <div class="lg:hidden space-y-3">
        <?php foreach ($users as $user): ?>
        <div class="bg-card border border-border rounded-2xl p-4">
            <div class="flex items-start gap-3">
                <img src="<?php echo htmlspecialchars($user['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-12 w-12 rounded-full object-cover shrink-0">
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-medium text-foreground truncate"><?php echo htmlspecialchars($user['full_name']); ?></p>
                            <p class="text-xs text-muted-foreground">@<?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                        <span class="px-2 py-0.5 rounded-lg text-xs font-medium shrink-0 <?php echo $user['level'] === 'admin' || $user['level'] === 'administrator' ? 'bg-red-500/15 text-red-500' : 'bg-blue-500/15 text-blue-500'; ?>">
                            <?php echo ucfirst($user['level']); ?>
                        </span>
                    </div>
                    <p class="text-xs text-muted-foreground mt-1 truncate"><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="flex items-center gap-3 mt-2 text-xs text-muted-foreground">
                        <span><?php echo $user['posts_count']; ?> posts</span>
                        <span><?php echo $user['followers_count']; ?> followers</span>
                        <span><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-3 pt-3 border-t border-border">
                <a href="/admin/users?id=<?php echo $user['id']; ?>" class="flex-1 h-9 rounded-lg bg-background hover:bg-accent transition flex items-center justify-center gap-2 text-sm">
                    <iconify-icon icon="solar:eye-linear" width="16"></iconify-icon>
                    <span>Chi tiết</span>
                </a>
                <a href="/u/<?php echo htmlspecialchars($user['username']); ?>" target="_blank" class="flex-1 h-9 rounded-lg bg-background hover:bg-accent transition flex items-center justify-center gap-2 text-sm">
                    <iconify-icon icon="solar:user-circle-linear" width="16"></iconify-icon>
                    <span>Profile</span>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Desktop Table View -->
    <div class="hidden lg:block bg-card border border-border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-background border-b border-border">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Level</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Stats</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Joined</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-muted-foreground uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-accent/50 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="<?php echo htmlspecialchars($user['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                                <div>
                                    <p class="font-medium text-foreground"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                    <p class="text-xs text-muted-foreground">@<?php echo htmlspecialchars($user['username']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-muted-foreground"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-lg text-xs font-medium <?php echo $user['level'] === 'admin' || $user['level'] === 'administrator' ? 'bg-red-500/15 text-red-500' : 'bg-blue-500/15 text-blue-500'; ?>">
                                <?php echo ucfirst($user['level']); ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-muted-foreground">
                            <span title="Posts"><?php echo $user['posts_count']; ?> posts</span> ·
                            <span title="Followers"><?php echo $user['followers_count']; ?> followers</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-muted-foreground"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="/admin/users?id=<?php echo $user['id']; ?>" class="h-8 w-8 rounded-lg hover:bg-accent transition flex items-center justify-center text-muted-foreground" title="Xem chi tiết">
                                    <iconify-icon icon="solar:eye-linear" width="18"></iconify-icon>
                                </a>
                                <a href="/u/<?php echo htmlspecialchars($user['username']); ?>" target="_blank" class="h-8 w-8 rounded-lg hover:bg-accent transition flex items-center justify-center text-muted-foreground" title="Xem profile">
                                    <iconify-icon icon="solar:user-circle-linear" width="18"></iconify-icon>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-2">
        <?php if ($page > 1): ?>
        <a href="/admin/users?search=<?php echo urlencode($search); ?>&level=<?php echo urlencode($level); ?>&page=<?php echo $page - 1; ?>" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium">Trước</a>
        <?php endif; ?>
        <span class="text-sm text-muted-foreground">Trang <?php echo $page; ?> / <?php echo $totalPages; ?></span>
        <?php if ($page < $totalPages): ?>
        <a href="/admin/users?search=<?php echo urlencode($search); ?>&level=<?php echo urlencode($level); ?>&page=<?php echo $page + 1; ?>" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium">Sau</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>

function changeUserLevel(userId) {
    const level = $(`#change-level-${userId}`).val();
    if (!confirm(`Đổi level thành "${level}"?`)) return;

    $.post('/api/controller/admin', {
        type: 'ADMIN_CHANGE_USER_LEVEL',
        user_id: userId,
        level: level,
        csrf_token: window.CSRF_TOKEN || ''
    }, function(data) {
        if (data.status === 'success') {
            toast.success('Đã cập nhật level');
            setTimeout(() => window.location.reload(), 500);
        } else {
            toast.error(data.message || 'Có lỗi xảy ra');
        }
    }, 'json').fail(function() {
        toast.error('Không thể kết nối tới máy chủ');
    });
}

function resetUserPassword(userId) {
    const newPassword = prompt('Nhập mật khẩu mới (ít nhất 6 ký tự):');
    if (!newPassword) return;
    if (newPassword.length < 6) {
        toast.error('Mật khẩu phải có ít nhất 6 ký tự');
        return;
    }

    $.post('/api/controller/admin', {
        type: 'ADMIN_RESET_PASSWORD',
        user_id: userId,
        new_password: newPassword,
        csrf_token: window.CSRF_TOKEN || ''
    }, function(data) {
        if (data.status === 'success') {
            toast.success('Đã reset mật khẩu');
        } else {
            toast.error(data.message || 'Có lỗi xảy ra');
        }
    }, 'json').fail(function() {
        toast.error('Không thể kết nối tới máy chủ');
    });
}

function deleteUser(userId) {
    if (!confirm('XÁC NHẬN XÓA USER?\n\nHành động này sẽ xóa vĩnh viễn user và tất cả dữ liệu liên quan. Không thể hoàn tác.')) {
        return;
    }

    $.post('/api/controller/admin', {
        type: 'ADMIN_DELETE_USER',
        user_id: userId,
        csrf_token: window.CSRF_TOKEN || ''
    }, function(data) {
        if (data.status === 'success') {
            toast.success('Đã xóa user');
            setTimeout(() => window.location.href = '/admin/users', 500);
        } else {
            toast.error(data.message || 'Có lỗi xảy ra');
        }
    }, 'json').fail(function() {
        toast.error('Không thể kết nối tới máy chủ');
    });
}

$(document).ready(function() {
    // Re-init custom selects for dynamically loaded content
    if (window.initCustomSelects) {
        // Reset initialized state for admin custom selects
        document.querySelectorAll('.custom-select-container').forEach(el => {
            el.dataset.initialized = 'false';
        });
        window.initCustomSelects();
    }
});
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_administrator/AdFooter.php'; ?>
