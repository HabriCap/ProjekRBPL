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

$semua_jenis = [
  "Material Bangunan",
  "Besi & Logam",
  "Listrik",
  "Keramik & Lantai",
  "Alat Pertukangan",
  "Kayu & Olahan"
];

$errors = [];

if (isset($_POST['submit'])) {

  /* ── 1. SANITASI & VALIDASI INPUT ── */

  $nomor_nota = trim($_POST['nomor_nota'] ?? '');
  $tanggal    = trim($_POST['tanggal']    ?? '');
  $supplier   = trim($_POST['supplier']  ?? '');

  if (!preg_match('/^[a-zA-Z0-9\-\/]+$/', $nomor_nota)) {
    $errors[] = "Nomor nota hanya boleh mengandung huruf, angka, tanda hubung, atau garis miring.";
  }

  $dt = DateTime::createFromFormat('Y-m-d', $tanggal);
  if (!$dt || $dt->format('Y-m-d') !== $tanggal) {
    $errors[] = "Format tanggal tidak valid.";
  }

  if (!preg_match('/^[a-zA-Z0-9 .,\-]+$/u', $supplier) || strlen($supplier) > 100) {
    $errors[] = "Nama supplier tidak valid atau terlalu panjang (maks. 100 karakter).";
  }

  $nama_barang   = $_POST['nama_barang']   ?? [];
  $jumlah_barang = $_POST['jumlah_barang'] ?? [];
  $jenis_barang  = $_POST['jenis_barang']  ?? [];

  $barang_bersih = [];
  for ($i = 0; $i < count($nama_barang); $i++) {
    $nama   = trim($nama_barang[$i]   ?? '');
    $jumlah = trim($jumlah_barang[$i] ?? '');
    $jenis  = trim($jenis_barang[$i]  ?? '');

    if ($nama === '' && $jumlah === '') continue; 

    if ($nama === '') {
      $errors[] = "Nama barang ke-" . ($i + 1) . " tidak boleh kosong.";
      continue;
    }
    if (!is_numeric($jumlah) || (int)$jumlah <= 0) {
      $errors[] = "Jumlah barang ke-" . ($i + 1) . " harus berupa angka positif.";
      continue;
    }
    if (!in_array($jenis, $semua_jenis)) {
      $errors[] = "Jenis barang ke-" . ($i + 1) . " tidak valid.";
      continue;
    }

    $barang_bersih[] = [
      'nama'   => htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'),
      'jumlah' => (int)$jumlah,
      'jenis'  => $jenis
    ];
  }

  if (empty($barang_bersih)) {
    $errors[] = "Minimal satu barang harus diisi dengan lengkap.";
  }

  $foto_nama_db = '';

  if (!isset($_FILES['foto_nota']) || $_FILES['foto_nota']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = "Foto nota wajib diunggah.";
  } else {
    $file        = $_FILES['foto_nota'];
    $max_size    = 5 * 1024 * 1024; // 5 MB
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $allowed_mime = ['image/jpeg', 'image/png'];

    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);

    if ($file['size'] > $max_size) {
      $errors[] = "Ukuran foto melebihi batas 5 MB.";
    } elseif (!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mime)) {
      $errors[] = "Format foto tidak valid. Hanya JPG dan PNG yang diizinkan.";
    } else {
      // Rename dengan uniqid agar tidak bisa ditebak / ditimpa
      $foto_nama_db = uniqid('nota_', true) . '.' . $ext;
      $upload_path  = "uploads/" . $foto_nama_db;

      if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        $errors[] = "Gagal mengunggah foto. Pastikan folder uploads/ dapat ditulis.";
        $foto_nama_db = '';
      }
    }
  }

  if (empty($errors)) {

  //insert nota
    $stmt_nota = mysqli_prepare(
      $koneksi,
      "INSERT INTO nota (nomor_nota, tanggal_nota, supplier, status, foto)
       VALUES (?, ?, ?, 'Belum Dicek', ?)"
    );
    mysqli_stmt_bind_param($stmt_nota, 'ssss', $nomor_nota, $tanggal, $supplier, $foto_nama_db);
    mysqli_stmt_execute($stmt_nota);
    $id_nota = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt_nota);

  //insert barang terkait nota
    $stmt_barang = mysqli_prepare(
      $koneksi,
      "INSERT INTO barang (id_nota, nama_barang, jumlah_barang, jenis_barang, status_barang)
       VALUES (?, ?, ?, ?, 'Belum Dicek')"
    );

    foreach ($barang_bersih as $b) {
      mysqli_stmt_bind_param($stmt_barang, 'isis', $id_nota, $b['nama'], $b['jumlah'], $b['jenis']);
      mysqli_stmt_execute($stmt_barang);
    }
    mysqli_stmt_close($stmt_barang);

    header("Location: input_nota_success.php");
    exit;
  }
}
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
overflow:hidden;
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
border:none;
flex-shrink:0;
transition:background 0.2s;
}

