<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: Login_register.php");
    exit();
}
$user_id = (int) $_SESSION['user_id'];
