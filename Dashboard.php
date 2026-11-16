<?php
require_once 'include/session.php';
require_once 'config/db.php';

$page = $_GET['page'] ?? 'Home';
$allowed = ['Home', 'History', 'Wishlist', 'Library', 'User'];
if (!in_array($page, $allowed, true)) {
  $page = 'Home';
}

$layout_page = $page;
include 'include/dashboard_start.php';

$files = [
  'Home' => 'home.php',
  'History' => 'History.php',
  'Wishlist' => 'Wishlist.php',
  'Library' => 'Library.php',
  'User' => 'user.php'
];
include $files[$page];

include 'include/dashboard_end.php';
