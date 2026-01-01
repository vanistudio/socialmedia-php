<?php
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    unset($_SESSION['email']);
}
header('Location: /login');
exit();