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

<style>
.ns-pay-wrap {
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-width: 820px;
  margin: 0 auto;
}

.ns-pay-card {
  background: var(--surface, #fffdf8);
  border: 1px solid var(--border-color, #dfd0c0);
  border-radius: 24px;
  padding: clamp(20px, 3.5vw, 34px);
  box-shadow: var(--shadow-md, 0 8px 24px rgba(62, 39, 35, 0.11));
  display: grid;
  grid-template-columns: minmax(150px, 210px) minmax(0, 1fr);
  gap: 28px;
  align-items: center;
}

.ns-pay-cover-col {
  text-align: center;
}

.ns-pay-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 999px;
  background: var(--surface-2, #f0e6da);
  color: var(--primary, #6f4e37);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-bottom: 12px;
}

.ns-pay-cover {
  width: 100%;
  max-width: 200px;
  aspect-ratio: 3 / 4;
  object-fit: cover;
  border-radius: 14px;
  box-shadow: 0 14px 28px rgba(44, 33, 27, 0.18);
}

.ns-pay-details h1 {
  margin: 6px 0 6px;
  font-family: var(--font-heading, "Playfair Display", serif);
  font-size: clamp(24px, 3vw, 32px);
  color: var(--primary-dark, #3e2723);
}

.ns-pay-author {
  margin: 0 0 18px;
  font-size: 14px;
  color: var(--text-secondary, #6b584b);
}

.ns-pay-summary {
  background: var(--bg-light, #fbf8f3);
  border: 1px solid var(--border-color, #dfd0c0);
  border-radius: 16px;
  padding: 16px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

.ns-pay-summary span {
  font-size: 13px;
  color: var(--text-secondary, #6b584b);
  font-weight: 500;
}

.ns-pay-summary strong {
  font-size: 22px;
  color: var(--primary-dark, #3e2723);
}

.ns-pay-note {
  background: var(--surface-2, #f0e6da);
  border-radius: 14px;
  padding: 12px 16px;
  margin-bottom: 20px;
  font-size: 12.5px;
  color: var(--text-secondary, #6b584b);
}

.ns-pay-note strong {
  display: block;
  color: var(--primary-dark, #3e2723);
  margin-bottom: 2px;
}

.ns-pay-actions {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}

.ns-pay-btn {
  padding: 12px 24px;
  border-radius: 999px;
  border: none;
  background: var(--primary, #6f4e37);
  color: #ffffff;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s ease, transform 0.2s ease;
}

.ns-pay-btn:hover {
  background: var(--primary-dark, #3e2723);
  transform: translateY(-1px);
}

.ns-pay-cancel {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-secondary, #6b584b);
  text-decoration: none;
}

.ns-pay-cancel:hover {
  color: var(--primary-dark, #3e2723);
  text-decoration: underline;
}

@media (max-width: 680px) {
  .ns-pay-card {
    grid-template-columns: 1fr;
  }

  .ns-pay-cover {
    max-width: 165px;
  }
}
</style>

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