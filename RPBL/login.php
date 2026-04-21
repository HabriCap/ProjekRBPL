<?php
session_start();
include "koneksi.php";

if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM user WHERE username=
    '$username' AND password='$password'");

    $data = mysqli_fetch_assoc($query);

    if($data){
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['login'] = true;

        if($data['role'] == 'admin_gudang'){
            header("location: dashboard_admin.php");
        } elseif($data['role'] == 'kasir') {
            header("location: dashboard_kasir.php");
        } elseif($data['role'] == 'manager') {
            header("location: dashboard_manager.php");
        }

    } else {
        echo "Login gagal";
    }
}
?>
