<header class="sticky top-0 z-50 w-full border-b border-border bg-background/80 backdrop-blur">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
        <a href="/" class="flex items-center gap-2 font-semibold text-foreground hover:text-vanixjnk transition-colors">
            <div class="h-8 w-8 rounded-lg bg-vanixjnk/15 flex items-center justify-center">
                <iconify-icon icon="solar:chat-round-like-linear" class="text-vanixjnk" width="20"></iconify-icon>
            </div>
            <span class="hidden sm:inline">Vani Social</span>
        </a>
        <div class="hidden md:flex flex-1 max-w-lg mx-8">
            <div class="relative w-full">
                <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" width="18"></iconify-icon>
                <input type="text" placeholder="Tìm kiếm người dùng, bài viết..." class="w-full h-10 rounded-lg border border-input bg-card px-10 text-sm outline-none focus:ring-2 focus:ring-vanixjnk/30 focus:border-vanixjnk/40 transition">
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button class="md:hidden h-10 w-10 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center justify-center" aria-label="Search">
                <iconify-icon icon="solar:magnifer-linear" width="20"></iconify-icon>
            </button>
            <button class="h-10 px-3 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium flex items-center gap-2">
                <iconify-icon icon="solar:add-circle-linear" width="18"></iconify-icon>
                <span class="hidden sm:inline">Đăng bài</span>
            </button>
            <button class="relative h-10 w-10 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center justify-center" aria-label="Notifications">
                <iconify-icon icon="solar:bell-linear" width="20"></iconify-icon>
                <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-vanixjnk text-white text-[10px] flex items-center justify-center">3</span>
            </button>
            <button class="relative h-10 w-10 rounded-lg border border-input bg-card hover:bg-accent transition flex items-center justify-center" aria-label="Messages">
                <iconify-icon icon="solar:chat-round-dots-linear" width="20"></iconify-icon>
                <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-vanixjnk text-white text-[10px] flex items-center justify-center">1</span>
            </button>
            <button id="theme-toggle" class="h-10 w-14 rounded-full border border-input bg-card hover:bg-accent transition px-1 flex items-center" aria-label="Toggle theme">
                <div class="w-full flex items-center">
                    <div class="h-8 w-8 rounded-full bg-vanixjnk/15 flex items-center justify-center translate-x-0 transition-transform duration-300">
                        <iconify-icon icon="solar:moon-linear" class="text-vanixjnk" width="18"></iconify-icon>
                    </div>
                </div>
            </button>
            <button class="h-10 w-10 rounded-full bg-vanixjnk/15 flex items-center justify-center border border-input hover:border-vanixjnk/40 transition" aria-label="Profile">
                <iconify-icon icon="solar:user-circle-linear" class="text-vanixjnk" width="22"></iconify-icon>
            </button>
        </div>
    </div>
    <nav class="hidden lg:block border-t border-border bg-background">
        <div class="container mx-auto px-4">
            <ul class="flex items-center gap-2 h-12">
                <li><a href="/" class="px-3 py-2 rounded-md text-sm font-medium text-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Trang chủ</a></li>
                <li><a href="/explore" class="px-3 py-2 rounded-md text-sm font-medium text-muted-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Khám phá</a></li>
                <li><a href="/notifications" class="px-3 py-2 rounded-md text-sm font-medium text-muted-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Thông báo</a></li>
                <li><a href="/messages" class="px-3 py-2 rounded-md text-sm font-medium text-muted-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Tin nhắn</a></li>
                <li><a href="/profile" class="px-3 py-2 rounded-md text-sm font-medium text-muted-foreground hover:bg-vanixjnk/10 hover:text-vanixjnk transition">Trang cá nhân</a></li>
            </ul>
        </div>
    </nav>
</header>
