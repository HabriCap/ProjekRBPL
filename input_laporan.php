<?php
include "koneksi.php";

/* HANDLE SUBMIT */
if(isset($_POST['simpan_laporan'])){
    $id_nota = $_POST['id_nota'];

    if(!empty($id_nota)){

        // CEK BIAR TIDAK DOUBLE
        $cek = mysqli_query($koneksi,"SELECT * FROM laporan WHERE id_nota='$id_nota'");

        if(mysqli_num_rows($cek) == 0){

            $insert = mysqli_query($koneksi,"
            INSERT INTO laporan (id_nota, tanggal_laporan)
            VALUES ('$id_nota', NOW())
            ");

            if(!$insert){
                die("Query gagal: ".mysqli_error($koneksi));
            }
        }
    }

    header("Location: success_check_barang.php");
    exit();
}

/* ambil nota yang sudah dicek */
$data_nota = mysqli_query($koneksi,"
SELECT DISTINCT n.*
FROM nota n
JOIN barang b ON b.id_nota = n.id_nota
WHERE n.status = 'Sudah Dicek'
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins;}
body{background:#efefef;}

.header{
background:#3f7aa3;
color:white;
padding:18px 20px;
position:relative;
display:flex;
align-items:center;
}

.header h2{font-size:18px;}

.circle-big{
position:absolute;
right:-20px;
top:10px;
width:90px;height:90px;
background:#5bb7c5;
border-radius:50%;
}

.circle-small{
position:absolute;
left:-10px;
top:50px;
width:40px;height:40px;
background:#5bb7c5;
border-radius:50%;
}

.container{padding:20px;}

.card{
background:white;
border-radius:20px;
padding:20px;
box-shadow:0 10px 25px rgba(0,0,0,0.1);
position:relative;
margin-bottom:20px;
}

.input{
width:100%;
height:38px;
border:none;
border-radius:12px;
background:#e9edf2;
margin-top:5px;
padding:8px;
}

.expand{
width:40px;height:40px;
border-radius:50%;
background:#e0e4e8;
display:flex;
align-items:center;
justify-content:center;
margin:auto;
margin-top:10px;
cursor:pointer;
}

.detail{
max-height:0;
overflow:hidden;
transition:0.4s;
}

.detail.show{max-height:2000px;}

.barang{
background:#7ea2b9;
border-radius:18px;
padding:15px;
margin-top:15px;
position:relative;
}

.dot-wrap{
position:absolute;
top:10px;
right:15px;
display:flex;
gap:8px;
}

.dot{
width:14px;height:14px;
background:#63c9d5;
border-radius:50%;
}

.label{
color:white;
font-size:12px;
margin-top:5px;
}

.field{
width:100%;
border:none;
border-radius:10px;
background:#d7dbe1;
padding:8px;
margin-top:4px;
}

.status{
display:flex;
gap:10px;
margin-top:10px;
}

.status button{
flex:1;
border:none;
padding:8px;
border-radius:10px;
color:white;
}

.green{background:#4cd964;}
.red{background:#ff3b30;}

.upload{
background:#e5e5e5;
border-radius:15px;
padding:20px;
text-align:center;
margin-top:10px;
font-size:12px;
cursor:pointer;
}

.submit{
width:100%;
background:#5e91b2;
color:white;
border:none;
padding:12px;
border-radius:14px;
margin-top:15px;
}
</style>
</head>

<body>

<div class="header">
<h2>Buat Laporan Barang Masuk</h2>
<div class="circle-big"></div>
<div class="circle-small"></div>
</div>

<div class="container">

<?php $no=1; while($n = mysqli_fetch_assoc($data_nota)) { ?>

<form method="POST">

<input type="hidden" name="id_nota" value="<?= $n['id_nota'] ?>">

<div class="card">

<div>
<label>Nomer Nota</label>
<input class="input" value="<?= $n['nomor_nota'] ?>">

<label style="margin-top:10px;">Tanggal Nota</label>
<input class="input" value="<?= $n['tanggal_nota'] ?>">
</div>

<!-- tombol dalam -->
<div class="expand" id="btn-inside-<?= $no ?>" onclick="toggle(<?= $no ?>)">⌄</div>

<!-- EXPAND -->
<div class="detail" id="detail-<?= $no ?>">

<label style="margin-top:10px;">Nama Supplier</label>
<input class="input" value="<?= $n['supplier'] ?>">

<?php
$data_barang = mysqli_query($koneksi,"
SELECT * FROM barang WHERE id_nota='".$n['id_nota']."'
");

$noBarang=1;
while($b = mysqli_fetch_assoc($data_barang)){
?>

<div class="barang">

<div class="dot-wrap">
<div class="dot"></div>
<div class="dot"></div>
<div class="dot"></div>
</div>

<div class="label">Barang ke-<?= $noBarang++ ?></div>
<input class="field" value="<?= $b['nama_barang'] ?>">

<div class="label">Jumlah</div>
<input class="field" value="<?= $b['jumlah_barang'] ?>">

<div class="status">
<button class="green" style="<?= $b['status_barang']=='Sesuai'?'':'opacity:.3;' ?>">Sesuai</button>
<button class="red" style="<?= $b['status_barang']=='Cacat'?'':'opacity:.3;' ?>">Cacat</button>
</div>

<?php if($b['status_barang']=='Cacat'){ ?>

<div class="label">Lampiran Bukti</div>
<div class="upload" onclick="openFile('<?= $b['foto_bukti'] ?>')">
<?= $b['foto_bukti'] ?>
</div>

<div class="label">Keterangan / Keluhan</div>
<div class="upload"><?= $b['keterangan'] ?></div>

<?php } ?>

</div>

<?php } ?>

<?php
$retur = mysqli_query($koneksi,"
SELECT * FROM retur r
JOIN barang b ON b.id_barang=r.id_barang
WHERE b.id_nota='".$n['id_nota']."'
LIMIT 1
");
$r = mysqli_fetch_assoc($retur);
?>

<?php if($r){ ?>

<div class="barang">

<div class="label">Tanggapan & Tindak Lanjut Supplier</div>
<div class="upload"><?= $r['tanggapan'] ?></div>

<div class="label">Lampiran Bukti Tindak Lanjut</div>
<div class="upload" onclick="openFile('<?= $r['tindaklanjut'] ?>')">
<?= $r['tindaklanjut'] ?>
</div>

</div>

<?php } ?>

<button type="submit" class="submit" name="simpan_laporan">Simpan Hasil Laporan</button>

</div>

</div>

</form>

<div class="expand" id="btn-outside-<?= $no ?>" style="display:none;" onclick="toggle(<?= $no ?>)">⌃</div>

<?php $no++; } ?>

</div>

<script>
function toggle(id){
    let detail = document.getElementById("detail-"+id);
    let insideBtn = document.getElementById("btn-inside-"+id);
    let outsideBtn = document.getElementById("btn-outside-"+id);

    detail.classList.toggle("show");

    if(detail.classList.contains("show")){
        insideBtn.style.display = "none";
        outsideBtn.style.display = "flex";
    } else {
        insideBtn.style.display = "flex";
        outsideBtn.style.display = "none";
    }
}

function openFile(file){
    if(file){
        window.open("uploads/"+file,"_blank");
    }
}
</script>

</body>
</html>