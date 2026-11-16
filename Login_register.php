<?php
session_start();
require_once 'config/db.php';
if (isset($_SESSION['user_id'])) {
  header("Location: Dashboard.php");
  exit();
}
$errors = [];
$active_form = "login";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (isset($_POST['login'])) {
    $active_form = "login";
    $email = trim($_POST['login_email'] ?? '');
    $password = $_POST['login_password'] ?? '';
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

      if ($user && $password === $user['password']) {
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
?>
<html>

<head>
  <title>Login & Register</title>
  <?php include 'include/Links.php'; ?>
</head>

<body>
  <div class="main">
    <div class="page">
      <center>

        <?php if (!empty($errors)): ?>
        <div class="error-box">
          <?php foreach ($errors as $error): ?>
          <p><?php echo $error; ?></p>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="container">

          <input type="radio" name="form" id="login" checked>
          <input type="radio" name="form" id="register">

          <div class="switch">
            <label for="login">Login</label>
            <label for="register">Register</label>
            <div class="slider"></div>
          </div>

          <!-- forms -->
          <div class="forms">
            <div class="forms-track">

              <!-- login -->
              <form class="login form" method="POST">
                <h2>Welcome Back</h2>
                <div class="inputbox">
                  <i class="fa-solid fa-envelope"></i>
                  <input type="email" placeholder="Email" name="login_email" autocomplete="email" required>
                </div>

                <div class="inputbox">
                  <i class="fa-solid fa-lock"></i>
                  <input type="password" placeholder="Password" name="login_password" autocomplete="current-password"
                    required>
                </div>

                <button type="submit" name="login">Login</button>
                <p>Forgot Password?</p>
              </form>

              <!-- register -->
              <form class="register form" method="POST">
                <h2>Create Account</h2>
                <div class="inputbox">
                  <i class="fa-solid fa-user"></i>
                  <input type="text" placeholder="Username" name="username" autocomplete="username" required>
                </div>

                <div class="inputbox">
                  <i class="fa-solid fa-envelope"></i>
                  <input type="email" placeholder="Email" name="register_email" autocomplete="email" required>
                </div>

                <div class="inputbox">
                  <i class="fa-solid fa-lock"></i>
                  <input type="password" placeholder="Password" name="register_password" autocomplete="new-password"
                    required>
                </div>

                <div class="inputbox">
                  <i class="fa-solid fa-lock"></i>
                  <input type="password" placeholder="Confirm Password" name="confirm_password"
                    autocomplete="new-password" required>
                </div>

                <button type="submit" name="register">Register</button>
              </form>

            </div>
          </div>

        </div>

      </center>
    </div>
  </div>
</body>

</html>
<style>
.main {
  padding: 0;
  /* box-sizing: border-box; */
}

.page {
  width: 100%;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(135deg, rgba(36, 21, 13, 0.72), rgba(78, 47, 27, 0.42)), url("./Assets/Authmn.jpg") center / cover no-repeat fixed;
  /* background: linear-gradient(135deg, rgba(36, 21, 13, 0.72), rgba(78, 47, 27, 0.42)), url("./Assets/Back1.jpg") center / cover no-repeat fixed; */
  /* background: linear-gradient(135deg, rgba(36, 21, 13, 0.72), rgba(78, 47, 27, 0.42)), url("./Assets/Back2.jpg") center / cover no-repeat fixed; */
  /* background: linear-gradient(135deg, rgba(36, 21, 13, 0.72), rgba(78, 47, 27, 0.42)), url("./Assets/Back3.jpg") center / cover no-repeat fixed; */
  /* background: linear-gradient(135deg, rgba(36, 21, 13, 0.72), rgba(78, 47, 27, 0.42)), url("./Assets/Back4.jpg") center / cover no-repeat fixed; */
  /* background: linear-gradient(135deg, rgba(36, 21, 13, 0.72), rgba(78, 47, 27, 0.42)), url("./Assets/Back5.jpg") center / cover no-repeat fixed; */
  /* background: linear-gradient(135deg, rgba(36, 21, 13, 0.72), rgba(78, 47, 27, 0.42)), url("./Assets/Back6.png") center / cover no-repeat fixed; */
  /* background: linear-gradient(135deg, rgba(36, 21, 13, 0.72), rgba(78, 47, 27, 0.42)), url("./Assets/Back7.jpg") center / cover no-repeat fixed; */
  /* background: linear-gradient(135deg, rgba(36, 21, 13, 0.72), rgba(78, 47, 27, 0.42)), url("./Assets/Back.png") center / cover no-repeat fixed; */
  /* background: linear-gradient(135deg, rgba(36, 21, 13, 0.72), rgba(78, 47, 27, 0.42)), url("./Assets/Back0.jpg") center / cover no-repeat fixed; */
  /* background: linear-gradient(135deg, rgba(36, 21, 13, 0.72), rgba(78, 47, 27, 0.42)), url("./Assets/Back8.jpg") center / cover no-repeat fixed; */
  /* background: linear-gradient(135deg, rgba(36, 21, 13, 0.72), rgba(78, 47, 27, 0.42)), url("./Assets/Back9.jpg") center / cover no-repeat fixed; */
  justify-content: center;

}

.page>center {
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  box-sizing: border-box;
}

.error-box {
  width: 650px;
  max-width: 100%;
  box-sizing: border-box;
  margin-bottom: 15px;
  padding: 12px 18px;
  border-radius: 12px;
  background: rgba(150, 45, 25, 0.35);
  border: 1px solid rgba(255, 103, 80, 0.35);
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
  position: absolute;
  top: 50px;
  z-index: 10;
}

.error-box p {
  margin: 4px 0;
  color: #ffd8d2;
  font-size: 13px;
  font-weight: 500;
  text-align: center;
}

.container {
  position: relative;
  width: 650px;
  max-width: 100%;
  min-height: 560px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 30px;
  box-sizing: border-box;
  background: rgba(255, 250, 243, 0.10);
  border: 1px solid rgba(255, 255, 255, 0.20);
  border-radius: 25px;
  box-shadow: 0 25px 80px rgba(0, 0, 0, 0.45),
    inset 0 1px 0 rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(22px);
  -webkit-backdrop-filter: blur(22px);
  overflow: hidden;
  z-index: 1;
}

input[type="radio"] {
  display: none;
}

.switch {
  position: relative;
  width: 90%;
  height: 50px;
  margin: 5px auto 15px;
  display: flex;
  align-items: center;
  justify-content: space-around;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 50px;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  overflow: hidden;
}

.switch label {
  position: relative;
  width: 50%;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  z-index: 3;
}

.slider {
  position: absolute;
  left: 5px;
  top: 5px;
  width: calc(50% - 5px);
  height: 40px;
  border-radius: 40px;
  background: var(--primary);
  transition: transform 0.35s ease;
  z-index: 1;
}

#register:checked~.switch .slider {
  transform: translateX(100%);
}

.forms {
  position: relative;
  width: 90%;
  overflow: hidden;
  z-index: 5;
}

.forms-track {
  display: flex;
  width: 200%;
  transition: transform 0.35s ease;
}

.form {
  width: 50%;
  flex-shrink: 0;
  padding: 30px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

#register:checked~.forms .forms-track {
  transform: translateX(-50%);
}

.form h2 {
  margin: 0 0 20px;
  color: #ffffff;
  font-size: 30px;
  font-weight: 700;
  text-align: center;
}

.inputbox {
  position: relative;
  width: 100%;
}

.inputbox i {
  position: absolute;
  left: 18px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--primary-light);
  pointer-events: none;
  z-index: 2;
}

.inputbox input {
  width: 100%;
  padding: 14px 18px 14px 48px;
  box-sizing: border-box;
  border: 1px solid rgba(255, 255, 255, 0.20);
  border-radius: 12px;
  outline: none;
  background: rgba(255, 255, 255, 0.10);
  color: #ffffff;
  font-size: 14px;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  transition: 0.25s ease;
}

.inputbox input::placeholder {
  color: rgba(255, 255, 255, 0.65);
}

.inputbox input:focus {
  border-color: var(--primary-light);
  background: rgba(255, 255, 255, 0.14);
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.05);
}

.form button {
  width: 100%;
  padding: 14px;
  border: none;
  border-radius: 12px;
  background: var(--primary);
  color: #ffffff;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.form button:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.30);
}

.form button:active {
  transform: translateY(0);
}

.form p {
  margin: 0;
  color: rgba(255, 255, 255, 0.70);
  font-size: 13px;
  text-align: center;
}

.error {
  padding: 10px;
  margin-bottom: 10px;
  border-radius: 10px;
  background: rgba(255, 80, 80, 0.15);
  border: 1px solid rgba(255, 100, 100, 0.25);
}

.error p {
  margin: 3px;
  color: #ffd0d0;
}
</style>