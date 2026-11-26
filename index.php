<?php
session_start();
if (isset($_SESSION['user_id'])) {
  header("Location: Dashboard.php");
  exit();
}
require_once 'config/db.php';
$sql = "SELECT * FROM novels ORDER BY rating DESC LIMIT 10";
$result = mysqli_query($conn, $sql);
if (!$result) {
  die("Database query failed.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>NOVELSTORE</title>
  <?php include 'include/Links.php'; ?>
</head>

<body class="novel-showcase-body">
  <header class="grid-navbar">
    <a href="index.php" class="nav-brand" aria-label="NovelStore home">
      <span class="nav-mark">N</span>
      <span>NOVELSTORE</span>
    </a>

    <nav class="nav-links" aria-label="Main navigation">
      <a class="grid-nav-cell active" href="index.php">Home</a>
      <a class="grid-nav-cell" href="Login_register.php">Library</a>
    </nav>

    <div class="nav-actions">
      <a href="Login_register.php" class="nav-login">Log in</a>
      <a href="Login_register.php#register" class="nav-register">Join now</a>
    </div>
  </header>

  <main class="hero-showcase">
    <div class="hero-orb orb-one"></div>
    <div class="hero-orb orb-two"></div>

    <div class="brand-lockup">
      <div class="brand-title-row">
        <span class="brand-ring" aria-hidden="true"></span>
        <span>NOVELSTORE</span>
      </div>
      <div class="brand-kicker">A WORLD BETWEEN THE PAGES</div>
    </div>

    <section class="coverflow" aria-label="Featured books carousel">

      <button class="slider-arrow slider-prev" type="button" aria-label="Previous book">
        <i class="fa-solid fa-caret-left"></i>
      </button>

      <div class="slider-track" id="novelSlider">
        <?php while ($novel = mysqli_fetch_assoc($result)): ?>

        <article class="slider-card">
          <a href="novel.php?id=<?= (int)$novel['novel_id'] ?>" class="slider-card-link">
            <img src="Assets/Cover/<?= rawurlencode(basename($novel['cover_image'])) ?>"
              alt="<?= htmlspecialchars($novel['title'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">

            <span class="panel-caption">
              <?= htmlspecialchars($novel['title'], ENT_QUOTES, 'UTF-8') ?>
            </span>
          </a>
        </article>

        <?php endwhile; ?>
      </div>

      <button class="slider-arrow slider-next" type="button" aria-label="Next book">
        <i class="fa-solid fa-caret-right"></i>
      </button>

      <div class="slider-dots" id="sliderDots"></div>

    </section>

    <div class="hero-bottom">
      <div class="hero-copy">
        <span class="hero-eyebrow">TURN THE PAGE</span>
        <h1>Find your <em>Next World.</em></h1>
        <p>Stories that stay with you.</p>
      </div>

      <div class="hero-cta-group">
        <a href="Login_register.php" class="cta-primary-glow">Start reading</a>
        <a href="Login_register.php#register" class="cta-secondary-dark">Explore collection</a>
      </div>
    </div>
  </main>

  <footer class="showcase-footer">
    <span>MADE FOR LOVE OF STORIES</span>
  </footer>

  <script>
  (() => {
    const slider = document.querySelector('.coverflow');
    const track = document.getElementById('novelSlider');
    const dotsWrap = document.getElementById('sliderDots');

    const cards = [...track.querySelectorAll('.slider-card')];
    const total = cards.length;

    if (!total) {
      slider.style.display = 'none';
      return;
    }

    let active = 0;
    let timer;
    let startX = 0;
    let pointerDown = false;

    const mod = n => (n + total) % total;
    const visibleRange = 3;

    cards.forEach((_, i) => {
      const dot = document.createElement('button');

      dot.type = 'button';
      dot.setAttribute('aria-label', `Show book ${i + 1}`);

      dot.addEventListener('click', () => {
        active = i;
        render();
        restart();
      });

      dotsWrap.appendChild(dot);
    });

    const dots = [...dotsWrap.children];

    function render() {
      cards.forEach((card, i) => {
        let offset = i - active;

        if (offset > total / 2) offset -= total;
        if (offset < -total / 2) offset += total;

        const abs = Math.abs(offset);

        card.style.setProperty('--offset', offset);
        card.style.setProperty('--abs', abs);

        card.classList.toggle('is-active', offset === 0);

        card.style.visibility = abs > visibleRange ? 'hidden' : 'visible';
        card.style.opacity = abs > visibleRange ? '0' : String(1 - abs * 0.17);
        card.style.pointerEvents = abs <= 1 ? 'auto' : 'none';
        card.style.zIndex = abs > visibleRange ? '0' : String(10 - abs);

        dots[i].classList.toggle('active', i === active);
      });
    }

    function step(direction) {
      active = mod(active + direction);
      render();
      restart();
    }

    function restart() {
      clearInterval(timer);
      if (total > 1) {
        timer = setInterval(() => {
          active = mod(active + 1);
          render();
        }, 4500);
      }
    }
    slider.querySelector('.slider-prev').addEventListener('click', () => step(-1));
    slider.querySelector('.slider-next').addEventListener('click', () => step(1));
    track.addEventListener('pointerdown', e => {
      startX = e.clientX;
      pointerDown = true;
    });
    track.addEventListener('pointerup', e => {
      if (!pointerDown) return;
      pointerDown = false;
      const dx = e.clientX - startX;
      if (Math.abs(dx) > 45) {
        step(dx < 0 ? 1 : -1);
      }
    });
    track.addEventListener('pointercancel', () => {
      pointerDown = false;
    });
    slider.addEventListener('mouseenter', () => clearInterval(timer));
    slider.addEventListener('mouseleave', restart);
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        clearInterval(timer);
      } else if (!slider.matches(':hover')) {
        restart();
      }
    });
    render();
    restart();
  })();
  </script>
</body>

</html>