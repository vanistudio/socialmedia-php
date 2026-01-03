<nav class="lg:hidden fixed bottom-4 left-1/2 -translate-x-1/2 z-50">
    <div class="bg-background/90 backdrop-blur border border-border shadow-lg rounded-2xl px-2 py-2">
        <ul class="flex items-center gap-1">
            <li>
                <a href="/" class="group flex flex-col items-center justify-center w-14 h-12 rounded-xl text-muted-foreground hover:text-vanixjnk hover:bg-vanixjnk/10 transition">
                    <iconify-icon icon="solar:home-2-linear" width="22"></iconify-icon>
                    <span class="text-[10px] font-medium mt-0.5">Home</span>
                </a>
            </li>
            <li>
                <a href="/explore" class="group flex flex-col items-center justify-center w-14 h-12 rounded-xl text-muted-foreground hover:text-vanixjnk hover:bg-vanixjnk/10 transition">
                    <iconify-icon icon="solar:compass-linear" width="22"></iconify-icon>
                    <span class="text-[10px] font-medium mt-0.5">Explore</span>
                </a>
            </li>
            <li>
                <button type="button" data-action="open-create-post-dialog" class="flex flex-col items-center justify-center w-14 h-12 rounded-xl bg-vanixjnk text-white hover:bg-vanixjnk/90 transition shadow-sm" aria-label="Create post">
                    <iconify-icon icon="solar:add-circle-linear" width="24"></iconify-icon>
                    <span class="text-[10px] font-semibold mt-0.5">Post</span>
                </button>
            </li>
            <li>
                <a href="/settings" class="group flex flex-col items-center justify-center w-14 h-12 rounded-xl text-muted-foreground hover:text-vanixjnk hover:bg-vanixjnk/10 transition">
                    <iconify-icon icon="solar:settings-linear" width="22"></iconify-icon>
                    <span class="text-[10px] font-medium mt-0.5">Settings</span>
                </a>
            </li>
            <li>
                <a href="<?php echo isset($Vani) && isset($_SESSION['email']) ? '/u/' . htmlspecialchars($Vani->user_list('username')) : '/settings'; ?>" class="group flex flex-col items-center justify-center w-14 h-12 rounded-xl text-muted-foreground hover:text-vanixjnk hover:bg-vanixjnk/10 transition">
                    <iconify-icon icon="solar:user-circle-linear" width="22"></iconify-icon>
                    <span class="text-[10px] font-medium mt-0.5">Me</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
<div class="lg:hidden h-20"></div>