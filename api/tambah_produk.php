<?php
include 'koneksi.php';

// Cek login admin
if (!isset($_COOKIE['role']) || $_COOKIE['role'] !== 'admin') {
    header("Location: /api/Login.php");
    exit;
}

if (!isset($_POST['tambah_produk'])) {
    header("Location: /api/dashboardadmin.php");
    exit;
}

$nama     = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
$harga    = intval($_POST['harga']);
$kategori = in_array($_POST['kategori'], ['minuman', 'makanan']) ? $_POST['kategori'] : 'minuman';

// ✅ PERBAIKAN: ambil dari hidden input URL, bukan $_FILES
$foto = mysqli_real_escape_string($koneksi, trim($_POST['foto'] ?? 'default.png'));
if (empty($foto)) $foto = 'default.png';

mysqli_query($koneksi, "INSERT INTO produk (nama_produk, harga, kategori, foto) 
                         VALUES ('$nama', $harga, '$kategori', '$foto')");

header("Location: /api/dashboardadmin.php");
exit;