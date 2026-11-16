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
    LIMIT 4
");

$latest_result = mysqli_query($conn, "
    SELECT novel_id, title, author, cover_image, price, rating
    FROM novels
    ORDER BY created_at DESC, novel_id DESC
    LIMIT 4
");

$all_result = mysqli_query($conn, "
    SELECT novel_id, title, author, cover_image, price, rating
    FROM novels
    ORDER BY title ASC
");
?>

<div class="panel-page">

  <!-- highest rated -->
  <div class="section-title">
    <h2>Highest Rated</h2>
  </div>

  <div class="novel-grid">
    <?php while ($novel = mysqli_fetch_assoc($rating_result)): ?>

    <article class="novel-card">
      <img src="Assets/Cover/<?php echo ($novel['cover_image']); ?>" alt="Cover">

      <div class="novel-info">
        <h3><?php echo ($novel['title']); ?></h3>
        <p><?php echo ($novel['author']); ?></p>

        <span class="rating">
          <p class="rating">
            Rating: <?php echo $novel['rating']; ?> <?php echo (int)$novel['rating']; ?>/5
          </p>
        </span>

        <span class="<?php echo (float)$novel['price'] == 0 ? 'free' : 'price'; ?>">
          <?php echo (float)$novel['price'] == 0 ? 'Free' : '₹' . number_format($novel['price'], 2); ?>
        </span>

        <a class="card-link" href="novel.php?id=<?php echo (int)$novel['novel_id']; ?>">View Book</a>

      </div>
    </article>

    <?php endwhile; ?>
  </div>
  <br />

  <!-- latest -->
  <div class="section-title">
    <h2>Latest Books</h2>
  </div>

  <div class="novel-grid">
    <?php while ($novel = mysqli_fetch_assoc($latest_result)): ?>

    <article class="novel-card">
      <img src="Assets/Cover/<?php echo ($novel['cover_image']); ?>" alt="Cover">

      <div class="novel-info">
        <h3><?php echo ($novel['title']); ?></h3>
        <p><?php echo ($novel['author']); ?></p>

        <span class="rating">
          <p class="rating">
            Rating: <?php echo $novel['rating']; ?> <?php echo (int)$novel['rating']; ?>/5
          </p>
        </span>

        <span class="<?php echo (float)$novel['price'] == 0 ? 'free' : 'price'; ?>">
          <?php echo (float)$novel['price'] == 0 ? 'Free' : '₹' . number_format($novel['price'], 2); ?>
        </span>

        <a class="card-link" href="novel.php?id=<?php echo (int)$novel['novel_id']; ?>">View Book</a>

      </div>
    </article>

    <?php endwhile; ?>
  </div>

  <!-- all novels -->
  <div class="section-title">
    <h2>All Novels</h2>
  </div>

  <div class="novel-grid">
    <?php while ($novel = mysqli_fetch_assoc($all_result)): ?>

    <article class="novel-card">
      <img src="Assets/Cover/<?php echo ($novel['cover_image']); ?>" alt="Cover">

      <div class="novel-info">
        <h3><?php echo ($novel['title']); ?></h3>
        <p><?php echo ($novel['author']); ?></p>

        <span class="rating">
          <p class="rating">
            Rating: <?php echo $novel['rating']; ?> <?php echo (int)$novel['rating']; ?>/5
          </p>
        </span>

        <span class="<?php echo (float)$novel['price'] == 0 ? 'free' : 'price'; ?>">
          <?php echo (float)$novel['price'] == 0 ? 'Free' : '₹' . number_format($novel['price'], 2); ?>
        </span>

        <a class="card-link" href="novel.php?id=<?php echo (int)$novel['novel_id']; ?>">View Book</a>

      </div>
    </article>

    <?php endwhile; ?>
  </div>

</div>

<?php if ($standalone_page) { include 'include/dashboard_end.php'; } ?>
