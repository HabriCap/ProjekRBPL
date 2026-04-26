<?php
include "koneksi.php";
include "Check_Login.php";
// Sesuaikan role yang bisa akses halaman ini (misal: kasir atau gudang)
// cekRole('kasir');

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

/* ─────────────────────────────────────────────
   KONSTANTA JENIS BARANG (single source of truth,
   urutan sama persis dengan file lain)
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
   KONSTANTA VALIDASI FILE UPLOAD
───────────────────────────────────────────── */
define('UPLOAD_DIR',      'uploads/');
define('MAX_FILE_SIZE',   5 * 1024 * 1024); // 5 MB
define('ALLOWED_EXT',     ['jpg','jpeg','png','gif','webp','pdf']);
define('ALLOWED_MIME',    ['image/jpeg','image/png','image/gif','image/webp','application/pdf']);

/* ─────────────────────────────────────────────
   HELPER: validasi & pindah file upload
   Return: nama file baru (string) atau false jika gagal
───────────────────────────────────────────── */
function prosesUpload(array $fileArr, int $idx): string|false
{
    $name = $fileArr['name'][$idx]    ?? '';
    $tmp  = $fileArr['tmp_name'][$idx] ?? '';
    $size = $fileArr['size'][$idx]    ?? 0;
    $err  = $fileArr['error'][$idx]   ?? UPLOAD_ERR_NO_FILE;

    if ($err === UPLOAD_ERR_NO_FILE || $name === '') return false;
    if ($err !== UPLOAD_ERR_OK)                       return false;
    if ($size > MAX_FILE_SIZE)                        return false;
    if (!is_uploaded_file($tmp))                      return false;

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true))           return false;

    $mime = mime_content_type($tmp);
    if (!in_array($mime, ALLOWED_MIME, true))         return false;

    $newName = 'bukti_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest    = UPLOAD_DIR . $newName;

    if (!move_uploaded_file($tmp, $dest)) return false;

    return $newName;
}

/* ─────────────────────────────────────────────
   HANDLE UPDATE (POST)
───────────────────────────────────────────── */
$errors = [];

