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

/* ─────────────────────────────────────────────
   AMBIL NOTA YANG PUNYA BARANG CACAT & BELUM DIRETUR
   (prepared statement)
───────────────────────────────────────────── */
$stmt_nota = mysqli_prepare($koneksi, "
  SELECT DISTINCT n.id_nota, n.nomor_nota, n.tanggal_nota, n.supplier
  FROM nota n
  JOIN barang b ON b.id_nota = n.id_nota
  WHERE LOWER(b.status_barang) = 'cacat'
  AND (b.status_retur IS NULL OR b.status_retur != 'sudah')
  ORDER BY n.id_nota DESC
");
mysqli_stmt_execute($stmt_nota);
$data_nota = mysqli_stmt_get_result($stmt_nota);
mysqli_stmt_close($stmt_nota);

/* ─────────────────────────────────────────────
   HANDLE FORM SUBMIT
───────────────────────────────────────────── */
$errors = [];

if (isset($_POST['submit'])) {

  /* ── 1. Validasi field utama ── */
  $nomor   = trim($_POST['nomor_retur']    ?? '');
  $tanggal = trim($_POST['tanggal_retur']  ?? '');
  $supplier = trim($_POST['retur_supplier'] ?? '');
  $tanggapan = trim($_POST['tanggapan']    ?? '');

  if ($nomor === '') {
    $errors[] = "Nomor retur tidak boleh kosong.";
  }

  $dt = DateTime::createFromFormat('Y-m-d', $tanggal);
  if (!$dt || $dt->format('Y-m-d') !== $tanggal) {
    $errors[] = "Format tanggal retur tidak valid.";
  }

  if ($supplier === '' || strlen($supplier) > 100) {
    $errors[] = "Nama supplier tidak valid atau terlalu panjang.";
  }

  $id_barang_list = $_POST['id_barang'] ?? [];
  $alasan_list    = $_POST['alasan']    ?? [];
  $jenis_list     = $_POST['jenis_retur'] ?? [];

  if (empty($id_barang_list)) {
    $errors[] = "Tidak ada barang yang dipilih untuk diretur.";
  }

  /* ── 2. Validasi & upload file tindak lanjut ── */
  $nama_file_global = "";
  $allowed_ext  = ['jpg', 'jpeg', 'png', 'pdf'];
  $allowed_mime = ['image/jpeg', 'image/png', 'application/pdf'];
  $max_size     = 5 * 1024 * 1024; // 5 MB

  $tl_error = $_FILES['tindaklanjut']['error'] ?? UPLOAD_ERR_NO_FILE;
  $tl_tmp   = $_FILES['tindaklanjut']['tmp_name'] ?? '';
  $tl_name  = $_FILES['tindaklanjut']['name'] ?? '';
  $tl_size  = $_FILES['tindaklanjut']['size'] ?? 0;

  if ($tl_error === UPLOAD_ERR_OK && $tl_name !== '') {
    $ext  = strtolower(pathinfo($tl_name, PATHINFO_EXTENSION));
    $mime = mime_content_type($tl_tmp);

    if ($tl_size > $max_size) {
      $errors[] = "File tindak lanjut melebihi batas 5 MB.";
    } elseif (!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mime)) {
      $errors[] = "Format file tindak lanjut tidak valid (JPG, PNG, atau PDF).";
    } else {
      $nama_file_global = uniqid('tl_', true) . '.' . $ext;
      if (!move_uploaded_file($tl_tmp, "uploads/" . $nama_file_global)) {
        $errors[] = "Gagal mengunggah file tindak lanjut.";
        $nama_file_global = "";
      }
    }
  }

  /* ── 3. Simpan ke DB jika tidak ada error ── */
  if (empty($errors)) {

    /* Prepared statement INSERT retur */
    $stmt_insert = mysqli_prepare($koneksi,
      "INSERT INTO retur
       (id_barang, nomor_retur, tanggal_retur, retur_supplier,
        jenis_retur, jumlah_cacat, alasan, foto_retur, tanggapan, tindaklanjut)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    /* Prepared statement UPDATE status_retur barang */
    $stmt_update = mysqli_prepare($koneksi,
      "UPDATE barang SET status_retur = 'sudah' WHERE id_barang = ?"
    );

    /* Prepared statement cek duplikat retur */
    $stmt_cek = mysqli_prepare($koneksi,
      "SELECT id_retur FROM retur WHERE id_barang = ?"
    );

    foreach ($id_barang_list as $i => $id_brg_raw) {

      /* Validasi id_barang integer */
      if (!ctype_digit((string)$id_brg_raw)) continue;
      $id_brg = (int)$id_brg_raw;

      /* Cek apakah barang sudah pernah diretur */
      mysqli_stmt_bind_param($stmt_cek, 'i', $id_brg);
      mysqli_stmt_execute($stmt_cek);
      $res_cek = mysqli_stmt_get_result($stmt_cek);
      if (mysqli_num_rows($res_cek) > 0) continue;

      $ket = trim($alasan_list[$i] ?? '');
      $jns = trim($jenis_list[$i] ?? '');

      /* Ambil data barang (jumlah & foto_bukti) dengan prepared statement */
      $stmt_brg = mysqli_prepare($koneksi,
        "SELECT jumlah_barang, foto_bukti FROM barang WHERE id_barang = ?"
      );
      mysqli_stmt_bind_param($stmt_brg, 'i', $id_brg);
      mysqli_stmt_execute($stmt_brg);
      $res_brg = mysqli_stmt_get_result($stmt_brg);
      $b = mysqli_fetch_assoc($res_brg);
      mysqli_stmt_close($stmt_brg);

      if (!$b) continue;

      $qty       = (int)$b['jumlah_barang'];
      $nama_foto = $b['foto_bukti'] ?? '';

      mysqli_stmt_bind_param(
        $stmt_insert, 'issssissss',
        $id_brg, $nomor, $tanggal, $supplier,
        $jns, $qty, $ket, $nama_foto, $tanggapan, $nama_file_global
      );
      mysqli_stmt_execute($stmt_insert);

      mysqli_stmt_bind_param($stmt_update, 'i', $id_brg);
      mysqli_stmt_execute($stmt_update);
    }

    mysqli_stmt_close($stmt_insert);
    mysqli_stmt_close($stmt_update);
    mysqli_stmt_close($stmt_cek);

    header("Location: status_success_input_retur.php");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Input Retur Barang</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* ===== RESET ===== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

body {
  background: #efefef;
}

/* ===== HEADER ===== */
.header {
  background: #3f7aa3;
  color: white;
  padding: 18px 20px;
  position: relative;
  display: flex;
  align-items: center;
  gap: 12px;
  overflow: hidden;
}

.back-btn {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #48b5c1;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  text-decoration: none;
  flex-shrink: 0;
  border: none;
  transition: background 0.2s;
}

.back-btn:hover {
  background: #3aa0ac;
}

.back-btn img {
  width: 20px;
}

.header h2 {
  font-size: 18px;
  font-weight: 500;
  color: white;
}

.header-circle-big {
  position: absolute;
  width: 90px;
  height: 90px;
  background: #5bb7c5;
  border-radius: 50%;
  right: -20px;
  top: 10px;
  pointer-events: none;
}

.header-circle-small {
  position: absolute;
  width: 45px;
  height: 45px;
  background: #5bb7c5;
  border-radius: 50%;
  left: -11px;
  top: 50px;
  pointer-events: none;
}

.header-circle-small_2 {
  position: absolute;
  width: 18px;
  height: 18px;
  background: #519eaa;
  border-radius: 50%;
  left: 0;
  top: 22px;
  pointer-events: none;
}

/* ===== CONTAINER ===== */
.container {
  padding: 25px 20px 80px;
}

/* ===== ERROR BOX ===== */
.error-box {
  background: #fff0f0;
  border: 1px solid #f5c2c7;
  border-radius: 14px;
  padding: 14px 16px;
  margin-bottom: 18px;
}

.error-box p {
  font-size: 13px;
  font-weight: 600;
  color: #842029;
  margin-bottom: 6px;
}

.error-box ul {
  padding-left: 18px;
}

.error-box ul li {
  font-size: 12px;
  color: #842029;
  margin-bottom: 3px;
}

/* ===== EMPTY STATE ===== */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #9ca3af;
}

.empty-state div {
  font-size: 40px;
  margin-bottom: 12px;
}

.empty-state p {
  font-size: 14px;
}

/* ===== CARD ===== */
.card {
  background: white;
  padding: 22px;
  border-radius: 22px;
  box-shadow: 0 15px 30px rgba(0,0,0,0.08);
  margin-bottom: 40px;
  position: relative;
}

.card.expanded {
  margin-bottom: 0;
}

/* ===== CLOSE BTN ===== */
.close-btn {
  position: absolute;
  top: -15px;
  left: 20px;
  background: white;
  border: 2px solid red;
  border-radius: 8px;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: red;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
}

/* ===== FORM GROUP ===== */
.form-group {
  margin-bottom: 14px;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #111;
}

.form-group input {
  width: 100%;
  height: 40px;
  border: none;
  border-radius: 12px;
  background: #e9edf2;
  padding: 0 12px;
  margin-top: 5px;
  font-family: "Poppins", sans-serif;
  font-size: 13px;
}

/* ===== BARANG BOX ===== */
.barang-box {
  background: #7ea2b9;
  padding: 15px;
  border-radius: 16px;
  margin-top: 15px;
}

.barang-title {
  color: white;
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 5px;
}

.barang-input {
  width: 100%;
  height: 40px;
  border-radius: 12px;
  border: none;
  background: #d7dbe1;
  padding: 0 12px;
  margin-bottom: 6px;
  font-family: "Poppins", sans-serif;
  font-size: 13px;
}

textarea.barang-input {
  height: 80px;
  padding: 10px 12px;
  resize: none;
  overflow-y: auto;
}

textarea.barang-input:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(94, 145, 178, 0.3);
}

/* ===== FOTO BUKTI (tetap tampilan box asli, + lightbox) ===== */
.upload-box {
  background: #d7dbe1;
  height: 90px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  color: #374151;
  cursor: pointer;
  text-align: center;
  margin-bottom: 6px;
  padding: 10px;
  word-break: break-all;
  transition: background 0.2s;
  border: none;
  width: 100%;
  font-family: "Poppins", sans-serif;
  gap: 6px;
}

.upload-box:hover {
  background: #c8cdd5;
}

.upload-box.no-foto {
  color: #9aa1a7;
  cursor: default;
}

/* ===== TANGGAPAN BOX ===== */
.tanggapan-box {
  background: #7ea2b9;
  padding: 15px;
  border-radius: 16px;
  margin-top: 20px;
}

/* ===== UPLOAD TINDAK LANJUT ===== */
.upload-tl {
  background: #d7dbe1;
  height: 70px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  color: #6b7280;
  cursor: pointer;
  text-align: center;
  margin-bottom: 6px;
  transition: background 0.2s;
}

.upload-tl:hover {
  background: #c8cdd5;
}

/* ===== DETAIL WRAPPER ===== */
.detail-wrapper {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.4s ease;
}

.detail-wrapper.show {
  max-height: 3000px;
}

/* ===== EXPAND BUTTONS ===== */
.expand-btn {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: #e0e4e8;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: none;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: background 0.2s;
  font-size: 15px;
  color: #374151;
}

.expand-btn.inside {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  bottom: -20px;
}

.expand-btn img {
  width: 18px;
  transition: transform 0.3s;
}

.expand-btn.outside {
  margin: 8px auto 24px;
  display: none;
}

/* ===== SUBMIT BTN ===== */
.submit-btn {
  width: 100%;
  height: 45px;
  border: none;
  border-radius: 14px;
  background: #5e91b2;
  color: white;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  margin-top: 12px;
  font-family: "Poppins", sans-serif;
  transition: background 0.2s;
}

.submit-btn:hover {
  background: #4a7a99;
}

/* ===== LIGHTBOX ===== */
.lightbox-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.85);
  z-index: 1000;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

