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

/* ─────────────────────────────────────────────
   KONSTANTA JENIS BARANG (single source of truth)
───────────────────────────────────────────── */
$semua_jenis = [
  "Material Bangunan",
  "Besi & Logam",
  "Listrik",
  "Keramik & Lantai",
  "Alat Pertukangan",
  "Kayu & Olahan"
];

/* ─────────────────────────────────────────────
   HANDLE SUBMIT
───────────────────────────────────────────── */
$errors = [];

if (isset($_POST['simpan_laporan'])) {

  $id_nota_raw = $_POST['id_nota'] ?? '';

  if (!ctype_digit((string)$id_nota_raw) || (int)$id_nota_raw <= 0) {
    $errors[] = "ID nota tidak valid.";
  } else {
    $id_nota_submit = (int)$id_nota_raw;

    /* Cek duplikat dengan prepared statement */
    $stmt_cek = mysqli_prepare($koneksi,
      "SELECT id_laporan FROM laporan WHERE id_nota = ?"
    );
    mysqli_stmt_bind_param($stmt_cek, 'i', $id_nota_submit);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);
    $sudah_ada = mysqli_stmt_num_rows($stmt_cek) > 0;
    mysqli_stmt_close($stmt_cek);

    if (!$sudah_ada) {
      $stmt_insert = mysqli_prepare($koneksi,
        "INSERT INTO laporan (id_nota, tanggal_laporan) VALUES (?, NOW())"
      );
      mysqli_stmt_bind_param($stmt_insert, 'i', $id_nota_submit);
      if (!mysqli_stmt_execute($stmt_insert)) {
        $errors[] = "Gagal menyimpan laporan: " . mysqli_stmt_error($stmt_insert);
      }
      mysqli_stmt_close($stmt_insert);
    }

    if (empty($errors)) {
      header("Location: success_check_barang.php");
      exit;
    }
  }
}

/* ─────────────────────────────────────────────
   AMBIL NOTA SUDAH DICEK (prepared statement)
───────────────────────────────────────────── */
$stmt_nota = mysqli_prepare($koneksi, "
  SELECT DISTINCT n.*
  FROM nota n
  JOIN barang b ON b.id_nota = n.id_nota
  WHERE n.status = 'Sudah Dicek'
  ORDER BY n.id_nota DESC
");
mysqli_stmt_execute($stmt_nota);
$data_nota = mysqli_stmt_get_result($stmt_nota);
mysqli_stmt_close($stmt_nota);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Buat Laporan Barang Masuk</title>

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

/* ===== DETAIL WRAPPER ===== */
.detail-wrapper {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.4s ease;
}

.detail-wrapper.show {
  max-height: 9999px;
}

/* ===== JENIS LIST (sama persis lihat_hasil) ===== */
.jenis-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 10px;
  margin-bottom: 15px;
}

.jenis-item {
  padding: 6px 12px;
  border-radius: 12px;
  border: 1px solid #cfd4da;
  font-size: 12px;
  background: #f5f6f8;
}

.jenis-active {
  background: #7ea2b9;
  color: white;
  border: none;
}

/* ===== BARANG BOX ===== */
.barang-box {
  background: #7ea2b9;
  padding: 15px;
  border-radius: 16px;
  margin-top: 12px;
  position: relative;
}

.dot-wrap {
  position: absolute;
  top: 10px;
  right: 15px;
  display: flex;
  gap: 8px;
}

