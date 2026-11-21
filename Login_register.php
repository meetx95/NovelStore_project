<?php
session_start();
require_once 'config/db.php';
if (isset($_SESSION['user_id'])) {
  header("Location: Dashboard.php");
  exit();
}

$errors = [];
$active_form = (isset($_GET['mode']) && $_GET['mode'] === 'register') || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '#register') !== false) ? "register" : "login";

$login_email_val = '';
$username_val = '';
$register_email_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (isset($_POST['login'])) {
    $active_form = "login";
    $email = trim($_POST['login_email'] ?? '');
    $password = $_POST['login_password'] ?? '';
    $login_email_val = $email;

    if ($email === '' || $password === '') {
      $errors[] = "Please enter email and password.";
    }

    if (empty($errors)) {
      $stmt = mysqli_prepare($conn, "SELECT user_id, username, password FROM users WHERE email = ?");
      mysqli_stmt_bind_param($stmt, "s", $email);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      $user = mysqli_fetch_assoc($result);
      mysqli_stmt_close($stmt);

      if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['user_id'];
        $_SESSION['username'] = $user['username'];
        header("Location: Dashboard.php");
        exit();
      } else {
        $errors[] = "Invalid email or password.";
      }
    }
  }

  if (isset($_POST['register'])) {
    $active_form = "register";
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['register_email'] ?? '');
    $password = $_POST['register_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $username_val = $username;
    $register_email_val = $email;

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
      $errors[] = "All fields are required.";
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = "Please enter a valid email.";
    }
    if ($username !== '' && strlen($username) < 3) {
      $errors[] = "Username must be at least 3 characters.";
    }
    if ($password !== '' && strlen($password) < 6) {
      $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm) {
      $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
      $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username = ? OR email = ?");
      mysqli_stmt_bind_param($stmt, "ss", $username, $email);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_store_result($stmt);
      if (mysqli_stmt_num_rows($stmt) > 0) {
        $errors[] = "Username or email already exists.";
      }
      mysqli_stmt_close($stmt);
    }

    if (empty($errors)) {
      $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
      mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password);
      if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$new_id;
        $_SESSION['username'] = $username;
        header("Location: Dashboard.php");
        exit();
      } else {
        $errors[] = "Registration failed. Please try again.";
        mysqli_stmt_close($stmt);
      }
    }
  }
}

