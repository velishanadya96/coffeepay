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

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");

    if (mysqli_num_rows($query) === 1) {
        $user = mysqli_fetch_assoc($query);

        if (password_verify($password, $user['password'])) {

            // Simpan ke cookie (bukan session, karena Vercel tidak support session)
            setcookie('user_id',   $user['id'],       time() + 3600, '/', '', true, true);
            setcookie('username',  $user['username'],  time() + 3600, '/', '', true, true);
            setcookie('role',      $user['role'],      time() + 3600, '/', '', true, true);

            if ($user['role'] === 'admin') {
                echo "<script>alert('Selamat datang Admin " . $user['username'] . "!'); window.location.href='dashboardadmin.php';</script>";
            } else {
                echo "<script>alert('Selamat datang Kasir " . $user['username'] . "!'); window.location.href='/api/Kasir.php';</script>";
            }
            exit;
        }
    }

    echo "<script>alert('Email atau password salah!'); window.history.back();</script>";
}
?>