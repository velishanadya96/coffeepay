<?php
// Hapus semua cookie
setcookie('user_id',  '', time() - 3600, '/');
setcookie('username', '', time() - 3600, '/');
setcookie('role',     '', time() - 3600, '/');

header("Location: /api/Login.php");
exit;
?>