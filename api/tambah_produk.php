<?php
include 'koneksi.php';

// Cek login admin
if (!isset($_COOKIE['role']) || $_COOKIE['role'] !== 'admin') {
    header("Location: /api/Login.php");
    exit;
}

// Cek apakah form dikirim
if (!isset($_POST['tambah_produk'])) {
    header("Location: dashboardadmin.php");
    exit;
}

$nama     = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
$harga    = intval($_POST['harga']);
$kategori = in_array($_POST['kategori'], ['minuman', 'makanan']) ? $_POST['kategori'] : 'minuman';

// Proses upload foto
$foto = 'default.png';
if (!empty($_FILES['foto']['name'])) {
    $ext  = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $foto = uniqid('img_') . '.' . $ext;
    move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/uploads/' . $foto);
}

// Simpan ke database
mysqli_query($koneksi, "INSERT INTO produk (nama_produk, harga, kategori, foto) 
                         VALUES ('$nama', $harga, '$kategori', '$foto')");

// Kembali ke halaman admin
header("Location: dashboardadmin.php");
exit;