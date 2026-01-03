<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media</title>
    <link rel="stylesheet" href="/public/css/globals.css?time=<?php echo time() ?>">
    <link rel="stylesheet" href="/public/css/sonner.css?time=<?php echo time() ?>">
    <script src="/public/js/globals.js?time=<?php echo time() ?>"></script>
    <script src="/public/js/sonner.js?time=<?php echo time() ?>"></script>
    <link rel="stylesheet" href="/public/css/theme.php?time=<?php echo time() ?>">
    <script>
        window.SONNER_CONFIG = <?php echo json_encode(require $_SERVER['DOCUMENT_ROOT'] . '/config/sonner.php'); ?>;
    </script>
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Signika:wght@300..700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        window.CSRF_TOKEN = '<?php echo get_csrf_token(); ?>';
    </script>
</head>

<body class="bg-background text-foreground font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        <?php include 'AuthNavigation.php'; ?>
        <main class="flex-grow flex items-center justify-center p-4">