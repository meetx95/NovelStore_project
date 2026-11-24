<?php
$standalone_page = basename($_SERVER['SCRIPT_FILENAME']) !== 'Dashboard.php';
if ($standalone_page) {
  $layout_page = 'Home';
  include 'include/dashboard_start.php';
}

$continue = null;
$stmt = mysqli_prepare($conn, "
    SELECT n.novel_id, n.title, n.author, n.cover_image,
           c.chapter_id, c.chapter_number, c.chapter_title, r.last_read_at
    FROM reading_history r
    INNER JOIN novels n ON r.novel_id = n.novel_id
    INNER JOIN chapters c ON r.chapter_id = c.chapter_id
    WHERE r.user_id = ?
    ORDER BY r.last_read_at DESC
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$continue = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$featured_rows = [];
$result = mysqli_query($conn, "
    SELECT novel_id, title, author, description, cover_image, price, rating
    FROM novels
    ORDER BY rating DESC, novel_id DESC
    LIMIT 8
");
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $featured_rows[] = $row;
  }
}

$hero_novel = $featured_rows[0] ?? null;
$side_novels = array_slice($featured_rows, 0, 3);
?>



<div class="home ns-home-wrap">

  <section class="ns-welcome-strip">
    <div>
      <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Reader', ENT_QUOTES, 'UTF-8'); ?></h1>
      <p>What would you like to read today?</p>
    </div>

    <div class="ns-pill-row">
      <a href="Dashboard.php?page=Home" class="ns-pill-chip active">Featured</a>
      <a href="Dashboard.php?page=Library" class="ns-pill-chip">Library</a>
      <a href="Dashboard.php?page=Wishlist" class="ns-pill-chip">Wishlist</a>
      <a href="Dashboard.php?page=History" class="ns-pill-chip">History</a>
    </div>
  </section>

  <!-- Spotlight Grid: Top Picks Rail + Hero Banner -->
  <section class="ns-spotlight-grid">
    <aside class="ns-side-panel">
      <div class="ns-side-heading">
        <span>Top Picks</span>
        <small>Rated</small>
      </div>

      <div class="ns-mini-stack">
        <?php foreach ($side_novels as $item): ?>
        <a href="novel.php?id=<?php echo (int)$item['novel_id']; ?>" class="ns-mini-card">
          <img src="Assets/Cover/<?php echo htmlspecialchars($item['cover_image'], ENT_QUOTES, 'UTF-8'); ?>"
            alt="Book cover">
          <div class="ns-mini-meta">
            <strong><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
            <span><?php echo htmlspecialchars($item['author'], ENT_QUOTES, 'UTF-8'); ?> • ★
              <?php echo number_format((float)$item['rating'], 1); ?></span>
          </div>
          <span class="ns-mini-dot" aria-hidden="true"></span>
        </a>
        <?php endforeach; ?>
      </div>
    </aside>

    <?php if ($hero_novel): ?>
    <div class="ns-hero-banner">
      <img src="Assets/Cover/<?php echo htmlspecialchars($hero_novel['cover_image'], ENT_QUOTES, 'UTF-8'); ?>"
        alt="Featured novel cover" class="ns-hero-backdrop">
      <div class="ns-hero-overlay"></div>
      <div class="ns-hero-content">
        <span class="ns-hero-tag">
          <i class="fa-solid fa-fire"></i> Trending Novel • ★
          <?php echo number_format((float)$hero_novel['rating'], 1); ?>/5
        </span>
        <h2><?php echo htmlspecialchars($hero_novel['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <div class="ns-hero-author">By <?php echo htmlspecialchars($hero_novel['author'], ENT_QUOTES, 'UTF-8'); ?></div>
        <p class="ns-hero-desc">
          <?php echo htmlspecialchars($hero_novel['description'] ?? 'Dive into curated chapters and build your reading collection.', ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <div class="ns-hero-actions">
          <a href="novel.php?id=<?php echo (int)$hero_novel['novel_id']; ?>" class="ns-btn-light">
            <i class="fa-solid fa-play"></i> View Book
          </a>
          <a href="Dashboard.php?page=Library" class="ns-btn-glass">
            <i class="fa-solid fa-book-open"></i> Browse Library
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </section>

  <!-- Continue Reading Strip -->
  <section class="continue-reading">
    <div class="ns-sec-header">
      <h2>Continue Reading</h2>
    </div>

    <?php if ($continue): ?>
    <div class="continue-card ns-continue-box">
      <div class="ns-continue-left">
        <img src="./Assets/Cover/<?php echo htmlspecialchars($continue['cover_image'], ENT_QUOTES, 'UTF-8'); ?>"
          alt="Book cover">
        <div class="ns-continue-meta continue-info">
          <h3><?php echo htmlspecialchars($continue['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <p>By <?php echo htmlspecialchars($continue['author'], ENT_QUOTES, 'UTF-8'); ?></p>
          <p>
            Chapter <?php echo (int)$continue['chapter_number']; ?> -
            <?php echo htmlspecialchars($continue['chapter_title'], ENT_QUOTES, 'UTF-8'); ?>
          </p>
          <small>Last read: <?php echo htmlspecialchars($continue['last_read_at'], ENT_QUOTES, 'UTF-8'); ?></small>
        </div>
      </div>

      <a class="read-btn"
        href="reader.php?novel_id=<?php echo (int)$continue['novel_id']; ?>&chapter=<?php echo (int)$continue['chapter_number']; ?>">
        Continue Reading
      </a>
    </div>
    <?php else: ?>
    <div class="empty-box">
      <p>You haven't started reading anything yet.</p>
      <a href="Dashboard.php?page=Library" class="ns-sec-link">Browse Library</a>
    </div>
    <?php endif; ?>
  </section>

  <!-- Popular Novels Grid -->
  <section>
    <div class="ns-sec-header">
      <h2>Popular Novels</h2>
      <a href="Dashboard.php?page=Library" class="ns-sec-link">See All &rsaquo;</a>
    </div>

    <div class="ns-cover-grid">
      <?php foreach ($featured_rows as $novel): ?>
      <?php $is_free = (float)$novel['price'] == 0; ?>
      <article class="novel-card ns-poster-card">
        <img src="Assets/Cover/<?php echo htmlspecialchars($novel['cover_image'], ENT_QUOTES, 'UTF-8'); ?>"
          alt="Book cover">
        <div class="ns-poster-body">
          <h3><?php echo htmlspecialchars($novel['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <p><?php echo htmlspecialchars($novel['author'], ENT_QUOTES, 'UTF-8'); ?></p>
          <div class="ns-poster-foot">
            <span class="ns-rate-pill">★ <?php echo number_format((float)$novel['rating'], 1); ?>/5</span>
            <span class="ns-price-pill <?php echo $is_free ? 'is-free' : 'is-paid'; ?>">
              <?php echo $is_free ? 'Free' : '₹' . number_format((float)$novel['price'], 2); ?>
            </span>
          </div>
          <a class="ns-poster-cta" href="novel.php?id=<?php echo (int)$novel['novel_id']; ?>">View Book</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

</div>

<?php if ($standalone_page) { include 'include/dashboard_end.php'; } ?>