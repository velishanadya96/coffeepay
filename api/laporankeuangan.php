<?php
include 'koneksi.php';

if (!isset($_COOKIE['role']) || $_COOKIE['role'] !== 'admin') {
    header("Location: /api/Login.php");
    exit;
}

// ── FILTER ──────────────────────────────────────────────────
$filter_periode = $_GET['periode'] ?? 'hari_ini';

$where = "";
switch ($filter_periode) {
    case 'hari_ini':   $where = "DATE(created_at) = CURDATE()"; break;
    case 'minggu_ini': $where = "YEARWEEK(created_at,1) = YEARWEEK(CURDATE(),1)"; break;
    case 'bulan_ini':  $where = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"; break;
    case 'semua':      $where = "1"; break;
    default:           $where = "DATE(created_at) = CURDATE()";
}

// ── METRIK RINGKASAN ───────────────────────────────────────
$q_total = mysqli_query($koneksi, "SELECT SUM(total) as total_pemasukan, COUNT(*) as jml_transaksi FROM transaksi WHERE $where");
$row_total = mysqli_fetch_assoc($q_total);
$total_pemasukan  = (int)($row_total['total_pemasukan'] ?? 0);
$jml_transaksi    = (int)($row_total['jml_transaksi']  ?? 0);

$q_produk = mysqli_query($koneksi, "SELECT SUM(td.qty) as total_item FROM transaksi t JOIN transaksi_detail td ON t.id = td.transaksi_id WHERE $where");
$row_produk = mysqli_fetch_assoc($q_produk);
$total_produk_terjual = (int)($row_produk['total_item'] ?? 0);

// ── DATA GRAFIK (7 hari terakhir / 4 minggu / 12 bulan) ───
$grafik_data  = [];
$grafik_label = [];
if ($filter_periode === 'hari_ini') {
    // Per jam hari ini
    for ($h = 8; $h <= 22; $h++) {
        $q = mysqli_query($koneksi, "SELECT SUM(total) as jml FROM transaksi WHERE DATE(created_at)=CURDATE() AND HOUR(created_at)=$h");
        $r = mysqli_fetch_assoc($q);
        $grafik_label[] = $h . ':00';
        $grafik_data[]  = (int)($r['jml'] ?? 0);
    }
} else {
    // Per 7 hari terakhir
    for ($d = 6; $d >= 0; $d--) {
        $q = mysqli_query($koneksi, "SELECT SUM(total) as jml FROM transaksi WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL $d DAY)");
        $r = mysqli_fetch_assoc($q);
        $dt = new DateTime("now - $d days");
        $grafik_label[] = $dt->format('d/m');
        $grafik_data[]  = (int)($r['jml'] ?? 0);
    }
}

