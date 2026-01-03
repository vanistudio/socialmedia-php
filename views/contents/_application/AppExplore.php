<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';

$isLoggedIn = isset($_SESSION['email']) && !empty($_SESSION['email']);
$currentUser = null;
$currentUserId = 0;
if ($isLoggedIn) {
    $currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '" . addslashes($_SESSION['email']) . "'");
    $currentUserId = intval($currentUser['id'] ?? 0);
}

// Trending posts (most liked in last 7 days)
$trendingPosts = $Vani->get_list("
    SELECT 
        p.*, 
        u.full_name, u.username, u.avatar,
        (SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id) as like_count,
        (SELECT COUNT(*) FROM `post_comments` WHERE `post_id` = p.id) as comment_count,
        " . ($currentUserId > 0 ? "(SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id AND `user_id` = '$currentUserId') as has_liked," : "0 as has_liked,") . "
        " . ($currentUserId > 0 ? "(SELECT COUNT(*) FROM `post_bookmarks` WHERE `post_id` = p.id AND `user_id` = '$currentUserId') as has_saved" : "0 as has_saved") . "
    FROM `posts` p 
    JOIN `users` u ON p.user_id = u.id
    WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY like_count DESC, comment_count DESC
    LIMIT 20
");

// Popular users (most followers)
$popularUsers = $Vani->get_list("
    SELECT 
        u.*,
        (SELECT COUNT(*) FROM `follows` WHERE `following_id` = u.id) as followers_count,
        (SELECT COUNT(*) FROM `posts` WHERE `user_id` = u.id) as posts_count
    FROM `users` u
    WHERE u.id != " . ($currentUserId > 0 ? $currentUserId : 0) . "
    ORDER BY followers_count DESC, posts_count DESC
    LIMIT 20
");

require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppHeader.php';
?>

<div class="w-full max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-foreground">Khám phá</h1>
        <p class="text-sm text-muted-foreground">Khám phá những bài viết và người dùng phổ biến</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-foreground mb-4">Bài viết nổi bật</h2>
                <?php if (empty($trendingPosts)): ?>
                    <div class="text-center py-12 bg-card border border-border rounded-2xl">
                        <p class="text-muted-foreground">Chưa có bài viết nào</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($trendingPosts as $post): ?>
                            <?php 
                            $post['user_id'] = $post['user_id'] ?? 0;
                            include $_SERVER['DOCUMENT_ROOT'] . '/views/components/_post_card.php'; 
                            ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-card border border-border rounded-2xl p-4">
                <h2 class="text-lg font-semibold text-foreground mb-4">Người dùng phổ biến</h2>
                <?php if (empty($popularUsers)): ?>
                    <p class="text-sm text-muted-foreground text-center py-8">Chưa có người dùng nào</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($popularUsers as $user): ?>
                            <a href="/u/<?php echo htmlspecialchars($user['username']); ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-accent transition">
                                <img src="<?php echo htmlspecialchars(!empty($user['avatar']) ? $user['avatar'] : 'https://placehold.co/200x200/png'); ?>" alt="Avatar" class="h-12 w-12 rounded-full object-cover">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-foreground truncate"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                    <p class="text-xs text-muted-foreground">@<?php echo htmlspecialchars($user['username']); ?></p>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        <?php echo $user['followers_count']; ?> người theo dõi &middot; <?php echo $user['posts_count']; ?> bài viết
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/layouts/_application/AppFooter.php'; ?>

