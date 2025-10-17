<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "toko_fandi";
$port = 3307;

$koneksi = mysqli_connect($host, $user, $pass, $db, $port);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
