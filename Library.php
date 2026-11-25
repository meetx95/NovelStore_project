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
    ORDER BY novel_id ASC
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