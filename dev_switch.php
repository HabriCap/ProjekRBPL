<?php
/**
 * dev_switch.php
 * ─────────────────────────────────────────────────────────
 * HALAMAN KHUSUS DEVELOPER — Jangan upload ke server produksi!
 * Memungkinkan akses langsung ke dashboard tiap role
 * tanpa proses login, untuk mempermudah bug-fixing.
 * ─────────────────────────────────────────────────────────
 */

/* ── Blokir akses di environment produksi ── */
define('DEV_ALLOWED_IPS', ['127.0.0.1', '::1']);

$client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($client_ip, DEV_ALLOWED_IPS, true)) {
    http_response_code(403);
    die('403 Forbidden — halaman ini hanya dapat diakses dari localhost.');
}

/* ── Mulai session & inject role ── */
session_start();

$allowed_roles = ['admin', 'kasir', 'manager'];

if (isset($_GET['role']) && in_array($_GET['role'], $allowed_roles, true)) {
    $role = $_GET['role'];

    /* Set session persis seperti login normal */
    $_SESSION['login'] = true;
    $_SESSION['role']  = $role;

    /* Mapping role → halaman dashboard */
    $dashboards = [
        'admin'   => 'User_Admin.php',
        'kasir'   => 'User_kasir.php',
        'manager' => 'User_Manajer.php',
    ];

    header("Location: " . $dashboards[$role]);
    exit;
}

/* ── Jika ada session aktif, tampilkan info ── */
$current_role = $_SESSION['role'] ?? null;
$is_logged_in = isset($_SESSION['login']) && $_SESSION['login'] === true;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🛠 Dev Role Switcher</title>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* ── Variables ── */
:root {
    --bg:        #0d1117;
    --surface:   #161b22;
    --border:    #30363d;
    --text:      #e6edf3;
    --muted:     #7d8590;
    --accent:    #58a6ff;
    --green:     #3fb950;
    --yellow:    #d29922;
    --red:       #f85149;
    --purple:    #bc8cff;
    --orange:    #ffa657;
}

* { margin:0; padding:0; box-sizing:border-box; }

body {
    background: var(--bg);
    color: var(--text);
    font-family: "Poppins", sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

/* ── Warning banner ── */
.warning-banner {
    background: linear-gradient(135deg, #5a1e00, #3a1200);
    border: 1px solid #f85149;
    border-radius: 10px;
    padding: 10px 16px;
    font-family: "JetBrains Mono", monospace;
    font-size: 11px;
    color: #f85149;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.warning-banner::before {
    content: "⚠";
    font-size: 14px;
}

/* ── Main card ── */
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    width: 100%;
    max-width: 420px;
    overflow: hidden;
}

/* ── Card header ── */
.card-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #1f6feb, #388bfd);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.card-header h1 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
    line-height: 1.2;
}

.card-header p {
    font-size: 12px;
    color: var(--muted);
    margin-top: 2px;
}

/* ── Session status bar ── */
.session-bar {
    padding: 10px 24px;
    background: #0d1117;
    border-bottom: 1px solid var(--border);
    font-family: "JetBrains Mono", monospace;
    font-size: 11px;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 8px;
}

.session-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.session-dot.active  { background: var(--green);  box-shadow: 0 0 6px var(--green); }
.session-dot.inactive { background: var(--muted); }

.session-role {
    color: var(--accent);
    font-weight: 700;
}

/* ── Role list ── */
.role-list {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.role-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: transparent;
    cursor: pointer;
    text-decoration: none;
    color: var(--text);
    transition: background 0.15s, border-color 0.15s, transform 0.12s;
    position: relative;
    overflow: hidden;
}

.role-btn::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    border-radius: 0 2px 2px 0;
    transition: opacity 0.2s;
}

.role-btn.admin::before   { background: var(--orange); }
.role-btn.kasir::before   { background: var(--green);  }
.role-btn.manager::before { background: var(--purple); }

.role-btn:hover {
    border-color: #484f58;
    background: #21262d;
    transform: translateX(2px);
}

.role-btn.active-role {
    border-color: var(--accent);
    background: #0d2044;
}

