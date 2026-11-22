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

<style>
.ns-home-wrap {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.ns-welcome-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  flex-wrap: wrap;
}

.ns-welcome-strip h1 {
  margin: 0;
  font-family: var(--font-heading, "Playfair Display", serif);
  font-size: clamp(24px, 2.6vw, 34px);
  color: var(--primary-dark, #3e2723);
}

.ns-welcome-strip p {
  margin: 2px 0 0;
  font-size: 14px;
  color: var(--text-secondary, #6b584b);
}

.ns-pill-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.ns-pill-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 999px;
  background: var(--surface, #fffdf8);
  border: 1px solid var(--border-color, #dfd0c0);
  color: var(--text-secondary, #6b584b);
  font-size: 12.5px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
}

.ns-pill-chip.active,
.ns-pill-chip:hover {
  background: var(--primary, #6f4e37);
  border-color: var(--primary, #6f4e37);
  color: #ffffff;
}

/* Main Spotlight + Left Rail Grid (inspired by homepage.jpg) */
.ns-spotlight-grid {
  display: grid;
  grid-template-columns: 270px minmax(0, 1fr);
  gap: 18px;
  align-items: stretch;
}

.ns-side-panel {
  background: var(--surface, #fffdf8);
  border: 1px solid var(--border-color, #dfd0c0);
  border-radius: 22px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  box-shadow: var(--shadow-sm, 0 2px 8px rgba(62, 39, 35, 0.07));
}

.ns-side-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 13px;
  font-weight: 700;
  color: var(--primary-dark, #3e2723);
  padding: 0 2px;
}

.ns-side-heading small {
  font-weight: 500;
  color: var(--text-muted, #9a8879);
}

.ns-mini-stack {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.ns-mini-card {
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 8px;
  border-radius: 14px;
  background: var(--bg-light, #fbf8f3);
  border: 1px solid var(--border-light, #eee5db);
  text-decoration: none;
  color: var(--text, #2c211b);
  transition: transform 0.18s ease, border-color 0.18s ease;
}

.ns-mini-card:hover {
  transform: translateX(3px);
  border-color: var(--secondary, #b08968);
}

.ns-mini-card img {
  width: 46px;
  height: 62px;
  border-radius: 9px;
  object-fit: cover;
  flex-shrink: 0;
}

.ns-mini-meta {
  min-width: 0;
  flex: 1;
}

.ns-mini-meta strong {
  display: block;
  font-size: 13px;
  font-weight: 700;
  color: var(--primary-dark, #3e2723);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ns-mini-meta span {
  display: block;
  font-size: 11.5px;
  color: var(--text-secondary, #6b584b);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ns-mini-dot {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2px solid var(--primary, #6f4e37);
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.ns-mini-dot::after {
  content: "";
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--primary, #6f4e37);
}

/* Hero Featured Banner */
.ns-hero-banner {
  position: relative;
  min-height: 360px;
  border-radius: 22px;
  overflow: hidden;
  background: #221611;
  border: 1px solid var(--border-color, #dfd0c0);
  display: flex;
  align-items: flex-end;
  padding: clamp(22px, 3.5vw, 38px);
  color: #ffffff;
  box-shadow: var(--shadow-md, 0 8px 24px rgba(62, 39, 35, 0.11));
}

.ns-hero-backdrop {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center 25%;
  filter: brightness(0.62) saturate(1.1);
}

.ns-hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, rgba(20, 13, 9, 0.92) 0%, rgba(20, 13, 9, 0.62) 55%, rgba(20, 13, 9, 0.25) 100%);
}

.ns-hero-content {
  position: relative;
  z-index: 2;
  max-width: 540px;
}

.ns-hero-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 999px;
  background: rgba(214, 191, 174, 0.18);
  border: 1px solid rgba(214, 191, 174, 0.35);
  color: #f5efe6;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.6px;
  margin-bottom: 12px;
}

.ns-hero-content h2 {
  margin: 0 0 8px;
  font-family: var(--font-heading, "Playfair Display", serif);
  font-size: clamp(28px, 3.6vw, 44px);
  line-height: 1.1;
  color: #ffffff;
}

.ns-hero-author {
  font-size: 13px;
  color: #d6bfae;
  margin-bottom: 10px;
  font-weight: 500;
}

.ns-hero-desc {
  margin: 0 0 20px;
  font-size: 13.5px;
  line-height: 1.6;
  color: rgba(251, 248, 243, 0.82);
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.ns-hero-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.ns-btn-light {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 999px;
  background: #ffffff;
  color: var(--primary-dark, #3e2723);
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.18s ease, background 0.18s ease;
}

.ns-btn-light:hover {
  transform: translateY(-2px);
  background: #f5efe6;
}

.ns-btn-glass {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.14);
  border: 1px solid rgba(255, 255, 255, 0.25);
  color: #ffffff;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  backdrop-filter: blur(8px);
  transition: background 0.18s ease;
}

.ns-btn-glass:hover {
  background: rgba(255, 255, 255, 0.24);
}

/* Continue Reading Strip */
.ns-continue-box {
  background: var(--surface, #fffdf8);
  border: 1px solid var(--border-color, #dfd0c0);
  border-radius: 20px;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  flex-wrap: wrap;
  box-shadow: var(--shadow-sm, 0 2px 8px rgba(62, 39, 35, 0.07));
}

.ns-continue-left {
  display: flex;
  align-items: center;
  gap: 16px;
  min-width: 0;
}

.ns-continue-left img {
  width: 62px;
  height: 86px;
  border-radius: 10px;
  object-fit: cover;
  flex-shrink: 0;
}

.ns-continue-meta h3 {
  margin: 0 0 3px;
  font-family: var(--font-heading, "Playfair Display", serif);
  font-size: 18px;
  color: var(--primary-dark, #3e2723);
}

.ns-continue-meta p {
  margin: 0 0 3px;
  font-size: 13px;
  color: var(--text-secondary, #6b584b);
}

.ns-continue-meta small {
  font-size: 11.5px;
  color: var(--text-muted, #9a8879);
}

/* Cover Grid Section */
.ns-sec-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
}

.ns-sec-header h2 {
  margin: 0;
  font-family: var(--font-heading, "Playfair Display", serif);
  font-size: 22px;
  color: var(--primary-dark, #3e2723);
}

.ns-sec-link {
  font-size: 12.5px;
  font-weight: 700;
  color: var(--primary, #6f4e37);
  text-decoration: none;
}

.ns-sec-link:hover {
  text-decoration: underline;
}

.ns-cover-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
  gap: 18px;
}

.ns-poster-card {
  background: var(--surface, #fffdf8);
  border: 1px solid var(--border-color, #dfd0c0);
  border-radius: 18px;
  padding: 9px;
  display: flex;
  flex-direction: column;
  transition: transform 0.22s ease, box-shadow 0.22s ease;
  box-shadow: var(--shadow-sm, 0 2px 8px rgba(62, 39, 35, 0.07));
}

.ns-poster-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md, 0 8px 24px rgba(62, 39, 35, 0.11));
}

.ns-poster-card img {
  width: 100%;
  aspect-ratio: 3 / 4;
  object-fit: cover;
  border-radius: 12px;
}

.ns-poster-body {
  padding: 10px 4px 4px;
  display: flex;
  flex-direction: column;
  flex: 1;
  gap: 5px;
}

.ns-poster-body h3 {
  margin: 0;
  font-family: var(--font-heading, "Playfair Display", serif);
  font-size: 15px;
  color: var(--primary-dark, #3e2723);
  line-height: 1.3;
}

.ns-poster-body p {
  margin: 0;
  font-size: 12px;
  color: var(--text-secondary, #6b584b);
}

.ns-poster-foot {
  margin-top: auto;
  padding-top: 6px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 6px;
  font-size: 12px;
}

.ns-rate-pill {
  color: var(--warning, #c28b3c);
  font-weight: 700;
}

.ns-price-pill {
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
}

.ns-price-pill.is-free {
  background: #e9f0e6;
  color: var(--success, #607d5b);
}

.ns-price-pill.is-paid {
  background: var(--surface-2, #f0e6da);
  color: var(--primary-dark, #3e2723);
}

.ns-poster-cta {
  margin-top: 8px;
  display: block;
  text-align: center;
  padding: 8px 12px;
  border-radius: 10px;
  background: var(--primary, #6f4e37);
  color: #ffffff;
  font-size: 12.5px;
  font-weight: 700;
  text-decoration: none;
  transition: background 0.18s ease;
}

.ns-poster-cta:hover {
  background: var(--primary-dark, #3e2723);
}

@media (max-width: 900px) {
  .ns-spotlight-grid {
    grid-template-columns: 1fr;
  }
}
</style>

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