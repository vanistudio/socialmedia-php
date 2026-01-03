<?php
$__isLoggedIn = isset($_SESSION['email']) && !empty($_SESSION['email']);
$__currentEmail = $__isLoggedIn ? $_SESSION['email'] : null;
$__currentUser = null;

if ($__isLoggedIn && isset($Vani) && $Vani) {
    $__currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '" . addslashes($__currentEmail) . "'");
}

$__displayName = $__currentUser['full_name'] ?? 'User';
$__username = $__currentUser['username'] ?? '';
?>

<header class="sticky top-0 z-50 w-full border-b border-border bg-background/80 backdrop-blur">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
        <a href="/" class="flex items-center gap-2 font-semibold text-foreground hover:text-vanixjnk transition-colors">
            <div class="h-8 w-8 rounded-lg bg-vanixjnk/15 flex items-center justify-center">
                <iconify-icon icon="solar:chat-round-like-linear" class="text-vanixjnk" width="20"></iconify-icon>
            </div>
            <span class="hidden sm:inline">Vanix Social</span>
        </a>
        <div class="hidden md:flex flex-1 max-w-lg mx-8">
            <div class="relative w-full">
                <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="18"></iconify-icon>
                <input type="text" id="global-search-input" placeholder="Tìm kiếm người dùng, bài viết..." class="w-full h-10 rounded-lg border border-input bg-card px-10 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30">
                <div id="search-results" class="absolute top-full left-0 right-0 mt-2 bg-card border border-border rounded-xl shadow-lg z-50 hidden max-h-96 overflow-y-auto"></div>
            </div>
        </div>
        <div class="flex items-center gap-2">

            <button type="button" id="open-search-dialog-btn" class="md:hidden h-10 w-10 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center justify-center" aria-label="Search">
                <iconify-icon icon="solar:magnifer-linear" width="20"></iconify-icon>
            </button>

            <?php if ($__isLoggedIn): ?>
                <button type="button" data-action="open-create-post-dialog" class="h-10 px-3 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="solar:add-circle-linear" width="18"></iconify-icon>
                    <span class="hidden sm:inline">Đăng bài</span>
                </button>

                <a href="/notifications" class="relative h-10 w-10 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center justify-center" aria-label="Notifications">
                    <iconify-icon icon="solar:bell-linear" width="20"></iconify-icon>
                    <span id="notifications-badge" class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-vanixjnk text-white text-[10px] flex items-center justify-center hidden">0</span>
                </a>

                <a href="/messages" class="relative h-10 w-10 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center justify-center" aria-label="Messages">
                    <iconify-icon icon="solar:chat-round-dots-linear" width="20"></iconify-icon>
                    <span id="unread-messages-badge" class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-vanixjnk text-white text-[10px] flex items-center justify-center hidden">0</span>
                </a>

                <a href="/settings" class="hidden sm:flex h-10 px-3 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium items-center gap-2" aria-label="Settings">
                    <iconify-icon icon="solar:settings-linear" width="18"></iconify-icon>
                    <span>Cài đặt</span>
                </a>

                <a href="<?php echo $__username ? '/u/' . htmlspecialchars($__username) : '/settings'; ?>" class="h-10 px-3 rounded-full bg-vanixjnk/10 text-vanixjnk hover:bg-vanixjnk/15 transition border border-input hover:border-vanixjnk/40 flex items-center gap-2" aria-label="Profile">
                    <iconify-icon icon="solar:user-circle-linear" class="text-vanixjnk" width="22"></iconify-icon>
                    <span class="hidden md:inline text-sm font-medium text-foreground"><?php echo htmlspecialchars($__displayName); ?></span>
                </a>

                <a href="/logout" class="hidden md:flex h-10 w-10 rounded-lg border border-input bg-card hover:bg-accent transition items-center justify-center" aria-label="Logout" title="Đăng xuất">
                    <iconify-icon icon="solar:logout-2-linear" width="20"></iconify-icon>
                </a>

            <?php else: ?>
                <a href="/login" class="h-10 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="solar:login-3-linear" width="18"></iconify-icon>
                    <span>Đăng nhập</span>
                </a>
                <a href="/register" class="hidden sm:flex h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium items-center gap-2">
                    <iconify-icon icon="solar:user-plus-rounded-linear" width="18"></iconify-icon>
                    <span>Đăng ký</span>
                </a>
            <?php endif; ?>

            <button id="theme-toggle" class="h-10 w-16 rounded-full border border-input bg-card hover:bg-accent transition px-1 flex items-center" aria-label="Toggle theme">
                <div class="w-full flex items-center">
                    <div class="h-8 w-8 rounded-full bg-vanixjnk/15 flex items-center justify-center translate-x-0 transition-transform duration-300">
                        <iconify-icon icon="solar:moon-linear" class="text-vanixjnk" width="18"></iconify-icon>
                    </div>
                </div>
            </button>

        </div>
    </div>

    <nav class="hidden lg:block border-t border-border bg-background">
        <div class="container mx-auto px-4">
            <ul class="flex items-center gap-2 h-12">
                <li><a href="/" class="px-3 py-2 rounded-md text-sm font-medium text-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Trang chủ</a></li>
                <li><a href="/explore" class="px-3 py-2 rounded-md text-sm font-medium text-muted-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Khám phá</a></li>

                <?php if ($__isLoggedIn): ?>
                    <li><a href="/notifications" class="px-3 py-2 rounded-md text-sm font-medium text-muted-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Thông báo</a></li>
                    <li><a href="/messages" class="px-3 py-2 rounded-md text-sm font-medium text-muted-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Tin nhắn</a></li>
                    <li><a href="/settings" class="px-3 py-2 rounded-md text-sm font-medium text-muted-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Cài đặt</a></li>
                    <li><a href="<?php echo $__username ? '/u/' . htmlspecialchars($__username) : '/settings'; ?>" class="px-3 py-2 rounded-md text-sm font-medium text-muted-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Trang cá nhân</a></li>
                <?php else: ?>
                    <li><a href="/login" class="px-3 py-2 rounded-md text-sm font-medium text-muted-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Đăng nhập</a></li>
                    <li><a href="/register" class="px-3 py-2 rounded-md text-sm font-medium text-muted-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>

