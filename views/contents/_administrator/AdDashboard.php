<?php
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_administrator/AdHeader.php';

// Get statistics
$totalUsers = $Vani->num_rows("SELECT id FROM users") ?: 0;
$totalPosts = $Vani->num_rows("SELECT id FROM posts") ?: 0;
$totalComments = $Vani->num_rows("SELECT id FROM post_comments") ?: 0;
$totalMessages = $Vani->num_rows("SELECT id FROM messages") ?: 0;
$totalReports = $Vani->num_rows("SELECT id FROM reports WHERE status = 'open'") ?: 0;
$totalModerationLogs = $Vani->num_rows("SELECT id FROM content_moderation_logs WHERE review_status IS NULL") ?: 0;

// Get recent users
$recentUsers = $Vani->get_list("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// Get recent posts
$recentPosts = $Vani->get_list("SELECT p.*, u.full_name, u.username, u.avatar FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5");

// Get today's stats
$todayUsers = $Vani->num_rows("SELECT id FROM users WHERE DATE(created_at) = CURDATE()") ?: 0;
$todayPosts = $Vani->num_rows("SELECT id FROM posts WHERE DATE(created_at) = CURDATE()") ?: 0;
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
            <p class="text-sm text-muted-foreground">Tổng quan hệ thống Vani Social</p>
        </div>
        <div class="text-sm text-muted-foreground">
            <?php echo date('d/m/Y H:i'); ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-card border border-border rounded-2xl p-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-blue-500/15 flex items-center justify-center">
                    <iconify-icon icon="solar:users-group-two-rounded-linear" class="text-blue-500" width="20"></iconify-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-foreground"><?php echo number_format($totalUsers); ?></p>
                    <p class="text-xs text-muted-foreground">Users</p>
                </div>
            </div>
        </div>

        <div class="bg-card border border-border rounded-2xl p-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-green-500/15 flex items-center justify-center">
                    <iconify-icon icon="solar:document-text-linear" class="text-green-500" width="20"></iconify-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-foreground"><?php echo number_format($totalPosts); ?></p>
                    <p class="text-xs text-muted-foreground">Posts</p>
                </div>
            </div>
        </div>

        <div class="bg-card border border-border rounded-2xl p-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-purple-500/15 flex items-center justify-center">
                    <iconify-icon icon="solar:chat-dots-linear" class="text-purple-500" width="20"></iconify-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-foreground"><?php echo number_format($totalComments); ?></p>
                    <p class="text-xs text-muted-foreground">Comments</p>
                </div>
            </div>
        </div>

        <div class="bg-card border border-border rounded-2xl p-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-cyan-500/15 flex items-center justify-center">
                    <iconify-icon icon="solar:chat-round-dots-linear" class="text-cyan-500" width="20"></iconify-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-foreground"><?php echo number_format($totalMessages); ?></p>
                    <p class="text-xs text-muted-foreground">Messages</p>
                </div>
            </div>
        </div>

        <div class="bg-card border border-border rounded-2xl p-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-orange-500/15 flex items-center justify-center">
                    <iconify-icon icon="solar:danger-triangle-linear" class="text-orange-500" width="20"></iconify-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-foreground"><?php echo number_format($totalReports); ?></p>
                    <p class="text-xs text-muted-foreground">Reports</p>
                </div>
            </div>
        </div>

        <div class="bg-card border border-border rounded-2xl p-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-red-500/15 flex items-center justify-center">
                    <iconify-icon icon="solar:shield-warning-linear" class="text-red-500" width="20"></iconify-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-foreground"><?php echo number_format($totalModerationLogs); ?></p>
                    <p class="text-xs text-muted-foreground">Pending</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Today Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-card border border-border rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                <iconify-icon icon="solar:calendar-linear" width="20"></iconify-icon>
                Hôm nay
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-background rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-blue-500"><?php echo $todayUsers; ?></p>
                    <p class="text-sm text-muted-foreground">Users mới</p>
                </div>
                <div class="bg-background rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold text-green-500"><?php echo $todayPosts; ?></p>
                    <p class="text-sm text-muted-foreground">Posts mới</p>
                </div>
            </div>
        </div>

        <div class="bg-card border border-border rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                <iconify-icon icon="solar:link-minimalistic-2-linear" width="20"></iconify-icon>
                Quick Actions
            </h3>
            <div class="grid grid-cols-2 gap-2">
                <a href="/admin/moderation" class="flex items-center gap-2 p-3 rounded-xl bg-background hover:bg-accent transition text-sm">
                    <iconify-icon icon="solar:shield-check-linear" width="18" class="text-red-500"></iconify-icon>
                    <span>Moderation</span>
                </a>
                <a href="/admin/blacklist" class="flex items-center gap-2 p-3 rounded-xl bg-background hover:bg-accent transition text-sm">
                    <iconify-icon icon="solar:forbidden-circle-linear" width="18" class="text-orange-500"></iconify-icon>
                    <span>Blacklist</span>
                </a>
                <a href="/admin/reports" class="flex items-center gap-2 p-3 rounded-xl bg-background hover:bg-accent transition text-sm">
                    <iconify-icon icon="solar:danger-triangle-linear" width="18" class="text-yellow-500"></iconify-icon>
                    <span>Reports</span>
                </a>
                <a href="/admin/users" class="flex items-center gap-2 p-3 rounded-xl bg-background hover:bg-accent transition text-sm">
                    <iconify-icon icon="solar:users-group-two-rounded-linear" width="18" class="text-blue-500"></iconify-icon>
                    <span>Users</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-card border border-border rounded-2xl">
            <div class="p-4 border-b border-border flex items-center justify-between">
                <h3 class="font-semibold text-foreground">Users mới nhất</h3>
                <a href="/admin/users" class="text-sm text-vanixjnk hover:underline">Xem tất cả</a>
            </div>
            <div class="divide-y divide-border">
                <?php foreach ($recentUsers as $user): ?>
                <div class="p-4 flex items-center gap-3">
                    <img src="<?php echo htmlspecialchars($user['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-foreground truncate"><?php echo htmlspecialchars($user['full_name']); ?></p>
                        <p class="text-xs text-muted-foreground">@<?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                    <span class="text-xs text-muted-foreground"><?php echo date('d/m', strtotime($user['created_at'])); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Posts -->
        <div class="bg-card border border-border rounded-2xl">
            <div class="p-4 border-b border-border flex items-center justify-between">
                <h3 class="font-semibold text-foreground">Posts mới nhất</h3>
                <a href="/" class="text-sm text-vanixjnk hover:underline">Xem tất cả</a>
            </div>
            <div class="divide-y divide-border">
                <?php foreach ($recentPosts as $post): ?>
                <div class="p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <img src="<?php echo htmlspecialchars($post['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Avatar" class="h-6 w-6 rounded-full object-cover">
                        <span class="text-sm font-medium text-foreground"><?php echo htmlspecialchars($post['full_name']); ?></span>
                        <span class="text-xs text-muted-foreground"><?php echo date('d/m H:i', strtotime($post['created_at'])); ?></span>
                    </div>
                    <p class="text-sm text-muted-foreground line-clamp-2"><?php echo htmlspecialchars(mb_substr($post['content'], 0, 100)); ?>...</p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_administrator/AdFooter.php'; ?>

