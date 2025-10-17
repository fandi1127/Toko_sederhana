<?php
include 'db.php';

$id = isset($_GET['id']) ? trim($_GET['id']) : '';

if ($id === '') {
    header('Location: indexpembeli.php');
    exit;
}

$id_esc = mysqli_real_escape_string($koneksi, $id);
$sql = "DELETE FROM pembeli WHERE id_pembeli = '$id_esc'";
$res = mysqli_query($koneksi, $sql);
if ($res) {
    header('Location: indexpembeli.php');
    exit;
} else {
    // show simple error for debugging
    echo '<h3>Gagal menghapus pembeli</h3>';
    echo '<p>' . htmlspecialchars(mysqli_error($koneksi)) . '</p>';
    echo '<p><a href="indexpembeli.php">Kembali</a></p>';
}
