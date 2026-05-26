<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CoffeePay – Laporan Keuangan</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght=1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg: #000000;
      --sidebar-bg: linear-gradient(180deg, #2b1055 0%, #120524 100%);
      --sidebar-active: rgba(255, 255, 255, 0.08);
      --panel-bg: #13091e;
      --card-bg: #231534;
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

    /* ─── SIDEBAR (SAMA PERSIS DENGAN KASIR & ADMIN) ─── */
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
      justify-content: space-between; gap: 20px; flex-shrink: 0;
      background: var(--bg);
    }
    .topbar-left { display: flex; align-items: center; gap: 15px; }
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

    .topbar-right { display: flex; align-items: center; gap: 12px; }
    .btn-reset-data {
      background: rgba(255, 23, 68, 0.1);
      border: 1px solid var(--red);
      color: var(--red);
      padding: 8px 16px;
      border-radius: 10px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-reset-data:hover {
      background: var(--red);
      color: #fff;
    }

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

    /* ─── KARTU RINGKASAN DATA (METRICS GRID) ─── */
    .metrics-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
    }
    .metric-card {
      background: var(--panel-bg);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 20px;
      position: relative;
      overflow: hidden;
    }
    .metric-card::before {
      content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
      background: var(--purple-light);
    }
    .metric-card.income::before { background: var(--green); }
    .metric-card.expense::before { background: var(--red); }
    
    .metric-title {
      font-size: 12px;
      font-weight: 500;
      color: var(--text-dim);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 8px;
    }
    .metric-value {
      font-size: 24px;
      font-weight: 700;
      color: var(--text);
      letter-spacing: -0.01em;
    }
    .metric-sub {
      font-size: 11px;
      color: var(--text-dim);
      margin-top: 6px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    /* ─── INTERACTIVE FILTER BAR ─── */
    .filter-panel {
      background: var(--panel-bg);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 16px 20px;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }
    .filter-group-left {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 12px;
    }
    .filter-label {
      font-size: 12px;
      font-weight: 500;
      color: var(--text-dim);
    }
    .select-filter {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid var(--border);
      color: var(--text);
      border-radius: 8px;
      padding: 8px 14px;
      font-size: 12px;
      outline: none;
      cursor: pointer;
    }
    .select-filter option {
      background: #13091e;
      color: var(--text);
    }

    /* ─── TABEL LAPORAN ─── */
    .table-container {
      background: var(--panel-bg);
      border: 1px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
    }
    .report-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      font-size: 13px;
    }
    .report-table th {
      background: rgba(255, 255, 255, 0.02);
      padding: 14px 20px;
      font-weight: 600;
      color: var(--text-dim);
      border-bottom: 1px solid var(--border);
      text-transform: uppercase;
      font-size: 11px;
      letter-spacing: 0.05em;
    }
    .report-table td {
      padding: 14px 20px;
      border-bottom: 1px solid rgba(147, 51, 234, 0.08);
      color: var(--text);
    }
    .report-table tr:last-child td {
      border-bottom: none;
    }
    .report-table tr:hover td {
      background: rgba(255, 255, 255, 0.01);
    }

    /* Badges Tipe */
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }
    .badge.badge-success {
      background: rgba(0, 230, 118, 0.1);
      color: var(--green);
    }
    .badge.badge-danger {
      background: rgba(255, 23, 68, 0.1);
      color: var(--red);
    }

    /* Empty state */
    .table-empty {
      padding: 60px 20px;
      text-align: center;
      color: var(--text-dim);
    }
    .table-empty svg {
      width: 40px; height: 40px; margin-bottom: 12px; opacity: 0.4;
      color: var(--text-dim);
    }

    /* ─── RESPONSIVE ─── */
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
      .filter-panel { flex-direction: column; align-items: stroke; }
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
      <a class="nav-item" href="dashboardadmin.php">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="3" y="3" width="18" height="18" rx="4" />
          <line x1="12" y1="8" x2="12" y2="16"/>
          <line x1="8" y1="12" x2="16" y2="12"/>
        </svg>
        Produk Admin
      </a>
      <a class="nav-item active" href="#">
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
      <a class="nav-item" href="Login.php">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
        </svg>
        Keluar
      </a>
    </div>
  </aside>

  <div class="main">
    
    <div class="topbar">
      <div class="topbar-left">
        <button class="btn-menu" onclick="bukaSidebar()">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <h1>Keuangan</h1>
      </div>
      <div class="topbar-right">
        <button class="btn-reset-data" onclick="resetLaporanData()">Reset Simulasi</button>
      </div>
    </div>

    <div class="content-body">
      
      <div class="metrics-grid">
        <div class="metric-card income">
          <div class="metric-title">Total Pendapatan</div>
          <div class="metric-value" id="valTotalPendapatan">Rp 0</div>
          <div class="metric-sub">Kotor dari riwayat kasir</div>
        </div>
        <div class="metric-card expense">
          <div class="metric-title">Estimasi Pengeluaran</div>
          <div class="metric-value" id="valTotalPengeluaran">Rp 0</div>
          <div class="metric-sub">HPP & Biaya Operasional</div>
        </div>
        <div class="metric-card">
          <div class="metric-title">Pendapatan Bersih</div>
          <div class="metric-value" id="valPendapatanBersih" style="color: var(--purple-light);">Rp 0</div>
          <div class="metric-sub">Net Profit Margin</div>
        </div>
        <div class="metric-card">
          <div class="metric-title">Total Transaksi</div>
          <div class="metric-value" id="valTotalTransaksi">0</div>
          <div class="metric-sub">Pesanan Selesai</div>
        </div>
      </div>

      <div class="filter-panel">
        <div class="filter-group-left">
          <span class="filter-label">Filter Tipe:</span>
          <select class="select-filter" id="filterTipe" onchange="renderLaporan()">
            <option value="semua">Semua Transaksi</option>
            <option value="Pemasukan">Pemasukan (Kasir)</option>
            <option value="Pengeluaran">Pengeluaran (Operasional)</option>
          </select>

          <span class="filter-label">Metode Pembayaran:</span>
          <select class="select-filter" id="filterMetode" onchange="renderLaporan()">
            <option value="semua">Semua Metode</option>
            <option value="tunai">Tunai</option>
            <option value="qris">QRIS</option>
          </select>
        </div>
        <div class="filter-label" id="txtWaktuUpdate">Diperbarui: Live</div>
      </div>

      <div class="table-container">
        <table class="report-table">
          <thead>
            <tr>
              <th>ID / Waktu</th>
              <th>Deskripsi / Produk</th>
              <th>Tipe</th>
              <th>Metode</th>
              <th>Jumlah / Nominal</th>
            </tr>
          </thead>
          <tbody id="laporanTableBody">
            </tbody>
        </table>
        <div class="table-empty" id="emptyState" style="display: none;">
          <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
          </svg>
          <p>Tidak ada data laporan keuangan yang sesuai filter.</p>
        </div>
      </div>

    </div>
  </div>

  <script>
    // Membaca atau memicu database dummy berestetika tinggi jika local storage kosong
    function dapatkanDataLaporan() {
      let data = localStorage.getItem('coffeepay_laporan');
      if (!data) {
        const dummy = [
          { id: "TX-1004", waktu: "Hari ini, 15:32", desc: "2x Kopi Susu Gula Aren, 1x Croissant", tipe: "Pemasukan", metode: "qris", jumlah: 65000 },
          { id: "TX-1003", waktu: "Hari ini, 14:15", desc: "1x Americano Hot", tipe: "Pemasukan", metode: "tunai", jumlah: 22000 },
          { id: "OP-2001", waktu: "Hari ini, 11:00", desc: "Pembelian Biji Kopi Esensial House Blend", tipe: "Pengeluaran", metode: "tunai", jumlah: 120000 },
          { id: "TX-1002", waktu: "Kemarin, 19:40", desc: "3x Cafe Latte, 2x Cinnamon Roll", tipe: "Pemasukan", metode: "qris", jumlah: 145000 },
          { id: "TX-1001", waktu: "Kemarin, 17:05", desc: "1x Matcha Latte Ice", tipe: "Pemasukan", metode: "tunai", jumlah: 28000 },
          { id: "OP-2000", waktu: "Kemarin, 09:00", desc: "Restock Susu UHT Cair (1 Karton)", tipe: "Pengeluaran", metode: "qris", jumlah: 185000 }
        ];
        localStorage.setItem('coffeepay_laporan', JSON.stringify(dummy));
        return dummy;
      }
      return JSON.parse(data);
    }

    function formatRupiah(angka) {
      return "Rp " + angka.toLocaleString('id-ID');
    }

    // Fungsi matematika komputasi kalkulator laporan keuangan utama
    function renderLaporan() {
      const listData = dapatkanDataLaporan();
      const tableBody = document.getElementById('laporanTableBody');
      const emptyState = document.getElementById('emptyState');
      
      const filterTipe = document.getElementById('filterTipe').value;
      const filterMetode = document.getElementById('filterMetode').value;

      tableBody.innerHTML = '';

      let totalPendapatan = 0;
      let totalPengeluaran = 0;
      let jumlahTransaksiSelesai = 0;

      // Filter array data & sekaligus melakukan perhitungan total matematika
      let dataTerfilter = listData.filter(item => {
        if (item.tipe === "Pemasukan") {
          totalPendapatan += item.jumlah;
          jumlahTransaksiSelesai++;
        } else if (item.tipe === "Pengeluaran") {
          totalPengeluaran += item.jumlah;
        }

        const cocokTipe = (filterTipe === 'semua' || item.tipe === filterTipe);
        const cocokMetode = (filterMetode === 'semua' || item.metode === filterMetode);
        return cocokTipe && cocokMetode;
      });

      // Render pembaruan ke kartu metrik
      document.getElementById('valTotalPendapatan').textContent = formatRupiah(totalPendapatan);
      document.getElementById('valTotalPengeluaran').textContent = formatRupiah(totalPengeluaran);
      document.getElementById('valPendapatanBersih').textContent = formatRupiah(totalPendapatan - totalPengeluaran);
      document.getElementById('valTotalTransaksi').textContent = jumlahTransaksiSelesai;

      if (dataTerfilter.length === 0) {
        emptyState.style.display = 'block';
        return;
      }
      emptyState.style.display = 'none';

      // Gambar baris tabel
      dataTerfilter.forEach(item => {
        const tr = document.createElement('tr');
        const badgeClass = item.tipe === 'Pemasukan' ? 'badge-success' : 'badge-danger';
        const metodeText = item.metode.toUpperCase();

        tr.innerHTML = `
          <td style="font-weight: 600; color: var(--purple-light);">${item.id}<br><span style="font-size: 11px; font-weight: 400; color: var(--text-dim);">${item.waktu}</span></td>
          <td>${item.desc}</td>
          <td><span class="badge ${badgeClass}">${item.tipe}</span></td>
          <td style="font-size: 11px; letter-spacing: 0.05em; color: var(--text-dim);">${metodeText}</td>
          <td style="font-weight: 700; text-align: right; color: ${item.tipe === 'Pemasukan' ? 'var(--green)' : 'var(--red)'}">
            ${item.tipe === 'Pemasukan' ? '+' : '-'} ${formatRupiah(item.jumlah)}
          </td>
        `;
        tableBody.appendChild(tr);
      });
    }

    function resetLaporanData() {
      localStorage.removeItem('coffeepay_laporan');
      renderLaporan();
    }

    function bukaSidebar() {
      document.getElementById('sidebar').classList.add('open');
      document.getElementById('sidebarOverlay').classList.add('show');
    }
    function tutupSidebar() {
      document.getElementById('sidebar').classList.remove('open');
      document.getElementById('sidebarOverlay').classList.remove('show');
    }

    window.onload = renderLaporan;
  </script>
</body>
</html>