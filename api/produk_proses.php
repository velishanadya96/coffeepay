<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['pesanan'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

// Generate kode unik TX-XXXXX
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

// Mulai transaksi MySQL
mysqli_begin_transaction($koneksi);

try {
    // Insert header transaksi
    $q1 = "INSERT INTO transaksi 
            (kode, nama_pelanggan, catatan, subtotal, diskon, pajak, total, uang_diberikan, kembalian, metode, kasir)
            VALUES ('$kode','$nama_pelanggan','$catatan',$subtotal,0,$pajak,$total,$uang,$kembalian,'$metode','$kasir')";

    if (!mysqli_query($koneksi, $q1)) throw new Exception(mysqli_error($koneksi));

    $transaksi_id = mysqli_insert_id($koneksi);

    // Insert detail item
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