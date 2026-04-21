<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: LoginRPBL.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    header("Location: LoginRPBL.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin</title>

<style>
:root{
  --primary:#3f7fa6;
  --soft:#eaf4fb;
  --bg:#f4f6f8;
}

*{
  box-sizing:border-box;
  font-family: 'Segoe UI', sans-serif;
}

body{
  margin:0;
  background:var(--bg);
  display:flex;
  justify-content:center;
}

/* Frame HP */
.mobile-frame{
  width:100%;
  max-width:420px;
  min-height:100vh;
  background:white;
  position:relative;
  overflow:hidden;
}

/* Header */
.header{
  background:var(--primary);
  color:white;
  padding:18px;
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.header h3{
  margin:0;
  font-weight:600;
}

.logout-btn{
  background:white;
  color:var(--primary);
  border:none;
  padding:6px 10px;
  border-radius:50px;
  font-size:12px;
  cursor:pointer;
}

/* Welcome Card */
.welcome{
  background:var(--primary);
  margin:20px;
  padding:20px;
  border-radius:18px;
  color:white;
  box-shadow:0 6px 15px rgba(0,0,0,0.1);
}

.welcome h4{
  margin:0 0 10px 0;
}

.welcome p{
  margin:0;
  font-size:14px;
  line-height:1.5;
}

/* Menu Cards */
.menu{
  padding:0 20px 20px 20px;
}

.menu-card{
  background:#fff;
  padding:18px;
  border-radius:18px;
  margin-bottom:15px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  box-shadow:0 4px 10px rgba(0,0,0,0.08);
  cursor:pointer;
  transition:0.2s;
}

.menu-card:hover{
  transform:scale(1.03);
}

.menu-text{
  font-size:14px;
  font-weight:500;
}

.arrow{
  background:#e9eef3;
  width:30px;
  height:30px;
  display:flex;
  justify-content:center;
  align-items:center;
  border-radius:50%;
  font-size:14px;
}
</style>
</head>
<body>

<div class="mobile-frame">

  <div class="header">
    <h3>Beranda</h3>
    <form action="logout.php" method="post">
      <button class="logout-btn">Logout</button>
    </form>
  </div>

  <div class="welcome">
    <h4>Selamat Datang, Admin Gudang 👋</h4>
    <p>
      Kelola pencatatan barang masuk, retur,
      dan arsip nota untuk memastikan data
      gudang tercatat dengan rapi dan akurat.
    </p>
  </div>

  <div class="menu">

    <div class="menu-card" onclick="location.href='input_nota.php'">
      <div class="menu-text">📥 Input Nota Barang Masuk</div>
      <div class="arrow">➜</div>
    </div>

    <div class="menu-card" onclick="location.href='input_retur.php'">
      <div class="menu-text">🔄 Input Retur Barang</div>
      <div class="arrow">➜</div>
    </div>

    <div class="menu-card" onclick="location.href='hasil_pemeriksaan.php'">
      <div class="menu-text">🔍 Lihat Hasil Pemeriksaan Barang</div>
      <div class="arrow">➜</div>
    </div>

    <div class="menu-card" onclick="location.href='arsip_nota.php'">
      <div class="menu-text">🗂 Arsip Nota & Pembayaran</div>
      <div class="arrow">➜</div>
    </div>

  </div>

</div>

</body>
</html>