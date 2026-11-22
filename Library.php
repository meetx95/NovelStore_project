<?php
$standalone_page = basename($_SERVER['SCRIPT_FILENAME']) !== 'Dashboard.php';
if ($standalone_page) {
  $layout_page = 'Library';
  include 'include/dashboard_start.php';
}

$rating_result = mysqli_query($conn, "
    SELECT novel_id, title, author, cover_image, price, rating
    FROM novels
    ORDER BY rating DESC, novel_id DESC
    LIMIT 14
");

$latest_result = mysqli_query($conn, "
    SELECT novel_id, title, author, cover_image, price, rating
    FROM novels
    ORDER BY created_at DESC, novel_id DESC
    LIMIT 7
");

$all_result = mysqli_query($conn, "
    SELECT novel_id, title, author, cover_image, price, rating
    FROM novels
    ORDER BY title ASC
");

function renderLibraryCard($novel)
{
  $is_free = (float)$novel['price'] == 0;
  $title = htmlspecialchars($novel['title'], ENT_QUOTES, 'UTF-8');
  $author = htmlspecialchars($novel['author'], ENT_QUOTES, 'UTF-8');
  $cover = htmlspecialchars($novel['cover_image'], ENT_QUOTES, 'UTF-8');
  $rating = number_format((float)$novel['rating'], 1);
  $price_label = $is_free ? 'Free' : '₹' . number_format((float)$novel['price'], 2);
  $price_class = $is_free ? 'is-free' : 'is-paid';
  $id = (int)$novel['novel_id'];

  echo <<<HTML
  <article class="novel-card ns-lib-card">
    <img src="Assets/Cover/{$cover}" alt="Cover">
    <div class="ns-lib-body">
      <h3>{$title}</h3>
      <p>{$author}</p>
      <div class="ns-lib-meta">
        <span class="ns-lib-rating">★ {$rating}/5</span>
        <span class="ns-lib-price {$price_class}">{$price_label}</span>
      </div>
      <a class="ns-lib-btn" href="novel.php?id={$id}">View Book</a>
    </div>
  </article>
HTML;
}
?>

<style>
.ns-lib-page {
  display: flex;
  flex-direction: column;
  gap: 28px;
}

.ns-lib-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.ns-lib-top h1 {
  margin: 0;
  font-family: var(--font-heading, "Playfair Display", serif);
  font-size: clamp(24px, 2.5vw, 32px);
  color: var(--primary-dark, #3e2723);
}

.ns-lib-tabs {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.ns-lib-tab {
  padding: 7px 15px;
  border-radius: 999px;
  background: var(--surface, #fffdf8);
  border: 1px solid var(--border-color, #dfd0c0);
  color: var(--text-secondary, #6b584b);
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.18s ease;
}

.ns-lib-tab:hover {
  background: var(--primary, #6f4e37);
  border-color: var(--primary, #6f4e37);
  color: #ffffff;
}

.ns-lib-sec-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

.ns-lib-sec-head h2 {
  margin: 0;
  font-family: var(--font-heading, "Playfair Display", serif);
  font-size: 21px;
  color: var(--primary-dark, #3e2723);
}

.ns-lib-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
  gap: 18px;
}

.ns-lib-card {
  background: var(--surface, #fffdf8);
  border: 1px solid var(--border-color, #dfd0c0);
  border-radius: 18px;
  padding: 9px;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  box-shadow: var(--shadow-sm, 0 2px 8px rgba(62, 39, 35, 0.07));
}

.ns-lib-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md, 0 8px 24px rgba(62, 39, 35, 0.11));
}

.ns-lib-card img {
  width: 100%;
  aspect-ratio: 3 / 4;
  object-fit: cover;
  border-radius: 12px;
}

.ns-lib-body {
  padding: 10px 4px 4px;
  display: flex;
  flex-direction: column;
  flex: 1;
  gap: 5px;
}

.ns-lib-body h3 {
  margin: 0;
  font-family: var(--font-heading, "Playfair Display", serif);
  font-size: 15px;
  color: var(--primary-dark, #3e2723);
  line-height: 1.3;
}

.ns-lib-body p {
  margin: 0;
  font-size: 12px;
  color: var(--text-secondary, #6b584b);
}

.ns-lib-meta {
  margin-top: auto;
  padding-top: 6px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12px;
}

.ns-lib-rating {
  color: var(--warning, #c28b3c);
  font-weight: 700;
}

.ns-lib-price {
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
}

.ns-lib-price.is-free {
  background: #e9f0e6;
  color: var(--success, #607d5b);
}

.ns-lib-price.is-paid {
  background: var(--surface-2, #f0e6da);
  color: var(--primary-dark, #3e2723);
}

.ns-lib-btn {
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

.ns-lib-btn:hover {
  background: var(--primary-dark, #3e2723);
}
</style>

<div class="panel-page ns-lib-page">

  <div class="ns-lib-top">
    <h1>Novel Library</h1>
    <div class="ns-lib-tabs">
      <a href="#highest-rated" class="ns-lib-tab">Highest Rated</a>
      <a href="#latest-books" class="ns-lib-tab">Latest Books</a>
      <a href="#all-novels" class="ns-lib-tab">All Novels</a>
    </div>
  </div>

  <!-- highest rated -->
  <section id="highest-rated">
    <div class="ns-lib-sec-head">
      <h2>Highest Rated</h2>
    </div>
    <div class="ns-lib-grid">
      <?php while ($novel = mysqli_fetch_assoc($rating_result)): ?>
      <?php renderLibraryCard($novel); ?>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- latest -->
  <section id="latest-books">
    <div class="ns-lib-sec-head">
      <h2>Latest Books</h2>
    </div>
    <div class="ns-lib-grid">
      <?php while ($novel = mysqli_fetch_assoc($latest_result)): ?>
      <?php renderLibraryCard($novel); ?>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- all novels -->
  <section id="all-novels">
    <div class="ns-lib-sec-head">
      <h2>All Novels</h2>
    </div>
    <div class="ns-lib-grid">
      <?php while ($novel = mysqli_fetch_assoc($all_result)): ?>
      <?php renderLibraryCard($novel); ?>
      <?php endwhile; ?>
    </div>
  </section>

</div>

<?php if ($standalone_page) {
  include 'include/dashboard_end.php';
} ?>