<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: index.php?error=login_dulu");
    exit;
}

function cekRole($role) {
    if ($_SESSION['role'] != $role) {

        if ($role == "admin") {
            header("Location: index.php?error=bukan_admin");
        }
        elseif ($role == "kasir") {
            header("Location: index.php?error=bukan_kasir");
        }
        elseif ($role == "manager") {
            header("Location: index.php?error=bukan_manager");
        }

        exit;
    }
}
?>