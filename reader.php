<?php
require_once 'include/session.php';
require_once 'config/db.php';

$novel_id = filter_input(INPUT_GET, 'novel_id', FILTER_VALIDATE_INT);
$chapter_number = filter_input(INPUT_GET, 'chapter', FILTER_VALIDATE_INT);

if (!$novel_id) {
  header("Location: Dashboard.php?page=Home");
  exit();
}

$stmt = mysqli_prepare($conn, "
  SELECT novel_id, title, author, cover_image, price
  FROM novels
  WHERE novel_id=?
");
mysqli_stmt_bind_param($stmt, "i", $novel_id);
mysqli_stmt_execute($stmt);
$novel = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$novel) {
  die("Book not found.");
}

$stmt = mysqli_prepare($conn, "SELECT purchase_id FROM purchases WHERE user_id=? AND novel_id=?");
mysqli_stmt_bind_param($stmt, "ii", $user_id, $novel_id);
mysqli_stmt_execute($stmt);
$purchased = (bool)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$is_free = (float)$novel['price'] == 0;
if (!$is_free && !$purchased) {
  header("Location: purchase.php?novel_id=" . $novel_id);
  exit();
}

if (!$chapter_number) {
  $stmt = mysqli_prepare($conn, "
    SELECT c.chapter_number
    FROM reading_history r
    INNER JOIN chapters c ON r.chapter_id=c.chapter_id
    WHERE r.user_id=? AND r.novel_id=?
    LIMIT 1
  ");
  mysqli_stmt_bind_param($stmt, "ii", $user_id, $novel_id);
  mysqli_stmt_execute($stmt);
  $saved = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
  mysqli_stmt_close($stmt);
  $chapter_number = $saved ? (int)$saved['chapter_number'] : 1;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM chapters WHERE novel_id=? AND chapter_number=?");
mysqli_stmt_bind_param($stmt, "ii", $novel_id, $chapter_number);
mysqli_stmt_execute($stmt);
$chapter = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$chapter) {
  header("Location: reader.php?novel_id=" . $novel_id . "&chapter=1");
  exit();
}

$stmt = mysqli_prepare($conn, "
  INSERT INTO reading_history(user_id,novel_id,chapter_id)
  VALUES(?,?,?)
  ON DUPLICATE KEY UPDATE chapter_id=VALUES(chapter_id), last_read_at=CURRENT_TIMESTAMP
");
mysqli_stmt_bind_param($stmt, "iii", $user_id, $novel_id, $chapter['chapter_id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "
  SELECT MIN(chapter_number) AS first_chapter, MAX(chapter_number) AS last_chapter
  FROM chapters
  WHERE novel_id=?
");
mysqli_stmt_bind_param($stmt, "i", $novel_id);
mysqli_stmt_execute($stmt);
$range = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$first = (int)$range['first_chapter'];
$last = (int)$range['last_chapter'];

$layout_page = 'Reader';
include 'include/dashboard_start.php';
?>

<div class="reader-page-content">
  <div class="section-title">
    <a href="novel.php?id=<?php echo (int)$novel_id; ?>" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>&nbsp; Book Details
    </a>
    <span class="muted">
      <?php echo htmlspecialchars($novel['title']); ?> · Chapter <?php echo (int)$chapter['chapter_number']; ?>
    </span>
  </div>

  <article class="reader-card-dashboard">
    <p class="eyebrow">CHAPTER <?php echo (int)$chapter['chapter_number']; ?></p>
    <h1><?php echo htmlspecialchars($chapter['chapter_title']); ?></h1>
    <p class="reader-author-dashboard">By <?php echo htmlspecialchars($novel['author']); ?></p>

    <div class="reader-content-dashboard">
      <?php
      $paragraphs = preg_split("/\r\n|\r|\n/", trim($chapter['content']));
      foreach ($paragraphs as $paragraph) {
        if (trim($paragraph) !== '') {
          echo '<p>' . nl2br(htmlspecialchars($paragraph)) . '</p>';
        }
      }
      ?>
    </div>

    <div class="reader-chapter-nav">
      <?php if ($chapter_number > $first): ?>
        <a class="secondary-action" href="reader.php?novel_id=<?php echo $novel_id; ?>&chapter=<?php echo $chapter_number - 1; ?>">
          <i class="fa-solid fa-angle-left"></i>&nbsp; Previous
        </a>
      <?php else: ?>
        <span></span>
      <?php endif; ?>

      <span>Chapter <?php echo $chapter_number; ?> of <?php echo $last; ?></span>

      <?php if ($chapter_number < $last): ?>
        <a class="primary-btn" href="reader.php?novel_id=<?php echo $novel_id; ?>&chapter=<?php echo $chapter_number + 1; ?>">
          Next&nbsp; <i class="fa-solid fa-angle-right"></i>
        </a>
      <?php else: ?>
        <span class="muted">End of book</span>
      <?php endif; ?>
    </div>
  </article>
</div>

<?php include 'include/dashboard_end.php'; ?>
