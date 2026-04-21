<?php
include "koneksi.php";
include "Check_Login.php";
cekRole('admin');

$query_nota = mysqli_query($koneksi,"SELECT * FROM nota WHERE status='Sudah Dicek' ORDER BY id_nota DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lihat Hasil Pemeriksaan Barang</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* ===== RESET ===== */
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:"Poppins",sans-serif;
}

body{
background:#efefef;
}

/* ===== HEADER (sama persis input_nota) ===== */
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
text-decoration:none;
}

.back-btn img{
width:20px;
}

.header h2{
font-size:18px;
font-weight:500;
color:white;
}

/* ===== HEADER DECORATION (sama persis input_nota) ===== */
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

/* ===== CONTAINER ===== */
.container{
padding:25px 20px 80px;
}

/* ===== CARD (sama persis input_nota) ===== */
.card{
background:white;
padding:22px;
border-radius:22px;
box-shadow:0 15px 30px rgba(0,0,0,0.08);
margin-bottom:40px;
position:relative;
}

/* ===== FORM GROUP (sama persis input_nota) ===== */
.form-group{
margin-bottom:14px;
}

.form-group label{
font-size:13px;
font-weight:600;
color:#111;
}

.form-group input{
width:100%;
height:40px;
border:none;
border-radius:12px;
background:#e9edf2;
padding:0 12px;
margin-top:5px;
font-family:"Poppins",sans-serif;
font-size:13px;
}

/* ===== COLLAPSE ===== */
.detail-wrapper{
max-height:0;
overflow:hidden;
transition:0.5s;
}

.detail-wrapper.show{
max-height:2000px;
}

/* ===== JENIS LIST (sama persis input_nota) ===== */
.jenis-list{
display:flex;
flex-wrap:wrap;
gap:8px;
margin-top:10px;
margin-bottom:15px;
}

.jenis-item{
padding:6px 12px;
border-radius:12px;
border:1px solid #cfd4da;
font-size:12px;
background:#f5f6f8;
}

.jenis-active{
background:#7ea2b9;
color:white;
border:none;
}

/* ===== BARANG BOX (sama persis input_nota wrapper) ===== */
.barang-box{
background:#7ea2b9;
padding:15px;
border-radius:16px;
margin-top:12px;
}

.barang-title{
color:white;
font-size:13px;
font-weight:500;
margin-bottom:5px;
}

.barang-input{
width:100%;
height:40px;
border-radius:12px;
border:none;
background:#d7dbe1;
padding:0 12px;
margin-bottom:6px;
font-family:"Poppins",sans-serif;
font-size:13px;
}

.label-white{
color:white;
font-size:12px;
font-weight:500;
}

/* ===== STATUS BADGE ===== */
.status{
display:inline-block;
padding:4px 12px;
border-radius:10px;
font-size:12px;
font-weight:500;
margin-top:4px;
margin-bottom:6px;
}

.status-sesuai{
background:#3be000;
color:white;
}

.status-cacat{
background:red;
color:white;
}

/* ===== FOTO & KETERANGAN BOX ===== */
.foto-box,
.keterangan-box{
width:100%;
background:#d7dbe1;
border-radius:12px;
padding:10px 12px;
font-size:12px;
font-family:"Poppins",sans-serif;
margin-top:6px;
margin-bottom:6px;
}

/* ===== EXPAND BUTTON (sama persis input_nota gaya) ===== */
.expand-btn{
position:absolute;
bottom:-22px;
left:50%;
transform:translateX(-50%);
width:42px;
height:42px;
border-radius:50%;
background:#e0e4e8;
display:flex;
justify-content:center;
align-items:center;
box-shadow:0 8px 20px rgba(0,0,0,0.08);
cursor:pointer;
}

.expand-btn img{
transition:0.3s;
width:18px;
}

.expand-btn.active img{
transform:rotate(180deg);
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <a href="User_Kasir.php" class="back-btn">
        <img src="logo_back.png">
    </a>
    <h2>Lihat Hasil Pemeriksaan Barang</h2>
    <div class="header-circle-big"></div>
    <div class="header-circle-small"></div>
    <div class="header-circle-small_2"></div>
</div>

<div class="container">

<?php
while($nota=mysqli_fetch_assoc($query_nota)){

$id_nota=$nota['id_nota'];

$query_barang=mysqli_query($koneksi,"SELECT * FROM barang WHERE id_nota='$id_nota'");
$query_jenis=mysqli_query($koneksi,"SELECT DISTINCT jenis_barang FROM barang WHERE id_nota='$id_nota'");

$jenis_aktif=[];
while($j=mysqli_fetch_assoc($query_jenis)){
    $jenis_aktif[]=$j['jenis_barang'];
}
?>

<div class="card" id="card-<?php echo $id_nota; ?>">

    <!-- DATA YANG SELALU TAMPIL -->
    <div class="form-group">
        <label>Nomer Nota</label>
        <input value="<?php echo $nota['nomor_nota']; ?>" readonly>
    </div>

    <div class="form-group">
        <label>Tanggal Nota</label>
        <input value="<?php echo $nota['tanggal_nota']; ?>" readonly>
    </div>

    <!-- DETAIL -->
    <div class="detail-wrapper" id="detail-<?php echo $id_nota; ?>">

        <div class="form-group">
            <label>Nama Supplier</label>
            <input value="<?php echo $nota['supplier']; ?>" readonly>
        </div>

        <label style="font-size:13px;font-weight:600;">Jenis Barang</label>
        <div class="jenis-list">
        <?php
        $semua_jenis=[
            "Material Bangunan",
            "Besi & Logam",
            "Keramik & Lantai",
            "Alat Pertukangan",
            "Kayu & Olahan",
            "Listrik"
        ];

        foreach($semua_jenis as $jenis){
            $class = in_array($jenis,$jenis_aktif) ? "jenis-item jenis-active" : "jenis-item";
        ?>
            <span class="<?php echo $class; ?>"><?php echo $jenis; ?></span>
        <?php } ?>
        </div>

        <?php
        $no=1;
        while($barang=mysqli_fetch_assoc($query_barang)){
        ?>

        <div class="barang-box">

            <div class="barang-title">Barang ke-<?php echo $no; ?></div>

            <input class="barang-input" value="<?php echo $barang['nama_barang']; ?>" readonly>

            <label class="label-white">Jumlah</label>
            <input class="barang-input" value="<?php echo $barang['jumlah_barang']; ?>" readonly>

            <label class="label-white">Status</label>
            <div class="status <?php echo ($barang['status_barang']=='Cacat') ? 'status-cacat' : 'status-sesuai'; ?>">
                <?php echo $barang['status_barang']; ?>
            </div>

            <?php if($barang['status_barang']=='Cacat'){ ?>

            <div style="margin-top:10px;"></div>
            <label class="label-white">Foto Bukti</label>
            <div class="foto-box"><?php echo $barang['foto_bukti']; ?></div>

            <label class="label-white">Keterangan / Keluhan</label>
            <div class="keterangan-box"><?php echo $barang['keterangan']; ?></div>

            <?php } ?>

        </div>

        <?php $no++; } ?>

    </div>

    <div class="expand-btn" onclick="toggleNota('<?php echo $id_nota; ?>',this)">
        <img src="logo_down.png">
    </div>

</div>

<?php } ?>

</div>

<script>
function toggleNota(id,btn){
    var detail = document.getElementById("detail-"+id);
    detail.classList.toggle("show");
    btn.classList.toggle("active");
}
</script>

</body>
</html>