<?php
require_once 'include/session.php';
require_once 'config/db.php';

$novel_id = filter_input(INPUT_GET, 'novel_id', FILTER_VALIDATE_INT);

if (!$novel_id) {
  header("Location: Dashboard.php?page=Home");
  exit;
}

$stmt = mysqli_prepare($conn, "
  SELECT novel_id, title, author, cover_image, price
  FROM novels
  WHERE novel_id=?");

if (!$stmt) {
  die("Database error.");
}

mysqli_stmt_bind_param($stmt, "i", $novel_id);
mysqli_stmt_execute($stmt);
$novel = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$novel) {
  die("Book not found.");
}

if ((float)$novel['price'] <= 0) {
  header("Location: reader.php?novel_id=" . $novel_id);
  exit;
}

$stmt = mysqli_prepare($conn, "
  SELECT purchase_id
  FROM purchases
  WHERE user_id=? AND novel_id=?
  LIMIT 1");

if (!$stmt) {
  die("Database error.");
}

mysqli_stmt_bind_param($stmt, "ii", $user_id, $novel_id);
mysqli_stmt_execute($stmt);
$purchase = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($purchase) {
  header("Location: reader.php?novel_id=" . $novel_id);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $amount = (float)$novel['price'];
  mysqli_begin_transaction($conn);

  try {
    $stmt = mysqli_prepare($conn, "
      INSERT INTO purchases (user_id, novel_id, amount)
      VALUES (?, ?, ?)
    ");

    if (!$stmt) {
      throw new Exception("Purchase query failed.");
    }

    mysqli_stmt_bind_param($stmt, "iid", $user_id, $novel_id, $amount);

    if (!mysqli_stmt_execute($stmt)) {
      throw new Exception("Purchase failed.");
    }

    $purchase_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    $payment_method = "DEMO";
    $transaction_id = "DEMO-" . strtoupper(bin2hex(random_bytes(6)));
    $payment_status = "PAID";

    $stmt = mysqli_prepare($conn, "
      INSERT INTO payments
        (purchase_id, user_id, novel_id, amount, payment_method, transaction_id, payment_status)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
      throw new Exception("Payment query failed.");
    }

    mysqli_stmt_bind_param(
      $stmt,
      "iiidsss",
      $purchase_id,
      $user_id,
      $novel_id,
      $amount,
      $payment_method,
      $transaction_id,
      $payment_status
    );

    if (!mysqli_stmt_execute($stmt)) {
      throw new Exception("Payment failed.");
    }

    mysqli_stmt_close($stmt);
    mysqli_commit($conn);

    header("Location: reader.php?novel_id=" . $novel_id);
    exit;
  } catch (Exception $e) {
    mysqli_rollback($conn);
    $error = "Payment failed. Please try again.";
  }
}

$layout_page = 'Purchase';
include 'include/dashboard_start.php';
?>



<div class="purchase-page-content ns-pay-wrap">
  <div>
    <a href="novel.php?id=<?php echo (int)$novel_id; ?>" class="ns-back-pill">
      <i class="fa-solid fa-arrow-left"></i> Back to Book
    </a>
  </div>

  <div class="purchase-card ns-pay-card">
    <div class="ns-pay-cover-col">
      <span class="ns-pay-badge">PREMIUM BOOK</span>
      <img class="ns-pay-cover"
        src="Assets/Cover/<?php echo htmlspecialchars($novel['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Book Cover">
    </div>

    <div class="ns-pay-details">
      <span class="ns-pay-badge">PAYMENT</span>
      <h1>Complete Purchase</h1>

      <p class="ns-pay-author">
        <?php echo htmlspecialchars($novel['title'], ENT_QUOTES, 'UTF-8'); ?>
        by <?php echo htmlspecialchars($novel['author'], ENT_QUOTES, 'UTF-8'); ?>
      </p>

      <?php if (isset($error)): ?>
      <div class="empty-box" style="margin-bottom:14px;">
        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
      </div>
      <?php endif; ?>

      <div class="ns-pay-summary">
        <span>Book Price</span>
        <strong>₹<?php echo number_format((float)$novel['price'], 2); ?></strong>
      </div>

      <div class="ns-pay-note">
        <strong>Payment</strong>
        <span>Pay to read novel</span>
      </div>

      <form method="POST" style="display:inline;">
        <button type="submit" class="ns-pay-btn">
          Pay ₹<?php echo number_format((float)$novel['price'], 2); ?>
        </button>
      </form>

      <div class="ns-pay-actions" style="margin-top:12px;">
        <a href="novel.php?id=<?php echo (int)$novel_id; ?>" class="ns-pay-cancel">Cancel</a>
      </div>
    </div>
  </div>
</div>

<?php include 'include/dashboard_end.php'; ?>