<?php
$standalone_page = basename($_SERVER['SCRIPT_FILENAME']) !== 'Dashboard.php';
if ($standalone_page) {
  $layout_page = 'History';
  include 'include/dashboard_start.php';
}
$stmt = mysqli_prepare($conn, "
    SELECT n.novel_id,n.title,n.author,n.cover_image,
           c.chapter_number,c.chapter_title,r.last_read_at
    FROM reading_history r
    INNER JOIN novels n ON r.novel_id=n.novel_id
    INNER JOIN chapters c ON r.chapter_id=c.chapter_id
    WHERE r.user_id=?
    ORDER BY r.last_read_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="panel-page">

  <div class="section-title">
    <h2>Reading History</h2>
  </div>

  <div class="history-list">
    <?php if (mysqli_num_rows($result)):
      while ($row = mysqli_fetch_assoc($result)): ?>

    <div class="history-item">
      <img src="Assets/Cover/<?php echo $row['cover_image']; ?>" alt="Cover">
      <div>
        <h3><?php echo ($row['title']); ?></h3>
        <p>Chapter <?php echo (int)$row['chapter_number']; ?> - <?php echo ($row['chapter_title']); ?></p>
        <small><b>Last read:</b> <?php echo ($row['last_read_at']); ?></small>
      </div>
      <a class="primary-btn"
        href="reader.php?novel_id=<?php echo (int)$row['novel_id']; ?>&chapter=<?php echo (int)$row['chapter_number']; ?>">
        Continue
      </a>
    </div>
    <?php
      endwhile;
    else:
      ?>
    <div class="empty-box">No reading history yet.</div>
    <?php endif; ?>

  </div>
</div>

<?php mysqli_stmt_close($stmt); ?>
<?php 
if ($standalone_page) {
  include 'include/dashboard_end.php';
}
 ?>