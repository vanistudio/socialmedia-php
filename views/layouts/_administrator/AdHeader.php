<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/function.php';

$isLoggedIn = isset($_SESSION['email']) && !empty($_SESSION['email']);
$currentUser = null;
$isAdmin = false;

if ($isLoggedIn) {
    $currentUser = $Vani->get_row("SELECT * FROM `users` WHERE `email` = '" . addslashes($_SESSION['email']) . "'");
    $isAdmin = ($currentUser['level'] ?? '') === 'admin' || ($currentUser['level'] ?? '') === 'administrator';
}

if (!$isLoggedIn || !$isAdmin) {
    header('Location: /login');
    exit;
}

$siteTitle = $Vani->get_row("SELECT value FROM settings WHERE `key` = 'siteTitle'");
$siteTitle = $siteTitle['value'] ?? 'Vani Social';
?>
<!DOCTYPE html>
<html lang="vi" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo htmlspecialchars($siteTitle); ?></title>
    <link rel="stylesheet" href="/public/css/globals.css?time=<?php echo time() ?>">
    <link rel="stylesheet" href="/public/css/sonner.css?time=<?php echo time() ?>">
    <script src="/public/js/globals.js?time=<?php echo time() ?>"></script>
    <script src="/public/js/sonner.js?time=<?php echo time() ?>"></script>
    <link rel="stylesheet" href="/public/css/theme.php?time=<?php echo time() ?>">
    <script>
        window.SONNER_CONFIG = <?php echo json_encode(require $_SERVER['DOCUMENT_ROOT'] . '/config/sonner.php'); ?>;
    </script>
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Signika:wght@300..700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        window.CSRF_TOKEN = '<?php echo get_csrf_token(); ?>';
    </script>
</head>

<body class="bg-background text-foreground font-sans antialiased">
    <div id="app" class="min-h-screen flex flex-col">
        <?php include 'AdNavigation.php'; ?>
        <main class="grow container mx-auto px-4 py-8">
