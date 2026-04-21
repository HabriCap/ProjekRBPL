<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: LoginRPBL.php");
    exit;
}

if ($_SESSION['role'] != 'kasir') {
    header("Location: LoginRPBL.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Kasir</title>

<style>
:root{
  --primary:#3f7fa6;
  --accent:#4bb3c1;
  --bg:#f2f2f2;
}
*{
box-sizing:border-box;
font-family:'Segoe UI',sans-serif;
}
body{margin:0;
background:var(--bg);
display:flex;
justify-content:center;
}
.mobile-frame{
  width:100%;
  max-width:420px;
  min-height:100vh;
  background:#f7f7f7;
  position:relative;
  overflow:hidden;
}
.header{
  background:var(--primary);
  color:white;
  padding:18px;
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.header h3{margin:0;font-weight:500;}
.logout-btn{
  background:#ffffff33;
  border:none;
  padding:6px 10px;
  border-radius:50%;
  cursor:pointer;
}
.welcome{
  background:#416f89;
  margin:20px;
  padding:20px;
  border-radius:18px;
  color:white;
  box-shadow:0 5px 12px rgba(255, 255, 255, 0.15);
}
.welcome h4{margin:0 0 8px 0;}
.welcome p{margin:0;font-size:14px;line-height:1.5;}

.menu{padding:0 20px 20px 20px;}
.menu-card{
  background:white;
  padding:18px;
  border-radius:18px;
  margin-bottom:15px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  box-shadow:0 4px 10px rgba(0,0,0,0.1);
  cursor:pointer;
  transition:0.2s;
}
.menu-card:hover{transform:scale(1.03);}
.menu-text{font-size:14px;font-weight:500;}
.arrow{
  background:#e9eef3;
  width:30px;height:30px;
  display:flex;align-items:center;justify-content:center;
  border-radius:50%;
}
.highlight{
  border:2px solid #1e90ff;
}
</style>
</head>
<body>

<div class="mobile-frame">

<div class="header">
  <h3>Beranda</h3>
  <form action="logout.php" method="post">
    <button class="logout-btn">↪</button>
  </form>
</div>

<div class="welcome">
  <h4>Selamat Datang, Kasir Toko 👋</h4>
  <p>
    Lakukan pengecekan fisik barang, perbarui status,
    dan susun laporan barang masuk secara akurat.
  </p>
</div>

<div class="menu">

  <div class="menu-card highlight" onclick="location.href='cek_barang.php'">
    <div class="menu-text">🔍 Pengecekkan Barang Fisik</div>
    <div class="arrow">➜</div>
  </div>

  <div class="menu-card" onclick="location.href='buat_laporan.php'">
    <div class="menu-text">➕ Buat Laporan Barang Masuk</div>
    <div class="arrow">➜</div>
  </div>

  <div class="menu-card" onclick="location.href='revisi_laporan.php'">
    <div class="menu-text">✏ Revisi Laporan</div>
    <div class="arrow">➜</div>
  </div>

  <div class="menu-card" onclick="location.href='arsip_laporan.php'">
    <div class="menu-text">🗂 Arsip Laporan</div>
    <div class="arrow">➜</div>
  </div>

</div>

</div>
</body>
</html>