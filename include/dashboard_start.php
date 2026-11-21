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
  <style>
  /* Dark pill rail + glowing active indicator inspired by Dashboard.jpg in NOVELSTORE palette */
  body.ns-dash-body {
    margin: 0;
    min-height: 100vh;
    background:
      radial-gradient(circle at 12% 18%, rgba(154, 107, 79, 0.16), transparent 36%),
      radial-gradient(circle at 85% 80%, rgba(111, 78, 55, 0.12), transparent 40%),
      #140e0b;
    color: var(--text, #2c211b);
    font-family: var(--font-body, "DM Sans", sans-serif);
    overflow-x: hidden;
  }

  .ns-dash-shell {
    display: flex;
    min-height: 100vh;
    padding: 16px;
    gap: 18px;
    align-items: flex-start;
  }

  /* Pill-shaped Dark Sidebar Rail */
  .ns-pill-rail {
    position: sticky;
    top: 16px;
    width: 66px;
    flex: 0 0 66px;
    min-height: calc(100vh - 32px);
    background: linear-gradient(180deg, #1d1410 0%, #150e0b 100%);
    border: 1px solid rgba(214, 191, 174, 0.14);
    border-radius: 999px;
    padding: 18px 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55);
    z-index: 40;
  }

  .ns-rail-brand {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    color: #fbf8f3;
    text-decoration: none;
    margin-bottom: 24px;
    font-size: 19px;
    background: rgba(154, 107, 79, 0.14);
    border: 1px solid rgba(214, 191, 174, 0.18);
    transition: transform 0.2s ease, background 0.2s ease;
  }

  .ns-rail-brand:hover {
    transform: scale(1.05);
    background: rgba(154, 107, 79, 0.25);
  }

  .ns-rail-nav,
  .ns-rail-bottom {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    width: 100%;
  }

  .ns-rail-bottom {
    margin-top: auto;
    padding-top: 16px;
  }

  .ns-rail-item {
    position: relative;
    width: 46px;
    height: 46px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    color: rgba(245, 239, 230, 0.58);
    text-decoration: none;
    transition: color 0.2s ease, background 0.25s ease, box-shadow 0.25s ease;
  }

  .ns-rail-item i {
    font-size: 16px;
    color: currentColor;
    border: none;
  }

  .ns-rail-item:hover {
    color: #fbf8f3;
    background: rgba(214, 191, 174, 0.08);
  }

  /* Active pill glow + left vertical accent bar */
  .ns-rail-item.active {
    color: #fbf8f3;
    background: radial-gradient(circle at 50% 50%, rgba(154, 107, 79, 0.36) 0%, rgba(111, 78, 55, 0.18) 70%, transparent 100%);
    box-shadow:
      inset 0 0 0 1px rgba(214, 191, 174, 0.42),
      0 0 18px rgba(154, 107, 79, 0.32);
  }

  .ns-rail-item.active::before {
    content: "";
    position: absolute;
    left: -8px;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 22px;
    border-radius: 999px;
    background: #d6bfae;
    box-shadow: 0 0 10px rgba(214, 191, 174, 0.85);
  }

  .ns-rail-tooltip {
    position: absolute;
    left: 58px;
    top: 50%;
    transform: translateY(-50%) translateX(-4px);
    background: #241812;
    color: #fbf8f3;
    border: 1px solid rgba(214, 191, 174, 0.22);
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.45);
    transition: opacity 0.18s ease, transform 0.18s ease;
    z-index: 60;
  }

  .ns-rail-item:hover .ns-rail-tooltip {
    opacity: 1;
    transform: translateY(-50%) translateX(0);
  }

  /* Main Workspace Surface */
  .ns-dash-workspace {
    flex: 1;
    min-width: 0;
    min-height: calc(100vh - 32px);
    background: var(--bg-light, #fbf8f3);
    border: 1px solid var(--border-color, #dfd0c0);
    border-radius: 26px;
    box-shadow: 0 24px 65px rgba(0, 0, 0, 0.42);
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .ns-topbar {
    min-height: 68px;
    padding: 12px clamp(18px, 2.5vw, 32px);
    background: var(--surface, #fffdf8);
    border-bottom: 1px solid var(--border-light, #eee5db);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
  }

  .ns-topbar-title {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  .ns-topbar-title strong {
    font-family: var(--font-heading, "Playfair Display", serif);
    font-size: 20px;
    letter-spacing: 0.8px;
    color: var(--primary-dark, #3e2723);
  }

  .ns-topbar-crumb {
    font-size: 13px;
    color: var(--text-secondary, #6b584b);
    background: var(--surface-2, #f0e6da);
    padding: 4px 11px;
    border-radius: 999px;
    font-weight: 500;
  }

  .ns-topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .ns-user-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 5px 12px 5px 6px;
    border-radius: 999px;
    background: var(--surface-2, #f0e6da);
    border: 1px solid var(--border-color, #dfd0c0);
    text-decoration: none;
    color: var(--primary-dark, #3e2723);
    font-size: 13px;
    font-weight: 600;
    transition: background 0.2s ease, border-color 0.2s ease;
  }

  .ns-user-pill:hover {
    background: var(--surface-3, #e5d5c4);
    border-color: var(--secondary, #b08968);
  }

  .ns-user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--primary, #6f4e37);
    color: #ffffff;
    display: grid;
    place-items: center;
    font-size: 13px;
    font-weight: 700;
    overflow: hidden;
  }

  .ns-user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .ns-page-body {
    flex: 1;
    width: 100%;
    max-width: 1520px;
    margin: 0 auto;
    padding: clamp(18px, 2.5vw, 34px);
  }

  /* Mobile bottom floating pill bar */
  @media (max-width: 767px) {
    .ns-dash-shell {
      flex-direction: column;
      padding: 10px 10px 84px;
      gap: 12px;
    }

    .ns-pill-rail {
      position: fixed;
      top: auto;
      bottom: 12px;
      left: 14px;
      right: 14px;
      width: auto;
      min-height: 58px;
      height: 58px;
      flex-direction: row;
      justify-content: space-around;
      padding: 6px 12px;
      border-radius: 999px;
    }

    .ns-rail-brand {
      display: none;
    }

    .ns-rail-nav,
    .ns-rail-bottom {
      flex-direction: row;
      width: auto;
      gap: 6px;
      margin: 0;
      padding: 0;
    }

    .ns-rail-item {
      width: 42px;
      height: 42px;
    }

    .ns-rail-item.active::before {
      left: 50%;
      top: -6px;
      transform: translateX(-50%);
      width: 20px;
      height: 3px;
    }

    .ns-rail-tooltip {
      display: none;
    }

    .ns-dash-workspace {
      min-height: calc(100vh - 94px);
      border-radius: 20px;
    }
  }
  </style>
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