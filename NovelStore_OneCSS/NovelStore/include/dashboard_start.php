<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/db.php';

$layout_page = $layout_page ?? 'Home';

$stmt = mysqli_prepare($conn, "SELECT username, email, profile_pic FROM users WHERE user_id=?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$layout_user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$layout_user = $layout_user ?: ['username' => 'User', 'email' => '', 'profile_pic' => ''];
$initial = strtoupper(substr($layout_user['username'] ?: 'U', 0, 1));

function dashboardActivePage($name)
{
  global $layout_page;
  return $layout_page === $name ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Novelstore: <?php echo htmlspecialchars($layout_page, ENT_QUOTES, 'UTF-8'); ?></title>
  <?php include __DIR__ . '/Links.php'; ?>
  
</head>

<body class="ns-dash-body">
  <div class="ns-dash-shell">

    <aside class="ns-pill-rail" aria-label="Dashboard Navigation">
      <a href="Dashboard.php?page=Home" class="ns-rail-brand" title="NOVELSTORE">
        <i class="fa-solid fa-book"></i>
      </a>

      <nav class="ns-rail-nav">
        <a href="Dashboard.php?page=Home" class="ns-rail-item <?php echo dashboardActivePage('Home'); ?>"
          aria-label="Home">
          <i class="fa-solid fa-house"></i>
          <span class="ns-rail-tooltip">Home</span>
        </a>

        <a href="Dashboard.php?page=History" class="ns-rail-item <?php echo dashboardActivePage('History'); ?>"
          aria-label="History">
          <i class="fa-solid fa-clock-rotate-left"></i>
          <span class="ns-rail-tooltip">History</span>
        </a>

        <a href="Dashboard.php?page=Wishlist" class="ns-rail-item <?php echo dashboardActivePage('Wishlist'); ?>"
          aria-label="Wishlist">
          <i class="fa-solid fa-heart"></i>
          <span class="ns-rail-tooltip">Wishlist</span>
        </a>

        <a href="Dashboard.php?page=Library" class="ns-rail-item <?php echo dashboardActivePage('Library'); ?>"
          aria-label="Library">
          <i class="fa-solid fa-book-open"></i>
          <span class="ns-rail-tooltip">Library</span>
        </a>
      </nav>

      <div class="ns-rail-bottom">
        <a href="Dashboard.php?page=User" class="ns-rail-item <?php echo dashboardActivePage('User'); ?>"
          aria-label="Profile">
          <i class="fa-solid fa-user"></i>
          <span class="ns-rail-tooltip">Profile</span>
        </a>

        <a href="logout.php" class="ns-rail-item" aria-label="Logout">
          <i class="fa-solid fa-right-from-bracket"></i>
          <span class="ns-rail-tooltip">Logout</span>
        </a>
      </div>
    </aside>

    <main class="ns-dash-workspace">
      <header class="ns-topbar">
        <div class="ns-topbar-title">
          <strong>NOVELSTORE</strong>
          <span class="ns-topbar-crumb">/ <?php echo htmlspecialchars($layout_page, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <div class="ns-topbar-right">
          <a href="Dashboard.php?page=User" class="ns-user-pill">
            <span class="ns-user-avatar">
              <?php if (!empty($layout_user['profile_pic'])): ?>
              <img src="<?php echo htmlspecialchars($layout_user['profile_pic'], ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar">
              <?php else: ?>
              <?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?>
              <?php endif; ?>
            </span>
            <span><?php echo htmlspecialchars($layout_user['username'], ENT_QUOTES, 'UTF-8'); ?></span>
          </a>
        </div>
      </header>

      <div class="ns-page-body">