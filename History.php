<?php
$standalone_page = basename($_SERVER['SCRIPT_FILENAME']) !== 'Dashboard.php';
if ($standalone_page) {
  $layout_page = 'History';
  include 'include/dashboard_start.php';
}

$stmt = mysqli_prepare($conn, "
    SELECT n.novel_id, n.title, n.author, n.cover_image,
           c.chapter_number, c.chapter_title, r.last_read_at
    FROM reading_history r
    INNER JOIN novels n ON r.novel_id = n.novel_id
    INNER JOIN chapters c ON r.chapter_id = c.chapter_id
    WHERE r.user_id = ?
    ORDER BY r.last_read_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$history_count = mysqli_num_rows($result);
?>

<style>
.ns-hist-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.ns-hist-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.ns-hist-header h2 {
  margin: 0;
  font-family: var(--font-heading, "Playfair Display", serif);
  font-size: clamp(22px, 2.4vw, 30px);
  color: var(--primary-dark, #3e2723);
}

.ns-hist-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 999px;
  background: var(--surface, #fffdf8);
  border: 1px solid var(--border-color, #dfd0c0);
  color: var(--text-secondary, #6b584b);
  font-size: 12px;
  font-weight: 600;
}

.ns-hist-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.ns-hist-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  padding: 14px 18px;
  border-radius: 18px;
  background: var(--surface, #fffdf8);
  border: 1px solid var(--border-color, #dfd0c0);
  box-shadow: var(--shadow-sm, 0 2px 8px rgba(62, 39, 35, 0.07));
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.ns-hist-card:hover {
  transform: translateY(-3px);
  border-color: var(--secondary, #b08968);
  box-shadow: var(--shadow-md, 0 8px 24px rgba(62, 39, 35, 0.11));
}

.ns-hist-main {
  display: flex;
  align-items: center;
  gap: 16px;
  min-width: 0;
  flex: 1;
}

.ns-hist-cover {
  width: 60px;
  height: 84px;
  border-radius: 10px;
  object-fit: cover;
  flex-shrink: 0;
  border: 1px solid var(--border-light, #eee5db);
}

.ns-hist-info {
  min-width: 0;
  flex: 1;
}

.ns-hist-info h3 {
  margin: 0 0 4px;
  font-family: var(--font-heading, "Playfair Display", serif);
  font-size: 18px;
  color: var(--primary-dark, #3e2723);
  line-height: 1.3;
}

.ns-hist-chapter {
  margin: 0 0 6px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-secondary, #6b584b);
}

.ns-hist-meta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  color: var(--text-muted, #9a8879);
}

.ns-hist-cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 999px;
  background: var(--primary, #6f4e37);
  color: #ffffff;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  white-space: nowrap;
  flex-shrink: 0;
  transition: background 0.18s ease, transform 0.18s ease;
}

.ns-hist-cta:hover {
  background: var(--primary-dark, #3e2723);
  transform: translateY(-1px);
}

.ns-hist-empty {
  padding: 40px 24px;
  text-align: center;
  border-radius: 18px;
  background: var(--surface, #fffdf8);
  border: 1px dashed var(--border-color, #dfd0c0);
  color: var(--text-secondary, #6b584b);
}

.ns-hist-empty a {
  display: inline-block;
  margin-top: 12px;
  padding: 9px 18px;
  border-radius: 999px;
  background: var(--primary, #6f4e37);
  color: #ffffff;
  font-size: 12.5px;
  font-weight: 700;
  text-decoration: none;
}

.ns-hist-empty a:hover {
  background: var(--primary-dark, #3e2723);
}

@media (max-width: 650px) {
  .ns-hist-card {
    flex-direction: column;
    align-items: stretch;
    gap: 14px;
    padding: 14px;
  }

  .ns-hist-cta {
    width: 100%;
  }
}
</style>

<div class="panel-page ns-hist-page">

  <div class="ns-hist-header">
    <h2>Reading History</h2>
    <span class="ns-hist-badge">
      <i class="fa-solid fa-clock-rotate-left"></i>
      <?php echo (int)$history_count; ?> <?php echo $history_count === 1 ? 'Entry' : 'Entries'; ?>
    </span>
  </div>

  <div class="history-list ns-hist-list">
    <?php if ($history_count > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <div class="history-item ns-hist-card">
      <div class="ns-hist-main">
        <img src="Assets/Cover/<?php echo htmlspecialchars($row['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Cover"
          class="ns-hist-cover">
        <div class="ns-hist-info">
          <h3><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <p class="ns-hist-chapter">
            Chapter <?php echo (int)$row['chapter_number']; ?> -
            <?php echo htmlspecialchars($row['chapter_title'], ENT_QUOTES, 'UTF-8'); ?>
          </p>
          <small class="ns-hist-meta">
            <i class="fa-regular fa-clock"></i>
            <b>Last read:</b> <?php echo htmlspecialchars($row['last_read_at'], ENT_QUOTES, 'UTF-8'); ?>
          </small>
        </div>
      </div>

      <a class="primary-btn ns-hist-cta"
        href="reader.php?novel_id=<?php echo (int)$row['novel_id']; ?>&chapter=<?php echo (int)$row['chapter_number']; ?>">
        <i class="fa-solid fa-book-open"></i> Continue
      </a>
    </div>
    <?php endwhile; ?>
    <?php else: ?>
    <div class="empty-box ns-hist-empty">
      <p>No reading history yet.</p>
      <a href="Dashboard.php?page=Library">Browse Library</a>
    </div>
    <?php endif; ?>
  </div>

</div>

<?php mysqli_stmt_close($stmt); ?>
<?php
if ($standalone_page) {
  include 'include/dashboard_end.php';
}
?>