<?php
include 'koneksi.php';

// Hanya admin yang boleh akses
if (!isset($_COOKIE['role']) || $_COOKIE['role'] !== 'admin') {
    header("Location: /api/Login.php");
    exit;
}

// AMBIL DATA PRODUK DARI DATABASE (Perbaikan agar tidak error)
$query = "SELECT * FROM produk ORDER BY id DESC"; // Sesuaikan 'produk' dengan nama tabel Anda
$produk_list = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CoffeePay – Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght=1,400&family=Inter:wght=300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ─── PALET WARNA DISAMAKAN PERSIS DENGAN KASIR ─── */
    :root {
      --bg: #000000;
      --sidebar-bg: linear-gradient(180deg, #2b1055 0%, #120524 100%);
      --sidebar-active: rgba(255, 255, 255, 0.08);
      --panel-bg: #13091e;
      --card-bg: #1a0028;
      --produk-bg: #120018;
      --border: rgba(147, 51, 234, 0.15);
      --purple: #7c3aed;
      --purple-light: #b388ff;
      --purple-dim: rgba(124,58,237,0.12);
      --text: #ffffff;
      --text-dim: #a594bd;
      --green: #22c55e;
      --red: #ef4444;
      --input-bg: #110016; 
    }

    html, body { height: 100%; overflow: hidden; background: var(--bg); }

    body {
      color: var(--text);
      font-family: 'Inter', sans-serif;
      display: flex;
      height: 100vh;
    }

    /* ─── SIDEBAR (SAMA PERSIS DENGAN KASIR) ─── */
    .sidebar {
      width: 240px;
      flex-shrink: 0;
      background: var(--sidebar-bg);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      padding: 30px 0 20px;
      z-index: 10;
      transition: transform 0.3s;
    }
    .sidebar-brand {
      padding: 0 24px 30px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .sidebar-brand .brand-text {
      font-size: 26px; font-weight: 700;
      color: var(--text); letter-spacing: -0.03em;
    }
    .sidebar-brand .brand-c {
      font-family: 'Playfair Display', serif;
      font-style: italic;
    }
    .nav-item {
      display: flex; align-items: center; gap: 14px;
      padding: 12px 24px;
      font-size: 14px; font-weight: 500;
      color: var(--text-dim); cursor: pointer;
      transition: all 0.2s; text-decoration: none;
      margin: 4px 12px;
      border-radius: 12px;
    }
    .nav-item:hover { color: var(--text); background: rgba(255,255,255,0.04); }
    .nav-item.active {
      color: var(--text);
      background: var(--sidebar-active);
      font-weight: 600;
    }
    .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.7; }
    .nav-item.active svg { opacity: 1; color: var(--purple-light); }
    
    /* Tombol Khususs Akses Antarmuka Kasir Diubah Agar Serasi */
    .kasir-link-wrap {
      padding: 0 24px;
      margin: 15px 0;
    }
    .btn-kasir-link {
      display: block;
      text-align: center;
      background: rgba(179, 136, 255, 0.1);
      border: 1px solid var(--purple-light);
      color: var(--purple-light);
      padding: 10px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      letter-spacing: 0.02em;
      transition: all 0.2s;
    }
    .btn-kasir-link:hover {
      background: var(--purple-light);
      color: #120524;
      box-shadow: 0 0 14px rgba(179, 136, 255, 0.3);
    }

    .sidebar-bottom { margin-top: auto; }

    /* Mobile sidebar overlay */
    .sidebar-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.6); z-index: 9;
    }
    .sidebar-overlay.show { display: block; }

    /* ─── MAIN WORKSPACE ─── */
    .main {
      flex: 1; display: flex;
      flex-direction: column;
      overflow: hidden; min-width: 0;
    }

    /* ─── TOPBAR ─── */
    .topbar {
      padding: 24px 30px 15px;
      display: flex; align-items: center;
      gap: 20px; flex-shrink: 0;
      background: var(--bg);
    }
    .btn-menu {
      display: none;
      background: transparent;
      border: 1px solid var(--border);
      color: var(--text);
      border-radius: 8px; padding: 6px 10px;
      cursor: pointer; flex-shrink: 0;
    }
    .btn-menu svg { width: 18px; height: 18px; display: block; }
    .topbar h1 { font-size: 36px; font-weight: 700; letter-spacing: -0.02em; }

    /* ─── WORKSPACE CONTENT ─── */
    .content-body {
      flex: 1;
      padding: 0 30px 24px;
      overflow-y: auto;
      background: var(--bg);
      display: flex;
      flex-direction: column;
      gap: 24px;
    }
    .content-body::-webkit-scrollbar { width: 6px; }
    .content-body::-webkit-scrollbar-thumb { background: rgba(147,51,234,0.3); border-radius: 4px; }

    /* Atas: Form Input & Header Kanan */
    .admin-grid-top {
      display: grid;
      grid-template-columns: 420px 1fr;
      gap: 40px;
      align-items: start;
    }

    /* ─── CONTAINER FORM INPUT ─── */
    .form-container {
      background: var(--panel-bg);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 20px;
      display: flex;
      gap: 16px;
      position: relative;
    }

    /* Upload Foto Box */
    .photo-upload-box {
      width: 120px;
      height: 120px;
      background: rgba(255, 255, 255, 0.02);
      border: 1px dashed rgba(255,255,255,0.1);
      border-radius: 12px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 6px;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: all 0.2s;
    }
    .photo-upload-box:hover {
      border-color: var(--purple-light);
      background: rgba(255,255,255,0.05);
    }
    .photo-upload-box svg {
      width: 28px; height: 28px;
      color: var(--text-dim);
      opacity: 0.4;
    }
    .photo-upload-box span {
      font-size: 10px;
      color: var(--text-dim);
      text-align: center;
      opacity: 0.5;
    }
    .photo-upload-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      position: absolute;
      inset: 0;
    }
    .photo-upload-box input[type="file"] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
    }

    /* Inputs Wrapper Kanan */
    .form-inputs {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .form-group {
      display: flex;
      flex-direction: column;
    }
    
    /* Input Field Oval Kapsul Sesuai Kasir */
    .form-control {
      width: 100%;
      background: rgba(255,255,255,0.03);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 10px 14px;
      color: var(--text);
      font-family: 'Inter', sans-serif;
      font-size: 12px;
      outline: none;
      transition: all 0.2s;
    }
    .form-control:focus {
      border-color: var(--purple-light);
    }
    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.25);
    }
    
    select.form-control {
      appearance: none;
      background-image: url("data:image/svg+xml;utf8,<svg fill='%23a594bd' height='14' viewBox='0 0 24 24' width='14' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
      background-repeat: no-repeat;
      background-position: right 14px center;
      padding-right: 30px;
    }
    select.form-control option {
      background: #120524;
      color: var(--text);
    }

    /* Tombol Aksi Tambah Produk */
    .btn-submit-container {
      margin-top: 2px;
      display: flex;
      justify-content: flex-end;
    }
    .btn-submit {
      background: rgba(94, 23, 235, 1);
      border: none;
      color: #fff;
      padding: 10px 20px;
      border-radius: 10px;
      font-size: 12px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-submit:hover {
      background: #6d28d9;
      transform: translateY(-1px);
    }
    .btn-submit svg {
      width: 14px;
      height: 14px;
    }

    /* ─── KANAN: PANEL PRODUK TERSEDIA ─── */
    .display-header-panel {
      padding-top: 4px;
    }
    .panel-title {
      font-size: 20px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 14px;
    }
    .search-wrapper {
      position: relative;
      max-width: 300px;
    }
    
    /* Input Cari Oval Kapsul */
    .search-input {
      width: 100%;
      background: rgba(255,255,255,0.03);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 10px 38px 10px 14px;
      color: var(--text);
      font-family: 'Inter', sans-serif;
      font-size: 12px;
      outline: none;
      transition: border-color 0.2s;
    }
    .search-input:focus {
      border-color: var(--purple-light);
    }
    .search-input::placeholder {
      color: rgba(255, 255, 255, 0.25);
    }
    .search-wrapper svg {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      width: 16px;
      height: 16px;
      color: var(--text-dim);
      opacity: 0.6;
      pointer-events: none;
    }

    /* Garis pembatas horizontal */
    .section-divider {
      border: none;
      border-top: 1px solid var(--border);
    }

    /* ─── EMPTY STATE VIEW ─── */
    .empty-state-view {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 80px 20px;
      color: var(--text-dim);
      opacity: 0.4;
      text-align: center;
      gap: 10px;
    }
    .empty-state-view svg {
      width: 48px;
      height: 48px;
    }
    .empty-state-view p {
      font-size: 13px;
      letter-spacing: 0.02em;
    }

    /* ─── RESPONSIVE ─── */
    @media (max-width: 992px) {
      .admin-grid-top {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      .search-wrapper {
        max-width: 100%;
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        position: fixed; left: 0; top: 0; bottom: 0;
        transform: translateX(-100%); z-index: 20;
      }
      .sidebar.open { transform: translateX(0); }
      .btn-menu { display: flex; }
      .topbar h1 { font-size: 28px; }
      .topbar { padding: 20px 20px 10px; }
      .content-body { padding: 0 20px 20px; }
      .form-container {
        flex-direction: column;
        align-items: center;
      }
      .photo-upload-box {
        width: 100%;
        height: 140px;
      }
    }
  </style>
</head>
<body>

  <div class="sidebar-overlay" id="sidebarOverlay" onclick="tutupSidebar()"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <span class="brand-text"><span class="brand-c">C</span>offeePay</span>
    </div>
    
    <nav>
      <a class="nav-item active" href="#">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="3" y="3" width="18" height="18" rx="4" />
          <line x1="12" y1="8" x2="12" y2="16"/>
          <line x1="8" y1="12" x2="16" y2="12"/>
        </svg>
        Produk Admin
      </a>
      <a class="nav-item" href="/api/laporankeuangan.php">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
        </svg>
        Laporan Keuangan
      </a>
    </nav>
    
    <div class="kasir-link-wrap">
      <a href="Kasir.php" class="btn-kasir-link">LIHAT DASHBOARD KASIR</a>
    </div>

    <div class="sidebar-bottom">
      <a class="nav-item" href="/api/logout.php">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
        </svg>
        Keluar
      </a>
    </div>
  </aside>

  <div class="main">
    
    <div class="topbar">
      <button class="btn-menu" onclick="bukaSidebar()">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <h1>Produk</h1>
    </div>

    <div class="content-body">
      
      <div class="admin-grid-top">
        
        <form class="form-container" id="formProduk" action="tambah_produk.php" method="POST" enctype="multipart/form-data">
          <div class="photo-upload-box">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <rect x="3" y="3" width="18" height="18" rx="3"/>
              <circle cx="8.5" cy="8.5" r="1.5"/>
              <polyline points="21 15 16 10 5 21"/>
            </svg>
            <span>Klik untuk<br/>upload foto</span>
            <img id="previewFoto" src="" style="display: none;" />
            <input type="file" name="foto" accept="image/*" onchange="previewImage(event)" />
          </div>

          <div class="form-inputs">
            <div class="form-group">
              <input type="text" name="nama_produk" class="form-control" placeholder="Nama produk..." required />
            </div>
            <div class="form-group">
              <input type="number" name="harga" class="form-control" placeholder="Harga (Rp)..." required min="0" />
            </div>
            <div class="form-group">
              <select name="kategori" class="form-control" required>
                <option value="" disabled selected style="color: rgba(255,255,255,0.25);">Kategori</option>
                <option value="minuman">Minuman</option>
                <option value="makanan">Makanan</option>
              </select>
            </div>
            
            <div class="btn-submit-container">
              <button type="submit" name="tambah_produk" class="btn-submit">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Produk
              </button>
            </div>
          </div>
        </form>

        <div class="display-header-panel">
          <h2 class="panel-title">Produk Tersedia</h2>
          <div class="search-wrapper">
            <input type="text" class="search-input" placeholder="Cari Produk..." />
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
          </div>
        </div>

      </div>

      <hr class="section-divider">

      <?php if (!$produk_list || mysqli_num_rows($produk_list) === 0): ?>
      <div class="empty-state-view">
        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
        <p>Belum ada produk yang ditambahkan.</p>
      </div>
      <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 16px;">
        <?php while ($p = mysqli_fetch_assoc($produk_list)): ?>
        <div style="background: var(--card-bg); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; padding: 12px; display: flex; flex-direction: column; gap: 10px;">
          <div style="width: 100%; aspect-ratio: 1; background: rgba(255,255,255,0.03); border-radius: 10px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
            <?php if ($p['foto'] && $p['foto'] !== 'default.png'): ?>
              <img src="uploads/<?= htmlspecialchars($p['foto']) ?>" style="width:100%; height:100%; object-fit:cover;" />
            <?php else: ?>
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width:32px;height:32px;color:#a594bd;opacity:0.3;"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <?php endif; ?>
          </div>
          <div style="text-align: center;">
            <div style="font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 2px;"><?= htmlspecialchars($p['nama_produk']) ?></div>
            <div style="font-size: 12px; color: var(--text-dim);">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
            <div style="font-size: 10px; color: var(--purple-light); margin-top: 2px; text-transform: uppercase;"><?= htmlspecialchars($p['kategori']) ?></div>
          </div>
          <a href="/api/produk_proses.php?hapus=<?= $p['id'] ?>" onclick="return confirm('Hapus produk ini?')" style="display:block; text-align:center; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; padding: 6px; border-radius: 8px; font-size: 11px; font-weight: 600; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.25)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">Hapus</a>
        </div>
        <?php endwhile; ?>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <script>
    function bukaSidebar() {
      document.getElementById('sidebar').classList.add('open');
      document.getElementById('sidebarOverlay').classList.add('show');
    }
    function tutupSidebar() {
      document.getElementById('sidebar').classList.remove('open');
      document.getElementById('sidebarOverlay').classList.remove('show');
    }

    function previewImage(event) {
      const input = event.target;
      const preview = document.getElementById('previewFoto');
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
      }
    }
  </script>
</body>
</html>