.role-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.role-badge {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.role-badge.admin   { background: rgba(255, 166, 87, 0.15); }
.role-badge.kasir   { background: rgba(63, 185, 80, 0.15);  }
.role-badge.manager { background: rgba(188, 140, 255, 0.15);}

.role-info {}
.role-name {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.2;
}

.role-name.admin   { color: var(--orange); }
.role-name.kasir   { color: var(--green);  }
.role-name.manager { color: var(--purple); }

.role-desc {
    font-size: 11px;
    color: var(--muted);
    margin-top: 2px;
}

.role-target {
    font-family: "JetBrains Mono", monospace;
    font-size: 10px;
    color: var(--muted);
    background: var(--bg);
    padding: 3px 7px;
    border-radius: 5px;
    border: 1px solid var(--border);
    flex-shrink: 0;
}

.active-pill {
    font-size: 9px;
    font-weight: 700;
    background: var(--accent);
    color: #000;
    padding: 2px 7px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    flex-shrink: 0;
}

/* ── Footer ── */
.card-footer {
    padding: 14px 24px;
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.btn-clear {
    font-size: 12px;
    color: var(--red);
    background: transparent;
    border: 1px solid var(--border);
    border-radius: 7px;
    padding: 6px 12px;
    cursor: pointer;
    font-family: "Poppins", sans-serif;
    transition: background 0.15s, border-color 0.15s;
}

.btn-clear:hover {
    background: rgba(248, 81, 73, 0.1);
    border-color: var(--red);
}

.footer-note {
    font-size: 10px;
    color: var(--muted);
    font-family: "JetBrains Mono", monospace;
}

/* ── Responsive ── */
@media (max-width: 480px) {
    .role-target { display: none; }
}
</style>
</head>
<body>

<div class="warning-banner">
    Dev only — jangan deploy ke server produksi
</div>

<div class="card">

    <!-- Header -->
    <div class="card-header">
        <div class="header-icon">🛠</div>
        <div>
            <h1>Dev Role Switcher</h1>
            <p>Pilih role untuk langsung masuk ke dashboard-nya</p>
        </div>
    </div>

    <!-- Session status -->
    <div class="session-bar">
        <?php if ($is_logged_in && $current_role): ?>
            <div class="session-dot active"></div>
            Session aktif &nbsp;·&nbsp; role: <span class="session-role"><?= htmlspecialchars($current_role) ?></span>
        <?php else: ?>
            <div class="session-dot inactive"></div>
            Tidak ada session aktif
        <?php endif; ?>
    </div>

    <!-- Role buttons -->
    <div class="role-list">

        <!-- Admin Gudang -->
        <a href="?role=admin" class="role-btn admin <?= $current_role === 'admin' ? 'active-role' : '' ?>">
            <div class="role-left">
                <div class="role-badge admin">📦</div>
                <div class="role-info">
                    <div class="role-name admin">Admin Gudang</div>
                    <div class="role-desc">Input nota, retur, lihat hasil, arsip</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <?php if ($current_role === 'admin'): ?>
                    <span class="active-pill">aktif</span>
                <?php else: ?>
                    <span class="role-target">User_Admin.php</span>
                <?php endif; ?>
            </div>
        </a>

        <!-- Kasir Toko -->
        <a href="?role=kasir" class="role-btn kasir <?= $current_role === 'kasir' ? 'active-role' : '' ?>">
            <div class="role-left">
                <div class="role-badge kasir">🧾</div>
                <div class="role-info">
                    <div class="role-name kasir">Kasir Toko</div>
                    <div class="role-desc">Cek barang, buat laporan, revisi</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <?php if ($current_role === 'kasir'): ?>
                    <span class="active-pill">aktif</span>
                <?php else: ?>
                    <span class="role-target">User_kasir.php</span>
                <?php endif; ?>
            </div>
        </a>

        <!-- Manager Toko -->
        <a href="?role=manager" class="role-btn manager <?= $current_role === 'manager' ? 'active-role' : '' ?>">
            <div class="role-left">
                <div class="role-badge manager">📋</div>
                <div class="role-info">
                    <div class="role-name manager">Manajer Toko</div>
                    <div class="role-desc">Review & validasi laporan barang masuk</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <?php if ($current_role === 'manager'): ?>
                    <span class="active-pill">aktif</span>
                <?php else: ?>
                    <span class="role-target">User_Manajer.php</span>
                <?php endif; ?>
            </div>
        </a>

    </div>

    <!-- Footer -->
    <div class="card-footer">
        <a href="?clear=1" class="btn-clear" onclick="return confirm('Hapus session aktif?')">
            🗑 Clear Session
        </a>
        <span class="footer-note">localhost only · <?= date('H:i:s') ?></span>
    </div>

</div>

<?php
/* ── Handle clear session ── */
if (isset($_GET['clear'])) {
    session_destroy();
    header("Location: dev_switch.php");
    exit;
}
?>

</body>
</html>