.dot {
  width: 14px;
  height: 14px;
  background: #63c9d5;
  border-radius: 50%;
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

.label-white {
  color: white;
  font-size: 12px;
  font-weight: 500;
  display: block;
  margin-bottom: 4px;
}

/* ===== STATUS BUTTONS (display only) ===== */
.status-display {
  display: flex;
  gap: 10px;
  margin-top: 10px;
  margin-bottom: 6px;
}

.status-display button {
  flex: 1;
  border: none;
  padding: 8px;
  border-radius: 10px;
  color: white;
  font-family: "Poppins", sans-serif;
  font-size: 13px;
  cursor: default;
}

.btn-green { background: #4cd964; }
.btn-red   { background: #ff3b30; }
.btn-dim   { opacity: 0.3; }

/* ===== KETERANGAN BOX ===== */
.keterangan-box {
  width: 100%;
  background: #d7dbe1;
  border-radius: 12px;
  padding: 10px 12px;
  font-size: 12px;
  font-family: "Poppins", sans-serif;
  margin-top: 4px;
  margin-bottom: 6px;
  min-height: 40px;
  word-break: break-word;
}

/* ===== FOTO BUKTI (sama seperti input_retur: box, klik lightbox) ===== */
.foto-bukti-wrapper {
  margin-top: 4px;
  margin-bottom: 6px;
}

.upload-box-view {
  background: #d7dbe1;
  height: 70px;
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

.upload-box-view:hover {
  background: #c8cdd5;
}

.upload-box-view.no-foto {
  color: #9aa1a7;
  cursor: default;
}

/* ===== TANGGAPAN / TINDAK LANJUT BOX ===== */
.tanggapan-box {
  background: #7ea2b9;
  padding: 15px;
  border-radius: 16px;
  margin-top: 12px;
}

/* ===== SUBMIT ===== */
.submit-btn {
  width: 100%;
  background: #5e91b2;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 14px;
  margin-top: 15px;
  font-family: "Poppins", sans-serif;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.submit-btn:hover {
  background: #4a7a99;
}

/* ===== EXPAND BUTTON ===== */
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
}

.expand-btn img {
  width: 18px;
  transition: transform 0.3s;
}

.expand-btn.inside {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  bottom: -20px;
}

.expand-btn.outside {
  margin: 8px auto 24px;
  display: none;
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
  <a href="User_Admin.php" class="back-btn" title="Kembali">
    <img src="logo_back.png" alt="Kembali">
  </a>
  <h2>Buat Laporan Barang Masuk</h2>
  <div class="header-circle-big"></div>
  <div class="header-circle-small"></div>
  <div class="header-circle-small_2"></div>
</div>

<!-- LIGHTBOX -->
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
    <p>⚠️ Terdapat kesalahan:</p>
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
    <div>📋</div>
    <p>Belum ada nota yang sudah diperiksa.</p>
  </div>

  <?php else: $no = 1; while ($n = mysqli_fetch_assoc($data_nota)):
    $id_nota = (int)$n['id_nota'];

    /* Ambil barang dengan prepared statement */
    $stmt_b = mysqli_prepare($koneksi, "SELECT * FROM barang WHERE id_nota = ?");
    mysqli_stmt_bind_param($stmt_b, 'i', $id_nota);
    mysqli_stmt_execute($stmt_b);
    $data_barang = mysqli_stmt_get_result($stmt_b);
    mysqli_stmt_close($stmt_b);

    /* Ambil jenis aktif dengan prepared statement */
    $stmt_j = mysqli_prepare($koneksi,
      "SELECT DISTINCT jenis_barang FROM barang WHERE id_nota = ?"
    );
    mysqli_stmt_bind_param($stmt_j, 'i', $id_nota);
    mysqli_stmt_execute($stmt_j);
    $res_jenis = mysqli_stmt_get_result($stmt_j);
    mysqli_stmt_close($stmt_j);

    $jenis_aktif = [];
    while ($jr = mysqli_fetch_assoc($res_jenis)) {
      $jenis_aktif[] = trim($jr['jenis_barang']);
    }

    /* Ambil data retur (1 baris) dengan prepared statement */
    $stmt_r = mysqli_prepare($koneksi,
      "SELECT r.* FROM retur r
       JOIN barang b ON b.id_barang = r.id_barang
       WHERE b.id_nota = ?
       LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt_r, 'i', $id_nota);
    mysqli_stmt_execute($stmt_r);
    $res_retur = mysqli_stmt_get_result($stmt_r);
    $retur = mysqli_fetch_assoc($res_retur);
    mysqli_stmt_close($stmt_r);
  ?>

  <form method="POST">
  <input type="hidden" name="id_nota" value="<?php echo $id_nota; ?>">

  <div class="card" id="card-<?php echo $no; ?>">

    <div class="form-group">
      <label>Nomor Nota</label>
      <input value="<?php echo htmlspecialchars($n['nomor_nota'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
    </div>

    <div class="form-group">
      <label>Tanggal Nota</label>
      <input value="<?php echo htmlspecialchars($n['tanggal_nota'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
    </div>

    <!-- Expand button dalam card -->
    <div class="expand-btn inside" id="btn-inside-<?php echo $no; ?>"
         onclick="toggleDetail('<?php echo $no; ?>')">
      <img src="logo_down.png" alt="expand">
    </div>

    <!-- DETAIL -->
    <div class="detail-wrapper" id="detail-<?php echo $no; ?>">

      <div class="form-group" style="margin-top:14px;">
        <label>Nama Supplier</label>
        <input value="<?php echo htmlspecialchars($n['supplier'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
      </div>

      <!-- JENIS BARANG INDIKATOR (sama persis lihat_hasil) -->
      <label style="font-size:13px;font-weight:600;">Jenis Barang</label>
      <div class="jenis-list">
        <?php foreach ($semua_jenis as $jenis):
          $aktif = false;
          foreach ($jenis_aktif as $ja) {
            if (trim($ja) === trim($jenis)) { $aktif = true; break; }
          }
        ?>
          <span class="jenis-item <?php echo $aktif ? 'jenis-active' : ''; ?>">
            <?php echo htmlspecialchars($jenis, ENT_QUOTES, 'UTF-8'); ?>
          </span>
        <?php endforeach; ?>
      </div>

      <!-- DAFTAR BARANG -->
      <?php $noBarang = 1; while ($b = mysqli_fetch_assoc($data_barang)):
        $is_cacat   = ($b['status_barang'] === 'Cacat');
        $foto_bukti = trim($b['foto_bukti'] ?? '');
        $foto_path  = 'uploads/' . $foto_bukti;
        $foto_ada   = ($foto_bukti !== '' && file_exists($foto_path));
      ?>

      <div class="barang-box">

        <div class="dot-wrap">
          <div class="dot"></div>
          <div class="dot"></div>
          <div class="dot"></div>
        </div>

        <div class="barang-title">Barang ke-<?php echo $noBarang; ?></div>

        <input class="barang-input"
               value="<?php echo htmlspecialchars($b['nama_barang'], ENT_QUOTES, 'UTF-8'); ?>"
               readonly>

        <label class="label-white">Jumlah</label>
        <input class="barang-input"
               value="<?php echo (int)$b['jumlah_barang']; ?>"
               readonly>

        <!-- Status display (non-interactive) -->
        <div class="status-display">
          <button type="button" class="btn-green <?php echo $is_cacat ? 'btn-dim' : ''; ?>">Sesuai</button>
          <button type="button" class="btn-red <?php echo !$is_cacat ? 'btn-dim' : ''; ?>">Cacat</button>
        </div>

        <?php if ($is_cacat): ?>

          <!-- FOTO BUKTI: box sama seperti input_retur, klik buka lightbox -->
          <label class="label-white" style="margin-top:8px;">Lampiran Bukti</label>
          <div class="foto-bukti-wrapper">
            <?php if ($foto_ada): ?>
              <button
                type="button"
                class="upload-box-view"
                onclick="bukaLightbox('<?php echo htmlspecialchars($foto_path, ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlspecialchars($foto_bukti, ENT_QUOTES, 'UTF-8'); ?>')"
                title="Klik untuk melihat foto">
                🖼️ <?php echo htmlspecialchars($foto_bukti, ENT_QUOTES, 'UTF-8'); ?>
              </button>
            <?php else: ?>
              <div class="upload-box-view no-foto">
                <?php echo $foto_bukti !== '' ? 'File tidak ditemukan.' : 'Tidak ada file'; ?>
              </div>
            <?php endif; ?>
          </div>

          <label class="label-white">Keterangan / Keluhan</label>
          <div class="keterangan-box">
            <?php
            $ket = trim($b['keterangan'] ?? '');
            echo $ket !== ''
              ? htmlspecialchars($ket, ENT_QUOTES, 'UTF-8')
              : '<span style="color:#9ca3af;">Tidak ada keterangan.</span>';
            ?>
          </div>

        <?php endif; ?>

      </div>

      <?php $noBarang++; endwhile; ?>

      <!-- TANGGAPAN & TINDAK LANJUT RETUR -->
      <?php if ($retur): 
        $tl_file  = trim($retur['tindaklanjut'] ?? '');
        $tl_path  = 'uploads/' . $tl_file;
        $tl_ada   = ($tl_file !== '' && file_exists($tl_path));
      ?>
      <div class="tanggapan-box">

        <div class="barang-title">Tanggapan & Tindak Lanjut Supplier</div>
        <div class="keterangan-box">
          <?php
          $tgp = trim($retur['tanggapan'] ?? '');
          echo $tgp !== ''
            ? htmlspecialchars($tgp, ENT_QUOTES, 'UTF-8')
            : '<span style="color:#9ca3af;">Tidak ada tanggapan.</span>';
          ?>
        </div>

        <label class="label-white" style="margin-top:8px;">Lampiran Bukti Tindak Lanjut</label>
        <div class="foto-bukti-wrapper">
          <?php if ($tl_ada): ?>
            <button
              type="button"
              class="upload-box-view"
              onclick="bukaLightbox('<?php echo htmlspecialchars($tl_path, ENT_QUOTES, 'UTF-8'); ?>','<?php echo htmlspecialchars($tl_file, ENT_QUOTES, 'UTF-8'); ?>')"
              title="Klik untuk melihat lampiran">
              🖼️ <?php echo htmlspecialchars($tl_file, ENT_QUOTES, 'UTF-8'); ?>
            </button>
          <?php else: ?>
            <div class="upload-box-view no-foto">
              <?php echo $tl_file !== '' ? 'File tidak ditemukan.' : 'Tidak ada lampiran'; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>
      <?php endif; ?>

      <button type="submit" class="submit-btn" name="simpan_laporan">
        Simpan Hasil Laporan
      </button>

    </div><!-- /detail-wrapper -->

  </div><!-- /card -->
  </form>

  <!-- Expand button luar card -->
  <div class="expand-btn outside" id="btn-outside-<?php echo $no; ?>"
       onclick="toggleDetail('<?php echo $no; ?>')">
    <img src="logo_down.png" alt="collapse" style="transform:rotate(180deg);">
  </div>

  <?php $no++; endwhile; endif; ?>

</div><!-- /container -->

<script>
/* ── Toggle expand / collapse ── */
function toggleDetail(id) {
  var detail     = document.getElementById("detail-"     + id);
  var insideBtn  = document.getElementById("btn-inside-" + id);
  var outsideBtn = document.getElementById("btn-outside-"+ id);
  var card       = document.getElementById("card-"       + id);

  detail.classList.toggle("show");
  if (card) card.classList.toggle("expanded");

  if (detail.classList.contains("show")) {
    insideBtn.style.display  = "none";
    outsideBtn.style.display = "flex";
  } else {
    insideBtn.style.display  = "flex";
    outsideBtn.style.display = "none";
  }
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