<!-- Search Dialog (Mobile) -->
<div id="search-dialog" class="dialog hidden fixed inset-0 z-50 flex items-start justify-center p-4 pt-20" data-state="closed">
    <div class="dialog-overlay fixed inset-0 bg-background/80 backdrop-blur-sm" onclick="closeSearchDialog()"></div>
    <div class="dialog-content relative bg-card border border-border rounded-2xl shadow-lg w-full max-w-lg max-h-[80vh] overflow-hidden">
        <div class="p-4 border-b border-border flex items-center gap-3">
            <div class="relative flex-1">
                <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="18"></iconify-icon>
                <input type="text" id="mobile-search-input" placeholder="Tìm kiếm người dùng, bài viết..." class="w-full h-10 rounded-lg border border-input bg-background px-10 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30">
            </div>
            <button type="button" onclick="closeSearchDialog()" class="h-10 w-10 rounded-lg border border-input bg-background hover:bg-accent transition flex items-center justify-center">
                <iconify-icon icon="solar:close-circle-linear" width="20"></iconify-icon>
            </button>
        </div>
        <div id="mobile-search-results" class="p-4 overflow-y-auto max-h-[calc(80vh-80px)]"></div>
    </div>
</div>

<script>
let searchTimeout;
function debounceSearch(callback, delay = 300) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(callback, delay);
}

function performSearch(query, targetElement) {
    if (query.length < 2) {
        $(targetElement).html('').addClass('hidden');
        return;
    }
    
    $.post('/api/controller/app', { type: 'SEARCH_ALL', query: query }, function(data) {
        if (data.status === 'success') {
            const users = data.users || [];
            const posts = data.posts || [];
            let html = '';
            
            if (users.length > 0) {
                html += '<div class="mb-4"><h3 class="text-sm font-semibold text-foreground mb-2">Người dùng</h3><div class="space-y-2">';
                users.forEach(function(user) {
                    html += `
                        <a href="/u/${user.username}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-accent transition">
                            <img src="${user.avatar || 'https://placehold.co/200x200/png'}" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-foreground truncate">${user.full_name}</p>
                                <p class="text-xs text-muted-foreground">@${user.username}</p>
                            </div>
                        </a>
                    `;
                });
                html += '</div></div>';
            }
            
            if (posts.length > 0) {
                html += '<div><h3 class="text-sm font-semibold text-foreground mb-2">Bài viết</h3><div class="space-y-2">';
                posts.forEach(function(post) {
                    html += `
                        <a href="/post/${post.id}" class="block p-3 rounded-lg border border-border hover:bg-accent transition">
                            <p class="text-sm text-foreground line-clamp-2">${post.content || ''}</p>
                            <p class="text-xs text-muted-foreground mt-1">@${post.username} &middot; ${post.like_count} thích &middot; ${post.comment_count} bình luận</p>
                        </a>
                    `;
                });
                html += '</div></div>';
            }
            
            if (html === '') {
                html = '<div class="text-center py-8"><p class="text-muted-foreground">Không tìm thấy kết quả</p></div>';
            }
            
            $(targetElement).html(html).removeClass('hidden');
        }
    }, 'json').fail(function() {
        $(targetElement).html('<div class="text-center py-8"><p class="text-red-500">Không thể kết nối tới máy chủ</p></div>').removeClass('hidden');
    });
}

$(document).ready(function() {
    // Desktop search
    $('#global-search-input').on('input', function() {
        const query = $(this).val().trim();
        debounceSearch(function() {
            performSearch(query, '#search-results');
        });
    });
    
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#global-search-input, #search-results').length) {
            $('#search-results').addClass('hidden');
        }
    });
    
    // Mobile search dialog
    $('#open-search-dialog-btn').on('click', function() {
        openSearchDialog();
    });
    
    $('#mobile-search-input').on('input', function() {
        const query = $(this).val().trim();
        debounceSearch(function() {
            performSearch(query, '#mobile-search-results');
        });
    });
});

function openSearchDialog() {
    const dialog = document.getElementById('search-dialog');
    if (dialog) {
        dialog.classList.remove('hidden');
        dialog.setAttribute('data-state', 'open');
        setTimeout(() => {
            document.getElementById('mobile-search-input')?.focus();
        }, 100);
    }
}

function closeSearchDialog() {
    const dialog = document.getElementById('search-dialog');
    if (dialog) {
        dialog.setAttribute('data-state', 'closed');
        setTimeout(() => {
            dialog.classList.add('hidden');
            $('#mobile-search-input').val('');
            $('#mobile-search-results').html('').addClass('hidden');
        }, 200);
    }
}
</script>
