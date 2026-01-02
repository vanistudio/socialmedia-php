<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';

// Username từ route /u/{username}
$username = isset($_GET['username']) ? check_string($_GET['username']) : '';
if (empty($username)) {
    header('Location: /');
    exit;
}

// Lấy user theo username
$profileUser = $Vani->get_row("SELECT * FROM `users` WHERE `username` = '$username'");
if (!$profileUser) {
    // Nếu không tồn tại, trả về home (hoặc có thể làm trang 404 sau)
    header('Location: /');
    exit;
}

// Check own profile
$isOwnProfile = isset($_SESSION['email']) && $_SESSION['email'] === $profileUser['email'];

$userProfile = [
    'fullName' => $profileUser['full_name'] ?? 'User',
    'username' => $profileUser['username'] ?? $username,
    'avatar' => !empty($profileUser['avatar']) ? $profileUser['avatar'] : ('https://i.pravatar.cc/150?u=' . urlencode($username)),
    'cover' => !empty($profileUser['banner']) ? $profileUser['banner'] : 'https://images.unsplash.com/photo-1501854140801-50d01698950b?q=80&w=2400&auto=format&fit=crop',
    'bio' => $profileUser['bio'] ?? '',
    'stats' => [
        'posts' => 0,
        'followers' => 0,
        'following' => 0,
    ],
    'isFollowing' => false,
    'isOwnProfile' => $isOwnProfile,
];

require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php';
?>

<div class="w-full max-w-5xl mx-auto">
    <!-- Cover Image -->
    <div class="h-48 md:h-64 bg-card border border-border rounded-2xl relative bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars($userProfile['cover']); ?>');">
    </div>

    <!-- Profile Header -->
    <div class="-mt-16 md:-mt-20 px-4 sm:px-8">
        <div class="flex flex-col sm:flex-row items-center sm:items-end gap-4">
            <!-- Avatar -->
            <div class="relative">
                <img src="<?php echo htmlspecialchars($userProfile['avatar']); ?>" alt="Avatar" class="h-32 w-32 md:h-40 md:w-40 rounded-full border-4 border-background bg-card object-cover">
                <div class="absolute bottom-2 right-2 h-6 w-6 bg-green-500 rounded-full border-2 border-background" title="Online"></div>
            </div>

            <!-- Info & Actions -->
            <div class="flex-1 flex flex-col sm:flex-row items-center justify-between w-full gap-4">
                <div class="text-center sm:text-left mt-2 sm:mt-0">
                    <h1 class="text-2xl md:text-3xl font-bold text-foreground"><?php echo htmlspecialchars($userProfile['fullName']); ?></h1>
                    <p class="text-sm text-muted-foreground">@<?php echo htmlspecialchars($userProfile['username']); ?></p>
                </div>

                <div class="flex items-center gap-2">
                    <?php if ($userProfile['isOwnProfile']): ?>
                        <a href="/settings" class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium flex items-center gap-2">
                            <iconify-icon icon="solar:settings-linear" width="18"></iconify-icon>
                            <span>Chỉnh sửa</span>
                        </a>
                    <?php else: ?>
                        <button class="h-10 px-4 rounded-lg bg-vanixjnk text-white hover:bg-vanixjnk/90 transition text-sm font-medium flex items-center gap-2">
                            <iconify-icon icon="solar:user-plus-rounded-linear" width="18"></iconify-icon>
                            <span>Theo dõi</span>
                        </button>
                        <button class="h-10 px-4 rounded-lg border border-input bg-card hover:bg-accent transition text-sm font-medium flex items-center gap-2">
                            <iconify-icon icon="solar:chat-round-dots-linear" width="18"></iconify-icon>
                            <span>Nhắn tin</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bio & Stats -->
        <div class="mt-6 space-y-4">
            <p class="text-sm text-muted-foreground max-w-2xl text-center sm:text-left"><?php echo htmlspecialchars($userProfile['bio']); ?></p>
            <div class="flex items-center justify-center sm:justify-start gap-6 text-sm">
                <div class="text-center sm:text-left">
                    <span class="font-bold text-foreground"><?php echo $userProfile['stats']['posts']; ?></span>
                    <span class="text-muted-foreground">bài viết</span>
                </div>
                <div class="text-center sm:text-left">
                    <span class="font-bold text-foreground"><?php echo $userProfile['stats']['followers']; ?></span>
                    <span class="text-muted-foreground">người theo dõi</span>
                </div>
                <div class="text-center sm:text-left">
                    <span class="font-bold text-foreground"><?php echo $userProfile['stats']['following']; ?></span>
                    <span class="text-muted-foreground">đang theo dõi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mt-8 border-b border-border">
        <nav class="-mb-px flex gap-6" aria-label="Tabs">
            <a href="#" class="shrink-0 border-b-2 border-vanixjnk px-1 pb-3 text-sm font-medium text-vanixjnk">
                Bài viết
            </a>
            <a href="#" class="shrink-0 border-b-2 border-transparent px-1 pb-3 text-sm font-medium text-muted-foreground hover:border-border hover:text-foreground">
                Ảnh
            </a>
            <a href="#" class="shrink-0 border-b-2 border-transparent px-1 pb-3 text-sm font-medium text-muted-foreground hover:border-border hover:text-foreground">
                Đã lưu
            </a>
        </nav>
    </div>

    <!-- Tab Content: Posts Grid -->
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Post 1 -->
        <div class="bg-card border border-border rounded-lg">
            <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=2070&auto=format&fit=crop" alt="Post image" class="w-full h-48 object-cover rounded-t-lg">
            <div class="p-4">
                <p class="text-sm text-foreground truncate">Một ngày thật tuyệt vời để học Tailwind CSS và PHP! ☀️</p>
                <p class="text-xs text-muted-foreground mt-1">2 giờ trước</p>
            </div>
        </div>
        <!-- Post 2 -->
        <div class="bg-card border border-border rounded-lg">
            <img src="https://images.unsplash.com/photo-1550745165-9bc0b252726a?q=80&w=2070&auto=format&fit=crop" alt="Post image" class="w-full h-48 object-cover rounded-t-lg">
            <div class="p-4">
                <p class="text-sm text-foreground truncate">Thiết kế giao diện mới với màu vanixjnk.</p>
                <p class="text-xs text-muted-foreground mt-1">Hôm qua</p>
            </div>
        </div>
        <!-- Post 3 -->
        <div class="bg-card border border-border rounded-lg">
            <img src="https://images.unsplash.com/photo-1587620962725-abab7fe55159?q=80&w=2070&auto=format&fit=crop" alt="Post image" class="w-full h-48 object-cover rounded-t-lg">
            <div class="p-4">
                <p class="text-sm text-foreground truncate">Cùng nhau chia sẻ kiến thức lập trình nào!</p>
                <p class="text-xs text-muted-foreground mt-1">3 ngày trước</p>
            </div>
        </div>
    </div>
</div>

<?php 
require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php'; 
?>
