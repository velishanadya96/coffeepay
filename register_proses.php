<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $email    = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password = trim($_POST['password']);

    // Validasi input kosong
    if (empty($username) || empty($email) || empty($password)) {
        echo "<script>alert('Semua kolom harus diisi!'); window.history.back();</script>";
        exit;
    }

    // Cek apakah email sudah terdaftar
    $cek_email = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        echo "<script>alert('Email ini sudah terdaftar!'); window.history.back();</script>";
        exit;
    }

    // OTOMATIS COCOKKAN DOMAIN EMAIL UNTUK JADI ADMIN
    $role = 'kasir';
    if (str_ends_with(strtolower($email), '@admincoffeepay.com')) {
        $role = 'admin';
    }

    // Amankan password dengan enkripsi password_hash
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    // Simpan ke database localhost
    $query = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password_hashed', '$role')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Registrasi Berhasil sebagai " . ucfirst($role) . "!'); window.location.href='Login.php';</script>";
    } else {
        echo "<script>alert('Gagal mendaftar, coba lagi.'); window.history.back();</script>";
    }
}
?>