.back-btn:hover{
background:#3aa0ac;
}

.back-btn img{
width:20px;
}

.header h2{
font-size:18px;
font-weight:500;
}

.header-circle-big{
position:absolute;
width:90px;
height:90px;
background:#5bb7c5;
border-radius:50%;
right:-20px;
top:10px;
pointer-events:none;
}

.header-circle-small{
position:absolute;
width:45px;
height:45px;
background:#5bb7c5;
border-radius:50%;
left:-11px;
top:50px;
pointer-events:none;
}

.header-circle-small_2{
position:absolute;
width:18px;
height:18px;
background:#519eaa;
border-radius:50%;
left:0;
top:22px;
pointer-events:none;
}

.container{
padding:25px 20px 80px;
}

.error-box{
background:#fff0f0;
border:1px solid #f5c2c7;
border-radius:14px;
padding:14px 16px;
margin-bottom:18px;
}

.error-box p{
font-size:13px;
font-weight:600;
color:#842029;
margin-bottom:6px;
}

.error-box ul{
padding-left:18px;
}

.error-box ul li{
font-size:12px;
color:#842029;
margin-bottom:3px;
}

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
cursor:default;
transition:0.2s;
}

.jenis-item.active{
background:#7ea2b9;
color:white;
border:none;
}

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
transition:background 0.2s;
}

.add-btn:hover{
background:#4a7a99;
}

.barang-wrapper{
background:#7ea2b9;
padding:15px;
border-radius:16px;
margin-top:12px;
}

.barang-box{
margin-bottom:15px;
position:relative;
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

.upload-box{
background:#e5e5e5;
min-height:120px;
border-radius:18px;
display:flex;
flex-direction:column;
align-items:center;
justify-content:center;
color:#9aa1a7;
font-size:12px;
text-align:center;
cursor:pointer;
transition:background 0.2s;
overflow:hidden;
padding:12px;
}

.upload-box:hover{
background:#d8d8d8;
}

.upload-box img#preview-foto{
max-width:100%;
max-height:200px;
border-radius:12px;
display:none;
margin-bottom:8px;
}

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
transition:background 0.2s;
}

.submit-btn:hover{
background:#4a7a99;
}

.jenis-aktif-info{
margin-top:12px;
font-size:13px;
color:#555;
}

</style>
</head>

<body>

<div class="header">

  <button class="back-btn" type="button" onclick="history.back()" title="Kembali">
    <img src="logo_back.png" alt="Kembali">
  </button>

  <h2>Input Nota Barang Masuk</h2>

  <div class="header-circle-big"></div>
  <div class="header-circle-small"></div>
  <div class="header-circle-small_2"></div>

</div>

<!-- FORM -->
<form method="POST" enctype="multipart/form-data">

