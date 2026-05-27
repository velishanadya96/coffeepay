<?php
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
$port = intval($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? 4000);
$user = $_ENV['DB_USER'] ?? getenv('DB_USER');
$pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS');
$db   = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'db_coffeepay';

// TiDB Cloud wajib SSL
$koneksi = mysqli_init();
mysqli_ssl_set($koneksi, NULL, NULL, NULL, NULL, 'TLSv1.2');

$connected = mysqli_real_connect(
    $koneksi,
    $host,
    $user,
    $pass,
    $db,
    $port,
    NULL,
    MYSQLI_CLIENT_SSL
);

if (!$connected) {
    die(json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal: ' . mysqli_connect_error()
    ]));
}
?>