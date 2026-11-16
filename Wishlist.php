<?php
$standalone_page = basename($_SERVER['SCRIPT_FILENAME']) !== 'Dashboard.php';
if ($standalone_page) {
  $layout_page = 'Wishlist';
  include 'include/dashboard_start.php';
}
if (isset($_GET['remove'])) {
    $novel_id = filter_input(INPUT_GET, 'remove', FILTER_VALIDATE_INT);
    if ($novel_id) {
        $stmt = mysqli_prepare($conn, "DELETE FROM wishlist WHERE user_id=? AND novel_id=?");
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $novel_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: Dashboard.php?page=Wishlist");
    exit();
}

$stmt = mysqli_prepare($conn, "
    SELECT n.novel_id,n.title,n.author,n.cover_image,n.price
    FROM wishlist w
    INNER JOIN novels n ON w.novel_id=n.novel_id
    WHERE w.user_id=?
    ORDER BY w.added_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="panel-page">
  <div class="section-title">
    <h2>My Wishlist</h2>
  </div>

  <div class="novel-grid">
    <?php if (mysqli_num_rows($result)): ?>
    <?php while ($novel = mysqli_fetch_assoc($result)): ?>

    <article class="novel-card">
      <img src="Assets/Cover/<?php echo ($novel['cover_image']); ?>" alt="Cover">
      <div class="novel-info">
        <h3><?php echo ($novel['title']); ?></h3>
        <p><?php echo ($novel['author']); ?></p>
        <span class="<?php echo (float)$novel['price'] == 0 ? 'free' : 'price'; ?>">
          <?php echo (float)$novel['price'] == 0 ? 'Free' : '₹' . number_format($novel['price'], 2); ?>
        </span>
        <a class="card-link" href="novel.php?id=<?php echo (int)$novel['novel_id']; ?>">View</a>
        <a class="remove-link"
          href="Dashboard.php?page=Wishlist&remove=<?php echo (int)$novel['novel_id']; ?>">Remove</a>
      </div>
    </article>

    <?php endwhile; ?>
    <?php else: ?>

    <div class="empty-box">Your wishlist is empty. Open a book and add it here.</div>
    <?php endif; ?>
  </div>
</div>

<?php mysqli_stmt_close($stmt); ?>

<?php if ($standalone_page) { include 'include/dashboard_end.php'; } ?>
