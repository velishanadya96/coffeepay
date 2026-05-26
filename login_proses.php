<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        echo "<script>alert('Email dan password harus diisi!'); window.history.back();</script>";
        exit;
    }

    // Cari user berdasarkan email
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($query) === 1) {
        $user = mysqli_fetch_assoc($query);

        // Verifikasi password yang dienkripsi
        if (password_verify($password, $user['password'])) {
            
            // Simpan data user ke Session PHP server
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            // REDIRECT BERDASARKAN ROLE DARI PHPMYADMIN
            if ($user['role'] === 'admin') {
                echo "<script>alert('Selamat datang Admin " . $user['username'] . "'); window.location.href='dashboardadmin.php';</script>";
            } else {
                echo "<script>alert('Selamat datang Kasir " . $user['username'] . "'); window.location.href='Kasir.php';</script>";
            }
            exit;
        }
    }

    // Jika tidak ditemukan atau password salah
    echo "<script>alert('Email atau password salah!'); window.history.back();</script>";
}
?>