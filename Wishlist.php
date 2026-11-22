<?php
$standalone_page = basename($_SERVER['SCRIPT_FILENAME']) !== 'Dashboard.php';
if ($standalone_page) {
  $layout_page = 'Wishlist';
  include 'include/dashboard_start.php';
}

if (isset($_GET['remove'])) {
  $novel_id = filter_input(INPUT_GET, 'remove', FILTER_VALIDATE_INT);
  if ($novel_id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM wishlist WHERE user_id=? AND novel_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $novel_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }
  header("Location: Dashboard.php?page=Wishlist");
  exit();
}

$stmt = mysqli_prepare($conn, "
    SELECT n.novel_id, n.title, n.author, n.cover_image, n.price
    FROM wishlist w
    INNER JOIN novels n ON w.novel_id = n.novel_id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<style>
  .ns-wish-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .ns-wish-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  .ns-wish-head h2 {
    margin: 0;
    font-family: var(--font-heading, "Playfair Display", serif);
    font-size: clamp(22px, 2.4vw, 30px);
    color: var(--primary-dark, #3e2723);
  }

  .ns-wish-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
    gap: 18px;
  }

  .ns-wish-card {
    background: var(--surface, #fffdf8);
    border: 1px solid var(--border-color, #dfd0c0);
    border-radius: 18px;
    padding: 9px;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: var(--shadow-sm, 0 2px 8px rgba(62, 39, 35, 0.07));
  }

  .ns-wish-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md, 0 8px 24px rgba(62, 39, 35, 0.11));
  }

  .ns-wish-card img {
    width: 100%;
    aspect-ratio: 3 / 4;
    object-fit: cover;
    border-radius: 12px;
  }

  .ns-wish-body {
    padding: 10px 4px 4px;
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 5px;
  }

  .ns-wish-body h3 {
    margin: 0;
    font-family: var(--font-heading, "Playfair Display", serif);
    font-size: 15px;
    color: var(--primary-dark, #3e2723);
    line-height: 1.3;
  }

  .ns-wish-body p {
    margin: 0;
    font-size: 12px;
    color: var(--text-secondary, #6b584b);
  }

  .ns-wish-badge {
    margin-top: auto;
    padding-top: 6px;
  }

  .ns-wish-price {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
  }

  .ns-wish-price.is-free {
    background: #e9f0e6;
    color: var(--success, #607d5b);
  }

  .ns-wish-price.is-paid {
    background: var(--surface-2, #f0e6da);
    color: var(--primary-dark, #3e2723);
  }

  .ns-wish-actions {
    margin-top: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .ns-wish-view {
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

  .ns-wish-view:hover {
    background: var(--primary-dark, #3e2723);
  }

  .ns-wish-remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 6px 10px;
    border-radius: 10px;
    background: transparent;
    border: 1px solid #e6c6c2;
    color: var(--danger, #a94b45);
    font-size: 11.5px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.18s ease, color 0.18s ease;
  }

  .ns-wish-remove:hover {
    background: #fdf0ee;
  }
</style>

<div class="panel-page ns-wish-page">
  <div class="ns-wish-head">
    <h2>My Wishlist</h2>
    <a href="Dashboard.php?page=Library" class="ns-lib-tab">Explore More Books</a>
  </div>

  <div class="ns-wish-grid">
    <?php if (mysqli_num_rows($result)): ?>
      <?php while ($novel = mysqli_fetch_assoc($result)): ?>
        <?php $is_free = (float)$novel['price'] == 0; ?>
        <article class="novel-card ns-wish-card">
          <img src="Assets/Cover/<?php echo htmlspecialchars($novel['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Cover">
          <div class="ns-wish-body">
            <h3><?php echo htmlspecialchars($novel['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p><?php echo htmlspecialchars($novel['author'], ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="ns-wish-badge">
              <span class="ns-wish-price <?php echo $is_free ? 'is-free' : 'is-paid'; ?>">
                <?php echo $is_free ? 'Free' : '₹' . number_format((float)$novel['price'], 2); ?>
              </span>
            </div>
            <div class="ns-wish-actions">
              <a class="ns-wish-view" href="novel.php?id=<?php echo (int)$novel['novel_id']; ?>">View</a>
              <a class="ns-wish-remove" href="Dashboard.php?page=Wishlist&remove=<?php echo (int)$novel['novel_id']; ?>">
                <i class="fa-solid fa-trash-can"></i> Remove
              </a>
            </div>
          </div>
        </article>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-box">Your wishlist is empty. Open a book and add it here.</div>
    <?php endif; ?>
  </div>
</div>

<?php mysqli_stmt_close($stmt); ?>

<?php if ($standalone_page) {
  include 'include/dashboard_end.php';
} ?>