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

<div class="book-page">

  <div class="section-title">
    <a href="Dashboard.php?page=Home" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>&nbsp; Back to Library
    </a>
  </div>

  <article class="book-detail-card">
    <div class="book-detail-main">

      <img class="book-detail-cover" src="Assets/Cover/<?php echo htmlspecialchars($novel['cover_image']); ?>"
        alt="<?php echo htmlspecialchars($novel['title']); ?>">

      <div class="book-detail-info">
        <?php if (!empty($novel['genre'])): ?>
        <span class="eyebrow"><?php echo htmlspecialchars($novel['genre']); ?></span>
        <?php endif; ?>

        <h1><?php echo htmlspecialchars($novel['title']); ?></h1>
        <p class="book-detail-author">By <?php echo htmlspecialchars($novel['author']); ?></p>

        <div class="book-rating-display">
          <span class="book-rating-stars">
            <?php
            $display_rating = $average_rating > 0 ? $average_rating : 0;
            for ($i = 1; $i <= 5; $i++) {
              echo $i <= round($display_rating) ? '★' : '☆';
            }
            ?>
          </span>
          <strong><?php echo number_format($display_rating, 1); ?>/5</strong>
          <span class="muted"><?php echo $total_ratings; ?> Ratings</span>
        </div>

        <p class="book-description">
          <?php echo nl2br(htmlspecialchars(trim($novel['description'] ?? ''))); ?>
        </p>

        <div class="book-meta-grid">
          <div class="book-meta-box">
            <strong><?php echo $total_chapters; ?></strong>
            <span>Chapters</span>
          </div>
          <div class="book-meta-box">
            <strong>Reading</strong>
            <span>Experience</span>
          </div>
          <div class="book-meta-box">
            <strong><?php echo $average_rating > 0 ? $average_rating : 'New'; ?></strong>
            <span>Reader Score</span>
          </div>
        </div>

        <div class="book-actions">
          <?php if ((float)$novel['price'] == 0 || $purchased): ?>
          <a class="primary-btn" href="reader.php?novel_id=<?php echo $novel_id; ?>">
            <i class="fa-solid fa-book-open"></i>&nbsp; Read Now
          </a>
          <?php else: ?>
          <a class="primary-btn" href="purchase.php?novel_id=<?php echo $novel_id; ?>">
            <i class="fa-solid fa-lock-open"></i>&nbsp; Purchase & Read
          </a>
          <?php endif; ?>

          <form method="POST">
            <button type="submit" name="wishlist_action" value="1" class="secondary-action dashboard-form-button">
              <i class="<?php echo $in_wishlist ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
              <?php echo $in_wishlist ? ' Saved' : ' Wishlist'; ?>
            </button>
          </form>
        </div>

        <div class="book-price">
          <?php if ((float)$novel['price'] == 0): ?>
          Free
          <?php else: ?>
          ₹<?php echo number_format((float)$novel['price'], 2); ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </article>

  <section class="book-section-block">
    <div class="section-title">
      <h2>Explore the Chapters</h2>
      <span class="muted"><?php echo $total_chapters; ?> chapters</span>
    </div>

    <?php if ($first_chapter): ?>
    <div class="history-item">
      <div>
        <h3>Chapter <?php echo (int)$first_chapter['chapter_number']; ?>:
          <?php echo htmlspecialchars($first_chapter['chapter_title']); ?></h3>
        <p>Begin your journey with the first chapter.</p>
      </div>
      <a class="primary-btn"
        href="reader.php?novel_id=<?php echo $novel_id; ?>&chapter=<?php echo (int)$first_chapter['chapter_number']; ?>">
        Start Reading
      </a>
    </div>
    <?php else: ?>
    <div class="empty-box">Chapters are not available yet.</div>
    <?php endif; ?>
  </section>


  <section class="book-section-block" id="rating">

    <div class="section-title">
      <h2>Rate This Novel</h2>
      <span class="muted">
        <?php echo $user_rating > 0
          ? 'You rated ' . $user_rating . '/5'
          : 'Your opinion matters'; ?>
      </span>
    </div>

    <div class="dashboard-rating-card">

      <div class="rating-information">
        <div class="rating-icon">
          <i class="fas fa-star"></i>
        </div>

        <div>
          <strong>
            <?php echo $user_rating > 0
              ? 'Update your rating'
              : 'Enjoyed this novel?'; ?>
          </strong>

          <p class="muted">
            Share your experience with other readers.
          </p>
        </div>
      </div>

      <form method="POST" class="dashboard-rating-form">

        <input type="hidden" name="rating_action" value="1">

        <div class="star-rating">
          <?php for ($i = 1; $i <= 5; $i++): ?>
          <label class="star-option" title="<?php echo $i; ?> out of 5 stars">
            <input type="radio" name="rating" value="<?php echo $i; ?>"
              <?php echo $user_rating === $i ? 'checked' : ''; ?> required>

            <i class="fas fa-star"></i>
          </label>
          <?php endfor; ?>
        </div>

        <button type="submit" class="primary-btn dashboard-form-button">
          <i class="fas fa-star"></i>
          <?php echo $user_rating > 0 ? 'Update Rating' : 'Submit Rating'; ?>
        </button>

      </form>

    </div>
  </section>


  <?php if (count($related_books) > 0): ?>
  <section class="book-section-block">
    <div class="section-title">
      <h2>More Like This</h2>
    </div>

    <div class="novel-grid">
      <?php foreach ($related_books as $book): ?>
      <article class="novel-card">
        <img src="Assets/Cover/<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Cover">
        <div class="novel-info">
          <h3><?php echo htmlspecialchars($book['title']); ?></h3>
          <p><?php echo htmlspecialchars($book['author']); ?></p>
          <p class="rating"><b>Rating:</b> <?php echo number_format((float)$book['rating'], 1); ?>/5</p>
          <span class="<?php echo (float)$book['price'] == 0 ? 'free' : 'price'; ?>">
            <?php echo (float)$book['price'] == 0 ? 'Free' : '₹' . number_format((float)$book['price'], 2); ?>
          </span>
          <a class="card-link" href="novel.php?id=<?php echo (int)$book['novel_id']; ?>">View Book</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="book-section-block">
    <div class="section-title">
      <h2>More Books to Discover</h2>
    </div>

    <div class="novel-grid">
      <?php while ($book = mysqli_fetch_assoc($more_books_result)): ?>
      <article class="novel-card">
        <img src="Assets/Cover/<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Cover">
        <div class="novel-info">
          <h3><?php echo htmlspecialchars($book['title']); ?></h3>
          <p><?php echo htmlspecialchars($book['author']); ?></p>
          <p class="rating"><b>Rating:</b> <?php echo number_format((float)$book['rating'], 1); ?>/5</p>
          <span class="<?php echo (float)$book['price'] == 0 ? 'free' : 'price'; ?>">
            <?php echo (float)$book['price'] == 0 ? 'Free' : '₹' . number_format((float)$book['price'], 2); ?>
          </span>
          <a class="card-link" href="novel.php?id=<?php echo (int)$book['novel_id']; ?>">View Book</a>
        </div>
      </article>
      <?php endwhile; ?>
    </div>
  </section>

</div>

<?php include 'include/dashboard_end.php'; ?>