<?php
include "koneksi.php";

/* ================= FILTER ================= */
$filter = $_GET['filter'] ?? 'menunggu';

$whereStatus = "";

if($filter == "disetujui"){
    $whereStatus = "AND l.status_laporan = 'Disetujui'";
}elseif($filter == "ditolak"){
    $whereStatus = "AND l.status_laporan = 'Ditolak'";
}else{
    $whereStatus = "AND l.status_laporan = 'Menunggu Persetujuan'";
}

/* ================= HANDLE SUBMIT ================= */
if(isset($_POST['submit_review'])){

$id_nota = $_POST['id_nota'];
$status = $_POST['status'] ?? '';
$catatan = $_POST['catatan'] ?? '';

if($status == ''){
    echo "<script>alert('Status belum dipilih!');history.back();</script>";
    exit();
}

if(!in_array($status, ['Disetujui','Ditolak'])){
    echo "<script>alert('Status tidak valid!');history.back();</script>";
    exit();
}

if($status == "Ditolak" && empty($catatan)){
    echo "<script>alert('Catatan revisi wajib diisi jika ditolak!');history.back();</script>";
    exit();
}

$status_safe = mysqli_real_escape_string($koneksi,$status);
$catatan_safe = mysqli_real_escape_string($koneksi,$catatan);

$cek = mysqli_query($koneksi,"SELECT * FROM laporan WHERE id_nota='$id_nota'");
$ada = mysqli_num_rows($cek);

if($ada > 0){
mysqli_query($koneksi,"
UPDATE laporan SET 
status_laporan='$status_safe',
catatanrevisi='$catatan_safe'
WHERE id_nota='$id_nota'
");
}else{
mysqli_query($koneksi,"
INSERT INTO laporan (id_nota, tanggal_laporan, status_laporan, catatanrevisi)
VALUES ('$id_nota', NOW(), '$status_safe', '$catatan_safe')
");
}

if($status == "Disetujui"){
    header("Location: status_success_yes_review.php");
} else {
    header("Location: status_success_no_review.php");
}
exit();
}

/* ================= QUERY ================= */
$query = mysqli_query($koneksi,"
SELECT n.*, l.status_laporan, l.catatanrevisi
FROM nota n
LEFT JOIN laporan l ON l.id_nota = n.id_nota
WHERE n.status='Sudah Dicek' $whereStatus
ORDER BY n.id_nota DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* ==== CSS ASLI LO (TIDAK DIUBAH) ==== */
*{font-family:Poppins;margin:0;padding:0;box-sizing:border-box;}
html, body{overflow-x:hidden;overflow-y:auto;}
body{background:#efefef;}

.header{background:#3f7aa3;color:white;padding:18px;display:flex;align-items:center;gap:10px;position:relative;}
.back{width:35px;height:35px;background:#48b5c1;border-radius:50%;display:flex;align-items:center;justify-content:center;}

.circle-big{position:absolute;width:90px;height:90px;background:#5bb7c5;border-radius:50%;right:-10px;top:10px;}
.circle-small{position:absolute;width:40px;height:40px;background:#5bb7c5;border-radius:50%;left:-10px;top:50px;}

.tabs{display:flex;gap:10px;padding:15px;}
.tab{padding:6px 14px;border-radius:12px;border:2px solid #ccc;font-size:13px;text-decoration:none;color:black;}
.active-tab{border:2px solid #3f7aa3;color:#3f7aa3;font-weight:500;}

.card{background:white;margin:15px;margin-bottom:40px;padding:18px;border-radius:20px;box-shadow:0 10px 25px rgba(0,0,0,0.1);position:relative;}
.input{width:100%;height:42px;border:none;border-radius:14px;background:#e9edf2;margin-top:5px;padding:10px;color:#000;}

.status{position:absolute;right:20px;top:18px;font-size:12px;color:#3f7aa3;}

.detail{display:none;}
.detail.show{display:block;}

.box{background:#7ea2b9;border-radius:20px;padding:18px;margin-top:15px;color:white;position:relative;}

.dot-wrap{position:absolute;top:10px;right:15px;display:flex;gap:6px;}
.dot{width:12px;height:12px;background:#63c9d5;border-radius:50%;}

.label-title{font-weight:600;font-size:15px;margin-bottom:8px;}
.label-strong{font-weight:600;font-size:15px;margin-top:10px;}

.field{width:100%;border:none;border-radius:14px;background:#F0F0F9;padding:10px;margin-top:5px;color:#000;}

.btn-row{display:flex;gap:10px;margin-top:10px;}
.btn{flex:1;padding:10px;border-radius:12px;border:2px solid transparent;background:transparent;cursor:pointer;}

.sesuai{border:2px solid #00ff66;}
.sesuai.active{background:#00ff66;}

.cacat{border:2px solid red;}
.cacat.active{background:red;color:white;}

.upload{background:#e5e7eb;border-radius:20px;padding:25px;text-align:center;margin-top:10px;cursor:pointer;}

.textarea{width:100%;border:none;border-radius:12px;background:#d7dbe1;padding:10px;margin-top:10px;}

.catatan-box{display:none;}
.submit{width:100%;margin-top:10px;background:#5e91b2;color:white;border:none;padding:12px;border-radius:12px;}

.expand{position:absolute;bottom:-20px;left:50%;transform:translateX(-50%);width:40px;height:40px;background:#e0e4e8;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;}

/* TAMBAHAN (jenis indikator) */
.jenis-wrap{margin-top:10px;}
.jenis-item{
display:inline-block;
padding:5px 10px;
border-radius:10px;
background:#cfd8dc;
margin:3px;
font-size:11px;
color:black;
}
.jenis-item.active{
background:#7ea2b9;
color:white;
}
</style>
</head>

<body>

<div class="header">
<div class="back">←</div>
<h3>Review Laporan Barang Masuk</h3>
<div class="circle-big"></div>
<div class="circle-small"></div>
</div>

<div class="tabs">
<a href="?filter=disetujui" class="tab <?= $filter=='disetujui'?'active-tab':'' ?>">Sudah Disetujui</a>
<a href="?filter=ditolak" class="tab <?= $filter=='ditolak'?'active-tab':'' ?>">Ditolak</a>
<a href="?filter=menunggu" class="tab <?= $filter=='menunggu'?'active-tab':'' ?>">Menunggu Persetujuan</a>
</div>

<?php while($n=mysqli_fetch_assoc($query)){ ?>

<div class="card">

<div class="status"><?= $n['status_laporan'] ?></div>

<label>Nomer Nota</label>
<input class="input" value="<?= $n['nomor_nota'] ?>" readonly>

<label>Tanggal Nota</label>
<input class="input" value="<?= $n['tanggal_nota'] ?>" readonly>

<div class="detail" id="detail-<?= $n['id_nota'] ?>">

<label>Nama Supplier</label>
<input class="input" value="<?= $n['supplier'] ?>" readonly>

<?php
$barang = mysqli_query($koneksi,"SELECT * FROM barang WHERE id_nota='".$n['id_nota']."'");

$dataBarang = [];
$jenisAktif = [];

while($b=mysqli_fetch_assoc($barang)){
    $dataBarang[] = $b;
    $jenisAktif[] = $b['jenis_barang'];
}

$semua_jenis = [
"Material Bangunan","Besi & Logam","Listrik",
"Keramik & Lantai","Alat Pertukangan","Kayu & Olahan"
];
?>

<!-- ✅ JENIS GLOBAL -->
<div class="jenis-wrap">
<?php foreach($semua_jenis as $j){ ?>
<span class="jenis-item <?= in_array($j,$jenisAktif)?'active':'' ?>">
<?= $j ?>
</span>
<?php } ?>
</div>

<?php $i=1; foreach($dataBarang as $b){ ?>

<div class="box">

<div class="label-title">Barang ke-<?= $i++ ?></div>
<input class="field" value="<?= $b['nama_barang'] ?>" readonly>

<div class="label-strong">Jumlah</div>
<input class="field" value="<?= $b['jumlah_barang'] ?>" readonly>

<div class="btn-row">
<button class="btn sesuai <?= $b['status_barang']=='Sesuai'?'active':'' ?>">Sesuai</button>
<button class="btn cacat <?= $b['status_barang']=='Cacat'?'active':'' ?>">Cacat</button>
</div>

<?php if($b['status_barang']=='Cacat'){ ?>

<div class="label-strong">Lampiran Bukti</div>
<div class="upload"><?= $b['foto_bukti'] ?></div>

<div class="label-strong">Keterangan / Keluhan</div>
<textarea class="textarea" readonly><?= $b['keterangan'] ?></textarea>

<?php } ?>

</div>

<?php } ?>

<?php
/* ✅ AMBIL RETUR SEKALI */
$retur = mysqli_query($koneksi,"
SELECT r.*
FROM retur r
JOIN barang b ON b.id_barang=r.id_barang
WHERE b.id_nota='".$n['id_nota']."'
ORDER BY r.id_retur DESC
LIMIT 1
");
$r = mysqli_fetch_assoc($retur);
?>

<?php if($r){ ?>

<!-- ✅ TANGGAPAN GLOBAL -->
<div class="box">

<div class="label-strong">Tanggapan & Tindak Lanjut Supplier</div>
<textarea class="textarea" readonly><?= $r['tanggapan'] ?></textarea>

<div class="label-strong">Lampiran Bukti Tindak Lanjut</div>
<div class="upload"><?= $r['tindaklanjut'] ?></div>

</div>

<?php } ?>

<form method="POST">
<input type="hidden" name="id_nota" value="<?= $n['id_nota'] ?>">
<input type="hidden" name="status" id="status-<?= $n['id_nota'] ?>">

<div class="btn-row">
<button type="button" class="btn sesuai" onclick="setStatus(this,'Disetujui',<?= $n['id_nota'] ?>)">Approve</button>
<button type="button" class="btn cacat" onclick="setStatus(this,'Ditolak',<?= $n['id_nota'] ?>)">Reject</button>
</div>

<div class="catatan-box">
<textarea name="catatan" class="textarea"><?= $n['catatanrevisi'] ?></textarea>
</div>

<button type="submit" name="submit_review" class="submit">Konfirmasi</button>
</form>

</div>

<div class="expand" onclick="toggle('<?= $n['id_nota'] ?>')">⌄</div>

</div>

<?php } ?>

<script>
function toggle(id){
document.getElementById("detail-"+id).classList.toggle("show");
}

function setStatus(btn,val,id){
let form = btn.closest("form");
form.querySelector("#status-"+id).value = val;

btn.parentElement.querySelectorAll(".btn").forEach(b=>b.classList.remove("active"));
btn.classList.add("active");

let catatanBox = form.querySelector(".catatan-box");
catatanBox.style.display = (val === "Ditolak") ? "block" : "none";
}
</script>

</body>
</html>