.lightbox-overlay.show {
  display: flex;
}

.lightbox-inner {
  position: relative;
  max-width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.lightbox-inner img {
  max-width: 100%;
  max-height: 80vh;
  border-radius: 16px;
  object-fit: contain;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}

.lightbox-close {
  position: absolute;
  top: -14px;
  right: -14px;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: white;
  color: #333;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: none;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  line-height: 1;
}

.lightbox-filename {
  color: #ffffffcc;
  font-size: 12px;
  text-align: center;
  word-break: break-all;
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">
  <!-- Back button berfungsi -->
  <a href="User_Admin.php" class="back-btn" title="Kembali">
    <img src="logo_back.png" alt="Kembali">
  </a>
  <h2>Input Retur Barang</h2>
  <div class="header-circle-big"></div>
  <div class="header-circle-small"></div>
  <div class="header-circle-small_2"></div>
</div>

<!-- LIGHTBOX OVERLAY -->
<div class="lightbox-overlay" id="lightboxOverlay" onclick="tutupLightbox(event)">
  <div class="lightbox-inner">
    <button class="lightbox-close" onclick="tutupLightboxBtn()" title="Tutup">✕</button>
    <img id="lightboxImg" src="" alt="Foto Bukti">
    <div class="lightbox-filename" id="lightboxFilename"></div>
  </div>
</div>

<div class="container">

  <!-- ERROR BOX -->
  <?php if (!empty($errors)): ?>
  <div class="error-box">
    <p>⚠️ Terdapat kesalahan, mohon perbaiki:</p>
    <ul>
      <?php foreach ($errors as $err): ?>
        <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- EMPTY STATE -->
  <?php if (mysqli_num_rows($data_nota) === 0): ?>
  <div class="empty-state">
    <div>📦</div>
    <p>Tidak ada barang cacat yang perlu diretur saat ini.</p>
  </div>

  <?php else: ?>

  <form method="POST" enctype="multipart/form-data">

  <?php $no = 1; while ($n = mysqli_fetch_assoc($data_nota)):
    $id_nota = (int)$n['id_nota'];

    /* Ambil barang cacat belum diretur dengan prepared statement */
    $stmt_b = mysqli_prepare($koneksi,
      "SELECT * FROM barang
       WHERE id_nota = ?
       AND LOWER(status_barang) = 'cacat'
       AND (status_retur IS NULL OR status_retur != 'sudah')"
    );
    mysqli_stmt_bind_param($stmt_b, 'i', $id_nota);
    mysqli_stmt_execute($stmt_b);
    $data_barang = mysqli_stmt_get_result($stmt_b);
    mysqli_stmt_close($stmt_b);
  ?>

  <div class="card" id="card-<?php echo $no; ?>">

    <div class="close-btn" onclick="tutupCard(<?php echo $no; ?>)">✖</div>

    <input type="hidden" name="id_nota" value="<?php echo $id_nota; ?>">

    <div class="form-group">
      <label>Nomor Nota</label>
      <input name="nomor_retur"
             value="<?php echo htmlspecialchars($n['nomor_nota'], ENT_QUOTES, 'UTF-8'); ?>"
             readonly>
    </div>

    <div class="form-group">
      <label>Tanggal Nota</label>
      <input type="date" name="tanggal_retur"
             value="<?php echo htmlspecialchars($n['tanggal_nota'], ENT_QUOTES, 'UTF-8'); ?>"
             readonly>
    </div>

    <div class="form-group" id="supplier-<?php echo $no; ?>" style="display:none;">
      <label>Nama Supplier</label>
      <input name="retur_supplier"
             value="<?php echo htmlspecialchars($n['supplier'], ENT_QUOTES, 'UTF-8'); ?>"
             readonly>
    </div>

    <!-- Expand button dalam card (sebelum detail terbuka) -->
    <div class="expand-btn inside" id="btn-inside-<?php echo $no; ?>"
         onclick="toggleDetail('<?php echo $no; ?>')">
      <img src="logo_down.png" alt="expand">
    </div>

    <div class="detail-wrapper" id="detail-<?php echo $no; ?>">

      <?php while ($b = mysqli_fetch_assoc($data_barang)):
        $id_barang  = (int)$b['id_barang'];
        $foto_bukti = trim($b['foto_bukti'] ?? '');
        $foto_path  = 'uploads/' . $foto_bukti;
        $foto_ada   = ($foto_bukti !== '' && file_exists($foto_path));
      ?>

      <div class="barang-box">

        <div class="barang-title">Nama Barang</div>
        <input class="barang-input"
               value="<?php echo htmlspecialchars($b['nama_barang'], ENT_QUOTES, 'UTF-8'); ?>"
               readonly>

        <div class="barang-title">Jumlah</div>
        <input class="barang-input"
               value="<?php echo (int)$b['jumlah_barang']; ?>"
               readonly>

        <!-- FOTO BUKTI: tampilan box asli, klik buka lightbox -->
        <div class="barang-title">Lampiran Bukti</div>
        <?php if ($foto_ada): ?>
          <button
            type="button"
            class="upload-box"
            onclick="bukaLightbox(
              '<?php echo htmlspecialchars($foto_path, ENT_QUOTES, 'UTF-8'); ?>',
              '<?php echo htmlspecialchars($foto_bukti, ENT_QUOTES, 'UTF-8'); ?>'
            )"
            title="Klik untuk melihat foto">
            🖼️ <?php echo htmlspecialchars($foto_bukti, ENT_QUOTES, 'UTF-8'); ?>
          </button>
        <?php else: ?>
          <div class="upload-box no-foto">
            <?php echo $foto_bukti !== '' ? 'File tidak ditemukan.' : 'Tidak ada file'; ?>
          </div>
        <?php endif; ?>

        <div class="barang-title">Keterangan / Keluhan</div>
        <textarea name="alasan[]" class="barang-input"><?php
          echo htmlspecialchars($b['keterangan'] ?? '', ENT_QUOTES, 'UTF-8');
        ?></textarea>

        <input type="hidden" name="id_barang[]"    value="<?php echo $id_barang; ?>">
        <input type="hidden" name="jenis_retur[]"  value="<?php echo htmlspecialchars($b['jenis_barang'], ENT_QUOTES, 'UTF-8'); ?>">

      </div>

      <?php endwhile; ?>

      <!-- TANGGAPAN & TINDAK LANJUT -->
      <div class="tanggapan-box">
        <div class="barang-title">Tanggapan & Tindak Lanjut Supplier</div>
        <textarea name="tanggapan" class="barang-input"></textarea>

        <div class="barang-title">Lampiran Bukti Tindak Lanjut (Opsional)</div>
        <label class="upload-tl" id="label-tl-<?php echo $no; ?>"
               for="input-tl-<?php echo $no; ?>">
          <span id="span-tl-<?php echo $no; ?>">Klik untuk upload dokumen (JPG/PNG/PDF, maks. 5MB)</span>
        </label>
        <input type="file" id="input-tl-<?php echo $no; ?>"
               name="tindaklanjut"
               accept="image/jpeg,image/png,application/pdf"
               hidden
               onchange="previewTL(this, <?php echo $no; ?>)">
      </div>

      <button class="submit-btn" type="submit" name="submit">
        Simpan Data Retur Barang
      </button>

    </div><!-- /detail-wrapper -->

  </div><!-- /card -->

  <!-- Expand button di luar card (setelah detail terbuka) -->
  <div class="expand-btn outside" id="btn-outside-<?php echo $no; ?>"
       onclick="toggleDetail('<?php echo $no; ?>')">
    <img src="logo_down.png" alt="collapse" style="transform:rotate(180deg);">
  </div>

  <?php $no++; endwhile; ?>

  </form>

  <?php endif; ?>

</div><!-- /container -->

<script>
/* ── Toggle expand detail nota ── */
function toggleDetail(id) {
  var detail    = document.getElementById("detail-"    + id);
  var insideBtn = document.getElementById("btn-inside-" + id);
  var outsideBtn= document.getElementById("btn-outside-"+ id);
  var supplier  = document.getElementById("supplier-"  + id);
  var card      = document.getElementById("card-"      + id);

  detail.classList.toggle("show");
  if (card) card.classList.toggle("expanded");

  if (detail.classList.contains("show")) {
    insideBtn.style.display  = "none";
    outsideBtn.style.display = "flex";
    if (supplier) supplier.style.display = "block";
  } else {
    insideBtn.style.display  = "flex";
    outsideBtn.style.display = "none";
    if (supplier) supplier.style.display = "none";
  }
}

/* ── Tutup / sembunyikan card ── */
function tutupCard(id) {
  var card     = document.getElementById("card-" + id);
  var outside  = document.getElementById("btn-outside-" + id);
  if (card)    card.style.display    = "none";
  if (outside) outside.style.display = "none";
}

/* ── Preview nama file tindak lanjut ── */
function previewTL(input, id) {
  var span = document.getElementById("span-tl-" + id);
  if (!input.files || !input.files[0]) return;

  var file = input.files[0];

  if (file.size > 5 * 1024 * 1024) {
    alert("Ukuran file melebihi 5 MB.");
    input.value = '';
    return;
  }
  var allowed = ['image/jpeg', 'image/png', 'application/pdf'];
  if (!allowed.includes(file.type)) {
    alert("Format tidak valid. Gunakan JPG, PNG, atau PDF.");
    input.value = '';
    return;
  }

  if (span) span.textContent = "📎 " + file.name;
}

/* ── Lightbox ── */
function bukaLightbox(src, filename) {
  document.getElementById("lightboxImg").src = src;
  document.getElementById("lightboxFilename").textContent = filename;
  document.getElementById("lightboxOverlay").classList.add("show");
  document.body.style.overflow = "hidden";
}

function tutupLightbox(e) {
  if (e.target === document.getElementById("lightboxOverlay")) {
    _closeLightbox();
  }
}

function tutupLightboxBtn() {
  _closeLightbox();
}

function _closeLightbox() {
  document.getElementById("lightboxOverlay").classList.remove("show");
  document.body.style.overflow = "";
  document.getElementById("lightboxImg").src = "";
}

document.addEventListener("keydown", function(e) {
  if (e.key === "Escape") _closeLightbox();
});
</script>

</body>
</html>