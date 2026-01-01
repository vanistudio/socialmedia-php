<?php
require $_SERVER['DOCUMENT_ROOT']. '/config/website.php';
require $_SERVER['DOCUMENT_ROOT']. '/utils/oklch.php';

header('Content-Type: text/css');

$vanixjnk_oklch = hex_to_oklch($vanixjnk);
?>
:root {
    --vanixjnk: <?php echo $vanixjnk_oklch; ?>;
}

.dark {
    --vanixjnk: <?php echo $vanixjnk_oklch; ?>;
}

@theme inline {
    --color-vanixjnk: <?php echo $vanixjnk_oklch; ?>;
}