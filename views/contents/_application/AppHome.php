<?php 
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php'; 
?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <div class="lg:col-span-3 space-y-6">
        <div class="bg-card border border-border rounded-lg p-4">
            <div class="flex items-start gap-4">
                <div class="h-10 w-10 rounded-full bg-vanixjnk/15 flex items-center justify-center border border-input">
                    <iconify-icon icon="solar:user-circle-linear" class="text-vanixjnk" width="22"></iconify-icon>
                </div>
                <div class="flex-1">
                    <textarea placeholder="Bạn đang nghĩ gì?" class="w-full bg-transparent text-foreground placeholder:text-muted-foreground outline-none resize-none"></textarea>
                    <div class="flex justify-between items-center mt-2 pt-2 border-t border-border">
                        <div class="flex gap-2 text-muted-foreground">
                            <button class="h-8 w-8 rounded-md hover:bg-accent hover:text-vanixjnk transition flex items-center justify-center" aria-label="Add image">
                                <iconify-icon icon="solar:gallery-wide-linear" width="18"></iconify-icon>
                            </button>
                            <button class="h-8 w-8 rounded-md hover:bg-accent hover:text-vanixjnk transition flex items-center justify-center" aria-label="Tag friends">
                                <iconify-icon icon="solar:user-plus-rounded-linear" width="18"></iconify-icon>
                            </button>
                            <button class="h-8 w-8 rounded-md hover:bg-accent hover:text-vanixjnk transition flex items-center justify-center" aria-label="Add location">
                                <iconify-icon icon="solar:map-point-wave-linear" width="18"></iconify-icon>
                            </button>
                        </div>
                        <button class="h-9 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium">Đăng</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-card border border-border rounded-lg">
            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="Avatar" class="h-10 w-10 rounded-full">
                    <div>
                        <p class="font-semibold text-foreground">Jane Doe</p>
                        <p class="text-xs text-muted-foreground">2 giờ trước</p>
                    </div>
                </div>
                <button class="h-8 w-8 rounded-md hover:bg-accent transition flex items-center justify-center text-muted-foreground" aria-label="More options">
                    <iconify-icon icon="solar:menu-dots-bold" width="18"></iconify-icon>
                </button>
            </div>
            <div class="px-4 pb-4">
                <p class="text-foreground">Một ngày thật tuyệt vời để học Tailwind CSS và PHP! ☀️</p>
            </div>
            <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=2070&auto=format&fit=crop" alt="Post image" class="w-full h-auto max-h-[500px] object-cover border-y border-border">
            <div class="p-2 flex justify-around">
                <button class="flex-1 flex items-center justify-center gap-2 py-2 rounded-md hover:bg-accent transition text-muted-foreground hover:text-vanixjnk">
                    <iconify-icon icon="solar:heart-linear" width="20"></iconify-icon>
                    <span class="text-sm font-medium">Thích</span>
                </button>
                <button class="flex-1 flex items-center justify-center gap-2 py-2 rounded-md hover:bg-accent transition text-muted-foreground hover:text-vanixjnk">
                    <iconify-icon icon="solar:chat-dots-linear" width="20"></iconify-icon>
                    <span class="text-sm font-medium">Bình luận</span>
                </button>
                <button class="flex-1 flex items-center justify-center gap-2 py-2 rounded-md hover:bg-accent transition text-muted-foreground hover:text-vanixjnk">
                    <iconify-icon icon="solar:share-linear" width="20"></iconify-icon>
                    <span class="text-sm font-medium">Chia sẻ</span>
                </button>
            </div>
        </div>
    </div>
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-card border border-border rounded-lg p-4">
            <h3 class="font-semibold text-foreground mb-4">Gợi ý cho bạn</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/150?u=a042581f4e29026704e" alt="Avatar" class="h-10 w-10 rounded-full">
                        <div>
                            <p class="font-semibold text-foreground text-sm">John Smith</p>
                            <p class="text-xs text-muted-foreground">Gợi ý cho bạn</p>
                        </div>
                    </div>
                    <button class="h-8 px-3 rounded-lg bg-vanixjnk/15 text-vanixjnk hover:bg-vanixjnk/25 transition text-xs font-bold">Kết bạn</button>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/150?u=a042581f4e29026704f" alt="Avatar" class="h-10 w-10 rounded-full">
                        <div>
                            <p class="font-semibold text-foreground text-sm">Emily White</p>
                            <p class="text-xs text-muted-foreground">Gợi ý cho bạn</p>
                        </div>
                    </div>
                    <button class="h-8 px-3 rounded-lg bg-vanixjnk/15 text-vanixjnk hover:bg-vanixjnk/25 transition text-xs font-bold">Kết bạn</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php'; 
?>