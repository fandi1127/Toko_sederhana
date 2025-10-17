<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? trim($_POST['id']) : '';
    $nama = isset($_POST['nama']) ? strtoupper(trim($_POST['nama'])) : '';
    $harga = isset($_POST['harga']) ? (int) $_POST['harga'] : 0;
    $stok = isset($_POST['stok']) ? (int) $_POST['stok'] : 0;

    if ($stok < 0) {
        echo "<script>alert('Stok tidak boleh negatif!'); history.back();</script>";
        exit;
    }

    // Basic sanitization
    $id_esc = mysqli_real_escape_string($koneksi, $id);
    $nama_esc = mysqli_real_escape_string($koneksi, $nama);
    $harga_esc = (int) $harga;
    $stok_esc = (int) $stok;

    // Insert with explicit columns (safer if DB schema changes)
    $sql = "INSERT INTO barang (id_barang, nama_barang, harga, stok) VALUES ('$id_esc', '$nama_esc', $harga_esc, $stok_esc)";
    mysqli_query($koneksi, $sql);

    header("Location: indexbarang.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Barang — Toko Fandi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">Toko Fandi</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="indexbarang.php">Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="indexpembeli.php">Pembeli</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <div class="card p-4 mx-auto" style="max-width:720px;">
                <h3 class="mb-3">Tambah Barang</h3>
                <form method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label">ID Barang</label>
                        <input name="id" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input name="nama" class="form-control" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Harga (Rp)</label>
                            <input name="harga" type="number" min="0" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Stok</label>
                            <input name="stok" type="number" min="0" class="form-control" required>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary">Simpan</button>
                        <a href="indexbarang.php" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
