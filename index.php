<?php
session_start();
include 'koneksi.php';

$error = "";
$notif = "";

if (isset($_GET['error'])) {

if ($_GET['error'] == "login_dulu") {
  $notif = "Silakan login terlebih dahulu!";
}
elseif ($_GET['error'] == "bukan_admin") {
  $notif = "Akses ditolak, Halaman khusus untuk admin";

}
elseif ($_GET['error'] == "bukan_kasir") {
  $notif = "Akses ditolak, Halaman khusus untuk kasir";
}
else if ($_GET['error'] == "bukan_manager") {
  $notif = "Akses ditolak, Halaman khusus untuk manager";
}

}
if (isset($_POST['login'])) {

  $username = $_POST['username'];
  $password = $_POST['password'];

  $query = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");
  $data = mysqli_fetch_assoc($query);

  if ($data && $password == $data['password']) {

  $_SESSION['login'] = true;
  $_SESSION['username'] = $data['username'];
  $_SESSION['role'] = $data['role'];

  if ($data['role'] == 'admin') {
    header("Location: User_Admin.php");
  } elseif ($data['role'] == 'kasir') {
    header("Location: User_Kasir.php");
  } elseif ($data['role'] == 'manager') {
    header ("Location: User_Manajer.php");
  } else {
    header("location: index.php");
  }
  exit;

} else {
    $error = "username atau password salah!";
}
}
?>


