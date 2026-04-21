<?php
include "koneksi.php";
include 'Check_Login.php';
cekRole('kasir');

if (!isset($_SESSION['login'])) {
  header("Location: index.php");
  exit;
}

if ($_SESSION['role'] != 'kasir') {
  header("Location: index.php");
  exit;
}
$query_nota = mysqli_query($koneksi, "SELECT * FROM nota ORDER BY id_nota DESC");

if(!$query_nota){
  die("Query nota gagal");
}

if(isset($_POST['simpan'])){
  
  if(isset($_POST['hasil'])){

foreach($_POST['hasil'] as $id_barang => $hasil){

$keterangan = $_POST['keterangan'][$id_barang] ?? "";

/* ================= UPLOAD FOTO ================= */

$nama_file = $_FILES['foto_bukti']['name'][$id_barang] ?? "";
$tmp_file = $_FILES['foto_bukti']['tmp_name'][$id_barang] ?? "";

$foto_simpan = "";

if($nama_file != ""){

$foto_simpan = time()."_".$nama_file;

move_uploaded_file($tmp_file,"uploads/".$foto_simpan);

}

/* ================= UPDATE BARANG ================= */

mysqli_query($koneksi,"UPDATE barang SET 
status_barang='$hasil',
foto_bukti='$foto_simpan',
keterangan='$keterangan'
WHERE id_barang='$id_barang'");

}

}

  $id_nota = $_POST['id_nota'];

  mysqli_query($koneksi, "UPDATE nota SET status='Sudah Dicek' WHERE id_nota='$id_nota'");

  header("Location: success_check_barang.php");
  exit;
}
?>

<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pengecekkan Barang Fisik</title>

    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
      rel="stylesheet"
    />

    <style>
      /* ===================================================== */
      /* ===================== RESET ========================= */
      /* ===================================================== */

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

      /* ===================================================== */
      /* ===================== HEADER ======================== */
      /* ===================================================== */

      .header {
        background: #3f7aa3;
        color: white;
        padding: 18px 20px;
        position: relative;
        overflow: hidden;

        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      /* Left group */
      .header-left {
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 2;
      }

      .header h2 {
        font-weight: 500;
        font-size: 18px;
      }

      /* Back button */
      .back-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #48b5c1;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
      }

      .back-btn img {
        width: 20px;
      }

      /* Logout */
      .logout-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #ffffff33;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        z-index: 2;
      }

      /* Decorative circles */
      .header-circle-big {
        position: absolute;
        width: 90px;
        height: 90px;
        background: #5bb7c5;
        border-radius: 50%;
        right: -20px;
        top: 13px;
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

      .status-wrapper{
        margin-top:15px;
      }
      /* ===================================================== */
      /* ===================== CONTENT ======================= */
      /* ===================================================== */

      .container {
        padding: 25px 20px 70px;
      }

      .page-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #1f2937;
      }

      /* ===================================================== */
      /* ===================== FORM CARD ===================== */
      /* ===================================================== */

      .form-card {
        background: white;
        padding: 25px 20px 35px;
        border-radius: 24px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        position: relative;
        margin-bottom:60px;
      }

      .form-group {
        margin-bottom: 18px;
      }

      .form-group label {
        font-size: 16px;
        font-weight: 800;
        display: block;
        margin-bottom: 8px;
        color: #111827;
      }

      .form-group input {
        width: 100%;
        height: 50px;
        border-radius: 16px;
        border: none;
        background: #e9edf2;
        padding: 0 15px;
        font-size: 14px;
        font-weight: 500;
        outline: none;
      }

      /* ================== JENIS BARANG ================== */

      .jenis-wrapper{
        margin-bottom:20px;
      }

      .jenis-wrapper label{
        font-size:16px;
        font-weight:800;
        display:block;
        margin-bottom:10px;
      }

        .jenis-list{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
      }

        .jenis-item{
        padding:6px 12px;
        border-radius:12px;
        border:2px solid #cfd4da;
        font-size:13px;
        color:#4b5563;
        background:white;
      }

        .jenis-active{
        background:#7ea8b6;
        color:white;
        border:none;
      }

      .detail-wrapper{
        max-height:0;
        overflow:hidden;
        transition:0.5s;

      }

      .detail-wrapper.show{
        max-height:2000px;
      }
      /* ================== BUTTON ================== */
      .btn-sesuai.active{
        background:#3be000;
        color:white;
      }

      .btn-cacat.active{
        background:red;
        color:white;
      }
      /* ================== BOX BARANG ================== */

        .barang-box{
        background:#7ea2b9;
        padding:18px;
        border-radius:20px;
        margin-top:15px;
        position:relative;
      }

        .barang-title{
        color:white;
        font-weight:600;
        margin-bottom:10px;
      }

        .barang-input{
        width:100%;
        height:42px;
        border-radius:14px;
        border:none;
        background:#d7dbe1;
        margin-bottom:10px;
        padding:10px;
      }

        .barang-btn{
        display:flex;
        gap:10px;
        margin-top:5px;
      }

        .btn-sesuai{
        flex:1;
        background:#7ea2b9;
        border:2px solid green;
        border-radius:12px;
        height:32px;
        font-weight:600;
      }

        .btn-cacat{
        flex:1;
        background:#7ea2b9;
        border:2px solid red;
        border-radius:12px;
        height:32px;
        font-weight:600;
      }

        .btn-sesuai.active{
        background:#00ff66;
        color:white;
      }
        .btn-cacat.active{
        background:red;
        color:white;
        }
        .btn-simpan{
        width:100%;
        margin-top:15px;
        height:40px;
        border:none;
        border-radius:16px;
        background:#5e91b2;
        color:white;
        font-weight:600;
      }

      .barang-wrapper{
        display:none;
        margin-top:15px;
      }

      .cacat-section{
        display:none;
        margin-top:10px;
      }

      .cacat-section.show{
        display:block;
      }

      .label-cacat{
color:white;
font-size:13px;
margin-top:10px;
display:block;
margin-bottom:6px;
}

