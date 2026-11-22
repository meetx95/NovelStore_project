<?php
require_once 'include/session.php';
require_once 'config/db.php';

$novel_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$novel_id) {
  header("Location: Dashboard.php?page=Home");
  exit();
}

$stmt = mysqli_prepare($conn, "SELECT * FROM novels WHERE novel_id = ?");
mysqli_stmt_bind_param($stmt, "i", $novel_id);
mysqli_stmt_execute($stmt);
$novel = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$novel) {
  die("Book not found.");
}

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM chapters WHERE novel_id = ?");
mysqli_stmt_bind_param($stmt, "i", $novel_id);
mysqli_stmt_execute($stmt);
$total_chapters = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "
  SELECT chapter_id, chapter_number, chapter_title
  FROM chapters
  WHERE novel_id = ?
  ORDER BY chapter_number ASC
  LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $novel_id);
mysqli_stmt_execute($stmt);
$first_chapter = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT wishlist_id FROM wishlist WHERE user_id=? AND novel_id=?");
mysqli_stmt_bind_param($stmt, "ii", $user_id, $novel_id);
mysqli_stmt_execute($stmt);
$in_wishlist = (bool)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT purchase_id FROM purchases WHERE user_id=? AND novel_id=?");
mysqli_stmt_bind_param($stmt, "ii", $user_id, $novel_id);
mysqli_stmt_execute($stmt);
$purchased = (bool)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_action'])) {
  if ($in_wishlist) {
    $stmt = mysqli_prepare($conn, "DELETE FROM wishlist WHERE user_id=? AND novel_id=?");
  } else {
    $stmt = mysqli_prepare($conn, "INSERT IGNORE INTO wishlist (user_id, novel_id) VALUES (?, ?)");
  }

  mysqli_stmt_bind_param($stmt, "ii", $user_id, $novel_id);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  header("Location: novel.php?id=" . $novel_id);
  exit();
}

$average_rating = 0;
$total_ratings = 0;