// ── TRANSAKSI TERAKHIR ─────────────────────────────────────
$q_recent = mysqli_query($koneksi,
    "SELECT t.*, GROUP_CONCAT(td.nama_produk, ' x', td.qty ORDER BY td.id SEPARATOR ', ') as items
     FROM transaksi t
     LEFT JOIN transaksi_detail td ON t.id = td.transaksi_id
     WHERE $where GROUP BY t.id ORDER BY t.created_at DESC LIMIT 5");

// ── TABEL SEMUA TRANSAKSI (untuk export CSV) ───────────────
$q_all = mysqli_query($koneksi,
    "SELECT t.*, GROUP_CONCAT(td.nama_produk, ' x', td.qty ORDER BY td.id SEPARATOR ' | ') as items
     FROM transaksi t
     LEFT JOIN transaksi_detail td ON t.id = td.transaksi_id
     WHERE $where GROUP BY t.id ORDER BY t.created_at DESC");

// Tanggal display
$tanggal_display = date('d / m / Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoffeePay – Laporan Keuangan</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg: #000000;
      --sidebar-bg: linear-gradient(180deg, #2b1055 0%, #120524 100%);
      --sidebar-active: rgba(255,255,255,0.08);
      --panel-bg: #13091e;
      --card-bg: #1a0d2e;
      --border: rgba(147,51,234,0.2);
      --purple: #6a1b9a;
      --purple-light: #b388ff;
      --purple-btn: #5e17eb;
      --text: #ffffff;
      --text-dim: #a594bd;
      --green: #00e676;
      --red: #ff1744;
      --teal: #00bcd4;
    }
    html, body { height: 100%; overflow: hidden; background: var(--bg); }
    body { color: var(--text); font-family: 'Inter', sans-serif; display: flex; height: 100vh; }

    /* ── SIDEBAR ── */
    .sidebar {
      width: 220px; flex-shrink: 0;
      background: var(--sidebar-bg);
      border-right: 1px solid var(--border);
      display: flex; flex-direction: column;
      padding: 30px 0 20px; z-index: 10;
    }
    .sidebar-brand { padding: 0 22px 30px; margin-bottom: 10px; }
    .brand-text { font-size: 24px; font-weight: 700; color: var(--text); letter-spacing: -0.03em; }
    .brand-c { font-family: 'Playfair Display', serif; font-style: italic; }
    .nav-item {
      display: flex; align-items: center; gap: 12px;
      padding: 11px 22px; font-size: 13px; font-weight: 500;
      color: var(--text-dim); cursor: pointer;
      transition: all 0.2s; text-decoration: none;
      margin: 3px 10px; border-radius: 10px;
    }
    .nav-item:hover { color: var(--text); background: rgba(255,255,255,0.04); }
    .nav-item.active { color: var(--text); background: var(--sidebar-active); font-weight: 600; }
    .nav-item svg { width: 16px; height: 16px; flex-shrink: 0; }
    .nav-item.active svg { color: var(--purple-light); }
    .kasir-link-wrap { padding: 0 22px; margin: 14px 0; }
    .btn-kasir-link {
      display: block; text-align: center;
      background: rgba(179,136,255,0.1); border: 1px solid var(--purple-light);
      color: var(--purple-light); padding: 10px 12px; border-radius: 10px;
      font-size: 11px; font-weight: 600; text-decoration: none; transition: all 0.2s;
    }
    .btn-kasir-link:hover { background: var(--purple-light); color: #120524; }
    .sidebar-bottom { margin-top: auto; }

    /* ── MAIN ── */
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

    .topbar {
      padding: 20px 28px 14px;
      display: flex; align-items: center; justify-content: space-between;
      background: var(--bg); flex-shrink: 0; border-bottom: 1px solid var(--border);
    }
    .topbar-left h1 { font-size: 26px; font-weight: 700; letter-spacing: -0.02em; }
    .topbar-right { display: flex; align-items: center; gap: 10px; }

    /* Pill tanggal */
    .date-pill {
      display: flex; align-items: center; gap: 8px;
      background: var(--card-bg); border: 1px solid var(--border);
      padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 500;
    }
    .date-pill svg { width: 15px; height: 15px; color: var(--purple-light); }

    /* Filter periode */
    .select-periode {
      background: var(--card-bg); border: 1px solid var(--border);
      color: var(--text); padding: 8px 14px; border-radius: 10px;
      font-size: 13px; font-family: 'Inter', sans-serif; cursor: pointer; outline: none;
    }

    /* ── CONTENT BODY ── */
    .content-body {
      flex: 1; padding: 22px 28px 22px;
      overflow-y: auto; display: flex; flex-direction: column; gap: 20px;
    }
    .content-body::-webkit-scrollbar { width: 5px; }
    .content-body::-webkit-scrollbar-thumb { background: rgba(147,51,234,0.3); border-radius: 4px; }

    /* ── METRIK CARDS ── */
    .metrics-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .metric-card {
      background: var(--card-bg); border: 1px solid var(--border);
      border-radius: 16px; padding: 20px 22px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .metric-left {}
    .metric-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-dim); margin-bottom: 8px; }
    .metric-value { font-size: clamp(20px, 2.2vw, 28px); font-weight: 700; letter-spacing: -0.02em; }
    .metric-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .metric-icon svg { width: 22px; height: 22px; }
    .icon-green { background: rgba(0,230,118,0.12); color: var(--green); }
    .icon-purple { background: rgba(94,23,235,0.2); color: var(--purple-light); }
    .icon-teal { background: rgba(0,188,212,0.12); color: var(--teal); }

    /* ── GRAFIK + TRANSAKSI ── */
    .bottom-row { display: grid; grid-template-columns: 1fr 300px; gap: 16px; }
    .chart-card, .recent-card {
      background: var(--card-bg); border: 1px solid var(--border); border-radius: 16px; padding: 22px;
    }
    .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
    .card-title { font-size: 15px; font-weight: 700; }

    .chart-wrap { height: 220px; position: relative; }

    /* Recent transaksi */
    .recent-list { display: flex; flex-direction: column; gap: 0; }
    .recent-row {
      padding: 10px 0; border-bottom: 1px solid rgba(147,51,234,0.08);
      display: flex; flex-direction: column; gap: 4px;
    }
    .recent-row:last-child { border-bottom: none; }
    .recent-top { display: flex; justify-content: space-between; align-items: center; }
    .recent-time { font-size: 11px; color: var(--purple-light); font-weight: 600; }
    .recent-total { font-size: 13px; font-weight: 700; color: var(--green); }
    .recent-items { font-size: 11px; color: var(--text-dim); line-height: 1.4; }
    .lainnya-link { text-align: right; margin-top: 12px; font-size: 12px; color: var(--purple-light); cursor: pointer; }

    /* ── TOMBOL CSV ── */
    .btn-csv {
      display: flex; align-items: center; gap: 8px;
      background: var(--purple-btn); border: none; color: #fff;
      padding: 10px 18px; border-radius: 12px; font-size: 13px; font-weight: 600;
      cursor: pointer; transition: all 0.2s;
    }
    .btn-csv:hover { background: #4a12c0; box-shadow: 0 0 16px rgba(94,23,235,0.4); }
    .btn-csv svg { width: 15px; height: 15px; }

    /* ── TABEL MODAL ── */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 100; align-items: center; justify-content: center; }
    .modal-overlay.show { display: flex; }
    .modal-box { background: #1a0d2e; border: 1px solid rgba(94,23,235,0.4); border-radius: 18px; padding: 28px; max-width: 720px; width: 92%; max-height: 80vh; display: flex; flex-direction: column; gap: 16px; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; }
    .modal-title { font-size: 17px; font-weight: 700; }
    .btn-close-modal { background: none; border: none; color: var(--text-dim); font-size: 22px; cursor: pointer; line-height: 1; }
    .modal-table-wrap { overflow-y: auto; flex: 1; }
    .report-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .report-table th { padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid var(--border); }
    .report-table td { padding: 12px 14px; border-bottom: 1px solid rgba(147,51,234,0.07); vertical-align: top; }
    .report-table tr:hover td { background: rgba(94,23,235,0.05); }
    .badge { display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .badge-tunai { background: rgba(0,230,118,0.12); color: var(--green); }
    .badge-qris  { background: rgba(179,136,255,0.12); color: var(--purple-light); }
  </style>
</head>
<body>

  <!-- MODAL TABEL LENGKAP -->
  <div class="modal-overlay" id="modalTabel">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">Semua Transaksi</div>
        <button class="btn-close-modal" onclick="document.getElementById('modalTabel').classList.remove('show')">✕</button>
      </div>
      <div class="modal-table-wrap">
        <table class="report-table">
          <thead>
            <tr>
              <th>Kode</th><th>Waktu</th><th>Pelanggan</th><th>Item</th><th>Metode</th><th style="text-align:right">Total</th>
            </tr>
          </thead>
          <tbody>
            <?php
            mysqli_data_seek($q_all, 0);
            while ($tx = mysqli_fetch_assoc($q_all)):
            ?>
            <tr>
              <td style="font-weight:600;color:var(--purple-light)"><?= htmlspecialchars($tx['kode']) ?></td>
              <td style="font-size:11px;color:var(--text-dim)"><?= date('d/m H:i', strtotime($tx['created_at'])) ?></td>
              <td><?= htmlspecialchars($tx['nama_pelanggan'] ?: '—') ?></td>
              <td style="max-width:180px;font-size:11px;color:var(--text-dim)"><?= htmlspecialchars($tx['items'] ?? '—') ?></td>
              <td><span class="badge badge-<?= $tx['metode'] ?>"><?= strtoupper($tx['metode']) ?></span></td>
              <td style="text-align:right;font-weight:700;color:var(--green)">Rp <?= number_format($tx['total'],0,',','.') ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <span class="brand-text"><span class="brand-c">C</span>offeePay</span>
    </div>
    <nav>
      <a class="nav-item" href="/api/dashboardadmin.php">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="4"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        Produk
      </a>
      <a class="nav-item active" href="/api/laporankeuangan.php">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
        Laporan Keuangan
      </a>
    </nav>
    <div class="kasir-link-wrap">
      <a href="Kasir.php" class="btn-kasir-link">LIHAT DASHBOARD KASIR</a>
    </div>
    <div class="sidebar-bottom">
      <a class="nav-item" href="/api/logout.php">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        Keluar
      </a>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <h1>Laporan Hari Ini</h1>
      </div>
      <div class="topbar-right">
        <div class="date-pill">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <?= $tanggal_display ?>
        </div>
        <form method="GET">
          <select name="periode" class="select-periode" onchange="this.form.submit()">
            <option value="hari_ini"   <?= $filter_periode === 'hari_ini'   ? 'selected' : '' ?>>Hari ini</option>
            <option value="minggu_ini" <?= $filter_periode === 'minggu_ini' ? 'selected' : '' ?>>Minggu ini</option>
            <option value="bulan_ini"  <?= $filter_periode === 'bulan_ini'  ? 'selected' : '' ?>>Bulan ini</option>
            <option value="semua"      <?= $filter_periode === 'semua'      ? 'selected' : '' ?>>Semua waktu</option>
          </select>
        </form>
      </div>
    </div>

    <div class="content-body">

      <!-- METRIK -->
      <div class="metrics-row">
        <div class="metric-card">
          <div class="metric-left">
            <div class="metric-label">Total Pemasukan</div>
            <div class="metric-value">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></div>
          </div>
          <div class="metric-icon icon-green">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          </div>
        </div>
        <div class="metric-card">
          <div class="metric-left">
            <div class="metric-label">Transaksi</div>
            <div class="metric-value"><?= $jml_transaksi ?></div>
          </div>
          <div class="metric-icon icon-purple">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          </div>
        </div>
        <div class="metric-card">
          <div class="metric-left">
            <div class="metric-label">Produk Terjual</div>
            <div class="metric-value"><?= $total_produk_terjual ?></div>
          </div>
          <div class="metric-icon icon-teal">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          </div>
        </div>
      </div>

      <!-- GRAFIK + RECENT -->
      <div class="bottom-row">
        <div class="chart-card">
          <div class="card-header">
            <div class="card-title">Grafik Pemasukan</div>
            <button class="btn-csv" onclick="cetakCSV()">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M12 18v-6M9 15l3 3 3-3"/></svg>
              Cetak CSV
            </button>
          </div>
          <div class="chart-wrap">
            <canvas id="grafikChart"></canvas>
          </div>
        </div>

        <div class="recent-card">
          <div class="card-header">
            <div class="card-title">Transaksi Terakhir</div>
          </div>
          <div class="recent-list">
            <?php
            $cnt = 0;
            mysqli_data_seek($q_recent, 0);
            while ($tx = mysqli_fetch_assoc($q_recent) and $cnt < 4):
            $cnt++;
            ?>
            <div class="recent-row">
              <div class="recent-top">
                <span class="recent-time"><?= date('H:i', strtotime($tx['created_at'])) ?></span>
                <span class="recent-total">Rp <?= number_format($tx['total'],0,',','.') ?></span>
              </div>
              <div class="recent-items"><?= htmlspecialchars(mb_strimwidth($tx['items'] ?? '—', 0, 55, '...')) ?></div>
            </div>
            <?php endwhile; ?>
            <?php if ($jml_transaksi === 0): ?>
            <div style="text-align:center;padding:30px;color:var(--text-dim);font-size:12px;opacity:.5;">Belum ada transaksi</div>
            <?php endif; ?>
          </div>
          <?php if ($jml_transaksi > 4): ?>
          <div class="lainnya-link" onclick="document.getElementById('modalTabel').classList.add('show')">Lainnya →</div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

  <script>
    // ── GRAFIK ────────────────────────────────────────────
    const labels = <?= json_encode($grafik_label) ?>;
    const values = <?= json_encode($grafik_data) ?>;

    const ctx = document.getElementById('grafikChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 220);
    gradient.addColorStop(0, 'rgba(94,23,235,0.5)');
    gradient.addColorStop(1, 'rgba(94,23,235,0.01)');

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          data: values,
          backgroundColor: gradient,
          borderColor: '#7c3aed',
          borderWidth: 1,
          borderRadius: 6,
          borderSkipped: false,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: 'rgba(147,51,234,0.08)' }, ticks: { color: '#a594bd', font: { size: 10 } } },
          y: {
            grid: { color: 'rgba(147,51,234,0.08)' },
            ticks: {
              color: '#a594bd', font: { size: 10 },
              callback: v => v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : v >= 1000 ? (v/1000).toFixed(0)+'rb' : v
            }
          }
        }
      }
    });

    // ── CSV EXPORT ────────────────────────────────────────
    const csvData = <?php
      $rows = [];
      mysqli_data_seek($q_all, 0);
      while ($r = mysqli_fetch_assoc($q_all)) {
          $rows[] = [
              'kode'      => $r['kode'],
              'waktu'     => $r['created_at'],
              'pelanggan' => $r['nama_pelanggan'] ?? '',
              'item'      => $r['items'] ?? '',
              'metode'    => $r['metode'],
              'subtotal'  => $r['subtotal'],
              'pajak'     => $r['pajak'],
              'total'     => $r['total'],
              'kasir'     => $r['kasir'] ?? '',
          ];
      }
      echo json_encode($rows);
    ?>;

    function cetakCSV() {
      if (csvData.length === 0) { alert('Tidak ada data untuk diekspor.'); return; }
      const header = ['Kode','Waktu','Pelanggan','Item','Metode','Subtotal','Pajak','Total','Kasir'];
      const rows   = csvData.map(r => [r.kode, r.waktu, r.pelanggan, '"'+r.item+'"', r.metode, r.subtotal, r.pajak, r.total, r.kasir]);
      const csv    = [header, ...rows].map(r => r.join(',')).join('\n');
      const blob   = new Blob([csv], { type: 'text/csv' });
      const a      = document.createElement('a');
      a.href       = URL.createObjectURL(blob);
      a.download   = 'laporan_coffeepay_<?= date('Ymd') ?>.csv';
      a.click();
    }
  </script>
</body>
</html>l