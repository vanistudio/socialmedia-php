<header class="sticky top-0 z-50 w-full border-b border-border bg-background/80 backdrop-blur">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">

        <!-- Left: Logo -->
        <a href="/" class="flex items-center gap-2 font-semibold text-foreground hover:text-vanixjnk transition-colors">
            <div class="h-8 w-8 rounded-lg bg-vanixjnk/15 flex items-center justify-center">
                <iconify-icon icon="solar:chat-round-like-linear" class="text-vanixjnk" width="20"></iconify-icon>
            </div>
            <span class="hidden sm:inline">Vanix Social</span>
        </a>

        <!-- Right: Theme toggle + Back -->
        <div class="flex items-center gap-2">
            <a href="/" class="h-10 px-3 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium flex items-center gap-2">
                <iconify-icon icon="solar:arrow-left-linear" width="18"></iconify-icon>
                <span class="hidden sm:inline">Trang chủ</span>
            </a>

            <button id="theme-toggle" class="h-10 w-14 rounded-full border border-input bg-card hover:bg-accent transition px-1 flex items-center" aria-label="Toggle theme">
                <div class="w-full flex items-center">
                    <div class="h-8 w-8 rounded-full bg-vanixjnk/15 flex items-center justify-center translate-x-0 transition-transform duration-300">
                        <iconify-icon icon="solar:moon-linear" class="text-vanixjnk" width="18"></iconify-icon>
                    </div>
                </div>
            </button>
        </div>
    </div>
</header>