$rating_query = @mysqli_prepare($conn, "
  SELECT COALESCE(AVG(rating), 0) AS average_rating, COUNT(*) AS total_ratings
  FROM novel_ratings
  WHERE novel_id = ?
");

if ($rating_query) {
  mysqli_stmt_bind_param($rating_query, "i", $novel_id);
  mysqli_stmt_execute($rating_query);
  $rating_data = mysqli_fetch_assoc(mysqli_stmt_get_result($rating_query));
  mysqli_stmt_close($rating_query);

  $average_rating = round((float)$rating_data['average_rating'], 1);
  $total_ratings = (int)$rating_data['total_ratings'];
}

$user_rating = 0;
$user_rating_query = @mysqli_prepare($conn, "
  SELECT rating
  FROM novel_ratings
  WHERE user_id=? AND novel_id=?
");

if ($user_rating_query) {
  mysqli_stmt_bind_param($user_rating_query, "ii", $user_id, $novel_id);
  mysqli_stmt_execute($user_rating_query);
  $user_rating_row = mysqli_fetch_assoc(mysqli_stmt_get_result($user_rating_query));
  mysqli_stmt_close($user_rating_query);

  if ($user_rating_row) {
    $user_rating = (int)$user_rating_row['rating'];
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating_action'])) {
  $submitted_rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);

  if ($submitted_rating !== false && $submitted_rating >= 1 && $submitted_rating <= 5) {
    $stmt = mysqli_prepare($conn, "
      INSERT INTO novel_ratings (user_id, novel_id, rating)
      VALUES (?, ?, ?)
      ON DUPLICATE KEY UPDATE rating=VALUES(rating), updated_at=CURRENT_TIMESTAMP
    ");
    mysqli_stmt_bind_param($stmt, "iii", $user_id, $novel_id, $submitted_rating);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "
      UPDATE novels n
      SET n.rating=(
        SELECT COALESCE(AVG(nr.rating), 0)
        FROM novel_ratings nr
        WHERE nr.novel_id=n.novel_id
      )
      WHERE n.novel_id=?
    ");
    mysqli_stmt_bind_param($stmt, "i", $novel_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }

  header("Location: novel.php?id=" . $novel_id . "#rating");
  exit();
}

$related_books = [];
if (!empty($novel['genre'])) {
  $stmt = mysqli_prepare($conn, "
    SELECT novel_id, title, author, cover_image, price, rating
    FROM novels
    WHERE novel_id != ? AND genre=?
    ORDER BY rating DESC, novel_id DESC
    LIMIT 6
  ");
  mysqli_stmt_bind_param($stmt, "is", $novel_id, $novel['genre']);
} else {
  $stmt = mysqli_prepare($conn, "
    SELECT novel_id, title, author, cover_image, price, rating
    FROM novels
    WHERE novel_id != ?
    ORDER BY rating DESC, novel_id DESC
    LIMIT 6
  ");
  mysqli_stmt_bind_param($stmt, "i", $novel_id);
}

mysqli_stmt_execute($stmt);
$related_result = mysqli_stmt_get_result($stmt);
while ($book = mysqli_fetch_assoc($related_result)) {
  $related_books[] = $book;
}
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "
  SELECT novel_id, title, author, cover_image, price, rating
  FROM novels
  WHERE novel_id != ?
  ORDER BY created_at DESC, novel_id DESC
  LIMIT 8
");
mysqli_stmt_bind_param($stmt, "i", $novel_id);
mysqli_stmt_execute($stmt);
$more_books_result = mysqli_stmt_get_result($stmt);

$layout_page = 'Book';
include 'include/dashboard_start.php';
?>



<div class="book-page ns-book-wrap">

  <div class="ns-book-topbar">
    <a href="Dashboard.php?page=Home" class="ns-back-pill">
      <i class="fa-solid fa-arrow-left"></i> Back to Library
    </a>
  </div>

  <article class="book-detail-card ns-book-hero">
    <div class="book-detail-main ns-book-hero-grid">

      <img class="book-detail-cover ns-book-cover"
        src="Assets/Cover/<?php echo htmlspecialchars($novel['cover_image'], ENT_QUOTES, 'UTF-8'); ?>"
        alt="<?php echo htmlspecialchars($novel['title'], ENT_QUOTES, 'UTF-8'); ?>">

      <div class="book-detail-info">
        <?php if (!empty($novel['genre'])): ?>
        <span class="ns-book-genre"><?php echo htmlspecialchars($novel['genre'], ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>

        <h1 class="ns-book-title"><?php echo htmlspecialchars($novel['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="ns-book-author">By <?php echo htmlspecialchars($novel['author'], ENT_QUOTES, 'UTF-8'); ?></p>

        <div class="ns-book-rating-line">
          <span class="ns-stars-gold">
            <?php
            $display_rating = $average_rating > 0 ? $average_rating : 0;
            for ($i = 1; $i <= 5; $i++) {
              echo $i <= round($display_rating) ? '★' : '☆';
            }
            ?>
          </span>
          <strong><?php echo number_format($display_rating, 1); ?>/5</strong>
          <span>(<?php echo $total_ratings; ?> <?php echo $total_ratings === 1 ? 'Rating' : 'Ratings'; ?>)</span>
        </div>

        <p class="ns-book-desc">
          <?php echo nl2br(htmlspecialchars(trim($novel['description'] ?? ''), ENT_QUOTES, 'UTF-8')); ?>
        </p>

        <div class="ns-book-stats-row">
          <div class="ns-mini-stat">
            <strong><?php echo $total_chapters; ?></strong>
            <span>Chapters</span>
          </div>
          <div class="ns-mini-stat">
            <strong>Reading</strong>
            <span>Experience</span>
          </div>
          <div class="ns-mini-stat">
            <strong><?php echo $average_rating > 0 ? $average_rating : 'New'; ?></strong>
            <span>Reader Score</span>
          </div>
        </div>

        <div class="ns-book-action-bar">
          <?php if ((float)$novel['price'] == 0 || $purchased): ?>
          <a class="ns-btn-primary" href="reader.php?novel_id=<?php echo $novel_id; ?>">
            <i class="fa-solid fa-book-open"></i> Read Now
          </a>
          <?php else: ?>
          <a class="ns-btn-primary" href="purchase.php?novel_id=<?php echo $novel_id; ?>">
            <i class="fa-solid fa-lock-open"></i> Purchase &amp; Read
          </a>
          <?php endif; ?>

          <form method="POST" style="margin:0;">
            <button type="submit" name="wishlist_action" value="1" class="ns-btn-outline">
              <i class="<?php echo $in_wishlist ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
              <?php echo $in_wishlist ? 'Saved' : 'Wishlist'; ?>
            </button>
          </form>

          <span class="ns-price-tag">
            <?php if ((float)$novel['price'] == 0): ?>
            Free
            <?php else: ?>
            ₹<?php echo number_format((float)$novel['price'], 2); ?>
            <?php endif; ?>
          </span>
        </div>
      </div>

    </div>
  </article>

  <section>
    <div class="ns-sec-title">
      <h2>Explore the Chapters</h2>
      <span><?php echo $total_chapters; ?> chapters</span>
    </div>

    <?php if ($first_chapter): ?>
    <div class="ns-chapter-banner">
      <div>
        <h3>
          Chapter <?php echo (int)$first_chapter['chapter_number']; ?>:
          <?php echo htmlspecialchars($first_chapter['chapter_title'], ENT_QUOTES, 'UTF-8'); ?>
        </h3>
        <p>Begin your journey with the first chapter.</p>
      </div>
      <a class="ns-btn-primary"
        href="reader.php?novel_id=<?php echo $novel_id; ?>&chapter=<?php echo (int)$first_chapter['chapter_number']; ?>">
        Start Reading
      </a>
    </div>
    <?php else: ?>
    <div class="empty-box">Chapters are not available yet.</div>
    <?php endif; ?>
  </section>

  <section id="rating">
    <div class="ns-sec-title">
      <h2>Rate This Novel</h2>
      <span>
        <?php echo $user_rating > 0
          ? 'You rated ' . $user_rating . '/5'
          : 'Your opinion matters'; ?>
      </span>
    </div>

    <div class="ns-rating-box">
      <div class="ns-rating-left">
        <div class="ns-rating-badge-icon">
          <i class="fas fa-star"></i>
        </div>
        <div>
          <strong>
            <?php echo $user_rating > 0
              ? 'Update your rating'
              : 'Enjoyed this novel?'; ?>
          </strong>
          <p style="margin:2px 0 0; font-size:13px; color:var(--text-secondary, #6b584b);">
            Share your experience with other readers.
          </p>
        </div>
      </div>

      <form method="POST" class="ns-rating-form" id="novelRatingForm">
        <input type="hidden" name="rating_action" value="1">

        <div class="ns-star-group">
          <?php for ($i = 1; $i <= 5; $i++): ?>
          <label class="ns-star-lbl <?php echo ($user_rating >= $i) ? 'is-selected' : ''; ?>"
            title="<?php echo $i; ?> out of 5 stars" data-val="<?php echo $i; ?>">
            <input type="radio" name="rating" value="<?php echo $i; ?>"
              <?php echo $user_rating === $i ? 'checked' : ''; ?> required>
            <i class="fas fa-star"></i>
          </label>
          <?php endfor; ?>
        </div>

        <button type="submit" class="ns-btn-primary">
          <i class="fas fa-star"></i>
          <?php echo $user_rating > 0 ? 'Update Rating' : 'Submit Rating'; ?>
        </button>
      </form>
    </div>
  </section>

  <?php if (count($related_books) > 0): ?>
  <section>
    <div class="ns-sec-title">
      <h2>More Like This</h2>
    </div>

    <div class="ns-grid-cards">
      <?php foreach ($related_books as $book): ?>
      <?php $is_rel_free = (float)$book['price'] == 0; ?>
      <article class="ns-card-item">
        <img src="Assets/Cover/<?php echo htmlspecialchars($book['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Cover">
        <div class="ns-card-info">
          <h3><?php echo htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <p><?php echo htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8'); ?></p>
          <p style="color:var(--warning, #c28b3c); font-weight:700;">
            ★ <?php echo number_format((float)$book['rating'], 1); ?>/5 •
            <span style="color:var(--primary-dark, #3e2723);">
              <?php echo $is_rel_free ? 'Free' : '₹' . number_format((float)$book['price'], 2); ?>
            </span>
          </p>
          <div class="ns-card-cta">
            <a href="novel.php?id=<?php echo (int)$book['novel_id']; ?>">View Book</a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section>
    <div class="ns-sec-title">
      <h2>More Books to Discover</h2>
    </div>

    <div class="ns-grid-cards">
      <?php while ($book = mysqli_fetch_assoc($more_books_result)): ?>
      <?php $is_more_free = (float)$book['price'] == 0; ?>
      <article class="ns-card-item">
        <img src="Assets/Cover/<?php echo htmlspecialchars($book['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Cover">
        <div class="ns-card-info">
          <h3><?php echo htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <p><?php echo htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8'); ?></p>
          <p style="color:var(--warning, #c28b3c); font-weight:700;">
            ★ <?php echo number_format((float)$book['rating'], 1); ?>/5 •
            <span style="color:var(--primary-dark, #3e2723);">
              <?php echo $is_more_free ? 'Free' : '₹' . number_format((float)$book['price'], 2); ?>
            </span>
          </p>
          <div class="ns-card-cta">
            <a href="novel.php?id=<?php echo (int)$book['novel_id']; ?>">View Book</a>
          </div>
        </div>
      </article>
      <?php endwhile; ?>
    </div>
  </section>

</div>

<script>
(function() {
  const labels = document.querySelectorAll('#novelRatingForm .ns-star-lbl');

  function paintStars(selectedVal) {
    labels.forEach(lbl => {
      const val = parseInt(lbl.getAttribute('data-val'), 10);
      lbl.classList.toggle('is-selected', selectedVal > 0 && val <= selectedVal);
    });
  }
  labels.forEach(lbl => {
    lbl.addEventListener('click', () => {
      const val = parseInt(lbl.getAttribute('data-val'), 10);
      paintStars(val);
    });
  });
})();
</script>

<?php include 'include/dashboard_end.php'; ?>