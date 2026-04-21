<?php
include 'koneksi.php';

$notif = "";

if(isset($_POST['cek'])){

  $username = $_POST['username'];

  $query = mysqli_query($koneksi,"SELECT * FROM user WHERE username='$username'");
  $data = mysqli_fetch_assoc($query);

  if($data){
    header("Location: reset_password.php?username=$username");
    exit;
  } else {
    $notif = "Username tidak ditemukan!";
  }

}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lupa Password</title>

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
margin-bottom:20px;
text-align:left;
}

.input-group label{
font-size:13px;
font-weight:500;
color:#5f6f81;
}

.input-group input{
width:100%;
padding:13px 15px;
margin-top:8px;
border-radius:14px;
border:1px solid #d9e2ec;
background:#f7fafc;
outline:none;
font-size:14px;
}

.input-group input:focus{
border-color:#5bb7c5;
box-shadow:0 0 0 3px rgba(91,183,197,0.15);
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

.error{
background:#ffdede;
color:#b30000;
padding:10px;
border-radius:10px;
font-size:13px;
margin-bottom:15px;
}

.circle{
position: absolute;
border-radius: 50%;
z-index: 1;
}

.c1 {
width: 90px;
height: 90px;
background: #5bb7c5;
top: 40px;
left: 40px;
}
.c2 {
width: 100px;
height: 100px;
background: #1dd1e5;
top: -35px;
        right: -44px;
      }
      .c3 {
        width: 50px;
        height: 50px;
        background: #8fd3dd;
        bottom: 120px;
        right: 60px;
      }
      .c4 {
        width: 110px;
        height: 110px;
        background: #48b5c1;
        border-radius: 50%;

        left: 33%;
        transform: translateX(-50%);
        bottom: -70px;
      }
      .c5 {
        width: 33px;
        height: 33px;
        background: #63c9d5;
        border-radius: 50%;
        top: 91%;
        left: 44%;
      }
      .c6 {
        width: 24px;
        height: 24px;
        background: #5cafb8;
        border-radius: 50%;
        top: 95%;
        left: 53%;
      }
      .c7 {
        width: 60px;
        height: 60px;
        background: #8ddee7;
        border-radius: 50%;
        top: 85%;
        left: 60%;
      }
      .c8 {
        width: 60px;
        height: 60px;
        background: #99e4ec;
        border-radius: 50%;
        top: 85%;
        left: 70%;
      }
      .c9 {
        width: 60px;
        height: 60px;
        background: #beebf0;
        border-radius: 50%;
        top: 85%;
        left: 80%;
      }
      .c10 {
        width: 60px;
        height: 60px;
        background: #77bfca;
        top: 50px;
        left: 150px;
      }
      .c11 {
        width: 50px;
        height: 50px;
        background: #98c6cd;
        top: 54px;
        left: 230px;
      }
      .c12 {
        width: 27px;
        height: 27px;
        background: #9ed4db;
        top: 26px;
        left: 126px;
      }
      .c13 {
        width: 19px;
        height: 19px;
        background: #75aeb6;
        top: 12px;
        left: 166px;
      }
</style>
</head>

<body>
<div class="bg-wrapper">
      <!-- Decorative Circles -->
      <div class="circle c1"></div>
      <div class="circle c2"></div>
      <div class="circle c3"></div>
      <div class="circle c4"></div>
      <div class="circle c5"></div>
      <div class="circle c6"></div>
      <div class="circle c7"></div>
      <div class="circle c8"></div>
      <div class="circle c9"></div>
      <div class="circle c10"></div>
      <div class="circle c11"></div>
      <div class="circle c12"></div>
      <div class="circle c13"></div>
    </div>
<div class="card">

<h2>Lupa Password</h2>
<p>Masukkan username untuk mereset password</p>

<?php if($notif!=""){ ?>
<div class="error"><?= $notif ?></div>
<?php } ?>

<form method="POST">

<div class="input-group">
<label>Username</label>
<input type="text" name="username" placeholder="Masukkan username" required>
</div>

<button type="submit" name="cek">Cari Akun</button>

</form>

<a href="index.php" class="back">← Kembali ke Login</a>

</div>

</body>
</html>