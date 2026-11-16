<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/db.php';

$layout_page = $layout_page ?? 'Home';

$stmt = mysqli_prepare($conn, "SELECT username, email, profile_pic FROM users WHERE user_id=?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$layout_user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$layout_user = $layout_user ?: ['username' => 'User'];

function dashboardActivePage($name)
{
  global $layout_page;
  return $layout_page === $name ? 'active' : '';
}
?>
<!DOCTYPE html>
<html>

<head>
  <title>Novelstore: <?php echo htmlspecialchars($layout_page); ?></title>
  <?php include __DIR__ . '/Links.php'; ?>
</head>

<body>
  <div class="main dashboard">

    <aside class="menubar">

      <a href="Dashboard.php?page=Home" class="sidebar-logo" title="NOVELSTORE">
        <i class="fa-solid fa-book"></i>
      </a>

      <nav class="menu">
        <a href="Dashboard.php?page=Home" class="logo <?php echo dashboardActivePage('Home'); ?>">
          <i class="fa-solid fa-house"></i>
          <span class="tooltip">Home</span>
        </a>

        <a href="Dashboard.php?page=History" class="logo <?php echo dashboardActivePage('History'); ?>">
          <i class="fa-solid fa-clock-rotate-left"></i>
          <span class="tooltip">History</span>
        </a>

        <a href="Dashboard.php?page=Wishlist" class="logo <?php echo dashboardActivePage('Wishlist'); ?>">
          <i class="fa-solid fa-heart"></i>
          <span class="tooltip">Wishlist</span>
        </a>

        <a href="Dashboard.php?page=Library" class="logo <?php echo dashboardActivePage('Library'); ?>">
          <i class="fa-solid fa-book-open"></i>
          <span class="tooltip">Library</span>
        </a>
      </nav>

      <div class="bottom-menu">
        <a href="Dashboard.php?page=User" class="logo <?php echo dashboardActivePage('User'); ?>">
          <i class="fa-solid fa-user"></i>
          <span class="tooltip">Profile</span>
        </a>
        <a href="logout.php" class="logo">
          <i class="fa-solid fa-right-from-bracket"></i>
          <span class="tooltip">Logout</span>
        </a>
      </div>

    </aside>

    <main class="dashboard-content">
      <div class="dashboard-topbar">
        <div>
          <strong>NOVELSTORE</strong>
          <span> / <?php echo ($layout_page); ?></span>
        </div>

        <a href="Dashboard.php?page=User" class="user-chip">
          <span><?php echo (substr($layout_user['username'], 0, 1)); ?></span>
        </a>
      </div>

      <div class="dashboard-page-content">