if (isset($_POST['simpan_revisi'])) {

    /* --- Validasi id_nota --- */
    $id_nota_raw = $_POST['id_nota'] ?? '';
    if (!ctype_digit((string)$id_nota_raw) || (int)$id_nota_raw <= 0) {
        $errors[] = "ID nota tidak valid.";
    } else {
        $id_nota = (int)$id_nota_raw;

        /* Verifikasi nota benar-benar ada & statusnya Ditolak */
        $stmt_cek = mysqli_prepare($koneksi,
            "SELECT n.id_nota FROM nota n
             JOIN laporan l ON l.id_nota = n.id_nota
             WHERE n.id_nota = ? AND l.status_laporan = 'Ditolak'
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt_cek, 'i', $id_nota);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);
        $valid_nota = mysqli_stmt_num_rows($stmt_cek) > 0;
        mysqli_stmt_close($stmt_cek);

        if (!$valid_nota) {
            $errors[] = "Nota tidak ditemukan atau tidak berstatus Ditolak.";
        }
    }

    /* --- Validasi array barang --- */
    $id_barang_arr     = $_POST['id_barang']     ?? [];
    $jumlah_arr        = $_POST['jumlah']        ?? [];
    $status_arr        = $_POST['status_barang'] ?? [];
    $keterangan_arr    = $_POST['keterangan']    ?? [];
    $hapus_arr         = $_POST['hapus_file']    ?? [];
    $file_lama_arr     = $_POST['file_lama']     ?? [];

    $jumlah_barang = count($id_barang_arr);

    if ($jumlah_barang === 0) {
        $errors[] = "Tidak ada data barang.";
    }

    for ($i = 0; $i < $jumlah_barang; $i++) {
        $id_b_raw = $id_barang_arr[$i] ?? '';
        if (!ctype_digit((string)$id_b_raw) || (int)$id_b_raw <= 0) {
            $errors[] = "ID barang ke-" . ($i + 1) . " tidak valid.";
        }

        $jml_raw = $jumlah_arr[$i] ?? '';
        if (!ctype_digit((string)$jml_raw) || (int)$jml_raw <= 0) {
            $errors[] = "Jumlah barang ke-" . ($i + 1) . " harus angka positif.";
        }

        $status_raw = $status_arr[$i] ?? '';
        if (!in_array($status_raw, ['Sesuai', 'Cacat'], true)) {
            $errors[] = "Status barang ke-" . ($i + 1) . " tidak valid.";
        }
    }

    /* --- Proses jika tidak ada error --- */
    if (empty($errors)) {
        for ($i = 0; $i < $jumlah_barang; $i++) {
            $id_b      = (int)$id_barang_arr[$i];
            $jumlah    = (int)$jumlah_arr[$i];
            $status    = $status_arr[$i];
            $keterangan = isset($keterangan_arr[$i]) ? trim($keterangan_arr[$i]) : '';
            $hapus      = isset($hapus_arr[$i]) ? (int)$hapus_arr[$i] : 0;
            $fileLama   = isset($file_lama_arr[$i]) ? basename($file_lama_arr[$i]) : '';

            /* Barang Sesuai: hanya update jumlah */
            if ($status === 'Sesuai') {
                $stmt_u = mysqli_prepare($koneksi,
                    "UPDATE barang SET jumlah_barang = ? WHERE id_barang = ?"
                );
                mysqli_stmt_bind_param($stmt_u, 'ii', $jumlah, $id_b);
                mysqli_stmt_execute($stmt_u);
                mysqli_stmt_close($stmt_u);
                continue;
            }

            /* Hapus file lama */
            if ($hapus === 1) {
                if ($fileLama !== '' && file_exists(UPLOAD_DIR . $fileLama)) {
                    unlink(UPLOAD_DIR . $fileLama);
                }
                $fileLama = '';
            }

            /* Upload file baru */
            $namaFileBaru = prosesUpload($_FILES['foto'] ?? [], $i);
            if ($namaFileBaru !== false) {
                /* Hapus file lama jika ada dan ada file baru */
                if ($fileLama !== '' && file_exists(UPLOAD_DIR . $fileLama)) {
                    unlink(UPLOAD_DIR . $fileLama);
                }
                $fileLama = $namaFileBaru;
            }

            /* Update barang Cacat */
            $stmt_u = mysqli_prepare($koneksi,
                "UPDATE barang
                 SET jumlah_barang = ?, keterangan = ?, foto_bukti = ?
                 WHERE id_barang = ?"
            );
            mysqli_stmt_bind_param($stmt_u, 'issi', $jumlah, $keterangan, $fileLama, $id_b);
            mysqli_stmt_execute($stmt_u);
            mysqli_stmt_close($stmt_u);
        }

        /* Reset status laporan → Menunggu Persetujuan */
        $stmt_lap = mysqli_prepare($koneksi,
            "UPDATE laporan
             SET status_laporan = 'Menunggu Persetujuan', catatanrevisi = NULL
             WHERE id_nota = ?"
        );
        mysqli_stmt_bind_param($stmt_lap, 'i', $id_nota);
        mysqli_stmt_execute($stmt_lap);
        mysqli_stmt_close($stmt_lap);

        /*
         * Reset status_retur HANYA untuk barang Sesuai.
         * Barang Cacat yang sudah status_retur = 'sudah' (dari input_retur.php)
         * TIDAK boleh di-reset, agar tidak hilang saat laporan direvisi ulang.
         */
        $stmt_ret = mysqli_prepare($koneksi,
            "UPDATE barang SET status_retur = NULL
             WHERE id_nota = ? AND LOWER(status_barang) = 'sesuai'"
        );
        mysqli_stmt_bind_param($stmt_ret, 'i', $id_nota);
        mysqli_stmt_execute($stmt_ret);
        mysqli_stmt_close($stmt_ret);

        header("Location: status_success_revisi_laporan.php");
        exit;
    }
}

