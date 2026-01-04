<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php';

$siteTitle = $Vani->get_row("SELECT value FROM settings WHERE `key` = 'siteTitle'");
$siteTitle = $siteTitle['value'] ?? 'Vani Social';

$siteTagline = $Vani->get_row("SELECT value FROM settings WHERE `key` = 'siteTagline'");
$siteTagline = $siteTagline['value'] ?? 'Connect with the world';

$contactEmail = $Vani->get_row("SELECT value FROM settings WHERE `key` = 'contactEmail'");
$contactEmail = $contactEmail['value'] ?? 'contact@vanixsocial.com';

$totalUsers = $Vani->num_rows("SELECT id FROM users") ?: 0;
$totalPosts = $Vani->num_rows("SELECT id FROM posts") ?: 0;
?>

<div class="w-full max-w-4xl mx-auto">
    <div class="bg-card border border-border rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-vanixjnk/20 via-purple-500/20 to-blue-500/20 p-12 text-center">
            <div class="h-20 w-20 mx-auto mb-6 rounded-2xl bg-vanixjnk/15 flex items-center justify-center">
                <iconify-icon icon="solar:chat-round-like-linear" class="text-vanixjnk" width="40"></iconify-icon>
            </div>
            <h1 class="text-4xl font-bold text-foreground mb-3"><?php echo htmlspecialchars($siteTitle); ?></h1>
            <p class="text-xl text-muted-foreground"><?php echo htmlspecialchars($siteTagline); ?></p>
        </div>

        <div class="p-8">
            <div class="max-w-2xl mx-auto text-center">
                <p class="text-lg text-muted-foreground leading-relaxed">
                    <?php echo htmlspecialchars($siteTitle); ?> là nền tảng mạng xã hội kết nối mọi người, 
                    nơi bạn có thể chia sẻ khoảnh khắc, kết nối bạn bè và khám phá nội dung thú vị từ cộng đồng.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-card border border-border rounded-2xl p-6 text-center">
            <div class="h-12 w-12 mx-auto mb-3 rounded-xl bg-blue-500/15 flex items-center justify-center">
                <iconify-icon icon="solar:users-group-two-rounded-linear" class="text-blue-500" width="24"></iconify-icon>
            </div>
            <p class="text-3xl font-bold text-foreground"><?php echo number_format($totalUsers); ?></p>
            <p class="text-sm text-muted-foreground">Người dùng</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-6 text-center">
            <div class="h-12 w-12 mx-auto mb-3 rounded-xl bg-green-500/15 flex items-center justify-center">
                <iconify-icon icon="solar:document-text-linear" class="text-green-500" width="24"></iconify-icon>
            </div>
            <p class="text-3xl font-bold text-foreground"><?php echo number_format($totalPosts); ?></p>
            <p class="text-sm text-muted-foreground">Bài viết</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-6 text-center">
            <div class="h-12 w-12 mx-auto mb-3 rounded-xl bg-purple-500/15 flex items-center justify-center">
                <iconify-icon icon="solar:shield-check-linear" class="text-purple-500" width="24"></iconify-icon>
            </div>
            <p class="text-3xl font-bold text-foreground">100%</p>
            <p class="text-sm text-muted-foreground">An toàn</p>
        </div>
        <div class="bg-card border border-border rounded-2xl p-6 text-center">
            <div class="h-12 w-12 mx-auto mb-3 rounded-xl bg-orange-500/15 flex items-center justify-center">
                <iconify-icon icon="solar:heart-linear" class="text-orange-500" width="24"></iconify-icon>
            </div>
            <p class="text-3xl font-bold text-foreground">24/7</p>
            <p class="text-sm text-muted-foreground">Hỗ trợ</p>
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl shadow-sm p-8 mb-6">
        <h2 class="text-2xl font-bold text-foreground text-center mb-8">Tính năng nổi bật</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="h-14 w-14 mx-auto mb-4 rounded-xl bg-vanixjnk/15 flex items-center justify-center">
                    <iconify-icon icon="solar:posts-carousel-horizontal-linear" class="text-vanixjnk" width="28"></iconify-icon>
                </div>
                <h3 class="font-semibold text-foreground mb-2">Chia sẻ bài viết</h3>
                <p class="text-sm text-muted-foreground">Đăng tải ảnh, video và suy nghĩ của bạn với bạn bè và cộng đồng.</p>
            </div>
            <div class="text-center">
                <div class="h-14 w-14 mx-auto mb-4 rounded-xl bg-vanixjnk/15 flex items-center justify-center">
                    <iconify-icon icon="solar:chat-round-dots-linear" class="text-vanixjnk" width="28"></iconify-icon>
                </div>
                <h3 class="font-semibold text-foreground mb-2">Nhắn tin trực tiếp</h3>
                <p class="text-sm text-muted-foreground">Kết nối và trò chuyện riêng tư với bạn bè theo thời gian thực.</p>
            </div>
            <div class="text-center">
                <div class="h-14 w-14 mx-auto mb-4 rounded-xl bg-vanixjnk/15 flex items-center justify-center">
                    <iconify-icon icon="solar:compass-linear" class="text-vanixjnk" width="28"></iconify-icon>
                </div>
                <h3 class="font-semibold text-foreground mb-2">Khám phá</h3>
                <p class="text-sm text-muted-foreground">Tìm kiếm và khám phá nội dung thú vị từ cộng đồng.</p>
            </div>
        </div>
    </div>

    <div class="bg-card border border-border rounded-2xl shadow-sm p-8 text-center">
        <h2 class="text-2xl font-bold text-foreground mb-4">Liên hệ với chúng tôi</h2>
        <p class="text-muted-foreground mb-6">Có câu hỏi hoặc góp ý? Chúng tôi luôn sẵn sàng lắng nghe.</p>
        <a href="mailto:<?php echo htmlspecialchars($contactEmail); ?>" class="inline-flex items-center gap-2 h-10 px-6 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition font-medium">
            <iconify-icon icon="solar:letter-linear" width="18"></iconify-icon>
            <span><?php echo htmlspecialchars($contactEmail); ?></span>
        </a>
    </div>

    <div class="mt-6 flex items-center justify-center gap-6 text-sm text-muted-foreground">
        <a href="/terms" class="hover:text-vanixjnk transition">Điều khoản sử dụng</a>
        <span>·</span>
        <a href="/privacy" class="hover:text-vanixjnk transition">Quyền riêng tư</a>
        <span>·</span>
        <a href="/" class="hover:text-vanixjnk transition">Trang chủ</a>
    </div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php'; ?>

