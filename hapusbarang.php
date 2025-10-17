<?php
include "db.php";
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
    mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang=$id");
}
header('Location: indexbarang.php');
exit;
