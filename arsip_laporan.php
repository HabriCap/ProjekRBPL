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

if (isset($_POST['arsipkan_laporan'])) {
    $id_laporan = $_POST['id_laporan'];

    if (!empty($id_laporan)) {

        // CEK BIAR TIDAK DOUBLE (sama seperti pola di input_laporan)
        $cek = mysqli_query($koneksi, "SELECT * FROM arsip_laporan WHERE id_laporan='$id_laporan'");

        if (mysqli_num_rows($cek) == 0) {

            $insert = mysqli_query($koneksi, "
                INSERT INTO arsip_laporan (id_laporan, tanggal_arsip)
                VALUES ('$id_laporan', NOW())
            ");

            if (!$insert) {
                die("Query gagal: " . mysqli_error($koneksi));
            }
        }
    }

    header("Location: success_arsip_laporan.php");
    exit();
}

$data_laporan = mysqli_query($koneksi, "
    SELECT l.*, n.nomor_nota, n.tanggal_nota, n.supplier, n.foto, n.status
    FROM laporan l
    JOIN nota n ON n.id_nota = l.id_nota
    WHERE l.id_laporan NOT IN (
        SELECT id_laporan FROM arsip_laporan
    )
    ORDER BY l.tanggal_laporan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip Laporan Barang Masuk</title>

    <!-- Font sama persis dengan input_nota -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: #efefef;
            overflow-x: hidden;
        }

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
        }

        .back-btn img {
            width: 20px;
        }

        .header h2 {
            font-size: 18px;
            font-weight: 500;
        }

        .header-circle-big {
            position: absolute;
            width: 90px;
            height: 90px;
            background: #5bb7c5;
            border-radius: 50%;
            right: -20px;
            top: 10px;
        }

        .header-circle-small {
            position: absolute;
            width: 45px;
            height: 45px;
            background: #5bb7c5;
            border-radius: 50%;
            left: -11px;
            top: 50px;
        }

        .header-circle-small_2 {
            position: absolute;
            width: 18px;
            height: 18px;
            background: #519eaa;
            border-radius: 50%;
            left: 0;
            top: 22px;
        }

        .container {
            padding: 25px 20px 80px;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 22px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 22px;
            position: relative;
        }

        .card h3 {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .badge-status {
            position: absolute;
            top: 18px;
            right: 18px;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: white;
        }

        .badge-approved {
            background: #4cd964;
        }

        .badge-pending {
            background: #ff9500;
        }


        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
        }

        .form-group input,
        .form-input {
            width: 100%;
            height: 40px;
            border: none;
            border-radius: 12px;
            background: #e9edf2;
            padding: 0 12px;
            margin-top: 5px;
            font-size: 13px;
            font-family: "Poppins", sans-serif;
        }

 
        .catatan-box {
            background: #e9edf2;
            border-radius: 12px;
            padding: 12px;
            font-size: 12px;
            color: #374151;
            margin-top: 5px;
            line-height: 1.6;
        }


        .jenis-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .jenis-item {
            padding: 6px 12px;
            border-radius: 12px;
            border: 1px solid #cfd4da;
            font-size: 12px;
            background: #f5f6f8;
            cursor: default;
            transition: 0.2s;
        }

        .jenis-item.active {
            background: #7ea2b9;
            color: white;
            border: none;
        }

        .barang-wrapper {
            background: #7ea2b9;
            padding: 15px;
            border-radius: 16px;
            margin-top: 12px;
        }

        .barang-box {
            margin-bottom: 15px;
        }

        .barang-box:last-child {
            margin-bottom: 0;
        }

        .barang-title {
            color: white;
            font-size: 13px;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .barang-input {
            width: 100%;
            height: 36px;
            border-radius: 10px;
            border: none;
            background: #d7dbe1;
            padding: 8px;
            margin-bottom: 6px;
            font-size: 12px;
            font-family: "Poppins", sans-serif;
        }

        .barang-label {
            color: white;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .dot-wrapper {
            position: absolute;
            right: 15px;
            top: 10px;
            display: flex;
            gap: 8px;
        }

        .dot {
            width: 14px;
            height: 14px;
            background: #63c9d5;
            border-radius: 50%;
        }

        .status-wrap {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }

        .btn-status {
            flex: 1;
            border: none;
            padding: 8px;
            border-radius: 10px;
            color: white;
            font-size: 12px;
            font-family: "Poppins", sans-serif;
            font-weight: 500;
        }

        .btn-sesuai {
            background: #4cd964;
        }

        .btn-cacat {
            background: #ff3b30;
        }


        .upload-box {
            background: #e5e5e5;
            border-radius: 15px;
            padding: 14px;
            text-align: center;
            font-size: 12px;
            color: #555;
            margin-top: 6px;
            cursor: pointer;
            word-break: break-all;
        }

        .tanggapan-wrapper {
            background: #7ea2b9;
            padding: 15px;
            border-radius: 16px;
            margin-top: 12px;
            position: relative;
        }

        /* =============================================
           EXPAND / COLLAPSE BUTTON
           ============================================= */
        .expand-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e4e8;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px auto 0;
            cursor: pointer;
            font-size: 18px;
            border: none;
            font-family: "Poppins", sans-serif;
        }

        .detail-section {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
        }

        .detail-section.show {
            max-height: 5000px;
        }


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
            margin-top: 15px;
            font-family: "Poppins", sans-serif;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9aa1a7;
            font-size: 14px;
        }

    </style>
