<?php
$standalone_page = basename($_SERVER['SCRIPT_FILENAME']) !== 'Dashboard.php';
if ($standalone_page) {
  $layout_page = 'Home';
  include 'include/dashboard_start.php';
}
$continue = null;
$stmt = mysqli_prepare($conn, "
    SELECT n.novel_id,n.title,n.author,n.cover_image,
           c.chapter_id,c.chapter_number,c.chapter_title,r.last_read_at
    FROM reading_history r
    INNER JOIN novels n ON r.novel_id=n.novel_id
    INNER JOIN chapters c ON r.chapter_id=c.chapter_id
    WHERE r.user_id=?
    ORDER BY r.last_read_at DESC
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$continue = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$result = mysqli_query($conn, "
    SELECT novel_id, title, author, description, cover_image, price, rating
    FROM novels
    ORDER BY rating DESC, novel_id DESC
    LIMIT 8
");
?>

<div class="home">

  <section class="welcome">
    <h1>Welcome, <?php echo ($_SESSION['username']); ?></h1>
    <p>What would you like to read today?</p>
  </section>

  <!-- reading -->
  <section class="continue-reading">
    <div class="section-title">
      <h2>Continue Reading</h2>
    </div>

    <?php if ($continue): ?>

    <div class="continue-card">
      <img src="./Assets/Cover/<?php echo $continue['cover_image']; ?>" alt="Book cover">
      <div class="continue-info">
        <h3><?php echo ($continue['title']); ?></h3>
        <p>By <?php echo ($continue['author']); ?></p>
        <p>Chapter
          <?php echo (int)$continue['chapter_number']; ?> -
          <?php echo ($continue['chapter_title']); ?>
        </p>
        <small>Last read: <?php echo ($continue['last_read_at']); ?></small>
        <br><br>
        <a class=" read-btn"
          href="reader.php?novel_id=<?php echo (int)$continue['novel_id']; ?>&chapter=<?php echo (int)$continue['chapter_number']; ?>">
          Continue Reading
        </a>
      </div>
    </div>

    <?php else: ?>

    <div class="empty-box">
      <p>You haven't started reading anything yet.</p>
      <a href="Dashboard.php?page=Library">Browse Library</a>
    </div>

    <?php endif; ?>
  </section>

  <section>
    <div class="section-title">
      <h2>Popular Novels</h2>
    </div>

    <div class="novel-grid">
      <?php while ($novel = mysqli_fetch_assoc($result)): ?>
      <article class="novel-card">
        <img src="Assets/Cover/<?php echo ($novel['cover_image']); ?>" alt="Book cover">
        <div class="novel-info">
          <h3><?php echo ($novel['title']); ?></h3>
          <p>Author: <?php echo ($novel['author']); ?></p>
          <p class=" rating novel_card_text"><b>Current Rating:</b>
            <?php echo number_format((float)$novel['rating'], 1); ?>/5
          </p>
          <span class="<?php echo (float)$novel['price'] == 0 ? 'free' : 'price'; ?>">
            <?php
              echo (float)$novel['price'] == 0 ? 'Free' : '₹' . number_format($novel['price'], 2);
              ?>
          </span>
          <a class="card-link" href="novel.php?id=<?php echo (int)$novel['novel_id']; ?>">View Book</a>
        </div>
      </article>

      <?php endwhile; ?>
    </div>
  </section>

</div>

<?php if ($standalone_page) { include 'include/dashboard_end.php'; } ?>
