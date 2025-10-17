<?php
include 'db.php';

$error = '';
$id = isset($_GET['id']) ? trim($_GET['id']) : '';
if ($id === '') {
    header('Location: indextransaksi.php');
    exit;
}

// fetch transaksi
$id_esc = mysqli_real_escape_string($koneksi, $id);
$res = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id_transaksi = '$id_esc'");
$tx = mysqli_fetch_assoc($res);
if (!$tx) {
    header('Location: indextransaksi.php');
    exit;
}

// load pembeli and barang
$pembeli_res = mysqli_query($koneksi, "SELECT id_pembeli, nama_pembeli FROM pembeli ORDER BY nama_pembeli ASC");
$barang_res = mysqli_query($koneksi, "SELECT id_barang, nama_barang, harga, stok FROM barang ORDER BY nama_barang ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pembeli = isset($_POST['id_pembeli']) ? trim($_POST['id_pembeli']) : '';
    $id_barang = isset($_POST['id_barang']) ? trim($_POST['id_barang']) : '';
    $jumlah = isset($_POST['jumlah']) ? (int) $_POST['jumlah'] : 0;

    if ($id_pembeli === '' || $id_barang === '' || $jumlah <= 0) {
        $error = 'Pembeli, barang, dan jumlah harus diisi (jumlah > 0).';
    } else {
        // start transaction
        if (function_exists('mysqli_begin_transaction')) mysqli_begin_transaction($koneksi); else mysqli_query($koneksi, 'START TRANSACTION');

        $old_barang = $tx['id_barang'];
        $old_jumlah = (int)$tx['jumlah'];

        // 1) restore old stock to old barang
        $oldb_esc = mysqli_real_escape_string($koneksi, $old_barang);
        $restore_ok = mysqli_query($koneksi, "UPDATE barang SET stok = stok + $old_jumlah WHERE id_barang = '$oldb_esc'");
        if (!$restore_ok) {
            if (function_exists('mysqli_rollback')) mysqli_rollback($koneksi); else mysqli_query($koneksi, 'ROLLBACK');
            $error = 'Gagal mengembalikan stok lama: ' . mysqli_error($koneksi);
        } else {
            // 2) check new barang stok
            $newb_esc = mysqli_real_escape_string($koneksi, $id_barang);
            $rb = mysqli_query($koneksi, "SELECT stok, harga FROM barang WHERE id_barang = '$newb_esc'");
            $rb_row = mysqli_fetch_assoc($rb);
            if (!$rb_row) {
                if (function_exists('mysqli_rollback')) mysqli_rollback($koneksi); else mysqli_query($koneksi, 'ROLLBACK');
                $error = 'Barang baru tidak ditemukan.';
            } elseif ((int)$rb_row['stok'] < $jumlah) {
                // not enough stock
                if (function_exists('mysqli_rollback')) mysqli_rollback($koneksi); else mysqli_query($koneksi, 'ROLLBACK');
                $error = 'Stok barang tidak mencukupi untuk jumlah yang diminta.';
            } else {
                // 3) update transaksi
                $idp_esc = mysqli_real_escape_string($koneksi, $id_pembeli);
                $total = ((int)$rb_row['harga']) * $jumlah;
                $sql = "UPDATE transaksi SET id_pembeli = '$idp_esc', id_barang = '$newb_esc', jumlah = $jumlah, total_harga = $total WHERE id_transaksi = '$id_esc'";
                $ok = mysqli_query($koneksi, $sql);
                if (!$ok) {
                    if (function_exists('mysqli_rollback')) mysqli_rollback($koneksi); else mysqli_query($koneksi, 'ROLLBACK');
                    $error = 'Gagal update transaksi: ' . mysqli_error($koneksi);
                } else {
                    // 4) decrement new barang stok
                    $dec = mysqli_query($koneksi, "UPDATE barang SET stok = stok - $jumlah WHERE id_barang = '$newb_esc' AND stok >= $jumlah");
                    if (!$dec || mysqli_affected_rows($koneksi) == 0) {
                        if (function_exists('mysqli_rollback')) mysqli_rollback($koneksi); else mysqli_query($koneksi, 'ROLLBACK');
                        $error = 'Gagal mengurangi stok baru. Perubahan dibatalkan.';
                    } else {
                        if (function_exists('mysqli_commit')) mysqli_commit($koneksi); else mysqli_query($koneksi, 'COMMIT');
                        header('Location: indextransaksi.php');
                        exit;
                    }
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Transaksi</title>
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
        <h3 class="mb-3">Edit Transaksi</h3>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <label class="form-label">Pembeli</label>
            <select name="id_pembeli" class="form-select" required>
              <option value="">Pilih pembeli...</option>
              <?php mysqli_data_seek($pembeli_res,0); while ($p = mysqli_fetch_assoc($pembeli_res)): ?>
                <option value="<?= htmlspecialchars($p['id_pembeli']) ?>" <?= ($p['id_pembeli'] == $tx['id_pembeli']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama_pembeli']) ?> (ID: <?= htmlspecialchars($p['id_pembeli']) ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Barang</label>
            <select name="id_barang" class="form-select" required>
              <option value="">Pilih barang...</option>
              <?php mysqli_data_seek($barang_res,0); while ($b = mysqli_fetch_assoc($barang_res)): ?>
                <option value="<?= htmlspecialchars($b['id_barang']) ?>" <?= ($b['id_barang'] == $tx['id_barang']) ? 'selected' : '' ?>><?= htmlspecialchars($b['nama_barang']) ?> — Rp <?= number_format($b['harga'],0,',','.') ?> — Stok: <?= htmlspecialchars($b['stok']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Jumlah</label>
            <input name="jumlah" type="number" min="1" class="form-control" value="<?= htmlspecialchars($tx['jumlah']) ?>" required>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-primary">Update Transaksi</button>
            <a href="indextransaksi.php" class="btn btn-outline-secondary">Kembali</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</body>
</html>
