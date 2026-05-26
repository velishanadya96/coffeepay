<?php
session_start();
include 'koneksi.php';

// Cek hak akses admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login.php");
    exit;
}

// PROSES TAMBAH PRODUK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_produk'])) {
    $nama_produk = mysqli_real_escape_string($koneksi, trim($_POST['nama_produk']));
    $harga       = intval($_POST['harga']);
    $kategori    = $_POST['kategori'];

    if (empty($nama_produk) || empty($harga) || empty($kategori)) {
        echo "<script>alert('Semua kolom wajib diisi!'); window.history.back();</script>";
        exit;
    }

    $nama_file_baru = 'default.png';

    // Upload Foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $nama_foto = $_FILES['foto']['name'];
        $ukuran    = $_FILES['foto']['size'];
        $tmp_name  = $_FILES['foto']['tmp_name'];
        
        $ekstensi_valid = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi_foto  = strtolower(pathinfo($nama_foto, PATHINFO_EXTENSION));

        if (!in_array($ekstensi_foto, $ekstensi_valid)) {
            echo "<script>alert('Format foto harus JPG, JPEG, PNG, atau WEBP!'); window.history.back();</script>";
            exit;
        }

        if ($ukuran > 2000000) { // Maks 2MB
            echo "<script>alert('Ukuran foto terlalu besar! Maksimal 2MB.'); window.history.back();</script>";
            exit;
        }

        $nama_file_baru = uniqid() . '.' . $ekstensi_foto;
        
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        move_uploaded_file($tmp_name, 'uploads/' . $nama_file_baru);
    }

    $query = "INSERT INTO produk (nama_produk, harga, kategori, foto) VALUES ('$nama_produk', $harga, '$kategori', '$nama_file_baru')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Menu baru berhasil disimpan!'); window.location.href='dashboardadmin.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan menu baru.'); window.history.back();</script>";
    }
}

// PROSES HAPUS PRODUK
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // Ambil nama file foto sebelum dihapus dari database
    $cek_foto = mysqli_query($koneksi, "SELECT foto FROM produk WHERE id = $id");
    if (mysqli_num_rows($cek_foto) === 1) {
        $data = mysqli_fetch_assoc($cek_foto);
        // Hapus file fisik foto jika bukan default.png
        if ($data['foto'] !== 'default.png' && file_exists('uploads/' . $data['foto'])) {
            unlink('uploads/' . $data['foto']);
        }
    }

    $query_hapus = "DELETE FROM produk WHERE id = $id";
    if (mysqli_query($koneksi, $query_hapus)) {
        echo "<script>alert('Menu berhasil dihapus!'); window.location.href='dashboardadmin.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus menu.'); window.location.href='dashboardadmin.php';</script>";
    }
}
?>