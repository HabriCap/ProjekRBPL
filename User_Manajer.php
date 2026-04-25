<?php
include 'Check_Login.php';
CekRole('manager');

if (!isset($_SESSION['login'])) {
  header("Location: index.php");
  exit;
}

if ($_SESSION['role'] != 'manager') {
  header("Location: index.php");
  exit;
}
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Manajer Toko</title>

    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
      rel="stylesheet"
    />

    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
      }

      body {
        background: #efefef;
        min-height: 100vh;
      }

      .header {
        background: #3f7aa3;
        color: white;
        padding: 20px;
        position: relative;
        overflow: hidden;
      }

      .header h2 {
        margin-left: 10px;
        font-weight: 600;
      }

      .logout-btn {
        position: absolute;
        right: 20px;
        top: 20px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ffffff33;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        z-index: 10;
        text-decoration: none;
        color: white;
      }

      .header-circle-big {
        position: absolute;
        width: 120px;
        height: 120px;
        background: #5bb7c5;
        border-radius: 50%;
        right: 0px;
        top: -0px;
      }
      .header-circle-big_2 {
        position: absolute;
        width: 40px;
        height: 40px;
        background: #b2e3ec;
        border-radius: 50%;
        right: 8px;
        top: 4px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        z-index: 10;
      }
      .header-circle-big_2 img {
        width: 22px;
        height: 22px;
      }
      .header-circle-small {
        position: absolute;
        width: 45px;
        height: 45px;
        background: #5bb7c5;
        border-radius: 50%;
        left: -11px;
        top: 51px;
      }
      .header-circle-small_2 {
        position: absolute;
        width: 18px;
        height: 18px;
        background: #519eaa;
        border-radius: 50%;
        left: 1px;
        top: 23px;
      }
      .header-circle-small_3 {
        position: absolute;
        width: 14px;
        height: 14px;
        background: #519eaa;
        border-radius: 50%;
        left: 45px;
        top: 53px;
      }

      .container {
        padding: 25px 20px;
      }

      /* Dev mode badge */
      .dev-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #1a1a2e;
        color: #bc8cff;
        font-size: 11px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        border: 1px solid #bc8cff55;
        margin-bottom: 16px;
        text-decoration: none;
        letter-spacing: 0.03em;
      }

      .dev-badge:hover {
        background: #2a2a3e;
      }

      .welcome-card {
        background: #3a6984;
        color: white;
        padding: 25px;
        border-radius: 18px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        position: relative;
        margin-bottom: 30px;
        margin-top: 35px;
      }

      .welcome-card h3 {
        font-weight: 600;
        margin-bottom: 10px;
      }

      .welcome-card p {
        font-size: 14px;
        line-height: 1.6;
      }
      .welcome-circle {
        position: absolute;
        width: 62px;
        height: 62px;
        background: #48b5c1;
        border-radius: 50%;
        top: -40px;
        left: -10px;
      }
      .welcome-circle_2 {
        position: absolute;
        width: 26px;
        height: 26px;
        background: #3c96a0;
        border-radius: 50%;
        top: -48px;
        left: 55px;
      }
      .welcome-circle_3 {
        position: absolute;
        width: 17px;
        height: 17px;
        background: #66d2de;
        border-radius: 50%;
        top: -21px;
        left: 77px;
      }
      .card-dots {
        position: absolute;
        bottom: -12px;
        right: 30px;
        display: flex;
        gap: 10px;
      }

      .card-dots span {
        width: 20px;
        height: 20px;
        background: #68d4e0;
        border-radius: 50%;
      }

      .menu-card {
        background: white;
        padding: 20px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        cursor: pointer;
        transition: 0.2s;
      }

      .menu-card:hover {
        transform: translateY(-3px);
      }

      .menu-left {
        display: flex;
        align-items: center;
        gap: 15px;
      }

      .menu-icon {
        width: 52px;
        min-width: 52px;
        height: 45px;
        border: 2px solid #7ca7c0;
        border-radius: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #3f7aa3;
        font-size: 18px;
      }
      .menu-icon img {
        width: 26px;
      }

      .menu-title {
        font-weight: 500;
        font-size: 16px;
      }

      .menu-arrow {
        width: 35px;
        height: 35px;
        background: #e4e9ee;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
      }
      .menu-arrow img {
        width: 23px;
      }

      @media (max-width: 480px) {
        .container {
          padding: 20px 15px;
        }
      }
    </style>
  </head>

  <body>
    <!-- HEADER -->
    <div class="header">
      <h2>Beranda</h2>
      <a href="logout.php" class="logout-btn" title="Logout">⮕</a>
      <div class="header-circle-big"></div>
      <a href="logout.php" class="header-circle-big_2" title="Logout">
        <img src="UI_GENERAL/logo_out_acc.png" alt="Logout" />
      </a>
      <div class="header-circle-small"></div>
      <div class="header-circle-small_2"></div>
      <div class="header-circle-small_3"></div>
    </div>

    <!-- CONTENT -->
    <div class="container">

      <?php if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1'): ?>
      <a href="dev_switch.php" class="dev-badge">🛠 Dev Mode · Ganti Role</a>
      <?php endif; ?>

      <!-- Welcome -->
      <div class="welcome-card">
        <div class="welcome-circle"></div>
        <div class="welcome-circle_2"></div>
        <div class="welcome-circle_3"></div>

        <h3>Selamat Datang, Manajer Toko</h3>
        <p>
          Tinjau dan validasi laporan barang masuk untuk memastikan akurasi sebelum diarsipkan
        </p>

        <div class="card-dots">
          <span></span>
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>

      <!-- Menu 1 -->
      <a href="review_laporan.php" style="text-decoration:none; color:inherit;">
      <div class="menu-card">
        <div class="menu-left">
          <div class="menu-icon">
            <img src="UI_MANAJER/logo_outbox_manajer.png" alt="" />
          </div>
          <div class="menu-title">Review Laporan Barang Masuk</div>
        </div>
        <div class="menu-arrow">
          <img src="UI_GENERAL/logo_foward.png" alt="" />
        </div>
      </div>
      </a>

    </div>
  </body>
</html>