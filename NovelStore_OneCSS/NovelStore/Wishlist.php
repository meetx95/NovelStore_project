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
    SELECT n.novel_id, n.title, n.author, n.cover_image, n.price
    FROM wishlist w
    INNER JOIN novels n ON w.novel_id = n.novel_id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>



<div class="panel-page ns-wish-page">
  <div class="ns-wish-head">
    <h2>My Wishlist</h2>
    <a href="Dashboard.php?page=Library" class="ns-lib-tab">Explore More Books</a>
  </div>

  <div class="ns-wish-grid">
    <?php if (mysqli_num_rows($result)): ?>
      <?php while ($novel = mysqli_fetch_assoc($result)): ?>
        <?php $is_free = (float)$novel['price'] == 0; ?>
        <article class="novel-card ns-wish-card">
          <img src="Assets/Cover/<?php echo htmlspecialchars($novel['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Cover">
          <div class="ns-wish-body">
            <h3><?php echo htmlspecialchars($novel['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p><?php echo htmlspecialchars($novel['author'], ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="ns-wish-badge">
              <span class="ns-wish-price <?php echo $is_free ? 'is-free' : 'is-paid'; ?>">
                <?php echo $is_free ? 'Free' : '₹' . number_format((float)$novel['price'], 2); ?>
              </span>
            </div>
            <div class="ns-wish-actions">
              <a class="ns-wish-view" href="novel.php?id=<?php echo (int)$novel['novel_id']; ?>">View</a>
              <a class="ns-wish-remove" href="Dashboard.php?page=Wishlist&remove=<?php echo (int)$novel['novel_id']; ?>">
                <i class="fa-solid fa-trash-can"></i> Remove
              </a>
            </div>
          </div>
        </article>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-box">Your wishlist is empty. Open a book and add it here.</div>
    <?php endif; ?>
  </div>
</div>

<?php mysqli_stmt_close($stmt); ?>

<?php if ($standalone_page) {
  include 'include/dashboard_end.php';
} ?>