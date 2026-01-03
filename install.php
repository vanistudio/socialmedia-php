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

    $defaultSettings = [
        ['key' => 'siteTitle', 'value' => 'Vanix Social', 'type' => 'string'],
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

    echo "✅ Install completed: database + tables + default settings seeded.\n";

} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
