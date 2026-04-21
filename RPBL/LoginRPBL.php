<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login Awal</title>

  <?php
    session_start();
    include 'koneksi.php';

    $error = ""; 

    if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");
    $data = mysqli_fetch_assoc($query);

    if ($data && $password == $data['password']) {

      $_SESSION['login'] = true;
      $_SESSION['username'] = $data['username'];
      $_SESSION['role'] = $data['role'];

      if ($data['role'] == 'admin') {
        header("Location: dashboard_admin.php");
      } elseif ($data['role'] == 'kasir') {
        header("Location: dashboard_kasir.php");
      } elseif ($data['role'] == 'manager') {
        header ("Location: dashboard_manager.php");
      } else {
        header("location: login.php");
      }
      exit;

    } else {
      $error = "Username atau password salah!";
    }
  }
?>
  <style>
    :root{
      --bg:#f6fbff;
      --card:#ffffff;
      --primary:#2b7be4;
      --muted:#6b7280;
      --radius:14px;
      --shadow: 0 8px 30px rgba(43,123,228,0.08);
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      background:var(--bg);
      display:flex;
      align-items:center;
      justify-content:center;
      padding:32px;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }

    .page-bg{
        position:fixed; 
        inset:0; 
        overflow:hidden; 
        z-index:0
    }
    .circle{
        position:absolute; 
        border-radius:70%;  
        opacity:1;
    }
    .c1{
        width:360px;
        height:360px; 
        background:linear-gradient(135deg,#d7e9ff,#b6d8ff); 
        top:-80px; 
        right:-120px}
    .c2{
        width:220px;
        height:220px; 
        background:linear-gradient(135deg,#e6f3ff,#cfe9ff); 
        bottom:-60px; 
        left:-80px
    }
    
    .container{width:100%; max-width:420px; z-index:1}
    .card{
      background:var(--card);
      border-radius:var(--radius);
      padding:28px;
      box-shadow:var(--shadow);
      border:1px solid rgba(43,123,228,0.06);
    }

 
    .small-title{font-size:14px;color:var(--primary); margin:0 0 6px 0; font-weight:600}
    .welcome{font-size:22px;margin:0 0 18px 0;color:#0f1724}

  
    .field{display:block;margin-bottom:14px}
    .label-text{display:block;font-size:13px;color:var(--muted); margin-bottom:6px}
    .input-wrap{
      display:flex; align-items:center;
      background:#f8fafc; border-radius:10px; padding:10px 12px;
      border:1px solid rgba(15,23,36,0.04);
    }
    .input-wrap .icon{width:20px;height:20px;color:#9aa6bf; margin-right:10px; flex:0 0 20px}
    .input-wrap input{border:0; background:transparent; outline:none; font-size:15px; flex:1}
    .toggle-btn{background:transparent;border:0;cursor:pointer;font-size:16px;padding:0 6px;color:#6b7280}

    
    .row{display:flex;align-items:center}
    .between{justify-content:space-between}
    .link{font-size:13px;color:var(--primary); text-decoration:none}


    .btn{width:100%; padding:12px 14px; border-radius:10px; border:0; cursor:pointer; font-weight:600; font-size:15px}
    .btn.primary{background:var(--primary); color:#fff; margin-top:8px; box-shadow:0 6px 18px rgba(43,123,228,0.12)}
    .separator{display:flex; align-items:center; gap:12px; margin:16px 0}
    .separator span{color:var(--muted); font-size:13px; width:100%; text-align:center}

   
    .btn.google{display:flex; align-items:center; gap:10px; justify-content:center; background:#fff; border:1px solid #e6eefc; color:#111}
    .google-logo{width:18px;height:18px}

    
    .error-msg{color:#b91c1c; font-size:13px; margin-bottom:8px; display:none}

  
    @media (max-width:420px){
      .container{padding:0 12px}
      .card{padding:20px}
    }
  </style>
</head>
<body>
  <div class="page-bg" aria-hidden="true">
    <div class="circle c1"></div>
    <div class="circle c2"></div>
  </div>

  <main class="container" role="main">
    <section class="card" aria-labelledby="loginTitle">
      <h1 id="loginTitle" class="small-title">Selamat Datang</h1>

      <form method="post" >
        <?php if ($error != ""): ?>
          <div class="error-msg" style="display:block;">
        <?= $error; ?>
        </div>
        <?php endif; ?>

        <label class="field" for="username">
          <span class="label-text">Username</span>
          <div class="input-wrap">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4 0-8 2-8 5v1h16v-1c0-3-4-5-8-5z"/></svg>
            <input name="username" id="username" type="text" placeholder="Masukkan Username" autocomplete="username" required />
          </div>
        </label>

        <label class="field" for="password">
          <span class="label-text">Password</span>
          <div class="input-wrap">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M17 8V7a5 5 0 0 0-10 0v1H5v12h14V8h-2zM9 7a3 3 0 0 1 6 0v1H9V7z"/></svg>
            <input name="password" id="password" type="password" placeholder="Masukkan Password" autocomplete="current-password" required />
            <button type="button" id="togglePwd" class="toggle-btn" aria-label="Tampilkan password">👁️</button>
          </div>
        </label>

        <div class="row between" style="margin-bottom:12px;">
          <a class="link" href="#" onclick="event.preventDefault(); alert('Fitur lupa password belum aktif pada demo ini.');">Lupa Password?</a>
        </div>

        <button class="btn primary" type="submit" name="login">Masuk</button>

        <div class="separator" aria-hidden="true"><span>— Atau masuk dengan —</span></div>

        <button type="button" class="btn google" id="googleBtn" aria-label="Masuk dengan Google">
          <svg class="google-logo" viewBox="0 0 533.5 544.3" aria-hidden="true" focusable="false">
            <path fill="#4285F4" d="M533.5 278.4c0-17.4-1.6-34.1-4.6-50.4H272v95.4h147.1c-6.3 34.1-25.1 62.9-53.6 82.2v68.2h86.6c50.6-46.6 81.4-115.4 81.4-195.4z"/>
            <path fill="#34A853" d="M272 544.3c72.6 0 133.6-24.1 178.1-65.4l-86.6-68.2c-24.1 16.2-55 25.8-91.5 25.8-70.4 0-130-47.5-151.4-111.4H33.6v69.9C77.9 486.6 169.6 544.3 272 544.3z"/>
            <path fill="#FBBC05" d="M120.6 325.1c-10.8-32.1-10.8-66.7 0-98.8V156.4H33.6c-43.8 86.9-43.8 189.6 0 276.5l87-69.8z"/>
            <path fill="#EA4335" d="M272 107.7c38.9 0 73.9 13.4 101.4 39.6l76-76C405.6 24.9 344.6 0 272 0 169.6 0 77.9 57.7 33.6 156.4l87 69.9C142 155.2 201.6 107.7 272 107.7z"/>
          </svg>
          Google
        </button>
      </form>
    </section>
  </main>

  <script>
    (function(){
      const toggle = document.getElementById('togglePwd');
      const pwd = document.getElementById('password');
      const form = document.getElementById('loginForm');
      const err = document.getElementById('error');
      const googleBtn = document.getElementById('googleBtn');

      // Toggle password visibility
      toggle.addEventListener('click', function(){
        if (pwd.type === 'password') {
          pwd.type = 'text';
          toggle.textContent = '🕶';
          toggle.setAttribute('aria-label', 'Sembunyikan password');
        } else {
          pwd.type = 'password';
          toggle.textContent = '👁️';
          toggle.setAttribute('aria-label', 'Tampilkan password');
        }
      });
       
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memeriksa...';

       
        setTimeout(function(){
          // Demo credential: demo / demo123
          if (username === 'demo' && password === 'demo123') {
            submitBtn.textContent = 'Berhasil! Mengalihkan...';
            // Simulasi redirect
            setTimeout(function(){
              alert('Login sukses (demo). Anda akan diarahkan ke dashboard.');
              submitBtn.disabled = false;
              submitBtn.textContent = 'Masuk';
            }, 700);
          } else {
            err.textContent = 'Username atau password salah.';
            err.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Masuk';
          }
        }, 900);
      });

  
      googleBtn.addEventListener('click', function(){
        alert('Login dengan Google belum diaktifkan.');
      });

      pwd.addEventListener('keydown', function(ev){
        if (ev.key === 'Enter') {
          ev.preventDefault();
          form.dispatchEvent(new Event('submit', {cancelable: true}));
        }
      });
  </script>
</body>
</html>
