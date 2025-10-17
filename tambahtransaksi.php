<?php
include 'db.php';

$error = '';

// load pembeli and barang for dropdowns
$pembeli_res = mysqli_query($koneksi, "SELECT id_pembeli, nama_pembeli FROM pembeli ORDER BY nama_pembeli ASC");
$barang_res = mysqli_query($koneksi, "SELECT id_barang, nama_barang, harga, stok FROM barang ORDER BY nama_barang ASC");

// quick check: does transaksi table exist? if not, show helpful message when trying to save
$transaksi_table_exists = true;
$chk = mysqli_query($koneksi, "SHOW TABLES LIKE 'transaksi'");
if (!$chk || mysqli_num_rows($chk) == 0) {
  $transaksi_table_exists = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pembeli = isset($_POST['id_pembeli']) ? trim($_POST['id_pembeli']) : '';
    $id_barang = isset($_POST['id_barang']) ? trim($_POST['id_barang']) : '';
    $qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 0;

    if ($id_pembeli === '' || $id_barang === '' || $qty <= 0) {
        $error = 'Pembeli, barang, dan kuantitas harus dipilih/diisi (qty > 0).';
    } else {
        // validate stock
        $idb_esc = mysqli_real_escape_string($koneksi, $id_barang);
        $res = mysqli_query($koneksi, "SELECT stok, harga FROM barang WHERE id_barang = '$idb_esc'");
        $row = mysqli_fetch_assoc($res);
        if (!$row) {
            $error = 'Barang tidak ditemukan.';
        } elseif ((int)$row['stok'] <= 0) {
            $error = 'Stok barang habis.';
        } elseif ($qty > (int)$row['stok']) {
            $error = 'Kuantitas melebihi stok yang tersedia.';
    } else {
            $harga = (int)$row['harga'];
            $total = $harga * $qty;
      // perform insert and stock decrement in a transaction
      if (!$transaksi_table_exists) {
        $error = "Tabel 'transaksi' tidak ditemukan. Pastikan tabel dibuat. Contoh DDL: CREATE TABLE transaksi (id_transaksi INT AUTO_INCREMENT PRIMARY KEY, id_pembeli VARCHAR(50), id_barang VARCHAR(50), qty INT, total INT, tanggal DATETIME);";
      } else {
        if (function_exists('mysqli_begin_transaction')) {
          mysqli_begin_transaction($koneksi);
        } else {
          mysqli_query($koneksi, 'START TRANSACTION');
        }
            $idp_esc = mysqli_real_escape_string($koneksi, $id_pembeli);
            // adjust to actual DB schema: columns are (id_pembeli, id_barang, jumlah, total_harga, tanggal)
            $sql = "INSERT INTO transaksi (id_pembeli, id_barang, jumlah, total_harga, tanggal) VALUES ('$idp_esc', '$idb_esc', $qty, $total, NOW())";
            $ok = mysqli_query($koneksi, $sql);
            if (!$ok) {
          if (function_exists('mysqli_rollback')) { mysqli_rollback($koneksi); } else { mysqli_query($koneksi, 'ROLLBACK'); }
          $error = 'Gagal menyimpan transaksi: ' . mysqli_error($koneksi) . "\nSQL: " . $sql;
            } else {
                // decrement stock
                $sql2 = "UPDATE barang SET stok = stok - $qty WHERE id_barang = '$idb_esc' AND stok >= $qty";
                $ok2 = mysqli_query($koneksi, $sql2);
          if (!$ok2 || mysqli_affected_rows($koneksi) == 0) {
            if (function_exists('mysqli_rollback')) { mysqli_rollback($koneksi); } else { mysqli_query($koneksi, 'ROLLBACK'); }
            $error = 'Gagal mengurangi stok. Transaksi dibatalkan. DB error: ' . mysqli_error($koneksi) . "\nSQL: " . $sql2;
          } else {
            if (function_exists('mysqli_commit')) { mysqli_commit($koneksi); } else { mysqli_query($koneksi, 'COMMIT'); }
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
  <title>Tambah Transaksi</title>
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
        <h3 class="mb-3">Tambah Transaksi</h3>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <label class="form-label">Pembeli</label>
            <select name="id_pembeli" class="form-select" required>
              <option value="">Pilih pembeli...</option>
              <?php while ($p = mysqli_fetch_assoc($pembeli_res)): ?>
                <option value="<?= htmlspecialchars($p['id_pembeli']) ?>"><?= htmlspecialchars($p['nama_pembeli']) ?> (ID: <?= htmlspecialchars($p['id_pembeli']) ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Barang</label>
            <select name="id_barang" class="form-select" required>
              <option value="">Pilih barang...</option>
              <?php mysqli_data_seek($barang_res, 0); while ($b = mysqli_fetch_assoc($barang_res)): ?>
                <option value="<?= htmlspecialchars($b['id_barang']) ?>"><?= htmlspecialchars($b['nama_barang']) ?> — Rp <?= number_format($b['harga'],0,',','.') ?> — Stok: <?= htmlspecialchars($b['stok']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Kuantitas</label>
            <input name="qty" type="number" min="1" class="form-control" required>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-primary">Simpan Transaksi</button>
            <a href="indextransaksi.php" class="btn btn-outline-secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</body>
</html>
