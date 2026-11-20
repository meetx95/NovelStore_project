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
  <style>
  body.novel-showcase-body {
    margin: 0;
    min-height: 100vh;
    background:
      radial-gradient(circle at 50% 42%,
        rgba(154, 107, 79, 0.22),
        transparent 46%),
      radial-gradient(circle at 50% 82%,
        rgba(111, 78, 55, 0.16),
        transparent 40%),
      linear-gradient(180deg, #16100d 0%, #1c1310 50%, #120c0a 100%);
    color: var(--bg-light, #fbf8f3);
    font-family: var(--font-body, "DM Sans", sans-serif);
    overflow-x: hidden;
  }

  .showcase-shell {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border: 1px solid rgba(214, 191, 174, 0.14);
  }

  .top-ticker {
    width: 100%;
    overflow: hidden;
    border-bottom: 1px solid rgba(214, 191, 174, 0.16);
    background: rgba(18, 12, 10, 0.75);
    padding: 7px 0;
    white-space: nowrap;
    user-select: none;
  }

  .ticker-track {
    display: inline-flex;
    gap: 36px;
    animation: tickerScroll 28s linear infinite;
    font-size: 10px;
    letter-spacing: 2.2px;
    text-transform: uppercase;
    color: rgba(245, 239, 230, 0.55);
  }

  @keyframes tickerScroll {
    0% {
      transform: translateX(0);
    }

    100% {
      transform: translateX(-50%);
    }
  }

  .grid-navbar {
    display: grid;
    grid-template-columns: 1fr repeat(4, minmax(110px, 150px)) 40px;
    border-bottom: 1px solid rgba(214, 191, 174, 0.16);
    min-height: 44px;
    align-items: stretch;
  }

  .grid-nav-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    border-right: 1px solid rgba(214, 191, 174, 0.16);
    padding: 0 18px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: rgba(245, 239, 230, 0.82);
    text-decoration: none;
    transition:
      background 0.2s ease,
      color 0.2s ease;
  }

  .grid-nav-cell:first-child {
    border-left: 1px solid rgba(214, 191, 174, 0.16);
    margin-left: 36px;
  }

  .grid-nav-cell:last-child {
    border-right: none;
  }

  .grid-nav-cell:hover,
  .grid-nav-cell.active {
    background: rgba(214, 191, 174, 0.08);
    color: #ffffff;
  }

  .hero-showcase {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 36px 0 48px;
    text-align: center;
    position: relative;
  }

  .brand-lockup {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    margin-bottom: 26px;
  }

  .brand-emblem {
    width: 24px;
    height: 24px;
    display: grid;
    grid-template-columns: repeat(2, 8px);
    gap: 3px;
    justify-content: center;
    align-content: center;
  }

  .brand-emblem span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #f5efe6;
  }

  .brand-emblem span:first-child {
    grid-column: 1 / -1;
    justify-self: center;
  }

  .brand-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: var(--font-heading, "Playfair Display", serif);
    font-size: 20px;
    font-weight: 600;
    letter-spacing: 1.5px;
    color: #fbf8f3;
  }

  .brand-ring {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2.5px solid #d6bfae;
    border-right-color: transparent;
    transform: rotate(-25deg);
  }

  .panel-carousel {
    width: 100%;
    max-width: 1480px;
    display: grid;
    grid-template-columns: 0.95fr repeat(5, 1fr) 0.95fr;
    align-items: center;
    gap: clamp(10px, 1.4vw, 18px);
    margin: 0 auto 30px;
    perspective: 1100px;
    overflow: hidden;
  }

  .story-panel {
    position: relative;
    height: clamp(210px, 26vw, 290px);
    border-radius: 14px;
    overflow: hidden;
    background: #2b1d17;
    border: 1px solid rgba(214, 191, 174, 0.22);
    box-shadow: 0 22px 45px rgba(0, 0, 0, 0.55);
    transition:
      transform 0.35s ease,
      box-shadow 0.35s ease;
  }

  .story-panel img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .panel-wing-left {
    height: clamp(260px, 32vw, 350px);
    border-radius: 0 16px 16px 0;
    border-left: none;
    transform: rotateY(26deg) scale(1.04);
    transform-origin: left center;
  }

  .panel-wing-right {
    height: clamp(260px, 32vw, 350px);
    border-radius: 16px 0 0 16px;
    border-right: none;
    transform: rotateY(-26deg) scale(1.04);
    transform-origin: right center;
  }

  .panel-inner-left {
    transform: rotateY(10deg);
  }

  .panel-center {
    transform: translateY(-4px) scale(0.98);
  }

  .panel-inner-right {
    transform: rotateY(-10deg);
  }

  .story-panel:hover {
    transform: translateY(-8px) scale(1.03);
    box-shadow: 0 28px 55px rgba(0, 0, 0, 0.72);
    z-index: 3;
  }

  .hero-copy {
    max-width: 540px;
    padding: 0 20px;
    margin: 0 auto 26px;
    font-size: 14px;
    line-height: 1.7;
    color: rgba(245, 239, 230, 0.68);
  }

  .hero-cta-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    flex-wrap: wrap;
  }

  .cta-primary-glow {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    padding: 0 24px;
    border-radius: 10px;
    background: linear-gradient(180deg, #3a261d 0%, #241712 100%);
    border: 1px solid rgba(214, 191, 174, 0.28);
    color: #fbf8f3;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 10px 26px rgba(154, 107, 79, 0.25);
    transition:
      transform 0.2s ease,
      border-color 0.2s ease;
  }

  .cta-primary-glow::after {
    content: "";
    position: absolute;
    bottom: -1px;
    left: 22%;
    right: 22%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #d6bfae, transparent);
  }

  .cta-primary-glow:hover {
    transform: translateY(-2px);
    border-color: rgba(214, 191, 174, 0.55);
  }

  .cta-secondary-dark {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    padding: 0 24px;
    border-radius: 10px;
    background: rgba(255, 253, 248, 0.04);
    border: 1px solid rgba(214, 191, 174, 0.14);
    color: rgba(245, 239, 230, 0.72);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition:
      background 0.2s ease,
      color 0.2s ease;
  }

  .cta-secondary-dark:hover {
    background: rgba(255, 253, 248, 0.09);
    color: #ffffff;
  }

  .about-showcase {
    max-width: 760px;
    margin: 0 auto;
    padding: 48px 24px 72px;
    text-align: center;
    border-top: 1px solid rgba(214, 191, 174, 0.12);
  }

  .about-showcase .section-label {
    font-size: 11px;
    letter-spacing: 2.4px;
    font-weight: 700;
    color: var(--secondary, #b08968);
    margin-bottom: 10px;
  }

  .about-showcase h2 {
    font-family: var(--font-heading, "Playfair Display", serif);
    font-size: clamp(28px, 3.6vw, 40px);
    color: #fbf8f3;
    margin-bottom: 14px;
  }

  .about-showcase h2 span {
    color: var(--secondary, #b08968);
  }

  .about-showcase p {
    color: rgba(245, 239, 230, 0.65);
    font-size: 15px;
    line-height: 1.8;
  }

  @media (max-width: 900px) {
    .grid-navbar {
      grid-template-columns: 1fr repeat(4, auto);
    }

    .grid-nav-cell:first-child {
      margin-left: 0;
      border-left: none;
    }

    .grid-nav-cell:last-child {
      display: none;
    }

    .panel-carousel {
      grid-template-columns: repeat(3, 1fr);
      padding: 0 16px;
    }

    .panel-wing-left,
    .panel-wing-right,
    .panel-inner-left,
    .panel-inner-right {
      display: none;
    }

    .panel-carousel .story-panel:nth-child(2),
    .panel-carousel .story-panel:nth-child(4),
    .panel-carousel .story-panel:nth-child(6) {
      display: block;
    }
  }

  @media (max-width: 600px) {
    .grid-navbar {
      grid-template-columns: repeat(3, 1fr);
    }

    .grid-nav-cell {
      padding: 10px 8px;
      font-size: 10px;
    }

    .panel-carousel {
      grid-template-columns: repeat(2, 1fr);
    }

    .panel-carousel .story-panel:nth-child(6) {
      display: none;
    }
  }
  </style>
</head>

<body class="novel-showcase-body">
  <div class="showcase-shell">
    <div class="top-ticker" aria-hidden="true">
      <div class="ticker-track">
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
        <span>DISCOVER NEW CHAPTERS WEEKLY</span>
      </div>
    </div>

    <header class="grid-navbar">
      <a href="index.php" class="grid-nav-cell active">HOME</a>
      <a href="Login_register.php" class="grid-nav-cell">LIBRARY</a>
      <a href="#about" class="grid-nav-cell">ABOUT</a>
      <a href="Login_register.php" class="grid-nav-cell">LOGIN</a>
      <a href="Login_register.php#register" class="grid-nav-cell">REGISTER</a>
      <div class="grid-nav-cell"></div>
    </header>

    <main class="hero-showcase">
      <div class="brand-lockup">
        <div class="brand-emblem" aria-hidden="true">
          <span></span>
          <span></span>
          <span></span>
        </div>
        <div class="brand-title-row">
          <span class="brand-ring" aria-hidden="true"></span>
          <span>NOVELSTORE</span>
        </div>
      </div>

      <div class="panel-carousel">
        <div class="story-panel panel-wing-left">
          <img src="Assets/Cover/cover4.jpg" alt="Featured novel cover" />
        </div>
        <div class="story-panel panel-inner-left">
          <img src="Assets/Cover/cover41.jpg" alt="Featured novel cover" />
        </div>
        <div class="story-panel">
          <img src="Assets/Cover/cover67.jpg" alt="Featured novel cover" />
        </div>
        <div class="story-panel panel-center">
          <img src="Assets/Cover/cover17.jpg" alt="Featured novel cover" />
        </div>
        <div class="story-panel">
          <img src="Assets/Cover/cover41.jpg" alt="Featured novel cover" />
        </div>
        <div class="story-panel panel-inner-right">
          <img src="Assets/Cover/cover67.jpg" alt="Featured novel cover" />
        </div>
        <div class="story-panel panel-wing-right">
          <img src="Assets/Cover/cover17.jpg" alt="Featured novel cover" />
        </div>
      </div>

      <p class="hero-copy">
        Bring your reading list to life with curated novels and immersive
        chapters. Browse free and premium stories, build your personal
        library, and track your progress in one place.
      </p>

      <div class="hero-cta-group">
        <a href="Login_register.php" class="cta-primary-glow">Start Reading</a>
        <a href="Login_register.php#register" class="cta-secondary-dark">Explore Books</a>
      </div>
    </main>

    <section id="about" class="about-showcase">
      <div class="section-label">ABOUT NOVELSTORE</div>
      <h2>A Place Where <span>Stories Live.</span></h2>
      <p>
        Read free novels, purchase premium books, save favorites, and continue
        reading from your last chapter. Everything you need for simple online
        reading is available in one place.
      </p>
    </section>
  </div>
</body>

</html>