/* ─────────────────────────────────────────────
   AMBIL NOTA YANG BERSTATUS DITOLAK
───────────────────────────────────────────── */
$stmt_nota = mysqli_prepare($koneksi,
    "SELECT n.*, l.catatanrevisi
     FROM nota n
     JOIN laporan l ON l.id_nota = n.id_nota
     WHERE l.status_laporan = 'Ditolak'
     ORDER BY n.id_nota DESC"
);
mysqli_stmt_execute($stmt_nota);
$result_nota = mysqli_stmt_get_result($stmt_nota);
mysqli_stmt_close($stmt_nota);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Revisi Laporan</title>

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
    transition: box-shadow 0.4s ease, margin-bottom 0.45s cubic-bezier(0.4, 0, 0.2, 1);
}

.card.expanded {
    margin-bottom: 0;
    box-shadow: 0 20px 40px rgba(0,0,0,0.13);
}

/* ===== STATUS BADGE — pojok kanan atas (seperti review_laporan) ===== */
.status-badge-corner {
    position: absolute;
    right: 20px;
    top: 18px;
    font-size: 12px;
    color: #e03b30;
    font-weight: 500;
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
    display: grid;
    grid-template-rows: 0fr;
    overflow: hidden;
    opacity: 0;
    transition:
        grid-template-rows 0.45s cubic-bezier(0.4, 0, 0.2, 1),
        opacity 0.35s ease;
}

.detail-wrapper > .detail-inner {
    overflow: hidden;
    padding-top: 0;
    transition: padding-top 0.45s cubic-bezier(0.4, 0, 0.2, 1);
}

.detail-wrapper.show {
    grid-template-rows: 1fr;
    opacity: 1;
}

.detail-wrapper.show > .detail-inner {
    padding-top: 2px;
}

/* ===== CATATAN REVISI ===== */
.catatan-revisi {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 12px;
    color: #7a5800;
    margin-top: 4px;
    margin-bottom: 6px;
    min-height: 40px;
    word-break: break-word;
}

.catatan-revisi strong {
    display: block;
    margin-bottom: 4px;
    font-size: 12px;
    color: #7a5800;
}

/* ===== JENIS LIST (identik dengan lihat_hasil & buat_laporan) ===== */
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

.barang-input[readonly] {
    cursor: default;
}

.label-white {
    color: white;
    font-size: 12px;
    font-weight: 500;
    display: block;
    margin-bottom: 4px;
}

/* ===== STATUS BUTTONS (identik buat_laporan & review_laporan) ===== */
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

/* ===== KETERANGAN TEXTAREA ===== */
.keterangan-textarea {
    width: 100%;
    border: none;
    border-radius: 12px;
    background: #d7dbe1;
    padding: 10px 12px;
    font-family: "Poppins", sans-serif;
    font-size: 12px;
    min-height: 70px;
    resize: none;
    margin-top: 4px;
    margin-bottom: 6px;
}

/* ===== UPLOAD BOX (identik buat_laporan) ===== */
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

/* ===== FILE ACTION BUTTONS ===== */
.file-btn {
    display: flex;
    gap: 8px;
    margin-top: 6px;
}

.file-btn button {
    flex: 1;
    padding: 7px;
    font-size: 12px;
    border-radius: 10px;
    cursor: pointer;
    font-family: "Poppins", sans-serif;
    font-weight: 500;
    transition: 0.2s;
    border: 2px solid transparent;
}

.btn-hapus {
    border-color: #ff3b30 !important;
    background: white;
    color: #ff3b30;
}

.btn-hapus.active-hapus {
    background: #ff3b30;
    color: white;
}

.btn-tambah {
    border-color: #5e91b2 !important;
    background: white;
    color: #5e91b2;
}

.btn-tambah.active-tambah {
    background: #5e91b2;
    color: white;
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

/* ===== EXPAND BUTTON (identik buat_laporan) ===== */
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
    transition: background 0.2s, box-shadow 0.2s;
}

.expand-btn:hover {
    background: #d4d9de;
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}

