<?php
session_start();
if (isset($_SESSION['user_id'])) {
  header("Location: Dashboard.php");
  exit();
}
?>
<!doctype html>
<html lang="en">

<head>
  <title>NOVELSTORE</title>
  <?php include 'include/Links.php'; ?>
</head>

<body class="novel-showcase-body">
  <!-- <div class="top-ticker" aria-label="NovelStore announcements">
    <div class="ticker-track">
      <?php //for ($i = 0; $i < 4; $i++) : 
      ?>
      <span>YOUR NEXT OBSESSION IS A PAGE AWAY <b>✦</b> NEW STORIES, NEW WORLDS <b>✦</b> READ AT YOUR OWN PACE
        <b>✦</b></span>
      <?php //endfor; 
      ?>
    </div>
  </div> -->

  <header class="grid-navbar">
    <a href="index.php" class="nav-brand" aria-label="NovelStore home"><span
        class="nav-mark">N</span><span>NOVELSTORE</span></a>
    <nav class="nav-links" aria-label="Main navigation">
      <a class="grid-nav-cell active" href="index.php">Home</a>
      <a class="grid-nav-cell" href="Login_register.php">Library</a>
    </nav>
    <div class="nav-actions">
      <a href="Login_register.php" class="nav-login">Log in</a>
      <a href="Login_register.php#register" class="nav-register">Join now
      </a>
    </div>
  </header>

  <main class="hero-showcase">
    <div class="hero-orb orb-one"></div>
    <div class="hero-orb orb-two"></div>
    <div class="brand-lockup">
      <div class="brand-emblem" aria-hidden="true"><span></span><span></span><span></span></div>
      <div class="brand-title-row"><span class="brand-ring" aria-hidden="true"></span><span>NOVELSTORE</span></div>
      <div class="brand-kicker">A WORLD BETWEEN THE PAGES</div>
    </div>

    <section class="coverflow" aria-label="Featured books carousel">
      <button class="slider-arrow slider-prev" type="button" aria-label="Previous books">‹</button>
      <div class="slider-track" id="novelSlider">
        <article class="slider-card"><img src="Assets/Cover/cover5.jpg" alt="Featured novel cover" loading="lazy"><span
            class="panel-caption">DARK FANTASY</span></article>
        <article class="slider-card"><img src="Assets/Cover/cover33.jpg" alt="Featured novel cover" loading="lazy">
        </article>
        <article class="slider-card"><img src="Assets/Cover/cover10.jpg" alt="Featured novel cover" loading="lazy">
        </article>
        <article class="slider-card"><img src="Assets/Cover/cover17.jpg" alt="Featured novel cover" loading="lazy"><span
            class="panel-caption">EDITOR'S PICK</span></article>
        <article class="slider-card"><img src="Assets/Cover/cover41.jpg" alt="Featured novel cover" loading="lazy">
        </article>
        <article class="slider-card"><img src="Assets/Cover/cover38.jpg" alt="Featured novel cover" loading="lazy">
        </article>
        <article class="slider-card"><img src="Assets/Cover/cover30.jpg" alt="Featured novel cover" loading="lazy"><span
            class="panel-caption">SCI-FI</span></article>
      </div>
      <button class="slider-arrow slider-next" type="button" aria-label="Next books">›</button>
      <div class="slider-dots" id="sliderDots" aria-label="Carousel position"></div>
    </section>

    <div class="hero-bottom">
      <div class="hero-copy"><span class="hero-eyebrow">TURN THE PAGE</span>
        <h1>Find your <em>next world.</em></h1>
        <p>Stories that stay with you.</p>
      </div>
      <div class="hero-cta-group"><a href="Login_register.php" class="cta-primary-glow">Start reading
          <span>↗</span></a><a href="Login_register.php#register" class="cta-secondary-dark">Explore collection</a>
      </div>
    </div>
  </main>

  <footer class="showcase-footer">
    <span>MADE FOR LOVE OF STORIES</span>
  </footer>

  <script>
  (() => {
    const track = document.getElementById('novelSlider');
    if (!track) return;
    const cards = [...track.querySelectorAll('.slider-card')];
    const dotsWrap = document.getElementById('sliderDots');
    const prev = document.querySelector('.slider-prev');
    const next = document.querySelector('.slider-next');
    let active = 0,
      timer, startX = 0;
    const mod = (n) => (n + cards.length) % cards.length;

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
        if (offset > cards.length / 2) offset -= cards.length;
        if (offset < -cards.length / 2) offset += cards.length;
        const abs = Math.abs(offset);
        card.style.setProperty('--offset', offset);
        card.style.setProperty('--abs', abs);
        card.classList.toggle('is-active', offset === 0);
        card.style.opacity = abs > 3 ? '0' : String(1 - abs * .17);
        card.style.pointerEvents = abs > 2 ? 'none' : 'auto';
        dots[i].classList.toggle('active', i === active);
      });
    }

    function step(dir) {
      active = mod(active + dir);
      render();
      restart();
    }

    function restart() {
      clearInterval(timer);
      timer = setInterval(() => {
        active = mod(active + 1);
        render();
      }, 4500);
    }
    prev.addEventListener('click', () => step(-1));
    next.addEventListener('click', () => step(1));
    track.addEventListener('pointerdown', e => {
      startX = e.clientX;
    });
    track.addEventListener('pointerup', e => {
      const dx = e.clientX - startX;
      if (Math.abs(dx) > 45) step(dx < 0 ? 1 : -1);
    });
    document.querySelector('.coverflow').addEventListener('mouseenter', () => clearInterval(timer));
    document.querySelector('.coverflow').addEventListener('mouseleave', restart);
    render();
    restart();
  })();
  </script>
</body>

</html>