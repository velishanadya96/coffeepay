<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role'])) {
    header("Location: Login.php");
    exit;
}

$produk_minuman = [];
$produk_makanan = [];
$result = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY nama_produk ASC");
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['kategori'] === 'minuman') $produk_minuman[] = $row;
    else                                 $produk_makanan[] = $row;
}
$produk_json = json_encode(['minuman' => $produk_minuman, 'makanan' => $produk_makanan]);
$kasir_name  = htmlspecialchars($_SESSION['username'] ?? 'Kasir');
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
      --sidebar-active: rgba(255,255,255,0.08);
      --panel-bg: #13091e;
      --card-bg: #1a0d2e;
      --border: rgba(147,51,234,0.15);
      --purple: #6a1b9a;
      --purple-light: #b388ff;
      --purple-btn: #5e17eb;
      --text: #ffffff;
      --text-dim: #a594bd;
      --green: #00e676;
      --red: #ff1744;
    }
    html, body { height: 100%; overflow: hidden; background: var(--bg); }
    body { color: var(--text); font-family: 'Inter', sans-serif; display: flex; height: 100vh; }

    /* SIDEBAR */
    .sidebar {
      width: 200px; flex-shrink: 0;
      background: var(--sidebar-bg);
      border-right: 1px solid var(--border);
      display: flex; flex-direction: column;
      padding: 30px 0 20px; z-index: 10;
    }
    .sidebar-brand { padding: 0 20px 30px; margin-bottom: 10px; }
    .brand-text { font-size: 22px; font-weight: 700; color: var(--text); letter-spacing: -0.03em; }
    .brand-c { font-family: 'Playfair Display', serif; font-style: italic; }
    .nav-item {
      display: flex; align-items: center; gap: 12px;
      padding: 11px 20px; font-size: 13px; font-weight: 500;
      color: var(--text-dim); cursor: pointer;
      transition: all 0.2s; text-decoration: none;
      margin: 2px 10px; border-radius: 10px;
    }
    .nav-item:hover { color: var(--text); background: rgba(255,255,255,0.04); }
    .nav-item.active { color: var(--text); background: var(--sidebar-active); font-weight: 600; }
    .nav-item svg { width: 16px; height: 16px; flex-shrink: 0; }
    .sidebar-bottom { margin-top: auto; }

    /* MAIN */
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }
    .topbar {
      padding: 18px 24px 14px;
      display: flex; align-items: center; justify-content: space-between;
      background: var(--bg); flex-shrink: 0; gap: 16px;
      border-bottom: 1px solid var(--border);
    }
    .topbar h1 { font-size: 26px; font-weight: 700; letter-spacing: -0.02em; }
    .kategori-tabs { display: flex; gap: 6px; }
    .tab-btn {
      background: rgba(255,255,255,0.05); border: 1px solid var(--border);
      color: var(--text-dim); padding: 7px 18px; border-radius: 999px;
      font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s;
    }
    .tab-btn.active { background: var(--purple-btn); border-color: var(--purple-btn); color: #fff; }

    /* CONTENT GRID */
    .content {
      flex: 1; display: grid;
      grid-template-columns: 1fr 340px;
      overflow: hidden; gap: 0;
    }

    /* PRODUK PANEL */
    .produk-panel {
      display: flex; flex-direction: column;
      overflow: hidden; padding: 20px 20px 0;
      border-right: 1px solid var(--border);
    }
    .produk-header { margin-bottom: 14px; }
    .kategori-label { font-size: 11px; font-weight: 700; color: var(--purple-light); letter-spacing: 0.1em; }
    .produk-scroll { flex: 1; overflow-y: auto; padding-bottom: 20px; }
    .produk-scroll::-webkit-scrollbar { width: 4px; }
    .produk-scroll::-webkit-scrollbar-thumb { background: rgba(147,51,234,0.3); border-radius: 4px; }
    .produk-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px,1fr)); gap: 14px; }
    .produk-card {
      background: var(--card-bg); border: 1px solid var(--border);
      border-radius: 14px; overflow: hidden; cursor: pointer;
      transition: all 0.2s; display: flex; flex-direction: column;
    }
    .produk-card:hover { border-color: var(--purple-btn); transform: translateY(-2px); box-shadow: 0 4px 20px rgba(94,23,235,0.2); }
    .produk-img-wrap {
      width: 100%; aspect-ratio: 1;
      background: rgba(255,255,255,0.03);
      display: flex; flex-direction: column;
      align-items: center; justify-content: center; gap: 8px;
      color: var(--text-dim); font-size: 11px; overflow: hidden;
    }
    .produk-img-wrap svg { width: 28px; height: 28px; opacity: 0.25; }
    .produk-info { padding: 10px; }
    .produk-info .nama { font-size: 12px; font-weight: 600; line-height: 1.3; margin-bottom: 3px; }
    .produk-info .harga { font-size: 11px; color: var(--purple-light); font-weight: 500; }

    /* PESANAN PANEL */
    .pesanan-panel {
      display: flex; flex-direction: column;
      overflow: hidden; background: var(--panel-bg);
    }
    .pesanan-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 18px 20px 14px; flex-shrink: 0;
      border-bottom: 1px solid var(--border);
    }
    .pesanan-header-left { display: flex; align-items: center; gap: 8px; }
    .pesanan-header h2 { font-size: 16px; font-weight: 700; }
    .pesanan-count {
      background: var(--purple-btn); color: #fff;
      font-size: 11px; font-weight: 700;
      padding: 2px 7px; border-radius: 999px; min-width: 22px; text-align: center;
    }
    .btn-clear { background: none; border: none; color: var(--text-dim); font-size: 12px; cursor: pointer; transition: color 0.2s; }
    .btn-clear:hover { color: var(--red); }

    /* LIST */
    .pesanan-list { flex: 1; overflow-y: auto; padding: 14px 20px; }
    .pesanan-list::-webkit-scrollbar { width: 4px; }
    .pesanan-list::-webkit-scrollbar-thumb { background: rgba(147,51,234,0.3); border-radius: 4px; }
    .pesanan-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; color: var(--text-dim); gap: 10px; opacity: 0.4; }
    .pesanan-empty svg { width: 36px; height: 36px; }
    .pesanan-empty p { font-size: 12px; text-align: center; line-height: 1.6; }
    .order-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: 9px 0; border-bottom: 1px solid rgba(147,51,234,0.1); gap: 8px;
    }
    .order-row:last-child { border-bottom: none; }
    .order-name { font-size: 13px; font-weight: 500; flex: 1; }
    .qty-ctrl { display: flex; align-items: center; gap: 6px; }
    .qty-btn {
      background: rgba(255,255,255,0.07); border: none; color: #fff;
      width: 22px; height: 22px; border-radius: 6px; cursor: pointer;
      font-size: 15px; display: flex; align-items: center; justify-content: center;
      transition: background 0.15s;
    }
    .qty-btn:hover { background: rgba(94,23,235,0.4); }
    .qty-val { font-size: 13px; min-width: 18px; text-align: center; }
    .order-price { font-size: 12px; color: var(--text-dim); min-width: 70px; text-align: right; }

    /* INPUTS */
    .pesanan-inputs { padding: 0 20px 10px; display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; }
    .input-field {
      width: 100%; background: rgba(255,255,255,0.04);
      border: 1.5px solid rgba(94,23,235,0.25); border-radius: 10px;
      padding: 10px 14px; color: var(--text); font-family: 'Inter', sans-serif;
      font-size: 13px; outline: none; transition: border-color 0.2s;
    }
    .input-field::placeholder { color: rgba(165,148,189,0.5); }
    .input-field:focus { border-color: var(--purple-btn); }

    /* RINGKASAN */
    .ringkasan { padding: 10px 20px 16px; flex-shrink: 0; border-top: 1px solid var(--border); }
    .ringkasan-row {
      display: flex; justify-content: space-between;
      font-size: 13px; color: var(--text-dim); padding: 4px 0;
    }
    .ringkasan-row.total { font-size: 15px; font-weight: 700; color: var(--purple-light); padding: 10px 0 6px; border-top: 1px solid var(--border); margin-top: 4px; }
    .tunai-wrap { margin: 8px 0 4px; }
    .tunai-wrap label { font-size: 12px; color: var(--text-dim); margin-bottom: 4px; display: block; }
    .kembalian-row { display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; color: var(--green); padding: 4px 0 8px; }
    .metode-label { font-size: 11px; color: var(--text-dim); text-align: center; margin: 6px 0 8px; text-transform: uppercase; letter-spacing: 0.08em; }
    .metode-row { display: flex; gap: 8px; margin-bottom: 10px; }
    .btn-metode {
      flex: 1; background: rgba(255,255,255,0.05);
      border: 1px solid var(--border); color: var(--text-dim);
      padding: 9px 6px; border-radius: 10px; font-size: 12px; font-weight: 600;
      cursor: pointer; display: flex; align-items: center; justify-content: center;
      gap: 6px; transition: all 0.2s; letter-spacing: 0.05em;
    }
    .btn-metode svg { width: 14px; height: 14px; }
    .btn-metode.active { background: var(--purple-btn); border-color: var(--purple-btn); color: #fff; }
    .btn-bayar {
      width: 100%; background: var(--purple-btn); border: none; color: #fff;
      padding: 13px; border-radius: 12px; font-size: 14px; font-weight: 700;
      letter-spacing: 0.1em; cursor: pointer; transition: all 0.2s;
    }
    .btn-bayar:hover:not(:disabled) { background: #4a12c0; box-shadow: 0 0 20px rgba(94,23,235,0.4); }
    .btn-bayar:disabled { opacity: 0.3; cursor: not-allowed; }

    /* MODAL SUKSES */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.75); z-index: 100;
      align-items: center; justify-content: center;
    }
    .modal-overlay.show { display: flex; }
    .modal-box {
      background: #1a0d2e; border: 1px solid rgba(94,23,235,0.4);
      border-radius: 20px; padding: 36px 32px; max-width: 340px; width: 90%;
      text-align: center; display: flex; flex-direction: column; gap: 14px;
    }
    .modal-icon { font-size: 48px; }
    .modal-title { font-size: 20px; font-weight: 700; }
    .modal-sub { font-size: 13px; color: var(--text-dim); line-height: 1.6; }
    .modal-kembalian { font-size: 22px; font-weight: 700; color: var(--green); }
    .modal-kode { font-size: 11px; color: var(--purple-light); letter-spacing: 0.08em; }
    .btn-modal-ok {
      background: var(--purple-btn); border: none; color: #fff;
      padding: 12px; border-radius: 12px; font-size: 14px; font-weight: 700;
      cursor: pointer; width: 100%; margin-top: 6px;
    }

    @media (max-width: 1024px) { .produk-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .content { grid-template-columns: 1fr; }
      .pesanan-panel { display: none; }
    }
  </style>
