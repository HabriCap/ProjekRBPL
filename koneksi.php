<?php
$koneksi = mysqli_connect("localhost","root","","rpbl_db");

if (!$koneksi) {
    die("koneksi gagal: " . mysqli_connect_error());
}
?>