</head>

<body>

<div class="header">

    <a class="back-btn" href="javascript:history.back()">
        <img src="logo_back.png" alt="back">
    </a>

    <h2>Arsip Laporan Barang Masuk</h2>

    <div class="header-circle-big"></div>
    <div class="header-circle-small"></div>
    <div class="header-circle-small_2"></div>

</div>

<div class="container">

<?php
$no = 1;

if (mysqli_num_rows($data_laporan) == 0): ?>

    <div class="empty-state">
        <div style="font-size:40px;">📭</div>
        <p style="margin-top:10px;">Belum ada laporan yang siap diarsipkan.</p>
    </div>

<?php else:
    while ($lap = mysqli_fetch_assoc($data_laporan)):

        /* Ambil semua barang dari nota ini */
        $data_barang = mysqli_query($koneksi, "
            SELECT * FROM barang WHERE id_nota='" . $lap['id_nota'] . "'
        ");

        /* Ambil semua jenis barang unik untuk indikator pills */
        $jenis_aktif = [];
        $tmp_barang = mysqli_query($koneksi, "
            SELECT DISTINCT jenis_barang FROM barang WHERE id_nota='" . $lap['id_nota'] . "'
        ");
        while ($j = mysqli_fetch_assoc($tmp_barang)) {
            $jenis_aktif[] = $j['jenis_barang'];
        }

        /* Ambil tanggapan supplier jika ada */
        $retur = mysqli_query($koneksi, "
            SELECT r.* FROM retur r
            JOIN barang b ON b.id_barang = r.id_barang
            WHERE b.id_nota='" . $lap['id_nota'] . "'
            LIMIT 1
        ");
        $r = mysqli_fetch_assoc($retur);

        $semua_jenis = [
            "Material Bangunan",
            "Besi & Logam",
            "Listrik",
            "Keramik & Lantai",
            "Alat Pertukangan",
            "Kayu & Olahan"
        ];
?>

    <!-- CARD LAPORAN #<?= $no ?> -->
    <form method="POST">

        <!-- Hidden field: id_laporan untuk INSERT arsip_laporan -->
        <input type="hidden" name="id_laporan" value="<?= $lap['id_laporan'] ?>">

        <div class="card">

            <!-- Badge status nota -->
            <span class="badge-status <?= $lap['status'] == 'Sudah Dicek' ? 'badge-approved' : 'badge-pending' ?>">
                <?= $lap['status'] == 'Sudah Dicek' ? 'Approved' : $lap['status'] ?>
            </span>

            <!-- Nomor & Tanggal Nota (selalu tampil) -->
            <div class="form-group" style="margin-top:5px;">
                <label>Nomer Nota</label>
                <input class="form-input" type="text" value="<?= htmlspecialchars($lap['nomor_nota']) ?>" readonly>
            </div>

            <div class="form-group">
                <label>Tanggal Nota</label>
                <input class="form-input" type="text" value="<?= htmlspecialchars($lap['tanggal_nota']) ?>" readonly>
            </div>

            <!-- Tombol expand/collapse (dalam card, awalnya tampil) -->
            <button type="button" class="expand-btn" id="btn-inside-<?= $no ?>" onclick="toggle(<?= $no ?>)">⌄</button>

            <!-- =============================================
                 DETAIL SECTION (expand/collapse)
                 ============================================= -->
            <div class="detail-section" id="detail-<?= $no ?>">

                <!-- Nama Supplier -->
                <div class="form-group" style="margin-top:12px;">
                    <label>Nama Supplier</label>
                    <input class="form-input" type="text" value="<?= htmlspecialchars($lap['supplier']) ?>" readonly>
                </div>

                <!-- Catatan Revisi (jika ada kolom catatan di laporan) -->
                <?php
                /* Cek apakah ada catatan di tabel laporan */
                if (!empty($lap['catatan'])): ?>
                <div class="form-group">
                    <label>Catatan Revisi</label>
                    <div class="catatan-box"><?= nl2br(htmlspecialchars($lap['catatan'])) ?></div>
                </div>
                <?php endif; ?>

                <!-- Jenis Barang Indikator Pills -->
                <label style="font-size:13px;font-weight:600;">Jenis Barang</label>
                <div class="jenis-list">
                    <?php foreach ($semua_jenis as $j): ?>
                    <div class="jenis-item <?= in_array($j, $jenis_aktif) ? 'active' : '' ?>">
                        <?= $j ?>
                    </div>
                    <?php endforeach; ?>
                </div>


                <div class="barang-wrapper">
                    <?php
                    $noBarang = 1;
                    mysqli_data_seek($data_barang, 0);
                    while ($b = mysqli_fetch_assoc($data_barang)):
                    ?>
                    <div class="barang-box" style="position:relative;">

                        <!-- Dots dekorasi (sama input_nota) -->
                        <div class="dot-wrapper">
                            <div class="dot"></div>
                            <div class="dot"></div>
                            <div class="dot"></div>
                        </div>

                        <div class="barang-title">Barang ke-<?= $noBarang++ ?></div>

                        <!-- Nama Barang -->
                        <input class="barang-input" type="text" value="<?= htmlspecialchars($b['nama_barang']) ?>" readonly>

                        <!-- Jumlah -->
                        <span class="barang-label">Jumlah</span>
                        <input class="barang-input" type="text" value="<?= htmlspecialchars($b['jumlah_barang']) ?>" readonly>

                        <!-- Status Sesuai / Cacat -->
                        <div class="status-wrap">
                            <button type="button" class="btn-status btn-sesuai"
                                style="<?= $b['status_barang'] == 'Sesuai' ? '' : 'opacity:.3;' ?>">
                                Sesuai
                            </button>
                            <button type="button" class="btn-status btn-cacat"
                                style="<?= $b['status_barang'] == 'Cacat' ? '' : 'opacity:.3;' ?>">
                                Cacat
                            </button>
                        </div>

                        <!-- Lampiran & Keterangan jika Cacat -->
                        <?php if ($b['status_barang'] == 'Cacat'): ?>

                        <span class="barang-label">Lampiran Bukti</span>
                        <div class="upload-box" onclick="bukaFile('<?= $b['foto_bukti'] ?>')">
                            <?= !empty($b['foto_bukti']) ? htmlspecialchars($b['foto_bukti']) : '(tidak ada foto)' ?>
                        </div>

                        <span class="barang-label">Keterangan / Keluhan</span>
                        <div class="upload-box">
                            <?= !empty($b['keterangan']) ? htmlspecialchars($b['keterangan']) : '-' ?>
                        </div>

                        <?php endif; ?>

                    </div>
                    <?php endwhile; ?>
                </div>
    
                <?php if ($r): ?>
                <div class="tanggapan-wrapper" style="position:relative;">

                    <div class="dot-wrapper">
                        <div class="dot"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                    </div>

                    <span class="barang-label">Tanggapan & Tindak Lanjut Supplier</span>
                    <div class="upload-box" style="margin-top:6px;">
                        <?= !empty($r['tanggapan']) ? htmlspecialchars($r['tanggapan']) : '-' ?>
                    </div>

                    <span class="barang-label">Lampiran Bukti Tindak Lanjut (Opt.)</span>
                    <div class="upload-box" onclick="bukaFile('<?= $r['tindaklanjut'] ?>')" style="margin-top:6px;">
                        <?= !empty($r['tindaklanjut']) ? htmlspecialchars($r['tindaklanjut']) : '(tidak ada lampiran)' ?>
                    </div>

                </div>
                <?php endif; ?>

                <!-- TOMBOL ARSIPKAN LAPORAN -->
                <button type="submit" class="submit-btn" name="arsipkan_laporan">
                    Arsipkan Laporan
                </button>

            </div>
            <!-- END DETAIL SECTION -->

        </div>
        <!-- END CARD -->

    </form>

    <!-- Tombol expand di luar card (muncul saat collapsed) -->
    <div class="expand-btn" id="btn-outside-<?= $no ?>" style="display:none;margin-bottom:15px;" onclick="toggle(<?= $no ?>)">⌃</div>

<?php
        $no++;
    endwhile;
endif;
?>

</div>
<!-- END CONTAINER -->

<script>

function toggle(id) {
    let detail = document.getElementById("detail-" + id);
    let insideBtn = document.getElementById("btn-inside-" + id);
    let outsideBtn = document.getElementById("btn-outside-" + id);

    detail.classList.toggle("show");

    if (detail.classList.contains("show")) {
        insideBtn.style.display = "none";
        outsideBtn.style.display = "flex";
    } else {
        insideBtn.style.display = "flex";
        outsideBtn.style.display = "none";
    }
}

function bukaFile(file) {
    if (file && file.trim() !== "") {
        window.open("uploads/" + file, "_blank");
    }
}
</script>

</body>
</html>