<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
  header("Location: Dashboard.php");
  exit();
}

$errors = [];

$active_form = (isset($_GET['mode']) && $_GET['mode'] === 'register')
  ? 'register'
  : 'login';

$login_email_val = '';
$username_val = '';
$register_email_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // LOGIN
  if (isset($_POST['login'])) {
    $active_form = 'login';

    $email = trim($_POST['login_email'] ?? '');
    $password = $_POST['login_password'] ?? '';

    $login_email_val = $email;

    if ($email === '' || $password === '') {
      $errors[] = "Please enter email and password.";
    }

    if (empty($errors)) {
      $stmt = mysqli_prepare(
        $conn,
        "SELECT user_id, username, password FROM users WHERE email = ?"
      );

      if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        $password_valid = false;

        if ($user) {
          // Modern hashed password
          if (password_verify($password, $user['password'])) {
            $password_valid = true;
          }
          // Temporary support for legacy plain-text accounts
          elseif ($password === $user['password']) {
            $password_valid = true;

            // Upgrade legacy password to secure hash
            $hashed_password = $password;

            $update = mysqli_prepare(
              $conn,
              "UPDATE users SET password = ? WHERE user_id = ?"
            );

            if ($update) {
              mysqli_stmt_bind_param(
                $update,
                "si",
                $hashed_password,
                $user['user_id']
              );

              mysqli_stmt_execute($update);
              mysqli_stmt_close($update);
            }
          }
        }

        if ($password_valid) {
          session_regenerate_id(true);

          $_SESSION['user_id'] = (int)$user['user_id'];
          $_SESSION['username'] = $user['username'];

          header("Location: Dashboard.php");
          exit();
        } else {
          $errors[] = "Invalid email or password.";
        }
      } else {
        $errors[] = "Unable to process login.";
      }
    }
  }

  // REGISTER
  if (isset($_POST['register'])) {
    $active_form = 'register';

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['register_email'] ?? '');
    $password = $_POST['register_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $username_val = $username;
    $register_email_val = $email;

    if (
      $username === '' ||
      $email === '' ||
      $password === '' ||
      $confirm === ''
    ) {
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

    // Check duplicate username/email
    if (empty($errors)) {
      $stmt = mysqli_prepare(
        $conn,
        "SELECT user_id FROM users WHERE username = ? OR email = ?"
      );

      if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
          $errors[] = "Username or email already exists.";
        }

        mysqli_stmt_close($stmt);
      } else {
        $errors[] = "Unable to check account details.";
      }
    }

    // Create account
    if (empty($errors)) {
      $hashed_password =
        $password;

      $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users (username, email, password)
                 VALUES (?, ?, ?)"
      );

      if ($stmt) {
        mysqli_stmt_bind_param(
          $stmt,
          "sss",
          $username,
          $email,
          $hashed_password
        );

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
      } else {
        $errors[] = "Unable to create account.";
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
  <title>NOVELSTORE - Login & Register</title>
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

      modeChip.innerHTML =
        '<i class="fa-solid fa-user-plus"></i> New Reader';

      history.replaceState(
        null,
        '',
        window.location.pathname + window.location.search + '#register'
      );
    } else {
      registerView.classList.remove('is-active');
      loginView.classList.add('is-active');

      headingTitle.textContent = 'Hello Reader';
      headingSub.textContent = 'Welcome to NOVELSTORE';

      modeChip.innerHTML =
        '<i class="fa-solid fa-book-open"></i> Reader Portal';

      history.replaceState(
        null,
        '',
        window.location.pathname + window.location.search
      );
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