.expand-btn img {
    width: 18px;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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

.expand-btn.outside img {
    transform: rotate(180deg);
}

/* ===== LIGHTBOX (identik buat_laporan & review_laporan) ===== */
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
    <a href="javascript:history.back()" class="back-btn" title="Kembali">
        <img src="logo_back.png" alt="Kembali">
    </a>
    <h2>Revisi Laporan</h2>
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
    <?php if (mysqli_num_rows($result_nota) === 0): ?>
    <div class="empty-state">
        <div>📋</div>
        <p>Tidak ada laporan yang perlu direvisi.</p>
    </div>

    <?php else: $no = 1; while ($n = mysqli_fetch_assoc($result_nota)):
        $id_nota = (int)$n['id_nota'];

        /* Ambil barang dengan prepared statement */
        $stmt_b = mysqli_prepare($koneksi, "SELECT * FROM barang WHERE id_nota = ?");
        mysqli_stmt_bind_param($stmt_b, 'i', $id_nota);
        mysqli_stmt_execute($stmt_b);
        $data_barang = mysqli_stmt_get_result($stmt_b);
        mysqli_stmt_close($stmt_b);

        /* Kumpulkan barang & jenis aktif */
        $list_barang = [];
        $jenis_aktif = [];
        while ($brow = mysqli_fetch_assoc($data_barang)) {
            $list_barang[] = $brow;
            $j = trim($brow['jenis_barang'] ?? '');
            if ($j !== '' && !in_array($j, $jenis_aktif, true)) {
                $jenis_aktif[] = $j;
            }
        }
    ?>

    <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id_nota" value="<?php echo $id_nota; ?>">

    <div class="card" id="card-<?php echo $no; ?>">

        <!-- Status badge -->
        <div class="status-badge-corner">Ditolak</div>

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
        <div class="detail-inner">

            <div class="form-group" style="margin-top:14px;">
                <label>Nama Supplier</label>
                <input value="<?php echo htmlspecialchars($n['supplier'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>

            <!-- CATATAN REVISI dari manager -->
            <?php $catatan = trim($n['catatanrevisi'] ?? ''); if ($catatan !== ''): ?>
            <label style="font-size:13px;font-weight:600;">Catatan Revisi</label>
            <div class="catatan-revisi">
                <strong>⚠️ Catatan dari Manager:</strong>
                <?php echo nl2br(htmlspecialchars($catatan, ENT_QUOTES, 'UTF-8')); ?>
            </div>
            <?php endif; ?>

            <!-- JENIS BARANG INDIKATOR (identik lihat_hasil, buat_laporan, review_laporan) -->
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
            <?php $noBarang = 1; foreach ($list_barang as $idx => $b):
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
                <input name="jumlah[]"
                       class="barang-input"
                       type="number"
                       min="1"
                       value="<?php echo (int)$b['jumlah_barang']; ?>"
                       required>

                <!-- Status display (identik buat_laporan & review_laporan) -->
                <div class="status-display">
                    <button type="button" class="btn-green <?php echo $is_cacat ? 'btn-dim' : ''; ?>">Sesuai</button>
                    <button type="button" class="btn-red <?php echo !$is_cacat ? 'btn-dim' : ''; ?>">Cacat</button>
                </div>

                <?php if ($is_cacat): ?>

                    <!-- FOTO BUKTI: upload-box-view identik buat_laporan, klik lightbox jika ada file -->
                    <label class="label-white" style="margin-top:8px;">Lampiran Bukti</label>
                    <div class="foto-bukti-wrapper">
                        <?php if ($foto_ada): ?>
                            <button
                                type="button"
                                class="upload-box-view"
                                id="file-text-<?php echo $idx; ?>"
                                data-file="<?php echo htmlspecialchars($foto_bukti, ENT_QUOTES, 'UTF-8'); ?>"
                                onclick="klikFotoBox(this)"
                                title="Klik untuk melihat foto">
                                🖼️ <?php echo htmlspecialchars($foto_bukti, ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                        <?php else: ?>
                            <div class="upload-box-view no-foto"
                                 id="file-text-<?php echo $idx; ?>"
                                 data-file="">
                                Tidak ada file
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="file-btn">
                        <button type="button" class="btn-hapus"
                                onclick="hapusFile(this, <?php echo $idx; ?>)">
                            Hapus
                        </button>
                        <button type="button" class="btn-tambah"
                                onclick="tambahFile(this, <?php echo $idx; ?>)">
                            Tambah / Ganti
                        </button>
                    </div>

                    <input type="file"
                           name="foto[]"
                           id="file-input-<?php echo $idx; ?>"
                           accept="image/*,.pdf"
                           style="display:none;">
                    <input type="hidden" name="hapus_file[]"  value="0" id="hapus-<?php echo $idx; ?>">
                    <input type="hidden" name="file_lama[]"   value="<?php echo htmlspecialchars($foto_bukti, ENT_QUOTES, 'UTF-8'); ?>">

                    <label class="label-white">Keterangan</label>
                    <textarea name="keterangan[]"
                              class="keterangan-textarea"
                              placeholder="Tulis keterangan keluhan..."><?php echo htmlspecialchars($b['keterangan'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                <?php else: ?>
                    <!--
                        Dummy inputs untuk barang Sesuai agar index array tetap sinkron
                        (foto, hapus_file, file_lama, keterangan tidak diproses di backend untuk Sesuai)
                    -->
                    <input type="file"   name="foto[]"        id="file-input-<?php echo $idx; ?>" style="display:none;">
                    <input type="hidden" name="hapus_file[]"  value="0">
                    <input type="hidden" name="file_lama[]"   value="">
                    <input type="hidden" name="keterangan[]"  value="">
                <?php endif; ?>

                <!-- Hidden: status & id barang agar PHP tahu mana Sesuai/Cacat -->
                <input type="hidden" name="status_barang[]" value="<?php echo htmlspecialchars($b['status_barang'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="id_barang[]"     value="<?php echo (int)$b['id_barang']; ?>">

            </div>

            <?php $noBarang++; endforeach; ?>

            <button type="submit" name="simpan_revisi" class="submit-btn">
                Simpan Hasil Revisi Laporan
            </button>

        </div><!-- /detail-inner -->
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
/* ── Toggle expand / collapse (identik buat_laporan) ── */
function toggleDetail(id) {
    var detail     = document.getElementById("detail-"      + id);
    var insideBtn  = document.getElementById("btn-inside-"  + id);
    var outsideBtn = document.getElementById("btn-outside-" + id);
    var card       = document.getElementById("card-"        + id);

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

/* ── Klik foto box: jika ada file → lightbox, jika kosong → abaikan ── */
function klikFotoBox(el) {
    var file = el.dataset.file;
    if (file && file !== '') {
        bukaLightbox('uploads/' + file, file);
    }
}

/* ── Hapus file ── */
function hapusFile(btn, idx) {
    var box = document.getElementById("file-text-" + idx);
    box.innerText      = "Tidak ada file";
    box.dataset.file   = "";
    box.className      = "upload-box-view no-foto";
    box.onclick        = null;

    document.getElementById("hapus-" + idx).value = 1;

    btn.classList.add("active-hapus");
    btn.nextElementSibling.classList.remove("active-tambah");
}

/* ── Tambah / Ganti file ── */
function tambahFile(btn, idx) {
    var input = document.getElementById("file-input-" + idx);
    var box   = document.getElementById("file-text-"  + idx);

    input.click();

    input.onchange = function () {
        var file = this.files[0];
        if (!file) return;

        /* Reset hapus flag */
        document.getElementById("hapus-" + idx).value = 0;

        /* Preview nama file di box, tidak buka lightbox langsung */
        box.innerText    = "📎 " + file.name;
        box.dataset.file = "";   /* file baru belum punya path server */
        box.className    = "upload-box-view";
        box.onclick      = null; /* tidak bisa dibuka di lightbox sebelum upload */

        btn.classList.add("active-tambah");
        btn.previousElementSibling.classList.remove("active-hapus");
    };
}

/* ── Lightbox ── */
function bukaLightbox(src, filename) {
    document.getElementById("lightboxImg").src           = src;
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

document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") _closeLightbox();
});
</script>

</body>
</html>