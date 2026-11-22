<?php
$standalone_page = basename($_SERVER['SCRIPT_FILENAME']) !== 'Dashboard.php';
if ($standalone_page) {
  $layout_page = 'History';
  include 'include/dashboard_start.php';
}

$stmt = mysqli_prepare($conn, "
    SELECT n.novel_id, n.title, n.author, n.cover_image,
           c.chapter_number, c.chapter_title, r.last_read_at
    FROM reading_history r
    INNER JOIN novels n ON r.novel_id = n.novel_id
    INNER JOIN chapters c ON r.chapter_id = c.chapter_id
    WHERE r.user_id = ?
    ORDER BY r.last_read_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$history_count = mysqli_num_rows($result);
?>



<div class="panel-page ns-hist-page">

  <div class="ns-hist-header">
    <h2>Reading History</h2>
    <span class="ns-hist-badge">
      <i class="fa-solid fa-clock-rotate-left"></i>
      <?php echo (int)$history_count; ?> <?php echo $history_count === 1 ? 'Entry' : 'Entries'; ?>
    </span>
  </div>

  <div class="history-list ns-hist-list">
    <?php if ($history_count > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <div class="history-item ns-hist-card">
      <div class="ns-hist-main">
        <img src="Assets/Cover/<?php echo htmlspecialchars($row['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Cover"
          class="ns-hist-cover">
        <div class="ns-hist-info">
          <h3><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <p class="ns-hist-chapter">
            Chapter <?php echo (int)$row['chapter_number']; ?> -
            <?php echo htmlspecialchars($row['chapter_title'], ENT_QUOTES, 'UTF-8'); ?>
          </p>
          <small class="ns-hist-meta">
            <i class="fa-regular fa-clock"></i>
            <b>Last read:</b> <?php echo htmlspecialchars($row['last_read_at'], ENT_QUOTES, 'UTF-8'); ?>
          </small>
        </div>
      </div>

      <a class="primary-btn ns-hist-cta"
        href="reader.php?novel_id=<?php echo (int)$row['novel_id']; ?>&chapter=<?php echo (int)$row['chapter_number']; ?>">
        <i class="fa-solid fa-book-open"></i> Continue
      </a>
    </div>
    <?php endwhile; ?>
    <?php else: ?>
    <div class="empty-box ns-hist-empty">
      <p>No reading history yet.</p>
      <a href="Dashboard.php?page=Library">Browse Library</a>
    </div>
    <?php endif; ?>
  </div>

</div>

<?php mysqli_stmt_close($stmt); ?>
<?php
if ($standalone_page) {
  include 'include/dashboard_end.php';
}
?>