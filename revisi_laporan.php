<?php
include "koneksi.php";

/* ================= HANDLE UPDATE ================= */
if(isset($_POST['simpan_revisi'])){

    $id_nota = $_POST['id_nota'];

    for($i=0;$i<count($_POST['id_barang']);$i++){

        $id_barang    = $_POST['id_barang'][$i];
        $jumlah       = $_POST['jumlah'][$i];
        $status       = $_POST['status_barang'][$i]; // TAMBAHAN: baca status dari hidden input
        $keterangan   = isset($_POST['keterangan'][$i]) ? $_POST['keterangan'][$i] : '';
        $hapus        = isset($_POST['hapus_file'][$i]) ? $_POST['hapus_file'][$i] : 0;
        $fileLama     = isset($_POST['file_lama'][$i]) ? $_POST['file_lama'][$i] : '';

        /* Kalau status Sesuai, skip update foto & keterangan */
        if($status == 'Sesuai'){
            mysqli_query($koneksi,"
                UPDATE barang SET 
                jumlah_barang='$jumlah'
                WHERE id_barang='$id_barang'
            ");
            continue;
        }

        /* ================= HAPUS FILE ================= */
        if($hapus == 1){
            $fileLama = "";
        }

        /* ================= UPLOAD FILE BARU ================= */
        if(!empty($_FILES['foto']['name'][$i])){

            $fileName = $_FILES['foto']['name'][$i];
            $tmp      = $_FILES['foto']['tmp_name'][$i];

            $ext     = pathinfo($fileName, PATHINFO_EXTENSION);
            $newName = "bukti_".time()."_".$i.".".$ext;
            $path    = "uploads/".$newName;

            if(move_uploaded_file($tmp, $path)){
                $fileLama = $newName;
            }
        }

        /* ================= UPDATE DB ================= */
        mysqli_query($koneksi,"
            UPDATE barang SET 
            jumlah_barang='$jumlah',
            keterangan='$keterangan',
            foto_bukti='$fileLama'
            WHERE id_barang='$id_barang'
        ");
    }

    /* RESET STATUS */
    mysqli_query($koneksi,"
        UPDATE laporan 
        SET status_laporan='Menunggu Persetujuan'
        WHERE id_nota='$id_nota'
    ");

    mysqli_query($koneksi,"
        UPDATE barang 
        SET status_retur=NULL 
        WHERE id_nota='$id_nota'
    ");

    header("Location: status_success_revisi_laporan.php");
    exit();
}

/* ================= DATA ================= */
$query = mysqli_query($koneksi,"
    SELECT n.*, l.catatanrevisi
    FROM nota n
    JOIN laporan l ON l.id_nota=n.id_nota
    WHERE l.status_laporan='Ditolak'
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
*{font-family:Poppins;margin:0;padding:0;box-sizing:border-box;}
body{background:#efefef;}

.header{
    background:#3f7aa3;
    color:white;
    padding:18px;
    display:flex;
    align-items:center;
    position:relative;
    gap:10px;
}

.back{
    width:35px;height:35px;
    background:#48b5c1;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}

.circle-big{
    position:absolute;
    width:90px;height:90px;
    background:#5bb7c5;
    border-radius:50%;
    right:-20px;
    top:10px;
}

.circle-small{
    position:absolute;
    width:40px;height:40px;
    background:#5bb7c5;
    border-radius:50%;
    left:-10px;
    top:50px;
}

.container{padding:20px;}

.card{
    background:white;
    border-radius:20px;
    padding:20px;
    margin-bottom:10px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

.input{
    width:100%;
    height:40px;
    border:none;
    border-radius:12px;
    background:#e9edf2;
    margin-top:5px;
    padding:8px;
}

.detail{display:none;}
.detail.show{display:block;}

.catatan{
    background:#e9edf2;
    border-radius:12px;
    padding:10px;
    margin-top:10px;
    font-size:12px;
}

.box{
    background:#7ea2b9;
    border-radius:20px;
    padding:15px;
    margin-top:15px;
    color:white;
}

.status-row{
    display:flex;
    gap:10px;
    margin-top:10px;
}

.status-btn{
    flex:1;
    padding:6px;
    border-radius:8px;
    font-size:12px;
    text-align:center;
}

.active-green{background:#00ff66;}
.active-red{background:red;color:white;}
.outline-green{border:2px solid #00ff66;}
.outline-red{border:2px solid red;}

.upload{
    background:#dcdcdc;
    border-radius:18px;
    padding:30px;
    text-align:center;
    margin-top:10px;
    font-size:13px;
    color:black;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
}

.file-btn{
    display:flex;
    gap:5px;
    margin-top:5px;
}

.file-btn button{
    flex:1;
    padding:5px;
    font-size:11px;
    border-radius:8px;
    cursor:pointer;
}

.delete{border:2px solid red;background:white;color:red;}
.add{border:2px solid #5e91b2;background:white;color:#5e91b2;}
.active-delete{background:red;color:white;}
.active-add{background:#5e91b2;color:white;}

.expand{
    width:40px;height:40px;
    border-radius:50%;
    background:#e0e4e8;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:10px auto 30px;
    cursor:pointer;
}

.submit{
    width:100%;
    background:#5e91b2;
    color:white;
    border:none;
    padding:12px;
    border-radius:12px;
    margin-top:15px;
}
</style>
</head>

<body>

<div class="header">
    <div class="back">←</div>
    <h3>Revisi Laporan</h3>
    <div class="circle-big"></div>
    <div class="circle-small"></div>
</div>

<div class="container">

<?php while($n=mysqli_fetch_assoc($query)){ ?>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="id_nota" value="<?= $n['id_nota'] ?>">

<div class="card">

    <label>Nomer Nota</label>
    <input class="input" value="<?= $n['nomor_nota'] ?>" readonly>

    <label>Tanggal Nota</label>
    <input class="input" value="<?= $n['tanggal_nota'] ?>" readonly>

    <div class="detail" id="detail-<?= $n['id_nota'] ?>">

        <label>Nama Supplier</label>
        <input class="input" value="<?= $n['supplier'] ?>" readonly>

        <label>Catatan Revisi</label>
        <div class="catatan"><?= $n['catatanrevisi'] ?></div>

        <?php
        $barang = mysqli_query($koneksi,"SELECT * FROM barang WHERE id_nota='".$n['id_nota']."'");
        $i=0;

        while($b=mysqli_fetch_assoc($barang)){
        ?>

        <div class="box">

            <div><b>Barang ke-<?= $i+1 ?></b></div>

            <input class="input" value="<?= $b['nama_barang'] ?>" readonly>

            <div><b>Jumlah</b></div>
            <input name="jumlah[]" class="input" value="<?= $b['jumlah_barang'] ?>">

            <div class="status-row">
                <div class="status-btn <?= $b['status_barang']=='Sesuai'?'active-green':'outline-green' ?>">Sesuai</div>
                <div class="status-btn <?= $b['status_barang']=='Cacat'?'active-red':'outline-red' ?>">Cacat</div>
            </div>

            <?php if($b['status_barang']=='Cacat'){ ?>

            <div><b>Lampiran Bukti</b></div>

            <div class="upload" 
                 id="file-text-<?= $i ?>" 
                 data-file="<?= $b['foto_bukti'] ?>"
                 onclick="openFile(this)">
                <?= $b['foto_bukti'] ? $b['foto_bukti'] : "Tidak ada file" ?>
            </div>

            <div class="file-btn">
                <button type="button" class="delete" onclick="hapusFile(this,<?= $i ?>)">Hapus</button>
                <button type="button" class="add" onclick="tambahFile(this,<?= $i ?>)">Tambah</button>
            </div>

            <input type="file" name="foto[]" id="file-<?= $i ?>" style="display:none;">
            <input type="hidden" name="hapus_file[]" value="0" id="hapus-<?= $i ?>">
            <input type="hidden" name="file_lama[]" value="<?= $b['foto_bukti'] ?>">

            <div><b>Keterangan</b></div>
            <textarea name="keterangan[]" class="input"><?= $b['keterangan'] ?></textarea>

            <?php } else { ?>
            <!-- ✅ FIX: Dummy hidden inputs untuk barang Sesuai agar index array tetap sinkron -->
            <input type="file"   name="foto[]"      id="file-<?= $i ?>" style="display:none;">
            <input type="hidden" name="hapus_file[]" value="0">
            <input type="hidden" name="file_lama[]"  value="">
            <input type="hidden" name="keterangan[]" value="">
            <?php } ?>

            <!-- ✅ FIX: Tambah hidden input status_barang agar PHP tahu mana Sesuai/Cacat -->
            <input type="hidden" name="status_barang[]" value="<?= $b['status_barang'] ?>">
            <input type="hidden" name="id_barang[]" value="<?= $b['id_barang'] ?>">

        </div>

        <?php $i++; } ?>

        <button type="submit" name="simpan_revisi" class="submit">
            Simpan Hasil Revisi Laporan
        </button>

    </div>

</div>

<div class="expand" onclick="toggle('<?= $n['id_nota'] ?>')">⌄</div>

</form>

<?php } ?>

</div>

<script>
function toggle(id){
    let d=document.getElementById("detail-"+id);
    d.classList.toggle("show");
}

function openFile(el){
    let file = el.dataset.file;
    if(file){
        window.open("uploads/"+file,"_blank");
    }
}

function hapusFile(btn,i){
    let box = document.getElementById("file-text-"+i);
    box.innerText = "Tidak ada file";
    box.dataset.file = "";
    document.getElementById("hapus-"+i).value = 1;
    btn.classList.add("active-delete");
    btn.nextElementSibling.classList.remove("active-add");
}

function tambahFile(btn,i){
    let input = document.getElementById("file-"+i);
    let box   = document.getElementById("file-text-"+i);

    input.click();

    btn.classList.add("active-add");
    btn.previousElementSibling.classList.remove("active-delete");

    input.onchange = function(){
        let file = this.files[0];
        if(file){
            box.innerText = file.name;
            box.dataset.file = file.name;
            document.getElementById("hapus-"+i).value = 0;
        }
    };
}
</script>

</body>
</html>