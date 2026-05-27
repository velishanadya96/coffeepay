<?php
session_start(); // Mulai session bertenaga PHP

// Hapus semua data session server
$_SESSION = array();

// Hapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session secara total
session_destroy();

// Alihkan pengguna kembali ke LandingPage.php
echo "<script>alert('Anda telah berhasil logout.'); window.location.href='index.html';</script>";
exit;
?>