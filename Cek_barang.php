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

$semua_jenis = [
  "Material Bangunan",
  "Besi & Logam",
  "Listrik",
  "Keramik & Lantai",
  "Alat Pertukangan",
  "Kayu & Olahan"
];

$stmt_nota = mysqli_prepare($koneksi, "SELECT * FROM nota ORDER BY id_nota DESC");
mysqli_stmt_execute($stmt_nota);
$result_nota = mysqli_stmt_get_result($stmt_nota);
mysqli_stmt_close($stmt_nota);

if (!$result_nota) {
  die("Query nota gagal.");
}

$errors = [];

if (isset($_POST['simpan'])) {

  $id_nota_raw = $_POST['id_nota'] ?? '';
  if (!ctype_digit((string)$id_nota_raw)) {
    $errors[] = "ID nota tidak valid.";
  } else {
    $id_nota = (int)$id_nota_raw;
  }

  $hasil_post = $_POST['hasil'] ?? [];

  if (empty($hasil_post)) {
    $errors[] = "Belum ada hasil pemeriksaan yang diisi.";
  } else {
    foreach ($hasil_post as $id_barang => $hasil) {
      if (!in_array($hasil, ['Sesuai', 'Cacat'])) {
        $errors[] = "Nilai hasil pemeriksaan tidak valid.";
        break;
      }
    }
  }

  if (empty($errors)) {

    $allowed_ext  = ['jpg', 'jpeg', 'png'];
    $allowed_mime = ['image/jpeg', 'image/png'];
    $max_size     = 5 * 1024 * 1024; // 5 MB

    /* Prepared statement untuk UPDATE barang */
    $stmt_barang = mysqli_prepare(
      $koneksi,
      "UPDATE barang
       SET status_barang = ?, foto_bukti = ?, keterangan = ?
       WHERE id_barang = ?"
    );

    foreach ($hasil_post as $id_barang_raw => $hasil) {

      if (!ctype_digit((string)$id_barang_raw)) continue;
      $id_barang = (int)$id_barang_raw;

      $keterangan = trim($_POST['keterangan'][$id_barang_raw] ?? '');

      $foto_simpan = '';
      $file_error  = $_FILES['foto_bukti']['error'][$id_barang_raw] ?? UPLOAD_ERR_NO_FILE;
      $tmp_file    = $_FILES['foto_bukti']['tmp_name'][$id_barang_raw] ?? '';
      $ori_name    = $_FILES['foto_bukti']['name'][$id_barang_raw] ?? '';
      $file_size   = $_FILES['foto_bukti']['size'][$id_barang_raw] ?? 0;

      if ($file_error === UPLOAD_ERR_OK && $tmp_file !== '') {

        $ext  = strtolower(pathinfo($ori_name, PATHINFO_EXTENSION));
        $mime = mime_content_type($tmp_file);

        if ($file_size > $max_size) {
          $errors[] = "Foto bukti barang #$id_barang melebihi batas 5 MB.";
          continue;
        }

        if (!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mime)) {
          $errors[] = "Format foto bukti barang #$id_barang tidak valid (JPG/PNG saja).";
          continue;
        }

        /* Rename dengan uniqid agar tidak bisa ditebak / ditimpa */
        $foto_simpan = uniqid('bukti_', true) . '.' . $ext;
        if (!move_uploaded_file($tmp_file, "uploads/" . $foto_simpan)) {
          $errors[] = "Gagal mengunggah foto bukti barang #$id_barang.";
          continue;
        }
      }

      mysqli_stmt_bind_param($stmt_barang, 'sssi', $hasil, $foto_simpan, $keterangan, $id_barang);
      mysqli_stmt_execute($stmt_barang);
    }

    mysqli_stmt_close($stmt_barang);

    if (empty($errors)) {
      $stmt_update_nota = mysqli_prepare(
        $koneksi,
        "UPDATE nota SET status = 'Sudah Dicek' WHERE id_nota = ?"
      );
      mysqli_stmt_bind_param($stmt_update_nota, 'i', $id_nota);
      mysqli_stmt_execute($stmt_update_nota);
      mysqli_stmt_close($stmt_update_nota);

      header("Location: success_check_barang.php");
      exit;
    }
  }
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

      /* ===== HEADER ===== */

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

      .back-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #48b5c1;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        border: none;
        flex-shrink: 0;
        transition: background 0.2s;
        text-decoration: none;
      }

      .back-btn:hover {
        background: #3aa0ac;
      }

      .back-btn img {
        width: 20px;
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
        pointer-events: none;
      }

      .header-circle-small {
        position: absolute;
        width: 45px;
        height: 45px;
        background: #5bb7c5;
        border-radius: 50%;
        left: -11px;
        top: 51px;
        pointer-events: none;
      }

      .header-circle-small_2 {
        position: absolute;
        width: 18px;
        height: 18px;
        background: #519eaa;
        border-radius: 50%;
        left: 1px;
        top: 23px;
        pointer-events: none;
      }

      .header-circle-small_3 {
        position: absolute;
        width: 14px;
        height: 14px;
        background: #519eaa;
        border-radius: 50%;
        left: 45px;
        top: 53px;
        pointer-events: none;
      }

      /* ===== CONTENT ===== */

      .container {
        padding: 25px 20px 70px;
      }

      .page-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #1f2937;
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

      /* ===== FORM CARD ===== */

      .form-card {
        background: white;
        padding: 25px 20px 35px;
        border-radius: 24px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        position: relative;
        margin-bottom: 60px;
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

      /* ===== JENIS BARANG ===== */

      .jenis-wrapper {
        margin-bottom: 20px;
      }

      .jenis-wrapper label {
        font-size: 16px;
        font-weight: 800;
        display: block;
        margin-bottom: 10px;
      }

      .jenis-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
      }

      .jenis-item {
        padding: 6px 12px;
        border-radius: 12px;
        border: 2px solid #cfd4da;
        font-size: 13px;
        color: #4b5563;
        background: white;
      }

      .jenis-active {
        background: #7ea8b6;
        color: white;
        border: none;
      }

      /* ===== DETAIL WRAPPER ===== */

      .detail-wrapper {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease;
      }

      .detail-wrapper.show {
        max-height: 4000px;
      }

      /* ===== STATUS BADGE ===== */

      .status-wrapper {
        margin-top: 15px;
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

      .status-badge.sudah {
        border-color: #16a34a;
        color: #16a34a;
      }

      .status-bottom {
        margin-top: 20px;
      }

      .detail-wrapper .status-top {
        display: none;
      }

      .form-card .status-bottom {
        display: none;
      }

      .detail-wrapper.show .status-bottom {
        display: block;
      }

      .form-card.expanded .status-top {
        display: none;
      }

      /* ===== BOX BARANG ===== */

      .barang-box {
        background: #7ea2b9;
        padding: 18px;
        border-radius: 20px;
        margin-top: 15px;
        position: relative;
      }

      .barang-title {
        color: white;
        font-weight: 600;
        margin-bottom: 10px;
      }

      .barang-input {
        width: 100%;
        height: 42px;
        border-radius: 14px;
        border: none;
        background: #d7dbe1;
        margin-bottom: 10px;
        padding: 10px;
      }

      .barang-btn {
        display: flex;
        gap: 10px;
        margin-top: 5px;
      }

      .btn-sesuai {
        flex: 1;
        background: #7ea2b9;
        border: 2px solid green;
        border-radius: 12px;
        height: 32px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
      }

      .btn-cacat {
        flex: 1;
        background: #7ea2b9;
        border: 2px solid red;
        border-radius: 12px;
        height: 32px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
      }

      .btn-sesuai.active {
        background: #00cc55;
        color: white;
      }

      .btn-cacat.active {
        background: red;
        color: white;
      }

      .btn-simpan {
        width: 100%;
        margin-top: 15px;
        height: 40px;
        border: none;
        border-radius: 16px;
        background: #5e91b2;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
      }

      .btn-simpan:hover {
        background: #4a7a99;
      }

      /* ===== CACAT SECTION ===== */

      .cacat-section {
        display: none;
        margin-top: 10px;
      }

      .cacat-section.show {
        display: block;
      }

      /* ===== UPLOAD BUKTI ===== */

      .upload-bukti-label {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 70px;
        border-radius: 16px;
        background: #d7dbe1;
        font-size: 13px;
        color: #6b7280;
        cursor: pointer;
        margin-bottom: 10px;
        gap: 8px;
        transition: background 0.2s;
        overflow: hidden;
        position: relative;
      }

      .upload-bukti-label:hover {
        background: #cfd4da;
      }

      .upload-bukti-label span {
        pointer-events: none;
        font-size: 13px;
        color: #6b7280;
      }

      .upload-bukti-label img.preview-bukti {
        max-width: 100%;
        max-height: 65px;
        border-radius: 10px;
        display: none;
      }

      .input-keterangan {
        width: 100%;
        border-radius: 16px;
        border: none;
        background: #d7dbe1;
        padding: 12px;
        font-size: 13px;
        min-height: 80px;
        resize: none;
      }

      /* ===== EXPAND BUTTON ===== */

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
        transition: transform 0.2s;
        border: none;
      }

      .expand-btn img {
        transition: transform 0.3s;
        width: 20px;
      }

      .expand-btn:hover {
        transform: translateX(-50%) translateY(-3px);
      }

      .expand-btn.active img {
        transform: rotate(180deg);
      }

      /* ===== RESPONSIVE ===== */

      @media (max-width: 480px) {
        .header h2 {
          font-size: 16px;
        }

        .back-btn {
          width: 34px;
          height: 34px;
        }

        .page-title {
          font-size: 18px;
        }
      }
    </style>
  </head>

  <body>
    <!-- HEADER -->
    <div class="header">
      <div class="header-left">
        <!-- Back button: kembali ke halaman kasir -->
        <a href="User_Kasir.php" class="back-btn" title="Kembali">
          <img src="logo_back.png" alt="Kembali" />
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

      <!-- ERROR BOX (dari submit) -->
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

      <?php while ($nota = mysqli_fetch_assoc($result_nota)):

        $id_nota = (int)$nota['id_nota'];

        $stmt_b = mysqli_prepare($koneksi, "SELECT * FROM barang WHERE id_nota = ?");
        mysqli_stmt_bind_param($stmt_b, 'i', $id_nota);
        mysqli_stmt_execute($stmt_b);
        $result_barang = mysqli_stmt_get_result($stmt_b);
        mysqli_stmt_close($stmt_b);

        $stmt_j = mysqli_prepare($koneksi,
          "SELECT DISTINCT jenis_barang FROM barang WHERE id_nota = ?"
        );
        mysqli_stmt_bind_param($stmt_j, 'i', $id_nota);
        mysqli_stmt_execute($stmt_j);
        $result_jenis = mysqli_stmt_get_result($stmt_j);
        mysqli_stmt_close($stmt_j);

        $jenis_aktif = [];
        while ($j = mysqli_fetch_assoc($result_jenis)) {
          $jenis_aktif[] = $j['jenis_barang'];
        }

        $status_sudah = ($nota['status'] === 'Sudah Dicek');
      ?>

      <div class="form-card" id="card-<?php echo $id_nota; ?>">

        <div class="form-group">
          <label>Nomor Nota</label>
          <input type="text" value="<?php echo htmlspecialchars($nota['nomor_nota'] ?? $nota['id_nota'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
        </div>

        <div class="form-group">
          <label>Tanggal Nota</label>
          <input type="text" value="<?php echo htmlspecialchars($nota['tanggal_nota'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
        </div>

        <div class="status-wrapper status-top">
          <span class="status-badge <?php echo $status_sudah ? 'sudah' : ''; ?>">
            <?php echo htmlspecialchars($nota['status'] ?? 'Belum Dicek', ENT_QUOTES, 'UTF-8'); ?>
          </span>
        </div>

        <!-- ===== DETAIL (disembunyikan) ===== -->
        <div class="detail-wrapper" id="detail-<?php echo $id_nota; ?>">

          <div class="jenis-wrapper">
            <label>Jenis Barang</label>
            <div class="jenis-list" id="jenis-indikator-<?php echo $id_nota; ?>">
              <?php foreach ($semua_jenis as $jenis):
                $aktif = false;
                foreach ($jenis_aktif as $ja) {
                  if (trim($ja) === trim($jenis)) {
                    $aktif = true;
                    break;
                  }
                }
                $cls = $aktif ? 'jenis-item jenis-active' : 'jenis-item';
              ?>
                <span class="<?php echo $cls; ?>"
                      data-jenis="<?php echo htmlspecialchars($jenis, ENT_QUOTES, 'UTF-8'); ?>">
                  <?php echo htmlspecialchars($jenis, ENT_QUOTES, 'UTF-8'); ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- FORM PEMERIKSAAN -->
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_nota" value="<?php echo $id_nota; ?>">

            <?php
            $no = 1;
            while ($barang = mysqli_fetch_assoc($result_barang)):
              $id_barang = (int)$barang['id_barang'];
            ?>

            <div class="barang-box">

              <div class="barang-title">Barang ke-<?php echo $no; ?></div>

              <input class="barang-input" type="text"
                     value="<?php echo htmlspecialchars($barang['nama_barang'], ENT_QUOTES, 'UTF-8'); ?>"
                     readonly>

              <label style="color:white;font-size:13px;">Jumlah</label>
              <input class="barang-input" type="number"
                     value="<?php echo (int)$barang['jumlah_barang']; ?>"
                     readonly>

              <div class="barang-btn">
                <input type="hidden"
                       name="hasil[<?php echo $id_barang; ?>]"
                       id="hasil-<?php echo $id_barang; ?>">

                <button type="button" class="btn-sesuai"
                        onclick="pilihStatus(<?php echo $id_barang; ?>,'Sesuai',this)">
                  Sesuai
                </button>

                <button type="button" class="btn-cacat"
                        onclick="pilihStatus(<?php echo $id_barang; ?>,'Cacat',this)">
                  Cacat
                </button>
              </div>

              <!-- CACAT SECTION -->
              <div class="cacat-section" id="cacat-<?php echo $id_barang; ?>">

                <label style="color:white;font-size:13px;display:block;margin-top:10px;margin-bottom:6px;">
                  Lampiran Foto Bukti
                </label>

                <label class="upload-bukti-label"
                       for="foto_input_<?php echo $id_barang; ?>">
                  <img class="preview-bukti"
                       id="preview-<?php echo $id_barang; ?>"
                       src="" alt="Preview">
                  <span id="upload-label-<?php echo $id_barang; ?>">＋ Pilih Foto (JPG/PNG, maks. 5MB)</span>
                </label>

                <input type="file"
                       id="foto_input_<?php echo $id_barang; ?>"
                       name="foto_bukti[<?php echo $id_barang; ?>]"
                       accept="image/jpeg,image/png"
                       hidden
                       onchange="previewBukti(this, <?php echo $id_barang; ?>)">

                <label style="color:white;font-size:13px;display:block;margin-bottom:6px;">
                  Keterangan / Keluhan
                </label>

                <textarea
                  name="keterangan[<?php echo $id_barang; ?>]"
                  class="input-keterangan"></textarea>

              </div>
            </div>

            <?php $no++; endwhile; ?>

            <button class="btn-simpan" type="submit" name="simpan">
              Simpan Hasil Pemeriksaan
            </button>

          </form>

          <!-- Status bawah (tampil saat expand) -->
          <div class="status-bottom">
            <span class="status-badge <?php echo $status_sudah ? 'sudah' : ''; ?>">
              <?php echo htmlspecialchars($nota['status'] ?? 'Belum Dicek', ENT_QUOTES, 'UTF-8'); ?>
            </span>
          </div>

        </div>

        <!-- EXPAND BUTTON -->
        <button type="button"
                class="expand-btn"
                onclick="toggleBarang('<?php echo $id_nota; ?>', this)"
                aria-label="Tampilkan detail">
          <img src="logo_down.png" alt="">
        </button>

      </div>

      <?php endwhile; ?>

    </div>

    <script>
    function toggleBarang(id, btn) {
      var detail = document.getElementById("detail-" + id);
      var card   = document.getElementById("card-"   + id);

      if (detail) {
        detail.classList.toggle("show");
        btn.classList.toggle("active");
        card.classList.toggle("expanded");
      }
    }

    /* ── Pilih status barang (Sesuai / Cacat) ── */
    function pilihStatus(id_barang, status, btn) {
      var input = document.getElementById("hasil-" + id_barang);
      if (input) input.value = status;

      var parent = btn.parentElement;
      parent.querySelector(".btn-sesuai").classList.remove("active");
      parent.querySelector(".btn-cacat").classList.remove("active");
      btn.classList.add("active");

      var section = document.getElementById("cacat-" + id_barang);
      if (section) {
        if (status === "Cacat") {
          section.classList.add("show");
        } else {
          section.classList.remove("show");
        }
      }
    }

    function previewBukti(input, id_barang) {
      var preview = document.getElementById("preview-" + id_barang);
      var label   = document.getElementById("upload-label-" + id_barang);

      if (!input.files || !input.files[0]) return;

      var file = input.files[0];

      if (file.size > 5 * 1024 * 1024) {
        alert("Ukuran file melebihi 5 MB.");
        input.value = '';
        return;
      }
      if (!['image/jpeg', 'image/png'].includes(file.type)) {
        alert("Format tidak valid. Gunakan JPG atau PNG.");
        input.value = '';
        return;
      }

      var reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        label.style.display   = 'none';
      };
      reader.readAsDataURL(file);
    }
    </script>

  </body>
</html>