<?php
include 'db.php';

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
if ($id === '') {
    header('Location: indextransaksi.php');
    exit;
}

$id_esc = mysqli_real_escape_string($koneksi, $id);

// fetch transaksi to know barang and jumlah
$r = mysqli_query($koneksi, "SELECT id_barang, jumlah FROM transaksi WHERE id_transaksi = '$id_esc'");
$tx = mysqli_fetch_assoc($r);
if (!$tx) {
    header('Location: indextransaksi.php');
    exit;
}

$idb_esc = mysqli_real_escape_string($koneksi, $tx['id_barang']);
$jumlah = (int)$tx['jumlah'];

// start transaction
if (function_exists('mysqli_begin_transaction')) mysqli_begin_transaction($koneksi); else mysqli_query($koneksi, 'START TRANSACTION');

// restore stock
$ok1 = mysqli_query($koneksi, "UPDATE barang SET stok = stok + $jumlah WHERE id_barang = '$idb_esc'");
if (!$ok1) {
    if (function_exists('mysqli_rollback')) mysqli_rollback($koneksi); else mysqli_query($koneksi, 'ROLLBACK');
    echo '<p>Gagal mengembalikan stok: ' . htmlspecialchars(mysqli_error($koneksi)) . '</p>';
    echo '<p><a href="indextransaksi.php">Kembali</a></p>';
    exit;
}

// delete transaksi
$ok2 = mysqli_query($koneksi, "DELETE FROM transaksi WHERE id_transaksi = '$id_esc'");
if (!$ok2) {
    if (function_exists('mysqli_rollback')) mysqli_rollback($koneksi); else mysqli_query($koneksi, 'ROLLBACK');
    echo '<p>Gagal menghapus transaksi: ' . htmlspecialchars(mysqli_error($koneksi)) . '</p>';
    echo '<p><a href="indextransaksi.php">Kembali</a></p>';
    exit;
}

// commit
if (function_exists('mysqli_commit')) mysqli_commit($koneksi); else mysqli_query($koneksi, 'COMMIT');
header('Location: indextransaksi.php');
exit;
