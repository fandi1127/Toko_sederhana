<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? trim($_POST['id']) : '';
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';

    // basic validation
    if ($nama === '') {
        $error = 'Nama pembeli harus diisi.';
    } else {
        $id_esc = mysqli_real_escape_string($koneksi, $id);
        $nama_esc = mysqli_real_escape_string($koneksi, $nama);
    $alamat_esc = mysqli_real_escape_string($koneksi, $alamat);

        // If user didn't provide an ID, let the database handle auto-increment by omitting the id column
        if ($id_esc === '') {
            $sql = "INSERT INTO pembeli (nama_pembeli, alamat) VALUES ('$nama_esc', '$alamat_esc')";
        } else {
            $sql = "INSERT INTO pembeli (id_pembeli, nama_pembeli, alamat) VALUES ('$id_esc', '$nama_esc', '$alamat_esc')";
        }

        $res = mysqli_query($koneksi, $sql);
        if ($res) {
            header('Location: indexpembeli.php');
            exit;
        } else {
            // expose DB error for debugging (safe on local development)
            $error = 'Database error: ' . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tambah Pembeli</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">Toko Fandi</a>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <div class="card p-4 mx-auto" style="max-width:720px;">
                <h3 class="mb-3">Tambah Pembeli</h3>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">ID Pembeli</label>
                        <input name="id" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Pembeli</label>
                        <input name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-primary">Simpan</button>
                        <a href="indexpembeli.php" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
