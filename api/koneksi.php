<?php
$host = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
$port = '4000';
$user = '2fwbX5uBv2zabko.root';
$pass = 'fJ6laxRpWkcYyNd1';
$db   = 'db_coffeepay'
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
    die("Koneksi TiDB gagal: " . mysqli_connect_error());
}
?>