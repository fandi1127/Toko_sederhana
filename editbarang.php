<?php
include "db.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$res = mysqli_query($koneksi, "SELECT * FROM barang WHERE id_barang=$id");
$data = mysqli_fetch_assoc($res);
if (!$data) {
    header('Location: indexbarang.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = strtoupper(mysqli_real_escape_string($koneksi, trim($_POST['nama'] ?? '')));
    $harga = isset($_POST['harga']) ? (int) $_POST['harga'] : 0;
    $stok = isset($_POST['stok']) ? (int) $_POST['stok'] : 0;
    if ($stok < 0) { echo "<script>alert('Stok tidak boleh negatif');history.back();</script>"; exit; }
    mysqli_query($koneksi, "UPDATE barang SET nama_barang='$nama', harga=$harga, stok=$stok WHERE id_barang=$id");
    header('Location: indexbarang.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Barang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container py-5">
        <div class="card p-4 mx-auto" style="max-width:720px;">
            <h3 class="mb-3">Edit Barang</h3>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Nama Barang</label>
                    <input name="nama" class="form-control" value="<?= htmlspecialchars($data['nama_barang']) ?>" required>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Harga</label>
                        <input name="harga" type="number" min="0" class="form-control" value="<?= htmlspecialchars($data['harga']) ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Stok</label>
                        <input name="stok" type="number" min="0" class="form-control" value="<?= htmlspecialchars($data['stok']) ?>">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary">Simpan</button>
                    <a href="indexbarang.php" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
