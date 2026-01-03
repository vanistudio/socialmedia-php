<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$database = $_ENV['DB_DATABASE'];

try {
    $pdo = new PDO(
        "mysql:host=$host",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE `$database`");

    // USERS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `full_name` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `username` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `email` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `password` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `level` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `session` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `avatar` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `banner` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `bio` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `location` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `website` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `birthday` DATE NULL DEFAULT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `settings` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `key` VARCHAR(191) NOT NULL,
            `value` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `type` VARCHAR(50) NULL DEFAULT 'string',
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `settings_key_unique` (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `follows` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `follower_id` INT NOT NULL,
            `following_id` INT NOT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_follow` (`follower_id`, `following_id`),
            KEY `idx_follower` (`follower_id`),
            KEY `idx_following` (`following_id`),
            CONSTRAINT `fk_follows_follower` FOREIGN KEY (`follower_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_follows_following` FOREIGN KEY (`following_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `posts` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `user_id` INT NOT NULL,
            `content` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `visibility` VARCHAR(20) NULL DEFAULT 'public',
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_posts_user` (`user_id`),
            KEY `idx_posts_created` (`created_at`),
            CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `post_media` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `post_id` BIGINT NOT NULL,
            `media_url` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `media_type` VARCHAR(20) NOT NULL DEFAULT 'image',
            `thumbnail_url` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_media_post` (`post_id`),
            KEY `idx_media_post_sort` (`post_id`, `sort_order`),
            CONSTRAINT `fk_media_post` FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `post_likes` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `post_id` BIGINT NOT NULL,
            `user_id` INT NOT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_like` (`post_id`, `user_id`),
            KEY `idx_likes_post` (`post_id`),
            KEY `idx_likes_user` (`user_id`),
            CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `post_bookmarks` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `post_id` BIGINT NOT NULL,
            `user_id` INT NOT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_bookmark` (`post_id`, `user_id`),
            KEY `idx_bookmarks_user` (`user_id`),
            KEY `idx_bookmarks_post` (`post_id`),
            CONSTRAINT `fk_bookmarks_post` FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_bookmarks_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `post_comments` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `post_id` BIGINT NOT NULL,
            `user_id` INT NOT NULL,
            `parent_id` BIGINT NULL DEFAULT NULL,
            `content` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_comments_post` (`post_id`),
            KEY `idx_comments_user` (`user_id`),
            KEY `idx_comments_parent` (`parent_id`),
            CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `post_comments`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `comment_likes` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `comment_id` BIGINT NOT NULL,
            `user_id` INT NOT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_comment_like` (`comment_id`, `user_id`),
            KEY `idx_comment_likes_comment` (`comment_id`),
            KEY `idx_comment_likes_user` (`user_id`),
            CONSTRAINT `fk_comment_likes_comment` FOREIGN KEY (`comment_id`) REFERENCES `post_comments`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_comment_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `notifications` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `user_id` INT NOT NULL,
            `actor_id` INT NULL DEFAULT NULL,
            `type` VARCHAR(50) NOT NULL,
            `entity_type` VARCHAR(50) NULL DEFAULT NULL,
            `entity_id` BIGINT NULL DEFAULT NULL,
            `is_read` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_notif_user_read` (`user_id`, `is_read`),
            KEY `idx_notif_user_time` (`user_id`, `created_at`),
            CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_notif_actor` FOREIGN KEY (`actor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_blocks` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `blocker_id` INT NOT NULL,
            `blocked_id` INT NOT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_block` (`blocker_id`, `blocked_id`),
            KEY `idx_blocker` (`blocker_id`),
            KEY `idx_blocked` (`blocked_id`),
            CONSTRAINT `fk_blocks_blocker` FOREIGN KEY (`blocker_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_blocks_blocked` FOREIGN KEY (`blocked_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `reports` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `reporter_id` INT NOT NULL,
            `target_type` VARCHAR(20) NOT NULL,
            `target_id` BIGINT NOT NULL,
            `reason` VARCHAR(100) NOT NULL,
            `detail` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'open',
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_reports_status` (`status`),
            KEY `idx_reports_reporter` (`reporter_id`),
            CONSTRAINT `fk_reports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `conversations` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(20) NOT NULL DEFAULT 'direct',
            `title` VARCHAR(255) NULL DEFAULT NULL,
            `created_by` INT NULL DEFAULT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_conv_type` (`type`),
            CONSTRAINT `fk_conv_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `conversation_members` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `conversation_id` BIGINT NOT NULL,
            `user_id` INT NOT NULL,
            `role` VARCHAR(20) NOT NULL DEFAULT 'member',
            `joined_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_conv_member` (`conversation_id`, `user_id`),
            KEY `idx_member_user` (`user_id`),
            KEY `idx_member_conv` (`conversation_id`),
            CONSTRAINT `fk_member_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_member_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `messages` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `conversation_id` BIGINT NOT NULL,
            `sender_id` INT NOT NULL,
            `content` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `media_url` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_msg_conv_time` (`conversation_id`, `created_at`),
            KEY `idx_msg_sender` (`sender_id`),
            CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `message_reads` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `message_id` BIGINT NOT NULL,
            `user_id` INT NOT NULL,
            `read_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_msg_read` (`message_id`, `user_id`),
            KEY `idx_reads_user` (`user_id`),
            CONSTRAINT `fk_reads_message` FOREIGN KEY (`message_id`) REFERENCES `messages`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_reads_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `blacklist_keywords` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `keyword` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_keyword` (`keyword`),
            KEY `idx_active` (`active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `content_moderation_logs` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `user_id` INT NOT NULL,
            `content_type` VARCHAR(50) NOT NULL,
            `content` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `related_id` BIGINT NULL DEFAULT NULL,
            `violations` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `blacklist_keywords` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `scores` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `source` VARCHAR(50) NULL DEFAULT NULL,
            `action` VARCHAR(50) NOT NULL DEFAULT 'blocked',
            `reviewed_by` INT NULL DEFAULT NULL,
            `review_status` VARCHAR(50) NULL DEFAULT NULL,
            `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user` (`user_id`),
            KEY `idx_content_type` (`content_type`),
            KEY `idx_related_id` (`related_id`),
            KEY `idx_action` (`action`),
            KEY `idx_review_status` (`review_status`),
            KEY `idx_created_at` (`created_at`),
            CONSTRAINT `fk_mod_log_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_mod_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $defaultSettings = [
        ['key' => 'siteTitle', 'value' => 'Vani Social', 'type' => 'string'],
        ['key' => 'siteDescription', 'value' => 'Mạng xã hội hiện đại với giao diện shadcn/radix, Tailwind CSS và màu chủ đạo vanixjnk.', 'type' => 'string'],
        ['key' => 'siteColor', 'value' => '#c176ff', 'type' => 'color'],
        ['key' => 'siteTheme', 'value' => 'light', 'type' => 'string'],
        ['key' => 'siteLanguage', 'value' => 'vi', 'type' => 'string'],
        ['key' => 'siteLogo', 'value' => '', 'type' => 'string'],
        ['key' => 'siteFavicon', 'value' => '', 'type' => 'string'],
    ];

    $stmt = $pdo->prepare("INSERT INTO `settings` (`key`, `value`, `type`) VALUES (:key, :value, :type)
        ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `type` = VALUES(`type`)");

    foreach ($defaultSettings as $setting) {
        $stmt->execute([
            ':key' => $setting['key'],
            ':value' => $setting['value'],
            ':type' => $setting['type'],
        ]);
    }

    $defaultBlacklistKeywords = [
        // Từ ngữ tục tĩu, thô tục
        'địt', 'đụ', 'đéo', 'đcm', 'đkm', 'vl', 'vcl', 'clgt', 'dm', 'đmm', 'đkm', 'đcmn',
        'lồn', 'buồi', 'cặc', 'dái', 'đít', 'lol', 'lz', 'lz', 'lồn', 'buồi', 'cặc',
        'đụ má', 'đụ mẹ', 'đụ cha', 'đụ bố', 'đụ con', 'đụ chó', 'đụ mẹ mày',
        'địt mẹ', 'địt cha', 'địt bố', 'địt con', 'địt chó',
        'đcm', 'đkm', 'đcmn', 'đkmd', 'đkmdm', 'đkmđm',
        'vl', 'vcl', 'vkl', 'vcc', 'vcl', 'vlxx',
        'clgt', 'clmm', 'clm', 'cl', 'clmđ', 'clmmđ',
        'dm', 'đmm', 'đm', 'đmmđ', 'đmmd', 'đmmđm',
        'fuck', 'fck', 'f*ck', 'f**k', 'f***',
        'shit', 'sh*t', 's**t', 's***',
        'bitch', 'b*tch', 'b**ch', 'b***h',
        'ass', 'a**', 'a***', 'asshole', 'a**hole',
        
        // Xúc phạm, chửi bới
        'chết tiệt', 'đồ khốn', 'đồ ngu', 'thằng ngu', 'con ngu', 'đồ ngu xuẩn',
        'đồ chó', 'đồ súc vật', 'đồ khốn nạn', 'đồ đê tiện', 'đồ hèn',
        'thằng chó', 'con chó', 'đồ chó má', 'đồ chó đẻ',
        'đồ điên', 'đồ khùng', 'đồ dại', 'đồ ngu dốt',
        'mẹ mày', 'cha mày', 'bố mày', 'ông mày', 'bà mày',
        'đồ khốn kiếp', 'đồ súc sinh', 'đồ thú vật',
        'ngu si', 'ngu dốt', 'ngu xuẩn', 'ngu ngốc',
        'đồ lừa đảo', 'đồ lừa', 'đồ lừa bịp',
        
        // Bạo lực, đe dọa
        'giết', 'chém', 'đánh chết', 'giết người', 'giết chết',
        'đánh chết', 'đập chết', 'bắn chết', 'chém chết',
        'giết mẹ', 'giết cha', 'giết bố', 'giết con',
        'chém chết', 'đánh chết', 'bắn chết', 'đập chết',
        'đánh vỡ đầu', 'đập vỡ đầu', 'chém đầu', 'cắt đầu',
        'bom', 'bom nổ', 'khủng bố', 'đánh bom', 'nổ bom',
        'đặt bom', 'phát nổ', 'nổ tung', 'nổ chết',
        'đánh nhau', 'đánh lộn', 'đánh đập', 'đánh đấm',
        'đâm', 'đâm chết', 'đâm vào', 'đâm người',
        'cắt cổ', 'cắt họng', 'cắt đầu', 'chặt đầu',
        'tự tử', 'tự sát', 'nhảy lầu', 'nhảy cầu',
        'đe dọa', 'đe dọa giết', 'đe dọa chém', 'đe dọa đánh',
        
        // Ma túy, chất cấm
        'ma túy', 'heroin', 'cần sa', 'cocain', 'cocaine',
        'thuốc lắc', 'thuốc phiện', 'ma túy đá', 'cần sa',
        'cần', 'cỏ', 'cỏ dại', 'cần sa', 'cần cỏ',
        'hàng', 'hàng trắng', 'hàng đá', 'hàng cỏ',
        'đá', 'đá cục', 'đá viên', 'ma túy đá',
        'thuốc', 'thuốc lắc', 'thuốc phiện', 'thuốc lá cần sa',
        'hút', 'hút chích', 'chích', 'tiêm chích',
        'buôn bán ma túy', 'mua bán ma túy', 'sử dụng ma túy',
        
        // Nội dung tình dục, khiêu dâm
        'sex', 's*x', 's**', 's***',
        'porn', 'p*rn', 'p**n', 'p***n',
        'xxx', 'x*x*x', 'x**x', 'x***x',
        'phim sex', 'phim khiêu dâm', 'phim người lớn',
        'ảnh sex', 'ảnh khiêu dâm', 'ảnh người lớn',
        'video sex', 'video khiêu dâm', 'video người lớn',
        'webcam', 'web cam', 'cam sex', 'cam khiêu dâm',
        'gái gọi', 'gái điếm', 'gái làng chơi',
        'mại dâm', 'mua dâm', 'bán dâm',
        'trai bao', 'gái bao', 'bao nuôi',
        
        // Từ lóng, biến thể
        'đm', 'đmm', 'đkm', 'đcm', 'đcmn', 'đkmd',
        'vl', 'vcl', 'vkl', 'vcc', 'vlxx', 'vlz',
        'clgt', 'clmm', 'clm', 'cl', 'clmđ', 'clmmđ',
        'dm', 'đmm', 'đm', 'đmmđ', 'đmmd', 'đmmđm',
        'đjt', 'đjt mẹ', 'đjt cha', 'đjt bố',
        'đụt', 'đụt mẹ', 'đụt cha', 'đụt bố',
        'địch', 'địch mẹ', 'địch cha', 'địch bố',
        'đếch', 'đếch mẹ', 'đếch cha', 'đếch bố',
        'đếch mẹ', 'đếch cha', 'đếch bố', 'đếch con',
        'đếch chó', 'đếch mẹ mày', 'đếch cha mày',
        
        // Spam, lừa đảo
        'lừa đảo', 'lừa bịp', 'lừa gạt', 'lừa dối',
        'lừa tiền', 'lừa tiền bạc', 'lừa tài sản',
        'scam', 'sc*m', 's**m', 's***m',
        'lừa', 'bịp', 'gạt', 'dối', 'lừa dối',
        'đa cấp', 'bán hàng đa cấp', 'kinh doanh đa cấp',
        'lừa đảo online', 'lừa đảo mạng', 'lừa đảo internet',
        
        // Từ ngữ phân biệt, kỳ thị
        'đồng tính', 'gay', 'les', 'lesbian', 'lgbt',
        'kỳ thị', 'phân biệt', 'phân biệt đối xử',
        'da đen', 'da trắng', 'da vàng', 'da đỏ',
        'mọi', 'mọi rợ', 'man di', 'man rợ',
        
        // Từ ngữ khác
        'cờ bạc', 'đánh bạc', 'cá độ', 'cờ bạc online',
        'rượu bia', 'say xỉn', 'nghiện rượu', 'nghiện bia',
        'tự tử', 'tự sát', 'nhảy lầu', 'nhảy cầu', 'tự vẫn',
    ];

    $stmt = $pdo->prepare("INSERT INTO `blacklist_keywords` (`keyword`, `active`) VALUES (:keyword, 1)
        ON DUPLICATE KEY UPDATE `active` = 1");
    
    foreach ($defaultBlacklistKeywords as $keyword) {
        $stmt->execute([':keyword' => $keyword]);
    }

    echo "✅ Install completed: database + tables + default settings + blacklist keywords seeded.\n";

} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
