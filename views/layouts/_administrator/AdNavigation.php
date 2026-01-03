<?php
$__currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$__currentPath = rtrim($__currentPath, '/') ?: '/';

function isAdminActivePath($path, $currentPath) {
    if ($path === '/admin' && $currentPath === '/admin') {
        return true;
    }
    if ($path !== '/admin' && strpos($currentPath, $path) === 0) {
        return true;
    }
    return false;
}

$__isDashboardActive = $__currentPath === '/admin';
$__isModerationActive = isAdminActivePath('/admin/moderation', $__currentPath);
$__isBlacklistActive = isAdminActivePath('/admin/blacklist', $__currentPath);
$__isReportsActive = isAdminActivePath('/admin/reports', $__currentPath);
$__isUsersActive = isAdminActivePath('/admin/users', $__currentPath);
$__isSettingsActive = isAdminActivePath('/admin/settings', $__currentPath);

// Get pending counts for badges
$pendingReportsCount = $Vani->num_rows("SELECT id FROM reports WHERE status = 'open'") ?: 0;
$pendingModerationCount = $Vani->num_rows("SELECT id FROM content_moderation_logs WHERE review_status IS NULL") ?: 0;
?>

<header class="sticky top-0 z-50 w-full border-b border-border bg-background/80 backdrop-blur">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
        <a href="/admin" class="flex items-center gap-2 font-semibold text-foreground hover:text-red-500 transition-colors">
            <div class="h-8 w-8 rounded-lg bg-red-500/15 flex items-center justify-center">
                <iconify-icon icon="solar:shield-user-linear" class="text-red-500" width="20"></iconify-icon>
            </div>
            <span class="hidden sm:inline">Admin Panel</span>
        </a>

        <!-- Search Box -->
        <div class="hidden md:flex flex-1 max-w-lg mx-8">
            <div class="relative w-full">
                <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="18"></iconify-icon>
                <input type="text" id="admin-search-input" placeholder="Tìm kiếm người dùng, bài viết..." class="w-full h-10 rounded-lg border border-input bg-card px-10 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30 focus-visible:border-red-500/50 hover:border-red-500/30">
                <div id="admin-search-results" class="absolute top-full left-0 right-0 mt-2 bg-card border border-border rounded-xl shadow-lg z-50 hidden max-h-96 overflow-y-auto"></div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="/" class="h-10 px-3 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium flex items-center gap-2" title="Về trang chủ">
                <iconify-icon icon="solar:home-2-linear" width="18"></iconify-icon>
                <span class="hidden sm:inline">Trang chủ</span>
            </a>

            <a href="/u/<?php echo htmlspecialchars($currentUser['username'] ?? ''); ?>" class="h-10 px-3 rounded-full bg-red-500/10 text-red-500 hover:bg-red-500/15 transition border border-input hover:border-red-500/40 flex items-center gap-2" aria-label="Profile">
                <iconify-icon icon="solar:user-circle-linear" class="text-red-500" width="22"></iconify-icon>
                <span class="hidden md:inline text-sm font-medium text-foreground"><?php echo htmlspecialchars($currentUser['full_name'] ?? 'Admin'); ?></span>
            </a>

            <button id="admin-theme-toggle" class="h-10 w-16 rounded-full border border-input bg-card hover:bg-accent transition px-1 flex items-center" aria-label="Toggle theme">
                <div class="w-full flex items-center">
                    <div class="h-8 w-8 rounded-full bg-red-500/15 flex items-center justify-center translate-x-0 transition-transform duration-300">
                        <iconify-icon icon="solar:moon-linear" class="text-red-500" width="18"></iconify-icon>
                    </div>
                </div>
            </button>

            <a href="/logout" class="hidden md:flex h-10 w-10 rounded-lg border border-input bg-card hover:bg-accent transition items-center justify-center" aria-label="Logout" title="Đăng xuất">
                <iconify-icon icon="solar:logout-2-linear" width="20"></iconify-icon>
            </a>
        </div>
    </div>

    <nav class="hidden lg:block border-t border-border bg-background">
        <div class="container mx-auto px-4">
            <ul class="flex items-center gap-2 h-12">
                <li>
                    <a href="/admin" class="px-3 py-2 rounded-md text-sm font-medium <?php echo $__isDashboardActive ? 'text-red-500 bg-red-500/10' : 'text-muted-foreground hover:bg-red-500/10 hover:text-red-500'; ?> transition flex items-center gap-2">
                        <iconify-icon icon="solar:chart-2-linear" width="18"></iconify-icon>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="/admin/moderation" class="px-3 py-2 rounded-md text-sm font-medium <?php echo $__isModerationActive ? 'text-red-500 bg-red-500/10' : 'text-muted-foreground hover:bg-red-500/10 hover:text-red-500'; ?> transition flex items-center gap-2 relative">
                        <iconify-icon icon="solar:shield-check-linear" width="18"></iconify-icon>
                        <span>Moderation</span>
                        <?php if ($pendingModerationCount > 0): ?>
                        <span class="absolute -top-1 -right-1 h-5 min-w-5 px-1 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center"><?php echo $pendingModerationCount > 99 ? '99+' : $pendingModerationCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="/admin/blacklist" class="px-3 py-2 rounded-md text-sm font-medium <?php echo $__isBlacklistActive ? 'text-red-500 bg-red-500/10' : 'text-muted-foreground hover:bg-red-500/10 hover:text-red-500'; ?> transition flex items-center gap-2">
                        <iconify-icon icon="solar:forbidden-circle-linear" width="18"></iconify-icon>
                        <span>Blacklist</span>
                    </a>
                </li>
                <li>
                    <a href="/admin/reports" class="px-3 py-2 rounded-md text-sm font-medium <?php echo $__isReportsActive ? 'text-red-500 bg-red-500/10' : 'text-muted-foreground hover:bg-red-500/10 hover:text-red-500'; ?> transition flex items-center gap-2 relative">
                        <iconify-icon icon="solar:danger-triangle-linear" width="18"></iconify-icon>
                        <span>Reports</span>
                        <?php if ($pendingReportsCount > 0): ?>
                        <span class="absolute -top-1 -right-1 h-5 min-w-5 px-1 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center"><?php echo $pendingReportsCount > 99 ? '99+' : $pendingReportsCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="/admin/users" class="px-3 py-2 rounded-md text-sm font-medium <?php echo $__isUsersActive ? 'text-red-500 bg-red-500/10' : 'text-muted-foreground hover:bg-red-500/10 hover:text-red-500'; ?> transition flex items-center gap-2">
                        <iconify-icon icon="solar:users-group-two-rounded-linear" width="18"></iconify-icon>
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="/admin/settings" class="px-3 py-2 rounded-md text-sm font-medium <?php echo $__isSettingsActive ? 'text-red-500 bg-red-500/10' : 'text-muted-foreground hover:bg-red-500/10 hover:text-red-500'; ?> transition flex items-center gap-2">
                        <iconify-icon icon="solar:settings-linear" width="18"></iconify-icon>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>