.upload-bukti{
width:100%;
height:70px;
border-radius:16px;
background:#d7dbe1;
display:flex;
align-items:center;
justify-content:center;
font-size:13px;
color:#6b7280;
cursor:pointer;
margin-bottom:10px;
}

.upload-bukti:hover{
background:#cfd4da;
}

.input-keterangan{
width:100%;
border-radius:16px;
border:none;
background:#d7dbe1;
padding:12px;
font-size:13px;
min-height:80px;
resize:none;
}
      /* ===================================================== */
      /* ===================== STATUS BADGE ================== */
      /* ===================================================== */

      .status-wrapper {
        margin-top: 10px;
      }

      .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 14px;
        border: 2px solid #ff3b00;
        color: #ff3b00;
        font-size: 14px;
        font-weight: 500;
        background: white;
      }

      .status-bottom{
        margin-top:20px;
      }

      .detail-wrapper .status-top{
        display:none;
      }

      .form-card .status-bottom{
        display:none;
      }

      .detail-wrapper.show .status-bottom{
        display:block;
      }
      /* ===================================================== */
      /* ===================== EXPAND BUTTON ================= */
      /* ===================================================== */

      .expand-btn {
        position: absolute;
        bottom: -22px;
        left: 50%;
        transform: translateX(-50%);
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        cursor: pointer;
        transition: 0.2s;
      }

      .expand-btn img {
        transition:0.3s;
      }

      .expand-btn:hover {
        transform: translateX(-50%) translateY(-3px);
      }

      .expand-btn.active img{
        transform:rotate(180deg);
      }

      .form-card.expanded .status-top{
        display:none;
      }
      /* ===================================================== */
      /* ===================== RESPONSIVE ==================== */
      /* ===================================================== */

      @media (max-width: 480px) {
        .header h2 {
          font-size: 16px;
        }

        .back-btn,
        .logout-btn {
          width: 34px;
          height: 34px;
        }

        .page-title {
          font-size: 18px;
        }

        .form-group label {
          font-size: 15px;
        }

        .form-group input {
          height: 46px;
        }
      }
    </style>
  </head>

  <body>
    <!-- HEADER -->
    <div class="header">
      <div class="header-left">
        <a href="User_Kasir.php" class="back-btn">
          <img src="logo_back.png" alt="" />
        </a>
        <h2>Pengecekkan Barang Fisik</h2>
      </div>

      <div class="header-circle-big"></div>
      <div class="header-circle-small"></div>
      <div class="header-circle-small_2"></div>
      <div class="header-circle-small_3"></div>
    </div>

    <!-- CONTENT -->
    <div class="container">
      <h3 class="page-title">Input Hasil Pengecekkan Fisik Barang</h3>
 <?php
