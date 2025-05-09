<!DOCTYPE html>
<html lang="en">

<?php
// check already logged in
session_start();
if (isset($_SESSION["username"])) {
  header("Location: index.php");
  exit;
}
?>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Acadive</title>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(to bottom right, #0a0f2c, #112244, #1a2a5c);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #03182d;
      box-shadow: 10px 10px 5px rgb(39, 40, 40);
    }

    .card {
      background: linear-gradient(to bottom right, #ffffff, #f1f5f9);
      border-radius: 16px;
      padding: 3rem;
      width: 100%;
      max-width: 550px;
      min-height: 425px;
      box-shadow:
        inset 0 0 8px rgba(0, 0, 0, 0.05),
        0 10px 25px rgba(0, 0, 0, 0.3),
        0 0 15px rgba(30, 58, 138, 0.2);
      border: 1px solid rgba(0, 0, 0, 0.05);
      animation: fadeInUp 0.8s ease forwards;
      opacity: 0;
      transform: translateY(20px);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-top: 30px;
    }


    .card:hover {
      transform: translateY(10px);
      box-shadow:
        0 12px 28px rgba(0, 0, 0, 0.35),
        0 0 20px rgba(30, 58, 138, 0.3);
    }

    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .logo {
      height: 100px;
      -moz-user-select: none;
      -webkit-user-select: none;
      user-select: none;
    }

    h1 {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 0.25rem;
      margin-left: -20px;
      color: #03182d;
    }

    p {
      font-size: 0.9rem;
      color: #4b5563;
      margin-bottom: 1.5rem;
    }

    label {
      display: block;
      margin-bottom: 0.25rem;
      font-weight: 600;
      color: #03182d;
    }

    input {
      width: 100%;
      padding: 0.6rem 0.8rem;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      background: #ffffff;
      color: #03182d;
      margin-bottom: 1rem;
      box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    input::placeholder {
      color: #9ca3af;
    }

    .toggle-password {
      display: flex;
      align-items: center;
      font-size: 0.8rem;
      margin-top: -0.75rem;
      margin-bottom: 1rem;
    }

    .toggle-password input {
      width: auto;
      margin-right: 0.5rem;
    }

    #showPassword {
      margin-top: 10px;
    }

    .btn {
      width: 100%;
      padding: 0.75rem;
      background-color: #1e3a8a;
      color: white;
      font-weight: 700;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .btn:hover {
      background-color: #374eb7;
      transform: scale(1.01);
    }

    .links {
      margin-top: 1rem;
      text-align: center;
      font-size: 0.85rem;
    }

    .links a {
      color: #1e3a8a;
      text-decoration: none;
      margin: 0 0.5rem;
    }

    .links a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>

  <div class="card">
    <?php
    if (isset($_SESSION["error"])) {
      echo '<div
      style="background-color: #f8d7da;  color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; text-align: center; gap: 0.5rem;">
      <i class="fas fa-triangle-exclamation" style="margin-right: 10px;"></i><span><b>ERROR:</b> ' . $_SESSION["error"] . '</span>
      </div>';
      unset($_SESSION["error"]);
    }
    ?>
    <div style="text-align: center;">
      <img class="logo" src="img/logo.svg" draggable="false" alt="Acadive Logo" />
      <h1>Login</h1>
    </div>

    <form action="process/login.php" method="POST">
      <div>
        <label for="username">Username</label>
        <input type="text" name="username" maxlength="32" id="username" placeholder="myusername123" required />
      </div>
      <div>
        <label for="password">Password</label>
        <input type="password" name="password" maxlength="128" id="password" placeholder="••••••••" required />
      </div>

      <div class="toggle-password">
        <input type="checkbox" id="showPassword" />
        <label for="showPassword">Show Password</label>
      </div>

      <input type="submit" value="Login" class="btn" />
    </form>
  </div>

  <script>
    const showPassword = document.getElementById('showPassword');
    const passwordInput = document.getElementById('password');

    showPassword.addEventListener('change', function () {
      passwordInput.type = this.checked ? 'text' : 'password';
    });
  </script>

</body>

</html>