<?php
session_start();
include 'koneksi.php';

$data = mysqli_query($koneksi, "SELECT * FROM nota ");

?>