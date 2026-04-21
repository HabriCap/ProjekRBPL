<?php
include "koneksi.php";

if(isset($_POST['reset'])) {
    $username = $_POST['username'];
    $password_baru = $_POST['password_baru'];

    $hashed = password_hash($password_baru, PASSWORD_DEFAULT);

    $cek = mysqli_query($koneksi, "SELECT * FROM USER WHERE username='$username'");

    if(mysqli_num_rows($cek) > 0){
        mysqli_query($koneksi, "UPDATE user SET password='$hashed' WHERE username='$username'");
        echo "password berhasil direset!";
    } else {
        echo "username tidak ditemukan!";
    }
}
?>