<div class="container">
  <?php if (!empty($errors)): ?>
  <div class="error-box">
    <p>Terdapat kesalahan, mohon perbaiki:</p>
    <ul>
      <?php foreach ($errors as $err): ?>
        <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <div class="card">

    <h3>Informasi Barang Masuk</h3>

    <div class="form-group">
      <label>Nomor Nota</label>
      <input type="text" name="nomor_nota"
             value="<?php echo htmlspecialchars($_POST['nomor_nota'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
             placeholder="Contoh: NT-2024-001" required>
    </div>

    <div class="form-group">
      <label>Tanggal Nota</label>
      <input type="date" name="tanggal"
             value="<?php echo htmlspecialchars($_POST['tanggal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
             required>
    </div>

    <div class="form-group">
      <label>Nama Supplier</label>
      <input type="text" name="supplier"
             value="<?php echo htmlspecialchars($_POST['supplier'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
             placeholder="Nama perusahaan/toko supplier" maxlength="100" required>
    </div>

    <label style="font-size:13px;font-weight:600;">Jenis Barang (Indikator)</label>
    <div class="jenis-list" id="jenis-indikator">
      <?php foreach ($semua_jenis as $j): ?>
        <div class="jenis-item" data-jenis="<?php echo htmlspecialchars($j, ENT_QUOTES, 'UTF-8'); ?>">
          <?php echo htmlspecialchars($j, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="jenis-aktif-info">
      Jenis Aktif: <b id="jenisAktifLabel">-</b>
    </div>

    <button type="button" class="add-btn" onclick="tambahBarang()">+</button>

    <div class="barang-wrapper" id="barangWrapper">

      <!-- BARANG 1 -->
      <div class="barang-box">
        <div class="barang-title">Barang ke-1</div>
        <input class="barang-input" name="nama_barang[]" placeholder="Nama Barang" required>
        <label style="color:white;font-size:12px;">Jumlah</label>
        <input class="barang-input" type="number" name="jumlah_barang[]" min="1" required>
        <label style="color:white;font-size:12px;">Jenis Barang</label>
        <select name="jenis_barang[]" class="barang-input" required onchange="updateJenis()">
          <option value="">-- Pilih Jenis Barang --</option>
          <?php foreach ($semua_jenis as $j): ?>
            <option value="<?php echo htmlspecialchars($j, ENT_QUOTES, 'UTF-8'); ?>">
              <?php echo htmlspecialchars($j, ENT_QUOTES, 'UTF-8'); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

    </div>

  </div>

  <div class="card">

    <h3>Lampiran Foto Nota</h3>

    <label class="upload-box" for="foto_nota_input">
      <img id="preview-foto" src="" alt="Preview Foto">
      <div id="upload-placeholder">
        <div style="font-size:20px;">＋</div>
        Upload Foto Nota<br>
        (JPG / PNG, maks. 5 MB)
      </div>
    </label>

    <input type="file" id="foto_nota_input" name="foto_nota"
           accept="image/jpeg,image/png" hidden required
           onchange="previewFoto(this)">

  </div>

  <button class="submit-btn" type="submit" name="submit">
    Submit Data Nota
  </button>

</div>

</form>

<script>

const SEMUA_JENIS = <?php echo json_encode($semua_jenis, JSON_UNESCAPED_UNICODE); ?>;

let nomorBarang = 2;

function tambahBarang() {
  const wrapper = document.getElementById("barangWrapper");

  const opsiJenis = SEMUA_JENIS.map(j =>
    `<option value="${escHtml(j)}">${escHtml(j)}</option>`
  ).join('');

  const html = `
    <div class="barang-box">
      <div class="barang-title">Barang ke-${nomorBarang}</div>
      <input class="barang-input" name="nama_barang[]" placeholder="Nama Barang" required>
      <label style="color:white;font-size:12px;">Jumlah</label>
      <input class="barang-input" type="number" name="jumlah_barang[]" min="1" required>
      <label style="color:white;font-size:12px;">Jenis Barang</label>
      <select name="jenis_barang[]" class="barang-input" required onchange="updateJenis()">
        <option value="">-- Pilih Jenis Barang --</option>
        ${opsiJenis}
      </select>
    </div>`;

  wrapper.insertAdjacentHTML("beforeend", html);
  nomorBarang++;
}

/* ── Update indikator jenis aktif ── */
function updateJenis() {
  const selected = [];
  document.querySelectorAll("select[name='jenis_barang[]']").forEach(s => {
    if (s.value !== '') selected.push(s.value);
  });

  document.querySelectorAll("#jenis-indikator .jenis-item").forEach(item => {
    const jenis = item.getAttribute("data-jenis");
    item.classList.toggle("active", selected.includes(jenis));
  });

  document.getElementById("jenisAktifLabel").textContent =
    selected.length > 0 ? [...new Set(selected)].join(", ") : "-";
}

/* ── Preview foto sebelum upload ── */
function previewFoto(input) {
  const preview     = document.getElementById("preview-foto");
  const placeholder = document.getElementById("upload-placeholder");

  if (input.files && input.files[0]) {
    const file = input.files[0];

    if (file.size > 5 * 1024 * 1024) {
      alert("Ukuran file melebihi 5 MB. Pilih file yang lebih kecil.");
      input.value = '';
      return;
    }
    if (!['image/jpeg', 'image/png'].includes(file.type)) {
      alert("Format file tidak valid. Hanya JPG dan PNG yang diizinkan.");
      input.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      preview.style.display = 'block';
      placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
  }
}

function escHtml(str) {
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}
</script>

</body>
</html>