<?php
session_start();
include 'koneksi.php';

// Proteksi: harus sudah login
if (!isset($_SESSION['role'])) {
    header("Location: Login.php");
    exit;
}

// Ambil semua produk dari database
$produk_minuman = [];
$produk_makanan = [];

$result = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY nama_produk ASC");
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['kategori'] === 'minuman') {
        $produk_minuman[] = $row;
    } else {
        $produk_makanan[] = $row;
    }
}

// Encode ke JSON untuk dipakai oleh JavaScript
$produk_json = json_encode([
    'minuman' => $produk_minuman,
    'makanan' => $produk_makanan,
]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CoffeePay – Kasir</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg: #000000;
      --sidebar-bg: linear-gradient(180deg, #2b1055 0%, #120524 100%);
      --sidebar-active: rgba(255, 255, 255, 0.08);
      --panel-bg: #13091e;
      --card-bg: #231534;
      --produk-bg: #0d0416;
      --border: rgba(147, 51, 234, 0.15);
      --purple: #6a1b9a;
      --purple-light: #b388ff;
      --purple-btn: #5e17eb;
      --text: #ffffff;
      --text-dim: #a594bd;
      --green: #00e676;
      --red: #ff1744;
    }

    html, body { height: 100%; overflow: hidden; background: var(--bg); }

    body {
      color: var(--text);
      font-family: 'Inter', sans-serif;
      display: flex;
      height: 100vh;
    }

    /* ─── SIDEBAR ─── */
    .sidebar {
      width: 240px;
      flex-shrink: 0;
      background: var(--sidebar-bg);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      padding: 30px 0 20px;
      z-index: 10;
    }
    .sidebar-brand {
      padding: 0 24px 30px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .sidebar-brand .logo-icon {
      width: 38px; height: 38px;
      border-radius: 50%;
      background: rgba(255,255,255,0.1);
      display: flex; align-items: center; justify-content: center;
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
    .sidebar-bottom { margin-top: auto; }

    /* Overlay */
    .sidebar-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.6); z-index: 9;
    }

    /* ─── MAIN ─── */
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
    .topbar h1 { font-size: 36px; font-weight: 700; letter-spacing: -0.02em; }

    .kategori-tabs { display: flex; gap: 12px; margin-left: 10px; }
    .tab-btn {
      background: transparent;
      border: none;
      color: var(--text-dim);
      font-family: 'Inter', sans-serif;
      font-size: 14px; font-weight: 600;
      padding: 6px 16px; border-radius: 20px;
      cursor: pointer;
      transition: all 0.2s;
    }
    .tab-btn:hover { color: var(--text); }
    .tab-btn.active { background: var(--purple-btn); color: #fff; }

    .btn-pesanan-mobile { display: none; }

    /* ─── CONTENT ─── */
    .content {
      flex: 1; display: grid;
      grid-template-columns: 1fr 340px;
      overflow: hidden;
      padding: 0 30px 24px;
      gap: 24px;
    }

    /* ─── PRODUK PANEL ─── */
    .produk-panel {
      display: flex; flex-direction: column;
      overflow: hidden;
    }
    .produk-header { padding: 5px 0 12px; flex-shrink: 0; }
    .kategori-label {
      font-size: 12px; font-weight: 700;
      color: var(--text-dim);
      text-transform: uppercase; letter-spacing: 0.05em;
    }

    .produk-scroll {
      flex: 1; overflow-y: auto;
      background: var(--produk-bg);
      border-radius: 16px;
      padding: 20px;
      border: 1px solid var(--border);
    }
    .produk-scroll::-webkit-scrollbar { width: 6px; }
    .produk-scroll::-webkit-scrollbar-thumb { background: rgba(147,51,234,0.3); border-radius: 4px; }

    .produk-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 16px;
      align-content: start;
    }

    /* Card placeholder figma style */
    .produk-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
      padding: 12px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .produk-img-wrap {
      width: 100%;
      aspect-ratio: 1;
      background: rgba(255,255,255,0.02);
      border-radius: 12px;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      gap: 8px; border: 1px dashed rgba(255,255,255,0.1);
    }
    .produk-img-wrap svg { width: 32px; height: 32px; color: var(--text-dim); opacity: 0.3; }
    .produk-img-wrap span { font-size: 11px; color: var(--text-dim); text-align: center; opacity: 0.4; }

    .produk-info { display: flex; flex-direction: column; align-items: center; gap: 4px; text-align: center;}
    .produk-info .nama { font-size: 14px; font-weight: 600; color: var(--text-dim); }
    .produk-info .harga { font-size: 13px; color: var(--text-dim); opacity: 0.6; }

    /* ─── PESANAN PANEL ─── */
    .pesanan-panel {
      display: flex; flex-direction: column;
      background: var(--panel-bg);
      border: 1px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
    }
    .pesanan-header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .pesanan-header-left { display: flex; align-items: center; gap: 8px; }
    .pesanan-header h2 { font-size: 16px; font-weight: 700; }
    .pesanan-count {
      background: var(--purple-btn);
      color: #fff; font-size: 11px; font-weight: 700;
      padding: 2px 8px; border-radius: 999px;
    }
    .btn-clear {
      background: transparent; border: none;
      color: #7a639b; font-size: 12px; font-weight: 500;
      cursor: pointer; transition: color 0.2s;
    }
    .btn-clear:hover { color: var(--red); }
    .btn-tutup-panel { display: none; }

    /* List pesanan */
    .pesanan-list { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; }
    .pesanan-empty {
      margin: auto; display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      gap: 12px; color: var(--text-dim); opacity: 0.5;
    }
    .pesanan-empty svg { width: 40px; height: 40px; }
    .pesanan-empty p { font-size: 12px; text-align: center; line-height: 1.6; }

    /* ─── INPUT TAMBAHAN & RINGKASAN ─── */
    .pesanan-inputs { padding: 0 20px 10px; display: flex; flex-direction: column; gap: 8px; }
    .input-field {
      width: 100%; background: rgba(255,255,255,0.03);
      border: 1px solid var(--border); border-radius: 8px;
      padding: 8px 12px; color: var(--text); font-size: 12px;
      font-family: 'Inter', sans-serif; outline: none;
    }
    .input-field::placeholder { color: rgba(255,255,255,0.25); }

    .ringkasan {
      border-top: 1px solid var(--border);
      padding: 16px 20px 20px;
    }
    .ringkasan-row {
      display: flex; justify-content: space-between;
      font-size: 12px; color: var(--text-dim); margin-bottom: 6px;
    }
    .ringkasan-row.total {
      font-size: 15px; font-weight: 700; color: #a855f7;
      border-top: 1px solid var(--border);
      padding-top: 10px; margin-top: 8px; margin-bottom: 12px;
    }
    
    .tunai-wrap { margin-bottom: 12px; }
    .tunai-wrap label { font-size: 11px; color: var(--text-dim); display: block; margin-bottom: 5px; }
    .tunai-wrap .input-group { display: flex; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
    .tunai-wrap input {
      flex: 1; background: transparent; border: none;
      padding: 8px 12px; color: var(--text); font-family: 'Inter', sans-serif;
      font-size: 13px; text-align: right; outline: none;
    }

    .metode-label { font-size: 10px; text-align: center; color: var(--text-dim); opacity: 0.5; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;}
    .metode-row { display: flex; gap: 8px; margin-bottom: 14px; }
    .btn-metode {
      flex: 1; padding: 10px;
      border-radius: 10px; border: 1px solid var(--border);
      background: transparent; color: var(--text);
      font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 700;
      cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;
      transition: all 0.2s;
    }
    .btn-metode svg { width: 14px; height: 14px; }
    .btn-metode.active { background: var(--purple-btn); border-color: var(--purple-btn); }

    .btn-bayar {
      width: 100%; padding: 12px;
      background: var(--purple-btn); border: none;
      border-radius: 10px; color: #fff;
      font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700;
      cursor: pointer; transition: all 0.2s;
    }
    .btn-bayar:disabled { opacity: 0.3; cursor: not-allowed; }

    /* RESPONSIVE LAYOUT */
    @media (max-width: 1024px) {
      .produk-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .content { grid-template-columns: 1fr; padding: 0 15px 15px; }
      .topbar { padding: 15px 15px 10px; }
      .topbar h1 { font-size: 24px; }
      .pesanan-panel { display: none; }
    }
  </style>
</head>
<body>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <span class="brand-text"><span class="brand-c">C</span>offeePay</span>
    </div>
    <nav>
      <a class="nav-item active" href="#">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z"/></svg>
        Kasir
      </a>
      <a class="nav-item" href="#">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
        Laporan Keuangan
      </a>
    </nav>
    <div class="sidebar-bottom">
      <a class="nav-item" href="logout.php">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        Keluar
      </a>
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <h1>Kasir</h1>
      <div class="kategori-tabs">
        <button class="tab-btn active" onclick="gantiKategori('minuman', this)">Minuman</button>
        <button class="tab-btn" onclick="gantiKategori('makanan', this)">Makanan</button>
      </div>
    </div>

    <div class="content">
      <div class="produk-panel">
        <div class="produk-header">
          <div class="kategori-label" id="kategoriLabel">COFFEE</div>
        </div>
        <div class="produk-scroll">
          <div class="produk-grid" id="produkGrid"></div>
        </div>
      </div>

      <div class="pesanan-panel" id="pesananPanel">
        <div class="pesanan-header">
          <div class="pesanan-header-left">
            <h2>Pesanan</h2>
            <span class="pesanan-count" id="pesananCount">0</span>
          </div>
          <button class="btn-clear">Hapus Semua</button>
        </div>

        <div class="pesanan-list" id="pesananList">
          <div class="pesanan-empty">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <p>Belum ada pesanan.<br/>Klik produk untuk menambahkan.</p>
          </div>
        </div>

        <div class="pesanan-inputs">
          <input type="text" class="input-field" placeholder="Catatan untuk pesanan ...">
          <input type="text" class="input-field" placeholder="Atas nama ...">
        </div>

        <div class="ringkasan">
          <div class="ringkasan-row">
            <span>Subtotal</span><span>Rp 0</span>
          </div>
          <div class="ringkasan-row">
            <span>Diskon</span><span>0</span>
          </div>
          <div class="ringkasan-row">
            <span>Pajak (10%)</span><span>Rp 0</span>
          </div>
          <div class="ringkasan-row total">
            <span>TOTAL</span><span>Rp 0</span>
          </div>

          <div class="tunai-wrap">
            <label>Uang Diberikan</label>
            <div class="input-group">
              <input type="text" placeholder="0" disabled />
            </div>
          </div>

          <div class="ringkasan-row" style="color: var(--green); font-weight: 600;">
            <span>Kembalian</span><span>0</span>
          </div>

          <div class="metode-label">Metode Pembayaran</div>
          <div class="metode-row">
            <button class="btn-metode active">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
              TUNAI
            </button>
            <button class="btn-metode">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
              QRIS
            </button>
          </div>

          <button class="btn-bayar" disabled>BAYAR</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Data produk dari database (di-inject oleh PHP)
    const semuaProduk = <?= $produk_json ?>;
    let kategoriAktif = 'minuman';

    function renderProduk() {
      const grid = document.getElementById('produkGrid');
      const label = document.getElementById('kategoriLabel');
      const daftar = semuaProduk[kategoriAktif] || [];

      label.textContent = kategoriAktif === 'minuman' ? 'COFFEE' : 'MAKANAN';
      grid.innerHTML = '';

      if (daftar.length === 0) {
        grid.innerHTML = `
          <div style="grid-column: 1/-1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 60px 20px; color: #a594bd; opacity: 0.4; gap: 10px; text-align:center;">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width:48px;height:48px;"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <p style="font-size:13px;">Belum ada produk di kategori ini.</p>
          </div>`;
        return;
      }

      daftar.forEach(p => {
        const card = document.createElement('div');
        card.className = 'produk-card';
        card.style.cursor = 'pointer';
        
        const fotoSrc = (p.foto && p.foto !== 'default.png')
          ? `uploads/${p.foto}`
          : null;

        card.innerHTML = `
          <div class="produk-img-wrap">
            ${fotoSrc
              ? `<img src="${fotoSrc}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" />`
              : `<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="1" y="1" width="18" height="18" rx="1"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span>${p.nama_produk}</span>`
            }
          </div>
          <div class="produk-info">
            <div class="nama">${p.nama_produk}</div>
            <div class="harga">Rp ${parseInt(p.harga).toLocaleString('id-ID')}</div>
          </div>`;

        card.addEventListener('click', () => tambahPesanan(p));
        grid.appendChild(card);
      });
    }

    function gantiKategori(kat, btn) {
      kategoriAktif = kat;
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      renderProduk();
    }

    // ─── PESANAN ───
    let pesanan = [];

    function tambahPesanan(produk) {
      const existing = pesanan.find(p => p.id === produk.id);
      if (existing) {
        existing.qty++;
      } else {
        pesanan.push({ ...produk, qty: 1 });
      }
      renderPesanan();
    }

    function renderPesanan() {
      const list = document.getElementById('pesananList');
      const count = document.getElementById('pesananCount');
      count.textContent = pesanan.reduce((s, p) => s + p.qty, 0);

      if (pesanan.length === 0) {
        list.innerHTML = `
          <div class="pesanan-empty">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <p>Belum ada pesanan.<br/>Klik produk untuk menambahkan.</p>
          </div>`;
        updateTotal();
        return;
      }

      list.innerHTML = '';
      pesanan.forEach((p, i) => {
        const row = document.createElement('div');
        row.style.cssText = 'display:flex; justify-content:space-between; align-items:center; padding: 8px 0; border-bottom: 1px solid rgba(147,51,234,0.1); font-size:13px;';
        row.innerHTML = `
          <div style="flex:1; color:#fff; font-weight:500;">${p.nama_produk}</div>
          <div style="display:flex; align-items:center; gap:8px; color:#a594bd;">
            <button onclick="ubahQty(${i}, -1)" style="background:rgba(255,255,255,0.07); border:none; color:#fff; width:22px; height:22px; border-radius:6px; cursor:pointer; font-size:14px;">−</button>
            <span style="min-width:20px; text-align:center; color:#fff;">${p.qty}</span>
            <button onclick="ubahQty(${i}, 1)" style="background:rgba(255,255,255,0.07); border:none; color:#fff; width:22px; height:22px; border-radius:6px; cursor:pointer; font-size:14px;">+</button>
            <span style="min-width:70px; text-align:right;">Rp ${(p.harga * p.qty).toLocaleString('id-ID')}</span>
          </div>`;
        list.appendChild(row);
      });
      updateTotal();
    }

    function ubahQty(i, delta) {
      pesanan[i].qty += delta;
      if (pesanan[i].qty <= 0) pesanan.splice(i, 1);
      renderPesanan();
    }

    function updateTotal() {
      const subtotal = pesanan.reduce((s, p) => s + (p.harga * p.qty), 0);
      const pajak = Math.round(subtotal * 0.1);
      const total = subtotal + pajak;

      document.querySelectorAll('.ringkasan-row')[0].querySelector('span:last-child').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
      document.querySelectorAll('.ringkasan-row')[2].querySelector('span:last-child').textContent = 'Rp ' + pajak.toLocaleString('id-ID');
      document.querySelector('.ringkasan-row.total span:last-child').textContent = 'Rp ' + total.toLocaleString('id-ID');

      const bayarBtn = document.querySelector('.btn-bayar');
      bayarBtn.disabled = (pesanan.length === 0);
    }

    document.querySelector('.btn-clear').addEventListener('click', () => {
      if (pesanan.length > 0 && confirm('Hapus semua pesanan?')) {
        pesanan = [];
        renderPesanan();
      }
    });

    // Inisialisasi awal
    renderProduk();
  </script>
</body>
</html>