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
                <input type="text" placeholder="Tìm kiếm người dùng, bài viết..." class="w-full h-10 rounded-lg border border-input bg-card px-10 text-sm shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-vanixjnk/30 focus-visible:border-vanixjnk/50 hover:border-vanixjnk/30">
            </div>
        </div>
        <div class="flex items-center gap-2">

            <button class="md:hidden h-10 w-10 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center justify-center" aria-label="Search">
                <iconify-icon icon="solar:magnifer-linear" width="20"></iconify-icon>
            </button>

            <?php if ($__isLoggedIn): ?>
                <button type="button" data-action="open-create-post-dialog" class="h-10 px-3 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="solar:add-circle-linear" width="18"></iconify-icon>
                    <span class="hidden sm:inline">Đăng bài</span>
                </button>

                <a href="/notifications" class="relative h-10 w-10 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center justify-center" aria-label="Notifications">
                    <iconify-icon icon="solar:bell-linear" width="20"></iconify-icon>
                    <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-vanixjnk text-white text-[10px] flex items-center justify-center">3</span>
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
