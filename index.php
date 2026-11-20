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
  <div class="showcase-shell">
    <div class="top-ticker" aria-label="NovelStore announcements">
      <div class="ticker-track">
        <?php for ($i = 0; $i < 4; $i++) : ?>
        <span>
          YOUR NEXT OBSESSION IS A PAGE AWAY
          <b>✦</b>
          NEW STORIES, NEW WORLDS
          <b>✦</b>
          READ AT YOUR OWN PACE
          <b>✦</b>
        </span>
        <?php endfor; ?>
      </div>
    </div>

    <header class="grid-navbar">
      <a href="index.php" class="nav-brand" aria-label="NovelStore home">
        <span class="nav-mark">N</span><span>NOVELSTORE</span>
      </a>
      <nav class="nav-links" aria-label="Main navigation">
        <a class="grid-nav-cell active" href="index.php">Home</a>
        <a class="grid-nav-cell" href="Login_register.php">Library</a>
        <a class="grid-nav-cell" href="#about">About</a>
      </nav>
      <div class="nav-actions">
        <a href="Login_register.php" class="nav-login">Log in</a>
        <a href="Login_register.php#register" class="nav-register">Join now <span>↗</span></a>
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

      <div class="panel-carousel" aria-label="Featured books">
        <div class="story-panel panel-wing-left">
          <img src="Assets/Cover/cover5.jpg" alt="Featured novel" loading="lazy">
          <span class="panel-caption">DARK FANTASY</span>
        </div>
        <div class="story-panel panel-inner-left">
          <img src="Assets/Cover/cover33.jpg" alt="Featured novel" loading="lazy">
        </div>
        <div class="story-panel panel-inner-left">
          <img src="Assets/Cover/cover10.jpg" alt="Featured novel" loading="lazy">
        </div>
        <div class="story-panel panel-center">
          <img src="Assets/Cover/cover17.jpg" alt="Featured novel" loading="lazy">
          <span class="panel-caption">EDITOR'S PICK</span>
        </div>
        <div class="story-panel panel-inner-right">
          <img src="Assets/Cover/cover41.jpg" alt="Featured novel" loading="lazy">
        </div>
        <div class="story-panel panel-inner-right">
          <img src="Assets/Cover/cover38.jpg" alt="Featured novel" loading="lazy">
        </div>
        <div class="story-panel panel-wing-right">
          <img src="Assets/Cover/cover30.jpg" alt="Featured novel" loading="lazy">
          <span class="panel-caption">SCI-FI</span>

        </div>
      </div>

      <div class="hero-bottom">
        <div class="hero-copy"><span class="hero-eyebrow">TURN THE PAGE</span>
          <h1>Find your <em>next world.</em></h1>
          <p>Stories that stay with you.</p>
        </div>
        <div class="hero-cta-group">
          <a href="Login_register.php" class="cta-primary-glow">Start reading <span>↗</span></a>
          <a href="Login_register.php#register" class="cta-secondary-dark">Explore collection</a>
        </div>
      </div>
    </main>

    <section id="about" class="about-showcase">
      <div class="about-mini"><span class="about-dot"></span> YOUR READING SPACE</div>
      <h2>Stories. <span>Unfold.</span></h2>
      <p>Discover. Save. Read.</p>
    </section>
    <footer class="showcase-footer">
      <span>© <?php echo date('Y'); ?> NOVELSTORE</span>
      <span>MADE FOR LOVE OF STORIES</span>
    </footer>
  </div>
</body>

</html>