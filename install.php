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

            -- identity
            `full_name` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `username` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `email` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `password` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `level` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `session` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,

            -- profile
            `avatar` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `banner` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `bio` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `location` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `website` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
            `birthday` DATE NULL DEFAULT NULL,

            -- meta
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    // SETTINGS (new schema)
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

    // Seed settings (idempotent)
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
