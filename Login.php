<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CoffeePay – Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: #09020f;
      color: #ffffff;
      font-family: 'Inter', sans-serif;
      min-height: 100vh; overflow-x: hidden;
      position: relative;
      display: flex; align-items: center; justify-content: center;
    }

    body::before {
      content: ''; position: fixed;
      top: -100px; right: -80px;
      width: 600px; height: 600px; border-radius: 50%;
      background: radial-gradient(circle at 60% 40%, #2b1055 0%, #120524 30%, transparent 70%);
      opacity: 0.9; pointer-events: none; z-index: 0;
    }
    body::after {
      content: ''; position: fixed;
      bottom: -120px; left: -60px;
      width: 420px; height: 420px; border-radius: 50%;
      background: radial-gradient(circle, #6a1b9a 0%, transparent 65%);
      opacity: 0.4; pointer-events: none; z-index: 0;
    }

    .page {
      position: relative; z-index: 1;
      width: 100%; min-height: 100vh;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      padding: 40px 16px 120px;
    }

    .brand {
      display: flex; align-items: center;
      justify-content: center; margin-bottom: 36px;
    }
    .brand-name {
      font-size: clamp(32px, 6vw, 52px);
      font-weight: 700; color: #ffffff;
      letter-spacing: -0.02em; line-height: 1;
    }
    .brand-name .brand-c {
      font-family: 'Playfair Display', serif;
      font-style: italic; font-size: 1.1em;
    }

    .form-card {
      width: 100%; max-width: 380px;
      display: flex; flex-direction: column; gap: 14px;
    }

    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-icon {
      position: absolute; left: 16px;
      width: 18px; height: 18px; pointer-events: none;
      display: flex; align-items: center; justify-content: center;
    }
    .input-icon svg {
      width: 16px; height: 16px; fill: none;
      stroke: #5e17eb; stroke-width: 1.8;
      stroke-linecap: round; stroke-linejoin: round;
    }
    .input-wrap input {
      width: 100%;
      background: rgba(255,255,255,0.04);
      border: 1.5px solid rgba(94,23,235,0.35);
      border-radius: 999px;
      padding: 13px 20px 13px 44px;
      color: #ffffff; font-family: 'Inter', sans-serif;
      font-size: 14px; font-weight: 400; outline: none;
      transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
    }
    .input-wrap input::placeholder { color: rgba(165,148,189,0.6); font-size: 13px; }
    .input-wrap input:focus {
      border-color: #5e17eb;
      background: rgba(94,23,235,0.08);
      box-shadow: 0 0 0 3px rgba(94,23,235,0.18);
    }

    .btn-submit {
      width: 100%; background: #5e17eb; border: none;
      color: #fff; font-family: 'Inter', sans-serif;
      font-size: 13px; font-weight: 700;
      letter-spacing: 0.12em; text-transform: uppercase;
      cursor: pointer; padding: 14px 26px; border-radius: 999px;
      box-shadow: 0 0 24px rgba(94,23,235,0.5);
      transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
      margin-top: 4px;
    }
    .btn-submit:hover {
      background: #4a12c0;
      box-shadow: 0 0 36px rgba(94,23,235,0.75);
      transform: translateY(-1px);
    }
    .btn-submit:active { transform: translateY(0); }

    .signup-hint {
      text-align: center; margin-top: 6px;
      font-size: 12px; color: #a594bd;
    }
    .signup-hint a {
      color: #b388ff; text-decoration: none;
      font-weight: 500; transition: color 0.2s;
    }
    .signup-hint a:hover { color: #d8b4fe; }

    .bottom-images {
      position: fixed; bottom: 0; left: 0;
      width: 100%; pointer-events: none; z-index: 2;
      height: clamp(140px, 22vw, 220px);
    }
    .img-cup {
      position: absolute; bottom: 0;
      left: clamp(-20px, 2vw, 40px);
      width: clamp(300px, 50vw, 550px); height: auto;
    }
    .img-cup img { width: 100%; height: auto; object-fit: contain; display: block; }

    @media (max-width: 480px) {
      .page { padding: 32px 16px 140px; justify-content: flex-start; padding-top: 60px; }
      .brand { margin-bottom: 28px; }
      .form-card { max-width: 100%; }
      .img-cup { width: clamp(200px, 70vw, 320px); }
    }
    @media (min-width: 768px) { .page { padding-bottom: 180px; } }
  </style>
</head>
<body>
  <div class="page">
    <div class="brand">
      <div class="brand-name"><span class="brand-c">C</span>offeePay</div>
    </div>
    <form class="form-card" action="login_proses.php" method="POST">
  
  <div class="input-wrap">
    <span class="input-icon">
      <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
    </span>
    <input type="email" name="email" placeholder="alamat email" autocomplete="email" required />
  </div>

  <div class="input-wrap">
    <span class="input-icon">
      <svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
    </span>
    <input type="password" name="password" placeholder="masukkan password" autocomplete="current-password" required />
  </div>

  <button type="submit" class="btn-submit">Login</button>
  
  <p class="signup-hint">
    Belum memiliki akun? <a href="Register.php">sign up</a>
  </p>
</form>
  </div>

  <div class="bottom-images">
    <div class="img-cup">
      <img src="coffcroissant.png" alt="produk">
    </div>
  </div>

  <script>
    function handleLogin() {
  const email    = document.querySelector('input[type="email"]').value.trim();
  const password = document.querySelector('input[type="password"]').value.trim();

  if (!email || !password) { 
    alert('Email dan password harus diisi!'); 
    return; 
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { 
    alert('Format email tidak valid!'); 
    return; 
  }

  const users = JSON.parse(localStorage.getItem('coffeepay_users') || '[]');
  
  // Mencari user yang email dan password-nya cocok
  const user = users.find(u => u.email.toLowerCase() === email.toLowerCase() && u.password === password);

  if (!user) { 
    alert('Email atau password salah! Belum punya akun? Silakan sign up terlebih dahulu.'); 
    return; 
  }

  // Menyimpan data sesi login saat ini agar bisa dibaca di halaman tujuan
  localStorage.setItem('coffeepay_session', JSON.stringify(user));

  // REDIRECT BERDASARKAN ROLE
  if (user.role === 'admin') {
    alert('Login Berhasil! Selamat datang Admin ' + user.username);
    window.location.href = 'dashboardadmin.php';
  } else {
    alert('Login Berhasil! Selamat datang Kasir ' + user.username);
    window.location.href = 'Kasir.php';
  }
}
  </script>
</body>
</html>
