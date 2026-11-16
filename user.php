<?php
$standalone_page = basename($_SERVER['SCRIPT_FILENAME']) !== 'Dashboard.php';
if ($standalone_page) {
  $layout_page = 'User';
  include 'include/dashboard_start.php';
}
require_once './include/session.php';
require_once './config/db.php';

$stmt = mysqli_prepare($conn, "
    SELECT username, email, profile_pic, created_at
    FROM users
    WHERE user_id = ?
");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
  header("Location: Dashboard.php?page=Home");
  exit();
}

function countFor($conn, $sql, $user_id)
{
  $stmt = mysqli_prepare($conn, $sql);

  if (!$stmt) {
    return 0;
  }

  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);

  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);
  return (int)($row['total'] ?? 0);
}

$wishlist_count = countFor(
  $conn,
  "SELECT COUNT(*) AS total
     FROM wishlist
     WHERE user_id = ?",
  $user_id
);

$purchase_count = countFor(
  $conn,
  "SELECT COUNT(*) AS total
     FROM purchases
     WHERE user_id = ?",
  $user_id
);

$history_count = countFor(
  $conn,
  "SELECT COUNT(DISTINCT novel_id) AS total
     FROM reading_history
     WHERE user_id = ?",
  $user_id
);


$username = htmlspecialchars($user['username']);
$email = htmlspecialchars($user['email']);

$profile_pic = !empty($user['profile_pic'])
  ? "Assets/Profile/" . htmlspecialchars($user['profile_pic'])
  : null;

$joined_date = date(
  "F Y",
  strtotime($user['created_at'])
);

$initial = strtoupper(substr($user['username'], 0, 1));
?>
<div class="profile-page">

  <section class="profile-hero">
    <div class="profile-cover"></div>
    <div class="profile-main">
      <div class="profile-avatar">
        <?php if ($profile_pic): ?>
          <img src="<?php echo $profile_pic; ?>" alt="Profile picture">
        <?php else: ?>
          <span>
            <?php echo $initial; ?>
          </span>
        <?php endif; ?>
      </div>

      <div class="profile-info">
        <span class="profile-label">
          MY ACCOUNT
        </span>
        <h1><?php echo $username; ?></h1>

        <p class="profile-email">
          <i class="fa-solid fa-envelope"></i>
          <?php echo $email; ?>
        </p>

        <p class="profile-joined">
          <i class="fa-regular fa-calendar"></i>
          Member since <?php echo $joined_date; ?>
        </p>
      </div>

      <div class="profile-actions">

        <a href="Dashboard.php?page=Home" class="profile-action secondary">
          <i class="fa-solid fa-house"></i>Home
        </a>

        <a href="logout.php" class="profile-action logout">
          <i class="fa-solid fa-arrow-right-from-bracket"></i>
          Logout
        </a>

      </div>
    </div>

  </section>

  <section class="profile-stats">

    <a href="Dashboard.php?page=Library" class="profile-stat">
      <div class="stat-icon purchase-icon">
        <i class="fa-solid fa-book"></i>
      </div>

      <div class="stat-content">
        <strong>
          <?php echo $purchase_count; ?>
        </strong>

        <span>Purchased Books</span>
      </div>

      <i class="fa-solid fa-chevron-right stat-arrow"></i>
    </a>


    <a href="Dashboard.php?page=Wishlist" class="profile-stat">

      <div class="stat-icon wishlist-icon">
        <i class="fa-solid fa-heart"></i>
      </div>

      <div class="stat-content">

        <strong>
          <?php echo $wishlist_count; ?>
        </strong>

        <span>
          Wishlist Books
        </span>

      </div>

      <i class="fa-solid fa-chevron-right stat-arrow"></i>

    </a>


    <a href="Dashboard.php?page=History" class="profile-stat">

      <div class="stat-icon history-icon">
        <i class="fa-solid fa-clock-rotate-left"></i>
      </div>

      <div class="stat-content">

        <strong>
          <?php echo $history_count; ?>
        </strong>

        <span>
          Books Read
        </span>

      </div>

      <i class="fa-solid fa-chevron-right stat-arrow"></i>

    </a>

  </section>

  <section class="account-layout">

    <div class="account-card">
      <div class="card-heading">
        <div class="heading-icon">
          <i class="fa-solid fa-user"></i>
        </div>

        <div>
          <h2>Account Information</h2>
          <p> Your personal account details </p>
        </div>

      </div>

      <div class="info-list">

        <div class="info-row">
          <div class="info-icon">
            <i class="fa-solid fa-user"></i>
          </div>

          <div class="info-text">
            <span>Username</span>
            <strong>
              <?php echo $username; ?>
            </strong>

          </div>

        </div>


        <div class="info-row">

          <div class="info-icon">
            <i class="fa-solid fa-envelope"></i>
          </div>

          <div class="info-text">

            <span>Email Address</span>

            <strong>
              <?php echo $email; ?>
            </strong>

          </div>

        </div>


        <div class="info-row">

          <div class="info-icon">
            <i class="fa-solid fa-calendar-days"></i>
          </div>

          <div class="info-text">

            <span>Member Since</span>

            <strong>
              <?php echo date(
                "d M Y",
                strtotime($user['created_at'])
              ); ?>
            </strong>

          </div>

        </div>

      </div>

    </div>


    <!-- Quick Access -->

    <div class="account-card">

      <div class="card-heading">

        <div class="heading-icon">
          <i class="fa-solid fa-bolt"></i>
        </div>

        <div>

          <h2>Quick Access</h2>

          <p>
            Manage your reading activity
          </p>

        </div>

      </div>


      <div class="quick-links">

        <a href="Dashboard.php?page=Library" class="quick-link">

          <div class="quick-link-icon">
            <i class="fa-solid fa-book-open"></i>
          </div>

          <div>

            <strong>
              My Library
            </strong>

            <span>
              View your purchased books
            </span>

          </div>

          <i class="fa-solid fa-chevron-right"></i>

        </a>


        <a href="Dashboard.php?page=Wishlist" class="quick-link">

          <div class="quick-link-icon heart">
            <i class="fa-solid fa-heart"></i>
          </div>

          <div>

            <strong>
              Wishlist
            </strong>

            <span>
              Books you've saved
            </span>

          </div>

          <i class="fa-solid fa-chevron-right"></i>

        </a>


        <a href="Dashboard.php?page=History" class="quick-link">

          <div class="quick-link-icon history">
            <i class="fa-solid fa-clock-rotate-left"></i>
          </div>

          <div>

            <strong>
              Reading History
            </strong>

            <span>
              Continue your reading journey
            </span>

          </div>

          <i class="fa-solid fa-chevron-right"></i>

        </a>

      </div>

    </div>

  </section>


  <!-- =========================
         DANGER / LOGOUT
    ========================== -->

  <section class="account-footer">

    <div>

      <span class="footer-icon">
        <i class="fa-solid fa-shield-halved"></i>
      </span>

      <div>

        <strong>
          Account & Security
        </strong>

        <p>
          Keep your account information secure.
        </p>

      </div>

    </div>

    <a href="logout.php" class="logout-button">
      <i class="fa-solid fa-arrow-right-from-bracket"></i>
      Sign Out
    </a>

  </section>

</div>

<?php if ($standalone_page) { include 'include/dashboard_end.php'; } ?>
