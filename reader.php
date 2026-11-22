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

<style>
  .ns-reader-wrap {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .ns-reader-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  .ns-reader-crumb {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-secondary, #6b584b);
    background: var(--surface, #fffdf8);
    border: 1px solid var(--border-color, #dfd0c0);
    padding: 6px 14px;
    border-radius: 999px;
  }

  .ns-reader-sheet {
    max-width: 860px;
    width: 100%;
    margin: 0 auto;
    background: var(--surface, #fffdf8);
    border: 1px solid var(--border-color, #dfd0c0);
    border-radius: 24px;
    padding: clamp(24px, 5vw, 54px);
    box-shadow: var(--shadow-md, 0 8px 24px rgba(62, 39, 35, 0.11));
  }

  .ns-reader-eyebrow {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--primary-light, #9a6b4f);
    margin-bottom: 8px;
  }

  .ns-reader-sheet h1 {
    margin: 0 0 6px;
    font-family: var(--font-heading, "Playfair Display", serif);
    font-size: clamp(26px, 3.4vw, 38px);
    line-height: 1.2;
    color: var(--primary-dark, #3e2723);
  }

  .ns-reader-byline {
    margin: 0 0 28px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--border-light, #eee5db);
    font-size: 13.5px;
    color: var(--text-secondary, #6b584b);
  }

  .ns-reader-body {
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(16.5px, 1.35vw, 19px);
    line-height: 1.95;
    color: var(--text, #2c211b);
  }

  .ns-reader-body p {
    margin: 0 0 1.35em;
  }

  .ns-reader-footer-nav {
    margin-top: 36px;
    padding-top: 22px;
    border-top: 1px solid var(--border-light, #eee5db);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
  }

  .ns-reader-progress {
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-secondary, #6b584b);
  }
</style>

<div class="reader-page-content ns-reader-wrap">
  <div class="ns-reader-top">
    <a href="novel.php?id=<?php echo (int)$novel_id; ?>" class="ns-back-pill">
      <i class="fa-solid fa-arrow-left"></i> Book Details
    </a>
    <span class="ns-reader-crumb">
      <?php echo htmlspecialchars($novel['title'], ENT_QUOTES, 'UTF-8'); ?> &middot; Chapter
      <?php echo (int)$chapter['chapter_number']; ?>
    </span>
  </div>

  <article class="reader-card-dashboard ns-reader-sheet">
    <span class="ns-reader-eyebrow">CHAPTER <?php echo (int)$chapter['chapter_number']; ?></span>
    <h1><?php echo htmlspecialchars($chapter['chapter_title'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="ns-reader-byline">By <?php echo htmlspecialchars($novel['author'], ENT_QUOTES, 'UTF-8'); ?></p>

    <div class="reader-content-dashboard ns-reader-body">
      <?php
      $paragraphs = preg_split("/\r\n|\r|\n/", trim($chapter['content']));
      foreach ($paragraphs as $paragraph) {
        if (trim($paragraph) !== '') {
          echo '<p>' . nl2br(htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8')) . '</p>';
        }
      }
      ?>
    </div>

    <div class="reader-chapter-nav ns-reader-footer-nav">
      <?php if ($chapter_number > $first): ?>
        <a class="ns-btn-outline"
          href="reader.php?novel_id=<?php echo $novel_id; ?>&chapter=<?php echo $chapter_number - 1; ?>">
          <i class="fa-solid fa-angle-left"></i> Previous
        </a>
      <?php else: ?>
        <span></span>
      <?php endif; ?>

      <span class="ns-reader-progress">Chapter <?php echo $chapter_number; ?> of <?php echo $last; ?></span>

      <?php if ($chapter_number < $last): ?>
        <a class="ns-btn-primary"
          href="reader.php?novel_id=<?php echo $novel_id; ?>&chapter=<?php echo $chapter_number + 1; ?>">
          Next <i class="fa-solid fa-angle-right"></i>
        </a>
      <?php else: ?>
        <span class="ns-reader-progress">End of book</span>
      <?php endif; ?>
    </div>
  </article>
</div>

<?php include 'include/dashboard_end.php'; ?>