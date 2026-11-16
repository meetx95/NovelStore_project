<?php
session_start();
if (isset($_SESSION['user_id'])) {
  header("Location: Dashboard.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>NOVELSTORE</title>
  <?php include 'include/Links.php'; ?>
</head>
<style>
.main {
  display: block;
}
</style>

<body>
  <div class="main landing">
    <header class="navbar">
      <div class="text-logo">NOVELSTORE</div>
      <div class="login_register">
        <a href="Login_register.php" class="btn">Login</a>
        <a href="Login_register.php#register" class="btn">Register</a>
      </div>
    </header>
    <br>

    <section class="info">
      <div class="info-content">
        <h1>Discover Your <span>Next Story</span></h1>
        <p class="info-description">
          Browse free and premium novels, build your library, and track your reading, all in one place.
        </p>
        <div class="info-buttons">
          <a href="Login_register.php" class="primary-btn">Start Reading</a>
          <a href="Login_register.php" class="secondary-btn">Explore Books</a>
        </div>
      </div>

      <div class="info-books">
        <div class="book book-1"><img src="Assets/Cover/cover17.jpg" alt="Book cover"></div>
        <div class="book book-2"><img src="Assets/Cover/cover41.jpg" alt="Book cover"></div>
        <div class="book book-3"><img src="Assets/Cover/cover67.jpg" alt="Book cover"></div>
      </div>
    </section>

    <section class="about">
      <div class="section-label">ABOUT NOVELSTORE</div>
      <h2>A Place Where <span>Stories Live.</span></h2>
      <p class="section-description">
        Read free novels, purchase premium books, save favorites, and continue reading from your last chapter.
        Everything you need for simple online reading is available in one place.
      </p>
    </section>
  </div>
</body>

</html>