<script>
function debounceAdminSearch(callback, delay = 300) {
    clearTimeout(window.adminSearchTimeout);
    window.adminSearchTimeout = setTimeout(callback, delay);
}

function performAdminSearch(query, targetElement) {
    if (query.length < 2) {
        $(targetElement).html('').addClass('hidden');
        return;
    }
    
    $.post('/api/controller/app', { type: 'SEARCH_ALL', query: query, csrf_token: window.CSRF_TOKEN || '' }, function(data) {
        if (data.status === 'success') {
            const users = data.users || [];
            const posts = data.posts || [];
            let html = '';
            
            if (users.length > 0) {
                html += '<div class="p-3 border-b border-border"><h3 class="text-xs font-semibold text-muted-foreground uppercase mb-2">Người dùng</h3><div class="space-y-1">';
                users.forEach(function(user) {
                    html += `
                        <a href="/admin/users?id=${user.id}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-accent transition">
                            <img src="${user.avatar || 'https://placehold.co/200x200/png'}" alt="Avatar" class="h-8 w-8 rounded-full object-cover">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-foreground truncate text-sm">${user.full_name}</p>
                                <p class="text-xs text-muted-foreground">@${user.username}</p>
                            </div>
                        </a>
                    `;
                });
                html += '</div></div>';
            }
            
            if (posts.length > 0) {
                html += '<div class="p-3"><h3 class="text-xs font-semibold text-muted-foreground uppercase mb-2">Bài viết</h3><div class="space-y-1">';
                posts.forEach(function(post) {
                    html += `
                        <a href="/post/${post.id}" class="block p-2 rounded-lg hover:bg-accent transition">
                            <p class="text-sm text-foreground line-clamp-2">${post.content || ''}</p>
                            <p class="text-xs text-muted-foreground mt-1">@${post.username}</p>
                        </a>
                    `;
                });
                html += '</div></div>';
            }
            
            if (html === '') {
                html = '<div class="p-8 text-center"><p class="text-muted-foreground text-sm">Không tìm thấy kết quả</p></div>';
            }
            
            $(targetElement).html(html).removeClass('hidden');
        }
    }, 'json');
}

$(document).ready(function() {
    $('#admin-search-input').on('input', function() {
        const query = $(this).val().trim();
        debounceAdminSearch(function() {
            performAdminSearch(query, '#admin-search-results');
        });
    });
    
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#admin-search-input, #admin-search-results').length) {
            $('#admin-search-results').addClass('hidden');
        }
    });
});
</script>
