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
    'img' => 'Assets/Cover/cover17.jpg',
    'title' => 'Featured Editions',
    'badge' => 'NOVELSTORE',
    'sub' => 'Curated Stories & Chapters'
  ],
  [
    'img' => 'Assets/Cover/cover41.jpg',
    'title' => 'Dark Fantasy & Mystery',
    'badge' => 'COLLECTION',
    'sub' => 'Serialized & Full Books'
  ],
  [
    'img' => 'Assets/Cover/cover67.jpg',
    'title' => 'Reader Favorites',
    'badge' => 'TOP PICKS',
    'sub' => 'Track Progress Anywhere'
  ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NOVELSTORE — Login & Register</title>
  <?php include 'include/Links.php'; ?>
  <style>
  /* Split-card editorial auth UI adapted to NOVELSTORE warm/coffee palette */
  body.auth-split-body {
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(14px, 3vw, 36px);
    background:
      linear-gradient(120deg, rgba(43, 29, 23, 0.85) 0%, rgba(43, 29, 23, 0.45) 52%, rgba(245, 239, 230, 0.92) 52.1%, rgba(245, 239, 230, 1) 100%),
      url("Assets/Authmn.jpg") left center / cover no-repeat fixed;
    font-family: var(--font-body, "DM Sans", sans-serif);
    color: var(--text, #2c211b);
    overflow-x: hidden;
  }

  .auth-split-card {
    width: min(1060px, 100%);
    min-height: 640px;
    background: var(--surface, #fffdf8);
    border-radius: 26px;
    padding: 12px;
    display: grid;
    grid-template-columns: 1.02fr 0.98fr;
    gap: 12px;
    box-shadow: 0 28px 75px rgba(27, 17, 12, 0.28);
    border: 1px solid rgba(255, 255, 255, 0.65);
  }

  /* Left Showcase Visual Panel */
  .auth-visual-panel {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    background: #1a120e;
    color: #ffffff;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 24px 24px 26px;
    min-height: 540px;
  }

  .auth-slide-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transform: scale(1.04);
    transition: opacity 0.55s ease, transform 0.8s ease;
    z-index: 0;
  }

  .auth-slide-bg.active {
    opacity: 1;
    transform: scale(1);
  }

  .auth-visual-scrim {
    position: relative;
    z-index: 1;
    inset: 0;
    position: absolute;
    background:
      linear-gradient(180deg, rgba(18, 12, 9, 0.65) 0%, rgba(18, 12, 9, 0.15) 45%, rgba(18, 12, 9, 0.82) 100%);
  }

  .auth-visual-top {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  .auth-visual-label {
    font-size: 13px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.88);
    text-decoration: none;
    letter-spacing: 0.2px;
  }

  .auth-visual-top-actions {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .visual-ghost-link {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.85);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    padding: 6px 8px;
  }

  .visual-ghost-link:hover {
    color: #ffffff;
  }

  .visual-pill-btn {
    border: 1px solid rgba(255, 255, 255, 0.55);
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
    font-size: 12px;
    font-weight: 600;
    padding: 7px 16px;
    border-radius: 999px;
    cursor: pointer;
    backdrop-filter: blur(8px);
    transition: background 0.2s ease, border-color 0.2s ease;
  }

  .visual-pill-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: #ffffff;
  }

  .auth-visual-bottom {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
  }

  .auth-credit-badge {
    display: flex;
    align-items: center;
    gap: 11px;
  }

  .auth-credit-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--primary, #6f4e37);
    border: 1.5px solid rgba(255, 255, 255, 0.45);
    display: grid;
    place-items: center;
    font-family: var(--font-heading, "Playfair Display", serif);
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
  }

  .auth-credit-text strong {
    display: block;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.25;
  }

  .auth-credit-text span {
    display: block;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
  }

  .auth-carousel-arrows {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .carousel-circle-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.35);
    background: rgba(20, 14, 10, 0.45);
    color: #ffffff;
    display: grid;
    place-items: center;
    cursor: pointer;
    backdrop-filter: blur(6px);
    transition: background 0.2s ease, transform 0.2s ease;
  }

  .carousel-circle-btn:hover {
    background: var(--primary, #6f4e37);
    border-color: transparent;
    transform: scale(1.05);
  }

  /* Right Form Panel */
  .auth-form-panel {
    padding: 22px clamp(20px, 4vw, 48px) 28px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .auth-top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  .auth-brand-mark {
    font-family: var(--font-heading, "Playfair Display", serif);
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: var(--primary-dark, #3e2723);
    text-decoration: none;
  }

  .auth-mode-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 999px;
    border: 1px solid var(--border-color, #dfd0c0);
    background: var(--bg-light, #fbf8f3);
    font-size: 11px;
    font-weight: 600;
    color: var(--text-secondary, #6b584b);
  }

  .auth-center-box {
    width: 100%;
    max-width: 360px;
    margin: 20px auto;
  }

  .auth-heading {
    text-align: center;
    margin-bottom: 24px;
  }

  .auth-heading h1 {
    margin: 0 0 6px;
    font-family: var(--font-body, "DM Sans", sans-serif);
    font-size: 28px;
    font-weight: 700;
    letter-spacing: -0.5px;
    color: var(--text, #2c211b);
  }

  .auth-heading p {
    margin: 0;
    font-size: 13px;
    color: var(--text-secondary, #6b584b);
  }

  .auth-error-alert {
    margin-bottom: 16px;
    padding: 10px 14px;
    border-radius: 10px;
    background: #fdf0ee;
    border: 1px solid #ecc4be;
    color: var(--danger, #a94b45);
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
  }

  .clean-field {
    margin-bottom: 12px;
  }

  .clean-field input {
    width: 100%;
    height: 44px;
    padding: 0 14px;
    border-radius: 8px;
    border: 1px solid #ded4c8;
    background: #ffffff;
    color: var(--text, #2c211b);
    font-size: 13.5px;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
  }

  .clean-field input::placeholder {
    color: #a39589;
  }

  .clean-field input:focus {
    border-color: var(--primary, #6f4e37);
    box-shadow: 0 0 0 3px rgba(111, 78, 55, 0.12);
  }

  .auth-row-helper {
    display: flex;
    justify-content: flex-end;
    margin: -2px 0 14px;
  }

  .helper-link-btn {
    background: none;
    border: none;
    padding: 0;
    font-size: 11.5px;
    color: #d45b47;
    cursor: pointer;
  }

  .helper-link-btn:hover {
    text-decoration: underline;
  }

  .auth-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 14px 0;
    color: var(--text-muted, #9a8879);
    font-size: 11.5px;
  }

  .auth-divider::before,
  .auth-divider::after {
    content: "";
    flex: 1;
    height: 1px;
    background: #e6dcd1;
  }

  .auth-alt-btn {
    width: 100%;
    height: 42px;
    border-radius: 8px;
    border: 1px solid #ded4c8;
    background: #ffffff;
    color: var(--text, #2c211b);
    font-size: 12.5px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    transition: background 0.2s ease, border-color 0.2s ease;
  }

  .auth-alt-btn:hover {
    background: var(--bg-light, #fbf8f3);
    border-color: var(--secondary, #b08968);
  }

  .auth-submit-coral {
    width: 100%;
    height: 44px;
    margin-top: 14px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #e05a47 0%, #c84735 100%);
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(200, 71, 53, 0.24);
    transition: transform 0.18s ease, filter 0.18s ease;
  }

  .auth-submit-coral:hover {
    transform: translateY(-1px);
    filter: brightness(1.04);
  }

  .auth-switch-footer {
    margin-top: 16px;
    text-align: center;
    font-size: 12px;
    color: var(--text-secondary, #6b584b);
  }

  .auth-switch-footer button {
    background: none;
    border: none;
    padding: 0;
    color: #d45b47;
    font-weight: 600;
    cursor: pointer;
  }

  .auth-switch-footer button:hover {
    text-decoration: underline;
  }

  .auth-bottom-socials {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 18px;
    padding-top: 10px;
  }

  .auth-bottom-socials a {
    color: var(--text, #2c211b);
    font-size: 14px;
    text-decoration: none;
    opacity: 0.85;
    transition: opacity 0.2s ease, color 0.2s ease;
  }

  .auth-bottom-socials a:hover {
    opacity: 1;
    color: var(--primary, #6f4e37);
  }

  @media (max-width: 860px) {
    body.auth-split-body {
      background: var(--bg, #f5efe6);
      padding: 12px;
    }

    .auth-split-card {
      grid-template-columns: 1fr;
      min-height: auto;
    }

    .auth-visual-panel {
      min-height: 250px;
    }
  }
  </style>
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

        <!-- LOGIN FORM -->
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