<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>

    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
      rel="stylesheet"
    />

    <style>

      .error-msg{
        background:#ffdede;
        color:#b30000;
        padding:10px;
        border-radius:10px;
        margin-bottom:15px;
        font-size:14px;
        text-align:center;
      }
      /* ===================================================== */
      /* ===================== RESET ========================= */
      /* ===================================================== */

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
      }

      /* ===================================================== */
      /* ===================== BODY ========================== */
      /* ===================================================== */

      body {
        min-height: 100dvh;
        background: linear-gradient(180deg, #eef3f7, #e6edf3);
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
      }

      /* ===================================================== */
      /* ================= DECORATIVE CIRCLES ================ */
      /* ===================================================== */
      /* Background wrapper pembatas */
      .bg-wrapper {
        position: fixed;
        inset: 0;
        overflow: hidden; /* INI YANG MEMOTONG */
        z-index: 0;
      }

      /* Pastikan login-card di atas */
      .login-card {
        position: relative;
        z-index: 2;
      }
      .circle {
        position: absolute;
        border-radius: 50%;
        z-index: 1;
      }

      .c1 {
        width: 90px;
        height: 90px;
        background: #5bb7c5;
        top: 40px;
        left: 40px;
      }
      .c2 {
        width: 100px;
        height: 100px;
        background: #1dd1e5;
        top: -35px;
        right: -44px;
      }
      .c3 {
        width: 50px;
        height: 50px;
        background: #8fd3dd;
        bottom: 120px;
        right: 60px;
      }
      .c4 {
        width: 110px;
        height: 110px;
        background: #48b5c1;
        border-radius: 50%;

        left: 33%;
        transform: translateX(-50%);
        bottom: -70px;
      }
      .c5 {
        width: 33px;
        height: 33px;
        background: #63c9d5;
        border-radius: 50%;
        top: 91%;
        left: 44%;
      }
      .c6 {
        width: 24px;
        height: 24px;
        background: #5cafb8;
        border-radius: 50%;
        top: 95%;
        left: 53%;
      }
      .c7 {
        width: 60px;
        height: 60px;
        background: #8ddee7;
        border-radius: 50%;
        top: 85%;
        left: 60%;
      }
      .c8 {
        width: 60px;
        height: 60px;
        background: #99e4ec;
        border-radius: 50%;
        top: 85%;
        left: 70%;
      }
      .c9 {
        width: 60px;
        height: 60px;
        background: #beebf0;
        border-radius: 50%;
        top: 85%;
        left: 80%;
      }
      .c10 {
        width: 60px;
        height: 60px;
        background: #77bfca;
        top: 50px;
        left: 150px;
      }
      .c11 {
        width: 50px;
        height: 50px;
        background: #98c6cd;
        top: 54px;
        left: 230px;
      }
      .c12 {
        width: 27px;
        height: 27px;
        background: #9ed4db;
        top: 26px;
        left: 126px;
      }
      .c13 {
        width: 19px;
        height: 19px;
        background: #75aeb6;
        top: 12px;
        left: 166px;
      }

      /* ===================================================== */
      /* ================= LOGIN CARD ======================== */
      /* ===================================================== */

      .login-card {
        margin-top: 117px;
        margin-bottom: 70px;
        position: relative;
        z-index: 2;
        width: 400px;
        background: #ffffff;
        padding: 45px 35px;
        border-radius: 28px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
      }

      /* Avatar */
      .avatar {
        width: 85px;
        height: 85px;
        margin: 0 auto 25px;
        background: linear-gradient(135deg, #5bb7c5, #3f7aa3);
        border-radius: 22px;
        display: flex;
        justify-content: center;
        align-items: center;
      }

      .avatar img {
        width: 50px;
      }

      /* Title */
      .login-card h2 {
        text-align: center;
        font-size: 22px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 30px;
        letter-spacing: 0.4px;
      }

      /* ===================================================== */
      /* ================= INPUT ============================= */
      /* ===================================================== */

      .input-group {
        margin-bottom: 20px;
      }

      .input-group label {
        font-size: 13px;
        font-weight: 500;
        color: #5f6f81;
      }

      .input-wrapper {
        position: relative;
        margin-top: 8px;
      }

      .input-wrapper input {
        width: 100%;
        padding: 14px 45px;
        border-radius: 16px;
        border: 1px solid #d9e2ec;
        background: #f7fafc;
        outline: none;
        font-size: 15px;
        font-weight: 500;
        color: #2c3e50;
        transition: 0.2s ease;
      }

      .input-wrapper input:focus {
        border-color: #5bb7c5;
        box-shadow: 0 0 0 3px rgba(91, 183, 197, 0.15);
      }

      /* Semi bold saat isi */
      .input-wrapper input:not(:placeholder-shown) {
        font-weight: 600;
      }

      /* Icons */
      .icon-left {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
      }

      .icon-right {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        cursor: pointer;
      }

      /* Forgot */
      .forgot {
        text-align: right;
        margin-bottom: 22px;
      }

      .forgot a {
        font-size: 13px;
        font-weight: 500;
        color: #4a90e2;
        text-decoration: none;
      }

      /* ===================================================== */
      /* ================= BUTTON ============================ */
      /* ===================================================== */

      .btn-login {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 26px;
        background: linear-gradient(90deg, #5bb7c5, #3f7aa3);
        color: white;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: 0.2s ease;
      }

      .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(63, 122, 163, 0.25);
      }

      /* ===================================================== */
      /* ================= DIVIDER =========================== */
      /* ===================================================== */

      .divider {
        text-align: center;
        margin: 28px 0;
        font-size: 13px;
        font-weight: 500;
        color: #8fa1b3;
        letter-spacing: 0.4px;
      }

      /* ===================================================== */
      /* ================= GOOGLE ============================ */
      /* ===================================================== */

      .google-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
      }

      .google-box {
        width: 75px;
        height: 75px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        cursor: pointer;
      }

      .google-box img {
        width: 40px;
      }

      .google-text-btn {
        padding: 8px 22px;
        border: 1px solid #d6dee6;
        background: #ffffff;
        border-radius: 16px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: 0.2s ease;
      }

      .google-text-btn:hover {
        background: #f2f6f9;
      }

      /* ===================================================== */
      /* ================= RESPONSIVE ======================== */
      /* ===================================================== */

      @media (max-width: 480px) {
        .login-card {
          width: 90%;
          padding: 35px 25px;
        }
      }
    </style>
  </head>

  <body>
    <div class="bg-wrapper">
      <!-- Decorative Circles -->
      <div class="circle c1"></div>
      <div class="circle c2"></div>
      <div class="circle c3"></div>
      <div class="circle c4"></div>
      <div class="circle c5"></div>
      <div class="circle c6"></div>
      <div class="circle c7"></div>
      <div class="circle c8"></div>
      <div class="circle c9"></div>
      <div class="circle c10"></div>
      <div class="circle c11"></div>
      <div class="circle c12"></div>
      <div class="circle c13"></div>
    </div>

    <div class="login-card">
      <div class="avatar">
        <img src="asset/Icon_User.png" alt="" />
      </div>

      <h2>Selamat Datang</h2>

      <form method="POST" >
        <?php if ($notif != ""): ?>
         <div class="error-msg" id="notif-msg">
          <?= $notif; ?>
        </div>
      <?php endif; ?>

        <?php if ($error != ""): ?>
          <div class="error-msg" id="error-msg" style="display:block;">
        <?= $error; ?>
        </div>
        <?php endif; ?>

      <div class="input-group">
        <label><strong>Username</strong></label>
        <div class="input-wrapper">
          <img src="asset/user_acc_login.png" class="icon-left" />
          <input type="text" name="username" id="username" placeholder="Masukkan Username" required />
        </div>
      </div>

      <div class="input-group">
        <label><strong>Password</strong></label>
        <div class="input-wrapper">
          <img src="asset/icon_lock.png" class="icon-left" />
          <input name="password" id="password"  type="password" placeholder="Masukkan Password" required />
          <img src="asset/icon_eyeclosed.png" class="icon-right" id="togglePassword" />
        </div>
      </div>

      <div class="forgot">
        <a href="lupa_password.php">Lupa Password?</a>
      </div>

      <button class="btn-login" type="submit" name="login">Masuk</button>

      <div class="divider">— Atau masuk dengan —</div>

      <div class="google-wrapper">
        <div class="google-box">
          <img src="asset/icon_google.png" />
        </div>
        <button class="google-text-btn">Google</button>
      </div>
    </div>
  </body>
</html>

<script>
setTimeout(function() {
  let notif = document.getElementById("notif-msg");
  let error = document.getElementById("error-msg");

  if (notif) {
    notif.style.display = "none";
  }

  if (error) {
    error.style.display = "none";
  }
}, 4000);

const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("password");

togglePassword.addEventListener("click", function () {

  if (password.type === "password") {
    password.type = "text";
    togglePassword.src = "asset/icon_eyeopen.png";
  } else {
    password.type = "password";
    togglePassword.src = "asset/icon_eyeclosed.png";
  }

});

</script>