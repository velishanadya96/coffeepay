<?php
include 'koneksi.php';

// Cek login via cookie (bukan session)
if (!isset($_COOKIE['role'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// ── HAPUS PRODUK (GET request dari link ?hapus=ID) ──────────
if (isset($_GET['hapus']) && $_COOKIE['role'] === 'admin') {
    $id = intval($_GET['hapus']);
    if ($id > 0) {
        mysqli_query($koneksi, "DELETE FROM produk WHERE id = $id");
    }
    header("Location: /api/dashboardadmin.php");
    exit;
}

// ── SIMPAN TRANSAKSI (POST JSON dari Kasir) ─────────────────
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['pesanan'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

// Generate kode TX-XXXXX
$kode_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi");
$kode_row   = mysqli_fetch_assoc($kode_query);
$kode       = 'TX-' . str_pad($kode_row['total'] + 1, 5, '0', STR_PAD_LEFT);

$nama_pelanggan = mysqli_real_escape_string($koneksi, $input['nama_pelanggan'] ?? '');
$catatan        = mysqli_real_escape_string($koneksi, $input['catatan'] ?? '');
$subtotal       = intval($input['subtotal']);
$pajak          = intval($input['pajak']);
$total          = intval($input['total']);
$uang           = intval($input['uang_diberikan']);
$kembalian      = intval($input['kembalian']);
$metode         = in_array($input['metode'], ['tunai','qris']) ? $input['metode'] : 'tunai';
$kasir          = mysqli_real_escape_string($koneksi, $input['kasir'] ?? '');

mysqli_begin_transaction($koneksi);

try {
    $q1 = "INSERT INTO transaksi 
            (kode, nama_pelanggan, catatan, subtotal, diskon, pajak, total, uang_diberikan, kembalian, metode, kasir)
            VALUES ('$kode','$nama_pelanggan','$catatan',$subtotal,0,$pajak,$total,$uang,$kembalian,'$metode','$kasir')";

    if (!mysqli_query($koneksi, $q1)) throw new Exception(mysqli_error($koneksi));

    $transaksi_id = mysqli_insert_id($koneksi);

    foreach ($input['pesanan'] as $item) {
        $produk_id     = intval($item['id']);
        $nama_produk   = mysqli_real_escape_string($koneksi, $item['nama_produk']);
        $harga         = intval($item['harga']);
        $qty           = intval($item['qty']);
        $subtotal_item = $harga * $qty;

        $q2 = "INSERT INTO transaksi_detail 
                (transaksi_id, produk_id, nama_produk, harga, qty, subtotal_item)
                VALUES ($transaksi_id, $produk_id, '$nama_produk', $harga, $qty, $subtotal_item)";
        if (!mysqli_query($koneksi, $q2)) throw new Exception(mysqli_error($koneksi));
    }

    mysqli_commit($koneksi);
    echo json_encode(['success' => true, 'kode' => $kode, 'transaksi_id' => $transaksi_id]);

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}