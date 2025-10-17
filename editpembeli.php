<?php
include 'db.php';

$error = '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

if ($id === '') {
    header('Location: indexpembeli.php');
    exit;
}

// fetch existing data
$id_esc = mysqli_real_escape_string($koneksi, $id);
$res = mysqli_query($koneksi, "SELECT * FROM pembeli WHERE id_pembeli = '$id_esc'");
$row = mysqli_fetch_assoc($res);
if (!$row) {
    header('Location: indexpembeli.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';

    if ($nama === '') {
        $error = 'Nama pembeli harus diisi.';
    } else {
  $nama_esc = mysqli_real_escape_string($koneksi, $nama);
  $alamat_esc = mysqli_real_escape_string($koneksi, $alamat);

  $sql = "UPDATE pembeli SET nama_pembeli = '$nama_esc', alamat = '$alamat_esc' WHERE id_pembeli = '$id_esc'";
        mysqli_query($koneksi, $sql);
        header('Location: indexpembeli.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Pembeli</title>
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
        <h3 class="mb-3">Edit Pembeli</h3>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <label class="form-label">ID Pembeli</label>
            <input class="form-control" value="<?= htmlspecialchars($row['id_pembeli']) ?>" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label">Nama Pembeli</label>
            <input name="nama" class="form-control" value="<?= htmlspecialchars($row['nama_pembeli']) ?>" required>
          </div>
          <!-- email field removed -->
          <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($row['alamat']) ?></textarea>
          </div>
          <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Update</button>
            <a href="indexpembeli.php" class="btn btn-outline-secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</body>
</html>
