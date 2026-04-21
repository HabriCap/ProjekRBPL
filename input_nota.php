<?php 
include "koneksi.php";
include 'Check_Login.php';
cekRole('admin');

if (!isset($_SESSION['login'])) {
  header("Location: index.php");
  exit;
}

if ($_SESSION['role'] != 'admin') {
  header("Location: index.php");
  exit;
}

if(isset($_POST['submit'])){

$nomor_nota = $_POST['nomor_nota'];
$tanggal = $_POST['tanggal'];
$supplier = $_POST['supplier'];

$nama_barang = $_POST['nama_barang'];
$jumlah_barang = $_POST['jumlah_barang'];
$jenis_barang = $_POST['jenis_barang'];
/* UPLOAD FOTO */

$foto = $_FILES['foto_nota']['name'];
$tmp = $_FILES['foto_nota']['tmp_name'];

$path = "uploads/".$foto;

move_uploaded_file($tmp,$path);

/* INSERT NOTA */

mysqli_query($koneksi,"INSERT INTO nota
(nomor_nota,tanggal_nota,supplier,status,foto)
VALUES
('$nomor_nota','$tanggal','$supplier','Belum Dicek','$foto')");

$id_nota = mysqli_insert_id($koneksi);
/* INSERT BARANG */

for($i=0;$i<count($nama_barang);$i++){

$barang = $nama_barang[$i];
$jumlah = $jumlah_barang[$i];
$jenis = $jenis_barang[$i];

if($barang == "" || $jumlah == ""){
continue;
}

mysqli_query($koneksi,"INSERT INTO barang
(id_nota,nama_barang,jumlah_barang,jenis_barang,status_barang)
VALUES
('$id_nota','$barang','$jumlah','$jenis','Belum Dicek')");

}

header("Location: input_nota_success.php");
exit;
}

$semua_jenis = [
  "Material Bangunan",
  "Besi & Logam",
  "Listrik",
  "Keramik & Lantai",
  "Alat Pertukangan",
  "Kayu & Olahan"
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Input Nota Barang Masuk</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:"Poppins",sans-serif;
}

body{
background:#efefef;
}

/* HEADER */

.header{
background:#3f7aa3;
color:white;
padding:18px 20px;
position:relative;
display:flex;
align-items:center;
gap:12px;
}

.back-btn{
width:38px;
height:38px;
border-radius:50%;
background:#48b5c1;
display:flex;
align-items:center;
justify-content:center;
cursor:pointer;
}

.back-btn img{
width:20px;
}

.header h2{
font-size:18px;
font-weight:500;
}

/* DECORATION */

.header-circle-big{
position:absolute;
width:90px;
height:90px;
background:#5bb7c5;
border-radius:50%;
right:-20px;
top:10px;
}

.header-circle-small{
position:absolute;
width:45px;
height:45px;
background:#5bb7c5;
border-radius:50%;
left:-11px;
top:50px;
}

.header-circle-small_2{
position:absolute;
width:18px;
height:18px;
background:#519eaa;
border-radius:50%;
left:0;
top:22px;
}

/* CONTENT */

.container{
padding:25px 20px 80px;
}

/* CARD */

.card{
background:white;
padding:22px;
border-radius:22px;
box-shadow:0 15px 30px rgba(0,0,0,0.08);
margin-bottom:22px;
}

.card h3{
font-size:16px;
margin-bottom:15px;
}

/* INPUT */

.form-group{
margin-bottom:14px;
}

.form-group label{
font-size:13px;
font-weight:600;
}

.form-group input{
width:100%;
height:40px;
border:none;
border-radius:12px;
background:#e9edf2;
padding:0 12px;
margin-top:5px;
}

/* JENIS BARANG */

.jenis-list{
display:flex;
flex-wrap:wrap;
gap:8px;
margin-top:10px;
}

.jenis-item{
padding:6px 12px;
border-radius:12px;
border:1px solid #cfd4da;
font-size:12px;
background:#f5f6f8;
cursor:pointer;
transition:0.2s;
}

.jenis-item.active{
background:#7ea2b9;
color:white;
border:none;
}
/* ADD BUTTON */

.add-btn{
width:30px;
height:30px;
background:#5e91b2;
color:white;
border:none;
border-radius:8px;
font-size:18px;
cursor:pointer;
margin-top:10px;
}

/* BARANG BOX */

.barang-wrapper{
background:#7ea2b9;
padding:15px;
border-radius:16px;
margin-top:12px;
}

.barang-box{
margin-bottom:15px;
}

.barang-title{
color:white;
font-size:13px;
margin-bottom:5px;
}

.barang-input{
width:100%;
height:36px;
border-radius:10px;
border:none;
background:#d7dbe1;
padding:8px;
margin-bottom:6px;
}

.barang-jenis .jenis-item{
background:#ffffff;
border:1px solid #cfd4da;
color:#374151;
}

.barang-jenis .jenis-item.active{
background:#3f7aa3;
color:white;
border:none;
}
/* DECORATIVE DOTS */

.dot-wrapper{
position:absolute;
right:20px;
top:8px;
display:flex;
gap:8px;
}

.dot{
width:14px;
height:14px;
background:#63c9d5;
border-radius:50%;
}

/* UPLOAD BOX */

.upload-box{
background:#e5e5e5;
height:120px;
border-radius:18px;
display:flex;
flex-direction:column;
align-items:center;
justify-content:center;
color:#9aa1a7;
font-size:12px;
text-align:center;
}

/* BUTTON */

.submit-btn{
width:100%;
height:45px;
border:none;
border-radius:14px;
background:#5e91b2;
color:white;
font-weight:600;
font-size:14px;
cursor:pointer;
}

</style>
</head>

<body>

<!-- HEADER -->

<div class="header">

<div class="back-btn">
<img src="logo_back.png">
</div>

<h2>Input Nota Barang Masuk</h2>

<div class="header-circle-big"></div>
<div class="header-circle-small"></div>
<div class="header-circle-small_2"></div>

</div>

<!-- CONTENT -->
<form method="POST" enctype="multipart/form-data">

<div class="container">

<!-- INFORMASI NOTA -->

<div class="card">

<h3>Informasi Barang Masuk</h3>

<div class="form-group">
<label>Nomer Nota</label>
<input type="text" name="nomor_nota" required>
</div>

<div class="form-group">
<label>Tanggal Nota</label>
<input type="date" name="tanggal" required>
</div>

<div class="form-group">
<label>Nama Supplier</label>
<input type="text" name="supplier" required>
</div>

<label>Jenis Barang</label>
<div class="jenis-list" id="jenis-indikator">

<div class="jenis-item" data-jenis="Material Bangunan">Material Bangunan</div>
<div class="jenis-item" data-jenis="Besi & Logam">Besi & Logam</div>
<div class="jenis-item" data-jenis="Listrik">Listrik</div>
<div class="jenis-item" data-jenis="Keramik & Lantai">Keramik & Lantai</div>
<div class="jenis-item" data-jenis="Alat Pertukangan">Alat Pertukangan</div>
<div class="jenis-item" data-jenis="Kayu & Olahan">Kayu & Olahan</div>

</div>

<button type="button" class="add-btn" onclick="tambahBarang()">+</button>

<div class="barang-wrapper" id="barangWrapper">

<!-- BARANG 1 -->
<div class="barang-box">

<div class="barang-title">Barang ke-1</div>

<input class="barang-input" name="nama_barang[]" placeholder="Nama Barang" required>

<label style="color:white;font-size:12px;">Jumlah</label>
<input class="barang-input" type="number" name="jumlah_barang[]" required>

<label style="color:white;font-size:12px;">Jenis Barang</label>
<select name="jenis_barang[]" class="barang-input" required onchange="updateJenis()">

<option value="">-- Pilih Jenis Barang --</option>

<?php foreach($semua_jenis as $j){ ?>
<option value="<?php echo $j; ?>"><?php echo $j; ?></option>
<?php } ?>

</select>

</div>

</div>

</div>

<div class="card">

<h3>Lampiran Foto Nota</h3>

<label class="upload-box">

<input type="file" name="foto_nota" accept="image/*" hidden required>

<div style="font-size:20px;">＋</div>

Upload Foto Nota<br>
(JPG / PNG, maks. 5 MB)

</label>

</div>

<button class="submit-btn" name="submit">
Submit Data Nota
</button>

</div>

</form>

<div style="margin-top:10px;font-size:13px;color:#333;">
Jenis Aktif : <b id="jenisAktifLabel">-</b>
</div>

<script>

let nomorBarang = 2;

function tambahBarang(){

let wrapper = document.getElementById("barangWrapper");

let html = `
<div class="barang-box">

<div class="barang-title">Barang ke-${nomorBarang}</div>

<input class="barang-input" name="nama_barang[]" placeholder="Nama Barang" required>

<label style="color:white;font-size:12px;">Jumlah</label>
<input class="barang-input" type="number" name="jumlah_barang[]" required>

<label style="color:white;font-size:12px;">Jenis Barang</label>
<select name="jenis_barang[]" class="barang-input" required onchange="updateJenis()">

<option value="">-- Pilih Jenis Barang --</option>
<option value="Material Bangunan">Material Bangunan</option>
<option value="Besi & Logam">Besi & Logam</option>
<option value="Listrik">Listrik</option>
<option value="Keramik & Lantai">Keramik & Lantai</option>
<option value="Alat Pertukangan">Alat Pertukangan</option>
<option value="Kayu & Olahan">Kayu & Olahan</option>

</select>

</div>
`;

wrapper.insertAdjacentHTML("beforeend",html);

nomorBarang++;

}

function updateJenis(){

let selected = [];

let selects = document.querySelectorAll("select[name='jenis_barang[]']");

selects.forEach(function(s){
if(s.value !== ""){
selected.push(s.value);
}
});

let items = document.querySelectorAll("#jenis-indikator .jenis-item");

items.forEach(function(item){
item.classList.remove("active");

let jenis = item.getAttribute("data-jenis");

if(selected.includes(jenis)){
item.classList.add("active");
}
});

}

</script>

</body>
</html>