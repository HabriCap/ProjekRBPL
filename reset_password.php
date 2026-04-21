<?php
include 'koneksi.php';

$username = $_GET['username'];
$notif = "";

if(isset($_POST['reset'])){

  $password_baru = $_POST['password'];

  mysqli_query($koneksi,
  "UPDATE user SET password='$password_baru' WHERE username='$username'");

  header("Location: index.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Reset Password</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:"Poppins",sans-serif;
}

body{
min-height:100vh;
display:flex;
justify-content:center;
align-items:center;
background:linear-gradient(180deg,#eef3f7,#e6edf3);
}

.card{
width:380px;
background:white;
padding:40px 35px;
border-radius:25px;
box-shadow:0 20px 40px rgba(0,0,0,0.08);
text-align:center;
}

.card h2{
margin-bottom:10px;
color:#2c3e50;
}

.card p{
font-size:14px;
color:#6c7a89;
margin-bottom:25px;
}

.input-group{
margin-bottom:22px;
text-align:left;
}

.input-group label{
font-size:13px;
font-weight:500;
color:#5f6f81;
}

.input-wrapper{
position:relative;
margin-top:8px;
}

.input-wrapper input{
width:100%;
padding:13px 45px 13px 15px;
border-radius:14px;
border:1px solid #d9e2ec;
background:#f7fafc;
outline:none;
font-size:14px;
}

.input-wrapper input:focus{
border-color:#5bb7c5;
box-shadow:0 0 0 3px rgba(91,183,197,0.15);
}

.icon-eye{
position:absolute;
right:12px;
top:50%;
transform:translateY(-50%);
width:20px;
cursor:pointer;
}

button{
width:100%;
padding:13px;
border:none;
border-radius:25px;
background:linear-gradient(90deg,#5bb7c5,#3f7aa3);
color:white;
font-weight:600;
cursor:pointer;
transition:0.2s;
}

button:hover{
transform:translateY(-2px);
box-shadow:0 10px 18px rgba(63,122,163,0.25);
}

.back{
margin-top:18px;
display:block;
font-size:13px;
text-decoration:none;
color:#4a90e2;
}

</style>
</head>

<body>

<div class="card">

<h2>Reset Password</h2>
<p>Masukkan password baru untuk akun <b><?= $username ?></b></p>

<form method="POST">

<div class="input-group">
<label>Password Baru</label>

<div class="input-wrapper">
<input type="password" name="password" id="password" placeholder="Masukkan password baru" required>
<img src="asset/icon_eyeclosed.png" class="icon-eye" id="togglePassword">
</div>

</div>

<button type="submit" name="reset">Reset Password</button>

</form>

<a href="index.php" class="back">← Kembali ke Login</a>

</div>

</body>
</html>

<script>
const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("password");

togglePassword.addEventListener("click", function(){

if(password.type === "password"){
password.type = "text";
togglePassword.src = "asset/icon_eyeopen.png";
}else{
password.type = "password";
togglePassword.src = "asset/icon_eyeclosed.png";
}

});
</script>