while($nota = mysqli_fetch_assoc($query_nota)){

$id_nota = $nota['id_nota'];

$query_barang = mysqli_query($koneksi,"SELECT * FROM barang WHERE id_nota='$id_nota'");
$query_jenis = mysqli_query($koneksi,"SELECT DISTINCT jenis_barang FROM barang WHERE id_nota='$id_nota'");

$jenis_aktif = [];

while($j = mysqli_fetch_assoc($query_jenis)) {
  $jenis_aktif[] = $j['jenis_barang'];
}
?>

<div class="form-card" id="card-<?php echo $id_nota; ?>">

<!-- DATA NOTA (SELALU TAMPIL) -->
<div class="form-group">
<label>Nomer Nota</label>
<input type="text" value="<?php echo $nota['id_nota']; ?>" readonly>
</div>

<div class="form-group">
<label>Tanggal Nota</label>
<input type="text" value="<?php echo $nota['tanggal_nota']; ?>" readonly>
</div>

<!-- STATUS -->
<div class="status-wrapper status-top">
<span class="status-badge">
<?php echo $nota['status'] ?? 'belum Dicek'; ?>
</span>
</div>


<!-- ================= DETAIL (DISEMBUNYIKAN) ================= -->
<div class="detail-wrapper" id="detail-<?php echo $id_nota; ?>">

<!-- JENIS BARANG -->
<div class="jenis-wrapper">
<label>Jenis Barang</label>

<div class="jenis-list">

<?php
$semua_jenis = [
"Material Bangunan",
"Besi & Logam",
"Keramik & Lantai",
"Alat Pertukangan",
"Kayu & Olahan",
"Listrik"
];

foreach($semua_jenis as $jenis){

if(in_array($jenis,$jenis_aktif)){
$class="jenis-item jenis-active";
}else{
$class="jenis-item";
}
?>

<span class="<?php echo $class; ?>">
<?php echo $jenis; ?>
</span>

<?php } ?>

</div>
</div>

<!-- DATA BARANG -->
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="id_nota" value="<?php echo $id_nota; ?>">
<?php
$no = 1;
while($barang = mysqli_fetch_assoc($query_barang)){
?>

<div class="barang-box">

<div class="barang-title">
Barang ke-<?php echo $no; ?>
</div>

<input class="barang-input"
type="text"
value="<?php echo $barang['nama_barang']; ?>"
readonly>

<label style="color:white;font-size:13px;">Jumlah</label>

<input class="barang-input"
type="number"
value="<?php echo $barang['jumlah_barang']; ?>"
readonly>

<div class="barang-btn">

<input type="hidden" 
name="hasil[<?php echo $barang['id_barang']; ?>]" 
id="hasil-<?php echo $barang['id_barang']; ?>">

<button
type="button"
class="btn-sesuai"
onclick="pilihStatus(<?php echo $barang['id_barang']; ?>,'Sesuai',this)">
Sesuai
</button>

<button
type="button"
class="btn-cacat"
onclick="pilihStatus(<?php echo $barang['id_barang']; ?>,'Cacat',this)">
Cacat
</button>

</div>

<div class="cacat-section" id="cacat-<?php echo $barang['id_barang']; ?>">

<label style="color:white;font-size:13px;">Lampiran Foto Bukti</label>

<input type="file"
name="foto_bukti[<?php echo $barang['id_barang']; ?>]"
class="barang-input">

<label style="color:white;font-size:13px;">Keterangan / Keluhan</label>

<textarea
name="keterangan[<?php echo $barang['id_barang']; ?>]"
class="barang-input"
style="height:80px;"></textarea>

</div>
</div>

<!-- SECTION CACAT -->

<?php
$no++;
}
?>

<button class="btn-simpan" type="submit" name="simpan">
Simpan Hasil Pemeriksaan
</button>

</form>

</div>

<!-- BUTTON EXPAND -->
<div class="expand-btn"
onclick="toggleBarang('<?php echo $id_nota; ?>', this)">
<img src="logo_down.png">
</div>
</div>

<?php
}
?>
  </div>
  <script>
   function toggleBarang(id,btn){

    var detail = document.getElementById("detail-"+id);
    var card = document.getElementById("card-"+id);

    if(detail) {
    detail.classList.toggle("show");
    btn.classList.toggle("active");
    card.classList.toggle("expanded");
    }
  }
   function pilihStatus(id_barang,status,btn){

var input = document.getElementById("hasil-"+id_barang);

if(input){
input.value = status;
}

var parent = btn.parentElement;

var sesuai = parent.querySelector(".btn-sesuai");
var cacat = parent.querySelector(".btn-cacat");

sesuai.classList.remove("active");
cacat.classList.remove("active");

btn.classList.add("active");

/* tampilkan section cacat */

var section = document.getElementById("cacat-"+id_barang);

if(section){

if(status=="Cacat"){
section.classList.add("show");
}else{
section.classList.remove("show");
}

}

}

document.querySelectorAll(".upload-bukti input").forEach(function(input){

input.addEventListener("change",function(){

let name = this.files[0]?.name;

if(name){
this.parentElement.querySelector("span").innerText = name;
}

});

});
  </script>
  </body>
</html>