</head>
<body>

  <!-- MODAL SUKSES -->
  <div class="modal-overlay" id="modalSukses">
    <div class="modal-box">
      <div class="modal-icon">✅</div>
      <div class="modal-title">Pembayaran Berhasil!</div>
      <div class="modal-kode" id="modalKode"></div>
      <div class="modal-sub" id="modalDetail"></div>
      <div class="modal-kembalian" id="modalKembalian"></div>
      <button class="btn-modal-ok" onclick="tutupModal()">Transaksi Baru</button>
    </div>
  </div>

  <aside class="sidebar">
    <div class="sidebar-brand">
      <span class="brand-text"><span class="brand-c">C</span>offeePay</span>
    </div>
    <nav>
      <a class="nav-item active" href="Kasir.php">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z"/></svg>
        Kasir
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
        <button class="tab-btn active" onclick="gantiKategori('minuman',this)">Minuman</button>
        <button class="tab-btn" onclick="gantiKategori('makanan',this)">Makanan</button>
      </div>
    </div>

    <div class="content">
      <!-- PRODUK -->
      <div class="produk-panel">
        <div class="produk-header">
          <div class="kategori-label" id="kategoriLabel">COFFEE</div>
        </div>
        <div class="produk-scroll">
          <div class="produk-grid" id="produkGrid"></div>
        </div>
      </div>

      <!-- PESANAN -->
      <div class="pesanan-panel" id="pesananPanel">
        <div class="pesanan-header">
          <div class="pesanan-header-left">
            <h2>Pesanan</h2>
            <span class="pesanan-count" id="pesananCount">0</span>
          </div>
          <button class="btn-clear" id="btnClear">Hapus Semua</button>
        </div>

        <div class="pesanan-list" id="pesananList">
          <div class="pesanan-empty">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <p>Belum ada pesanan.<br/>Klik produk untuk menambahkan.</p>
          </div>
        </div>

        <div class="pesanan-inputs">
          <input type="text" class="input-field" id="inputCatatan" placeholder="Catatan untuk pesanan ...">
          <input type="text" class="input-field" id="inputNama" placeholder="Atas nama ...">
        </div>

        <div class="ringkasan">
          <div class="ringkasan-row"><span>Subtotal</span><span id="valSubtotal">Rp 0</span></div>
          <div class="ringkasan-row"><span>Diskon</span><span>0</span></div>
          <div class="ringkasan-row"><span>Pajak (10%)</span><span id="valPajak">Rp 0</span></div>
          <div class="ringkasan-row total"><span>TOTAL</span><span id="valTotal">Rp 0</span></div>

          <div class="tunai-wrap">
            <label>Uang Diberikan</label>
            <input type="number" class="input-field" id="inputUang" placeholder="0" min="0" oninput="hitungKembalian()">
          </div>
          <div class="kembalian-row"><span>Kembalian</span><span id="valKembalian">0</span></div>

          <div class="metode-label">Metode Pembayaran</div>
          <div class="metode-row">
            <button class="btn-metode active" id="btnTunai" onclick="pilihMetode('tunai')">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
              TUNAI
            </button>
            <button class="btn-metode" id="btnQris" onclick="pilihMetode('qris')">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
              QRIS
            </button>
          </div>

          <button class="btn-bayar" id="btnBayar" disabled onclick="prosesPayment()">BAYAR</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const PRODUK_DB    = <?= $produk_json ?>;
    const KASIR_NAME   = "<?= $kasir_name ?>";
    let kategoriAktif  = 'minuman';
    let pesanan        = [];
    let metodeAktif    = 'tunai';

    // ── PRODUK ──────────────────────────────────────────────
    function renderProduk() {
      const grid   = document.getElementById('produkGrid');
      const label  = document.getElementById('kategoriLabel');
      const daftar = PRODUK_DB[kategoriAktif] || [];
      label.textContent = kategoriAktif === 'minuman' ? 'COFFEE' : 'MAKANAN';
      grid.innerHTML = '';

      if (daftar.length === 0) {
        grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:#a594bd;opacity:.4;font-size:13px;">Belum ada produk di kategori ini.</div>`;
        return;
      }
      daftar.forEach(p => {
        const card = document.createElement('div');
        card.className = 'produk-card';
        const fotoSrc = (p.foto && p.foto !== 'default.png') ? `uploads/${p.foto}` : null;
        card.innerHTML = `
          <div class="produk-img-wrap">
            ${fotoSrc ? `<img src="${fotoSrc}" style="width:100%;height:100%;object-fit:cover;">` : `<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`}
          </div>
          <div class="produk-info">
            <div class="nama">${p.nama_produk}</div>
            <div class="harga">Rp ${parseInt(p.harga).toLocaleString('id-ID')}</div>
          </div>`;
        card.addEventListener('click', () => tambahItem(p));
        grid.appendChild(card);
      });
    }

    function gantiKategori(kat, btn) {
      kategoriAktif = kat;
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      renderProduk();
    }

    // ── PESANAN ──────────────────────────────────────────────
    function tambahItem(p) {
      const ex = pesanan.find(x => x.id == p.id);
      if (ex) ex.qty++;
      else pesanan.push({ id: p.id, nama_produk: p.nama_produk, harga: parseInt(p.harga), qty: 1 });
      renderPesanan();
    }

    function ubahQty(i, d) {
      pesanan[i].qty += d;
      if (pesanan[i].qty <= 0) pesanan.splice(i, 1);
      renderPesanan();
    }

    function renderPesanan() {
      const list  = document.getElementById('pesananList');
      const count = document.getElementById('pesananCount');
      count.textContent = pesanan.reduce((s,p) => s + p.qty, 0);

      if (pesanan.length === 0) {
        list.innerHTML = `<div class="pesanan-empty"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"/></svg><p>Belum ada pesanan.<br/>Klik produk untuk menambahkan.</p></div>`;
        updateTotal();
        return;
      }
      list.innerHTML = '';
      pesanan.forEach((p, i) => {
        const row = document.createElement('div');
        row.className = 'order-row';
        row.innerHTML = `
          <span class="order-name">${p.nama_produk}</span>
          <div class="qty-ctrl">
            <button class="qty-btn" onclick="ubahQty(${i},-1)">−</button>
            <span class="qty-val">${p.qty}</span>
            <button class="qty-btn" onclick="ubahQty(${i},1)">+</button>
          </div>
          <span class="order-price">Rp ${(p.harga * p.qty).toLocaleString('id-ID')}</span>`;
        list.appendChild(row);
      });
      updateTotal();
    }

    function updateTotal() {
      const subtotal = pesanan.reduce((s,p) => s + p.harga * p.qty, 0);
      const pajak    = Math.round(subtotal * 0.1);
      const total    = subtotal + pajak;

      document.getElementById('valSubtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
      document.getElementById('valPajak').textContent    = 'Rp ' + pajak.toLocaleString('id-ID');
      document.getElementById('valTotal').textContent    = 'Rp ' + total.toLocaleString('id-ID');

      document.getElementById('btnBayar').disabled = (pesanan.length === 0);
      hitungKembalian();
    }

    function hitungKembalian() {
      const total = pesanan.reduce((s,p) => s + p.harga * p.qty, 0);
      const pajak = Math.round(total * 0.1);
      const grand = total + pajak;
      const uang  = parseInt(document.getElementById('inputUang').value) || 0;
      const kemb  = uang - grand;

      const el = document.getElementById('valKembalian');
      if (metodeAktif === 'qris') {
        el.textContent = '—';
        el.style.color = 'var(--text-dim)';
      } else {
        el.textContent = kemb >= 0 ? 'Rp ' + kemb.toLocaleString('id-ID') : '—';
        el.style.color = kemb >= 0 ? 'var(--green)' : 'var(--red)';
      }
    }

    function pilihMetode(m) {
      metodeAktif = m;
      document.getElementById('btnTunai').classList.toggle('active', m === 'tunai');
      document.getElementById('btnQris').classList.toggle('active',  m === 'qris');
      const uangWrap = document.getElementById('inputUang').closest('.tunai-wrap');
      uangWrap.style.opacity = m === 'qris' ? '0.35' : '1';
      document.getElementById('inputUang').disabled = (m === 'qris');
      hitungKembalian();
    }

    // ── BAYAR ────────────────────────────────────────────────
    async function prosesPayment() {
      if (pesanan.length === 0) return;

      const subtotal = pesanan.reduce((s,p) => s + p.harga * p.qty, 0);
      const pajak    = Math.round(subtotal * 0.1);
      const total    = subtotal + pajak;
      const uang     = parseInt(document.getElementById('inputUang').value) || 0;

      if (metodeAktif === 'tunai' && uang < total) {
        alert('Uang yang diberikan kurang!'); return;
      }

      const payload = {
        pesanan:        pesanan,
        catatan:        document.getElementById('inputCatatan').value,
        nama_pelanggan: document.getElementById('inputNama').value,
        subtotal:       subtotal,
        pajak:          pajak,
        total:          total,
        uang_diberikan: metodeAktif === 'qris' ? total : uang,
        kembalian:      metodeAktif === 'qris' ? 0 : (uang - total),
        metode:         metodeAktif,
        kasir:          KASIR_NAME,
      };

      document.getElementById('btnBayar').disabled = true;
      document.getElementById('btnBayar').textContent = 'Memproses...';

      try {
        const res  = await fetch('transaksi_proses.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
          tampilModal(data.kode, total, payload.kembalian, metodeAktif);
        } else {
          alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
          document.getElementById('btnBayar').disabled = false;
          document.getElementById('btnBayar').textContent = 'BAYAR';
        }
      } catch (e) {
        alert('Koneksi gagal. Coba lagi.');
        document.getElementById('btnBayar').disabled = false;
        document.getElementById('btnBayar').textContent = 'BAYAR';
      }
    }

    function tampilModal(kode, total, kembalian, metode) {
      document.getElementById('modalKode').textContent   = kode;
      document.getElementById('modalDetail').textContent =
        'Total Rp ' + total.toLocaleString('id-ID') + ' via ' + metode.toUpperCase();
      document.getElementById('modalKembalian').textContent =
        metode === 'tunai' ? 'Kembalian: Rp ' + kembalian.toLocaleString('id-ID') : '✓ Pembayaran QRIS';
      document.getElementById('modalSukses').classList.add('show');
    }

    function tutupModal() {
      document.getElementById('modalSukses').classList.remove('show');
      pesanan = [];
      document.getElementById('inputCatatan').value = '';
      document.getElementById('inputNama').value    = '';
      document.getElementById('inputUang').value    = '';
      renderPesanan();
      document.getElementById('btnBayar').textContent = 'BAYAR';
    }

    document.getElementById('btnClear').addEventListener('click', () => {
      if (pesanan.length > 0 && confirm('Hapus semua pesanan?')) { pesanan = []; renderPesanan(); }
    });

    renderProduk();
  </script>
</body>
</html>