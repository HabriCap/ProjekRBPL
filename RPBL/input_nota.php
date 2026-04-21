<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['login'])) {
    header("Location: LoginRPBL.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: LoginRPBL.php");
    exit();
}


if (isset($_POST['submit'])) {

    $nomor = $_POST['nomor_nota'];
    $tanggal = $_POST['tanggal'];
    $supplier = $_POST['supplier'];
    $jenis = $_POST['jenis_barang'];
    $dibuat_oleh = $_SESSION['username'];

    // Upload foto
    $foto = "";
    if ($_FILES['foto']['name'] != "") {
        $namaFile = time() . "_" . $_FILES['foto']['name'];
        move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $namaFile);
        $foto = $namaFile;
    }

    // Simpan nota (arsip)
    mysqli_query($koneksi, "INSERT INTO nota 
    (nomor_nota,tanggal,supplier,foto)
    VALUES ('$nomor_nota','$tanggal','$supplier','$foto')");

    // PROSES BARANG MASUK KE TABEL BARANG
    foreach ($_POST['nama_barang'] as $key => $value) {

        $nama_barang = $value;
        $jumlah = $_POST['jumlah_barang'][$key];

        // Cek apakah barang sudah ada
        $cek = mysqli_query($koneksi, 
            "SELECT * FROM barang WHERE nama_barang='$nama_barang'"
        );

        if (mysqli_num_rows($cek) > 0) {
            // Kalau sudah ada → tambah jumlah
            mysqli_query($koneksi,
                "UPDATE barang 
                 SET jumlah = jumlah + $jumlah_barang
                 WHERE nama_barang='$nama_barang'"
            );
        } else {
            // Kalau belum ada → insert baru
            mysqli_query($koneksi,
                "INSERT INTO barang (nama_barang,jumlah_barang,jenis_barang)
                 VALUES ('$nama_barang','$jumlah_barang','$jenis_barang')"
            );
        }
    }

    header("Location: input_nota.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Input Nota</title>

<style>
body{
  margin:0;
  background:#f4f6f8;
  display:flex;
  justify-content:center;
  font-family:'Segoe UI',sans-serif;
}
.mobile{
  max-width:420px;
  width:100%;
  min-height:100vh;
  background:white;
}
.header{
  background:#3f7fa6;
  color:white;
  padding:18px;
}
.container{padding:20px;}
.card{
  background:#fff;
  padding:18px;
  border-radius:18px;
  box-shadow:0 4px 10px rgba(0,0,0,0.1);
  margin-bottom:20px;
}
.card h4{margin-top:0;}
input,select{
  width:100%;
  padding:10px;
  border-radius:10px;
  border:1px solid #ddd;
  margin-bottom:12px;
}
.barang-box{
  background:#6fa0bd;
  padding:15px;
  border-radius:15px;
}
.barang-box input{
  background:#dbe8f2;
  border:none;
}
.btn-add{
  background:#3f7fa6;
  color:white;
  border:none;
  padding:8px 12px;
  border-radius:50%;
  cursor:pointer;
}
.submit-btn{
  width:100%;
  background:#5d99c1;
  border:none;
  padding:12px;
  border-radius:12px;
  color:white;
  font-weight:bold;
}
.upload-box{
  border:2px dashed #ddd;
  padding:20px;
  text-align:center;
  border-radius:15px;
  color:#999;
}
</style>
</head>
<body>

<div class="mobile">
<div class="header">
  <strong>⬅ Input Nota Barang Masuk</strong>
</div>

<div class="container">

<form method="POST" enctype="multipart/form-data">

<div class="card">
<h4>Informasi Barang Masuk</h4>

<label>Nomor Nota</label>
<input type="text" name="nomor_nota" required>

<label>Tanggal Nota</label>
<input type="date" name="tanggal" required>

<label>Nama Supplier</label>
<input type="text" name="supplier" required>

<label>Jenis Barang</label>
<select name="jenis_barang" required>
<option>Material Bangunan</option>
<option>Besi & Logam</option>
<option>Listrik</option>
<option>Keramik & Lantai</option>
<option>Alat Pertukangan</option>
<option>Kayu & Olahan</option>
</select>

<div class="barang-box" id="barang-wrapper">
  <div class="barang-item">
    <input type="text" name="nama_barang[]" placeholder="Nama Barang">
    <input type="number" name="jumlah[]" placeholder="Jumlah">
  </div>
</div>

<br>
<button type="button" class="btn-add" onclick="tambahBarang()">+</button>

</div>

<div class="card">
<h4>Lampiran Foto Nota</h4>
<div class="upload-box">
<input type="file" name="foto">
</div>
</div>

<button type="submit" name="submit" class="submit-btn">
Submit Data Nota
</button>

</form>

</div>
</div>

<script>
function tambahBarang(){
    var wrapper = document.getElementById("barang-wrapper");
    var div = document.createElement("div");
    div.classList.add("barang-item");
    div.innerHTML = `
        <br>
        <input type="text" name="nama_barang[]" placeholder="Nama Barang">
        <input type="number" name="jumlah[]" placeholder="Jumlah">
    `;
    wrapper.appendChild(div);
}
</script>

</body>
</html>