$slides = [
  [
    'img' => 'Assets/bg/bg1.jpg',
    'title' => 'Featured Editions',
    'badge' => 'NOVELSTORE',
    'sub' => 'Curated Stories & Chapters'
  ],
  [
    'img' => 'Assets/bg/bg2.jpg',
    'title' => 'Dark Fantasy & Mystery',
    'badge' => 'COLLECTION',
    'sub' => 'Serialized & Full Books'
  ],
  [
    'img' => 'Assets/bg/bg3.jpg',
    'title' => 'Reader Favorites',
    'badge' => 'TOP PICKS',
    'sub' => 'Track Progress Anywhere'
  ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>NOVELSTORE: Login & Register</title>
  <?php include 'include/Links.php'; ?>
</head>

<body class="auth-split-body">
  <div class="auth-split-card">

    <!-- Left Visual Showcase Panel -->
    <div class="auth-visual-panel">
      <?php foreach ($slides as $idx => $s): ?>
      <img src="<?php echo htmlspecialchars($s['img'], ENT_QUOTES, 'UTF-8'); ?>"
        alt="<?php echo htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8'); ?>"
        class="auth-slide-bg <?php echo $idx === 0 ? 'active' : ''; ?>">
      <?php endforeach; ?>
      <div class="auth-visual-scrim"></div>

      <div class="auth-visual-top">
        <a href="index.php" class="auth-visual-label">Selected Stories</a>
        <div class="auth-visual-top-actions">
          <button type="button" class="visual-ghost-link" onclick="switchAuthTab('login')">Sign In</button>
          <button type="button" class="visual-pill-btn" onclick="switchAuthTab('register')">Join Us</button>
        </div>
      </div>

      <div class="auth-visual-bottom">
        <div class="auth-credit-badge">
          <div class="auth-credit-avatar" id="slideAvatar">N</div>
          <div class="auth-credit-text">
            <strong id="slideTitle"><?php echo htmlspecialchars($slides[0]['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
            <span
              id="slideSub"><?php echo htmlspecialchars($slides[0]['badge'] . ' • ' . $slides[0]['sub'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        </div>

        <div class="auth-carousel-arrows">
          <button type="button" class="carousel-circle-btn" id="prevSlide" aria-label="Previous cover">
            <i class="fa-solid fa-arrow-left"></i>
          </button>
          <button type="button" class="carousel-circle-btn" id="nextSlide" aria-label="Next cover">
            <i class="fa-solid fa-arrow-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Right Form Panel -->
    <div class="auth-form-panel">
      <div class="auth-top-bar">
        <a href="index.php" class="auth-brand-mark">NOVELSTORE</a>
        <span class="auth-mode-chip" id="modeChip">
          <i class="fa-solid fa-book-open"></i>
          <?php echo $active_form === 'register' ? 'New Reader' : 'Reader Portal'; ?>
        </span>
      </div>

      <div class="auth-center-box">
        <div class="auth-heading">
          <h1 id="authHeadingTitle"><?php echo $active_form === 'register' ? 'Create Account' : 'Hi Reader'; ?></h1>
          <p id="authHeadingSub">
            <?php echo $active_form === 'register' ? 'Join NOVELSTORE and start reading' : 'Welcome to NOVELSTORE'; ?>
          </p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="auth-error-alert">
          <?php foreach ($errors as $error): ?>
          <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- login form -->
        <form method="POST" action="" id="loginFormView"
          class="auth-form-view <?php echo $active_form === 'login' ? 'is-active' : ''; ?>">
          <div class="clean-field">
            <input type="email" name="login_email" placeholder="Email" autocomplete="email"
              value="<?php echo htmlspecialchars($login_email_val, ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <div class="clean-field">
            <input type="password" name="login_password" placeholder="Password" autocomplete="current-password"
              required>
          </div>

          <div class="auth-row-helper">
            <button type="button" class="helper-link-btn"
              onclick="alert('Contact support or use your registered email to recover access.')">
              Forgot password ?
            </button>
          </div>

          <div class="auth-divider">or</div>

          <button type="button" class="auth-alt-btn" onclick="switchAuthTab('register')">
            <i class="fa-solid fa-user-plus"></i>
            <span>Create a New Reader Account</span>
          </button>

          <button type="submit" name="login" class="auth-submit-coral">Login</button>

          <div class="auth-switch-footer">
            Don't have an account? <button type="button" onclick="switchAuthTab('register')">Sign up</button>
          </div>
        </form>

        <!-- REGISTER FORM -->
        <form method="POST" action="" id="registerFormView"
          class="auth-form-view <?php echo $active_form === 'register' ? 'is-active' : ''; ?>">
          <div class="clean-field">
            <input type="text" name="username" placeholder="Username" autocomplete="username"
              value="<?php echo htmlspecialchars($username_val, ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <div class="clean-field">
            <input type="email" name="register_email" placeholder="Email" autocomplete="email"
              value="<?php echo htmlspecialchars($register_email_val, ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <div class="clean-field">
            <input type="password" name="register_password" placeholder="Password (min. 6 characters)"
              autocomplete="new-password" required>
          </div>

          <div class="clean-field">
            <input type="password" name="confirm_password" placeholder="Confirm Password" autocomplete="new-password"
              required>
          </div>

          <button type="submit" name="register" class="auth-submit-coral">Register</button>

          <div class="auth-switch-footer">
            Already have an account? <button type="button" onclick="switchAuthTab('login')">Login</button>
          </div>
        </form>
      </div>

      <div class="auth-bottom-socials" aria-label="NOVELSTORE links">
        <a href="index.php" title="Home"><i class="fa-solid fa-house"></i></a>
        <a href="index.php#about" title="About"><i class="fa-solid fa-book"></i></a>
        <a href="Login_register.php" title="Account"><i class="fa-solid fa-user"></i></a>
      </div>
    </div>

  </div>

  <script>
  function switchAuthTab(mode) {
    const loginView = document.getElementById('loginFormView');
    const registerView = document.getElementById('registerFormView');
    const headingTitle = document.getElementById('authHeadingTitle');
    const headingSub = document.getElementById('authHeadingSub');
    const modeChip = document.getElementById('modeChip');

    if (mode === 'register') {
      loginView.classList.remove('is-active');
      registerView.classList.add('is-active');
      headingTitle.textContent = 'Create Account';
      headingSub.textContent = 'Join NOVELSTORE and start reading';
      modeChip.innerHTML = '<i class="fa-solid fa-user-plus"></i> New Reader';
      if (window.location.hash !== '#register') {
        history.replaceState(null, '', '#register');
      }
    } else {
      registerView.classList.remove('is-active');
      loginView.classList.add('is-active');
      headingTitle.textContent = 'Hi Reader';
      headingSub.textContent = 'Welcome to NOVELSTORE';
      modeChip.innerHTML = '<i class="fa-solid fa-book-open"></i> Reader Portal';
      if (window.location.hash === '#register') {
        history.replaceState(null, '', window.location.pathname + window.location.search);
      }
    }
  }

  if (window.location.hash === '#register') {
    switchAuthTab('register');
  }

  const slidesData = <?php echo json_encode($slides, JSON_UNESCAPED_SLASHES); ?>;
  const slideImgs = document.querySelectorAll('.auth-slide-bg');
  const slideTitleEl = document.getElementById('slideTitle');
  const slideSubEl = document.getElementById('slideSub');
  const slideAvatarEl = document.getElementById('slideAvatar');
  let currentSlide = 0;

  function showSlide(index) {
    currentSlide = (index + slidesData.length) % slidesData.length;
    slideImgs.forEach((img, i) => {
      img.classList.toggle('active', i === currentSlide);
    });
    const item = slidesData[currentSlide];
    slideTitleEl.textContent = item.title;
    slideSubEl.textContent = item.badge + ' • ' + item.sub;
    slideAvatarEl.textContent = item.title.charAt(0).toUpperCase();
  }

  document.getElementById('prevSlide').addEventListener('click', () => showSlide(currentSlide - 1));
  document.getElementById('nextSlide').addEventListener('click', () => showSlide(currentSlide + 1));
  </script>
</body>

</html>
<style>
/* NOVELSTORE authentication page — cinematic, responsive layout */
body.auth-split-body {
  min-height: 100svh;
  margin: 0;
  padding: clamp(16px, 3vw, 42px);
  display: grid;
  place-items: center;
  box-sizing: border-box;
  overflow-x: hidden;
  color: #30231c;
  font-family: var(--font-body, "DM Sans", sans-serif);
  background: #160f0d url("Assets/Authmn.jpg") center / cover no-repeat fixed;
  position: relative;
  isolation: isolate;
}

body.auth-split-body::before {
  content: "";
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background:
    radial-gradient(ellipse at 18% 18%, rgba(177, 104, 70, .20), transparent 38%),
    radial-gradient(ellipse at 86% 78%, rgba(102, 62, 45, .24), transparent 44%),
    linear-gradient(135deg, rgba(15, 10, 9, .72), rgba(27, 15, 12, .82));
}

body.auth-split-body::after {
  content: "";
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background: radial-gradient(ellipse at center, transparent 35%, rgba(0, 0, 0, .32) 100%);
}

.auth-split-card {
  width: min(1120px, 100%);
  min-height: min(720px, calc(100svh - 64px));
  display: grid;
  grid-template-columns: minmax(0, 1.08fr) minmax(390px, .92fr);
  gap: 14px;
  padding: 14px;
  box-sizing: border-box;
  border: 1px solid rgba(255, 255, 255, .30);
  border-radius: 28px;
  background: rgba(248, 242, 234, .94);
  box-shadow: 0 34px 100px rgba(0, 0, 0, .46), 0 0 0 1px rgba(255, 255, 255, .05);
  backdrop-filter: blur(22px);
  -webkit-backdrop-filter: blur(22px);
}

/* Image panel and slide stacking */
.auth-visual-panel {
  min-height: 600px;
  position: relative;
  overflow: hidden;
  isolation: isolate;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: clamp(22px, 3vw, 34px);
  border-radius: 22px;
  color: #fff;
  background: #211410;
}

.auth-slide-bg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  z-index: -2;
  opacity: 0;
  transform: scale(1.055);
  transition: opacity .65s ease, transform 1.4s cubic-bezier(.2, .7, .2, 1);
  will-change: opacity, transform;
}

.auth-slide-bg.active {
  opacity: 1;
  transform: scale(1);
}

/* Scrim sits ABOVE the photos and BELOW all controls/content. */
.auth-visual-scrim {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background:
    linear-gradient(180deg, rgba(12, 8, 7, .55) 0%, rgba(12, 8, 7, .08) 28%, rgba(12, 8, 7, .08) 48%, rgba(12, 8, 7, .76) 100%),
    linear-gradient(90deg, rgba(12, 8, 7, .26), transparent 42%, rgba(12, 8, 7, .20)),
    linear-gradient(135deg, rgba(104, 53, 34, .12), transparent 60%);
}

.auth-visual-top,
.auth-visual-bottom {
  position: relative;
  z-index: 2;
}

.auth-visual-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
}

.auth-visual-label {
  color: rgba(255, 255, 255, .94);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .16em;
  text-transform: uppercase;
  text-decoration: none;
}

.auth-visual-top-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.visual-ghost-link,
.visual-pill-btn {
  cursor: pointer;
  color: #fff;
  font: inherit;
  font-size: 12px;
  transition: .2s ease;
}

.visual-ghost-link {
  padding: 9px 10px;
  border: 0;
  background: transparent;
  color: rgba(255, 255, 255, .82);
}

.visual-ghost-link:hover {
  color: #fff;
}

.visual-pill-btn {
  padding: 9px 17px;
  border: 1px solid rgba(255, 255, 255, .48);
  border-radius: 999px;
  background: rgba(255, 255, 255, .12);
  backdrop-filter: blur(10px);
}

.visual-pill-btn:hover {
  background: rgba(255, 255, 255, .23);
  border-color: #fff;
}

.auth-visual-bottom {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 18px;
}

.auth-credit-badge {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}

.auth-credit-avatar {
  flex: 0 0 42px;
  width: 42px;
  height: 42px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, .52);
  background: rgba(255, 255, 255, .16);
  color: white;
  font: 700 16px var(--font-heading, "Playfair Display", serif);
  backdrop-filter: blur(12px);
}

.auth-credit-text strong {
  display: block;
  margin-bottom: 3px;
  font-size: 14px;
  font-weight: 700;
}

.auth-credit-text span {
  display: block;
  font-size: 11px;
  line-height: 1.5;
  color: rgba(255, 255, 255, .72);
}

.auth-carousel-arrows {
  display: flex;
  gap: 8px;
}

.carousel-circle-btn {
  width: 38px;
  height: 38px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, .38);
  background: rgba(12, 9, 8, .30);
  color: #fff;
  cursor: pointer;
  backdrop-filter: blur(10px);
  transition: transform .2s ease, background .2s ease, border-color .2s ease;
}

.carousel-circle-btn:hover {
  transform: translateY(-2px);
  background: rgba(255, 255, 255, .18);
  border-color: #fff;
}

/* Form panel */
.auth-form-panel {
  min-width: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: clamp(22px, 3vw, 36px) clamp(22px, 4vw, 48px) 26px;
}

.auth-top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.auth-brand-mark {
  color: #422c22;
  text-decoration: none;
  font: 700 15px var(--font-heading, "Playfair Display", serif);
  letter-spacing: .13em;
}

.auth-mode-chip {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 8px 12px;
  border: 1px solid #e5d6c8;
  border-radius: 999px;
  background: #f6eee5;
  color: #765c49;
  font-size: 11px;
  font-weight: 650;
}

.auth-center-box {
  width: 100%;
  max-width: 390px;
  margin: auto;
  padding: 30px 0;
}

.auth-heading {
  text-align: left;
  margin-bottom: 26px;
}

.auth-heading h1 {
  margin: 0 0 8px;
  color: #2f211a;
  font: 700 clamp(28px, 3vw, 36px) var(--font-heading, "Playfair Display", serif);
  letter-spacing: -.025em;
}

.auth-heading p {
  margin: 0;
  color: #887365;
  font-size: 13px;
  line-height: 1.6;
}

.auth-error-alert {
  margin-bottom: 16px;
  padding: 12px 14px;
  border: 1px solid #e7bdb4;
  border-radius: 12px;
  background: #fff1ee;
  color: #9e4538;
  font-size: 12.5px;
}

.auth-error-alert p {
  margin: 3px 0;
}

.auth-form-view {
  display: none;
}

.auth-form-view.is-active {
  display: block;
  animation: authFadeIn .25s ease;
}

@keyframes authFadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.clean-field {
  margin-bottom: 13px;
}

.clean-field input {
  box-sizing: border-box;
  width: 100%;
  height: 49px;
  padding: 0 15px;
  border: 1px solid #e2d6ca;
  border-radius: 12px;
  outline: none;
  background: #fffdfa;
  color: #33251e;
  font: 13.5px var(--font-body, "DM Sans", sans-serif);
  transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}

.clean-field input::placeholder {
  color: #aa9788;
}

.clean-field input:focus {
  border-color: #9a6a4d;
  background: #fff;
  box-shadow: 0 0 0 4px rgba(154, 106, 77, .13);
}

.auth-row-helper {
  display: flex;
  justify-content: flex-end;
  margin: 0 0 16px;
}

.helper-link-btn {
  border: 0;
  padding: 0;
  background: transparent;
  color: #9b6247;
  font-size: 11.5px;
  cursor: pointer;
}

.helper-link-btn:hover {
  text-decoration: underline;
}

.auth-divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 17px 0;
  color: #a28e7d;
  font-size: 11px;
}

.auth-divider::before,
.auth-divider::after {
  content: "";
  flex: 1;
  height: 1px;
  background: #e8ddd2;
}

.auth-alt-btn {
  width: 100%;
  min-height: 46px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  border: 1px solid #e2d6ca;
  border-radius: 12px;
  background: #fffdfa;
  color: #4d392c;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  transition: .2s ease;
}

.auth-alt-btn:hover {
  border-color: #bd967b;
  background: #f9f2eb;
}

.auth-submit-coral {
  width: 100%;
  min-height: 48px;
  margin-top: 14px;
  border: 0;
  border-radius: 12px;
  background: linear-gradient(135deg, #9b6247, #70442f);
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 10px 22px rgba(112, 68, 47, .20);
  transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
}

.auth-submit-coral:hover {
  transform: translateY(-1px);
  filter: brightness(1.06);
  box-shadow: 0 13px 26px rgba(112, 68, 47, .25);
}

.auth-switch-footer {
  margin-top: 19px;
  text-align: center;
  color: #887365;
  font-size: 12px;
}

.auth-switch-footer button {
  border: 0;
  padding: 0;
  background: transparent;
  color: #9b6247;
  font-weight: 700;
  cursor: pointer;
}

.auth-switch-footer button:hover {
  text-decoration: underline;
}

.auth-bottom-socials {
  display: flex;
  justify-content: center;
  gap: 20px;
  padding-top: 12px;
}

.auth-bottom-socials a {
  color: #806654;
  font-size: 13px;
  opacity: .8;
  text-decoration: none;
  transition: color .2s ease, opacity .2s ease;
}

.auth-bottom-socials a:hover {
  color: #9b6247;
  opacity: 1;
}

@media (max-width: 900px) {
  body.auth-split-body {
    padding: 18px;
    background-attachment: scroll;
  }

  .auth-split-card {
    grid-template-columns: minmax(0, 1fr) minmax(340px, .95fr);
    min-height: 650px;
  }

  .auth-visual-panel {
    min-height: 560px;
  }

  .auth-form-panel {
    padding: 24px;
  }
}

@media (max-width: 700px) {
  body.auth-split-body {
    display: block;
    padding: 12px;
    min-height: 100svh;
  }

  .auth-split-card {
    width: 100%;
    min-height: 0;
    grid-template-columns: 1fr;
    padding: 9px;
    gap: 9px;
    border-radius: 22px;
  }

  .auth-visual-panel {
    min-height: 230px;
    padding: 19px;
    border-radius: 16px;
  }

  .auth-visual-label {
    font-size: 10px;
  }

  .auth-visual-bottom {
    gap: 10px;
  }

  .auth-credit-avatar {
    width: 36px;
    height: 36px;
    flex-basis: 36px;
  }

  .auth-credit-text strong {
    font-size: 12px;
  }

  .auth-credit-text span {
    font-size: 10px;
  }

  .carousel-circle-btn {
    width: 34px;
    height: 34px;
  }

  .auth-form-panel {
    padding: 20px 18px 18px;
  }

  .auth-center-box {
    max-width: none;
    margin: 0;
    padding: 28px 0 24px;
  }

  .auth-heading {
    margin-bottom: 21px;
  }

  .auth-heading h1 {
    font-size: 29px;
  }
}

@media (max-width: 390px) {
  .auth-visual-top-actions {
    gap: 5px;
  }

  .visual-ghost-link {
    padding: 7px 5px;
  }

  .visual-pill-btn {
    padding: 8px 12px;
  }

  .auth-brand-mark {
    font-size: 13px;
    letter-spacing: .09em;
  }

  .auth-mode-chip {
    padding: 7px 9px;
    font-size: 10px;
  }
}

@media (prefers-reduced-motion: reduce) {

  .auth-slide-bg,
  .auth-form-view.is-active,
  .carousel-circle-btn,
  .auth-submit-coral {
    transition: none;
    animation: none;
  }
}
</style>