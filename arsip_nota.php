<?php
include "koneksi.php";

/* =======================
   AMBIL DATA NOTA
======================= */
$data_nota = mysqli_query($koneksi,"
SELECT * FROM nota
WHERE id_nota NOT IN (
    SELECT id_nota FROM arsip_nota
)
");

/* =======================
   SIMPAN ARSIP
======================= */
if(isset($_POST['arsip'])){

    $id_nota = $_POST['id_nota'];
    $tanggal = date("Y-m-d");

    mysqli_query($koneksi,"
    INSERT INTO arsip_nota (id_nota, tanggal_arsip)
    VALUES ('$id_nota','$tanggal')
    ");

    header("Location: arsip_nota.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Arsip Nota</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
*{font-family:Poppins;margin:0;padding:0;box-sizing:border-box;}
body{background:#efefef;}

.container{padding:20px;}

.card{
background:white;
border-radius:20px;
padding:20px;
margin-bottom:20px;
box-shadow:0 10px 20px rgba(0,0,0,0.1);
position:relative;
}

.form-group input{
width:100%;
height:40px;
border:none;
border-radius:10px;
background:#e9edf2;
margin-bottom:10px;
padding:10px;
}

/* EXPAND */
.detail{
max-height:0;
overflow:hidden;
transition:0.4s;
}

.detail.show{
max-height:2000px;
}

.expand{
text-align:center;
cursor:pointer;
margin:10px 0;
}

/* BARANG */
.barang{
background:#7ea2b9;
padding:15px;
border-radius:15px;
margin-top:10px;
}

.barang input{
width:100%;
margin-bottom:5px;
border:none;
border-radius:8px;
padding:8px;
}

/* BUTTON */
.btn{
width:100%;
height:45px;
border:none;
border-radius:15px;
background:#5e91b2;
color:white;
font-weight:bold;
margin-top:10px;
}

/* LAMPIRAN */
.lampiran{
background:#ddd;
padding:20px;
border-radius:15px;
text-align:center;
cursor:pointer;
}
</style>
</head>

<body>

<div class="container">

<?php while($n = mysqli_fetch_assoc($data_nota)){ ?>

<div class="card">

<form method="POST">

<input type="hidden" name="id_nota" value="<?= $n['id_nota'] ?>">

<div class="form-group">
<label>Nomer Nota</label>
<input value="<?= $n['nomor_nota'] ?>" readonly>
</div>

<div class="form-group">
<label>Tanggal Nota</label>
<input value="<?= $n['tanggal_nota'] ?>" readonly>
</div>

<div class="expand" onclick="toggle(this)">▼</div>

<div class="detail">

<div class="form-group">
<label>Nama Supplier</label>
<input value="<?= $n['supplier'] ?>" readonly>
</div>

<!-- JENIS BARANG -->
<div>
<?php
$jenis = mysqli_query($koneksi,"
SELECT DISTINCT jenis_barang FROM barang
WHERE id_nota='".$n['id_nota']."'
");

while($j = mysqli_fetch_assoc($jenis)){
echo "<span style='background:#eee;padding:5px 10px;border-radius:10px;margin:2px;display:inline-block;'>".$j['jenis_barang']."</span>";
}
?>
</div>

<!-- BARANG -->
<?php
$data_barang = mysqli_query($koneksi,"
SELECT * FROM barang
WHERE id_nota='".$n['id_nota']."'
");

$no=1;
while($b = mysqli_fetch_assoc($data_barang)){
?>

<div class="barang">

<b>Barang ke-<?= $no ?></b>

<input value="<?= $b['nama_barang'] ?>" readonly>

<label>Jumlah</label>
<input value="<?= $b['jumlah_barang'] ?>" readonly>

<label>Status</label>
<input value="<?= $b['status_barang'] ?>" readonly>

<?php if($b['status_barang'] == 'cacat'){ ?>

<label>Foto Dokumentasi</label>
<div class="lampiran" onclick="openFile('<?= $b['foto_bukti'] ?>')">
<?= $b['foto_bukti'] ?>
</div>

<label>Keterangan</label>
<textarea><?= $b['keterangan'] ?></textarea>

<?php } ?>

</div>

<?php $no++; } ?>

<!-- LAMPIRAN NOTA -->
<label>Lampiran Nota</label>
<div class="lampiran" onclick="openFile('<?= $n['foto'] ?>')">
<?= $n['foto'] ?>
</div>

<button class="btn" name="arsip">
Arsipkan Nota
</button>

</div>

</form>

</div>

<?php } ?>

</div>

<script>
function toggle(el){
let detail = el.nextElementSibling;
detail.classList.toggle("show");
el.innerHTML = detail.classList.contains("show") ? "▲" : "▼";
}

function openFile(file){
if(file){
window.open("uploads/"+file,"_blank");
}
}
</script>

</body>
</html>