<?php
include "db.php";

$q = '';
if (isset($_GET['q'])) {
	$q = mysqli_real_escape_string($koneksi, trim($_GET['q']));
}

// Simple query: look up transaksi by id or nama pembeli (if joined)
$select = "t.*, p.nama_pembeli, b.nama_barang";
if ($q !== '') {
	$sql = "SELECT $select FROM transaksi t LEFT JOIN pembeli p ON p.id_pembeli = t.id_pembeli LEFT JOIN barang b ON b.id_barang = t.id_barang WHERE t.id_transaksi LIKE '%$q%' ORDER BY t.id_transaksi DESC";
} else {
	$sql = "SELECT $select FROM transaksi t LEFT JOIN pembeli p ON p.id_pembeli = t.id_pembeli LEFT JOIN barang b ON b.id_barang = t.id_barang ORDER BY t.id_transaksi DESC";
}
$transaksi = @mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Transaksi - Toko Fandi</title>
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
					<li class="nav-item"><a class="nav-link active" href="indextransaksi.php">Transaksi</a></li>
				</ul>
			</div>
		</div>
	</nav>

	<main class="container py-5">
		<div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4">
			<div>
				<h2 class="h4">Daftar Transaksi</h2>
				<p class="text-muted small">Riwayat transaksi pembelian.</p>
			</div>
			<div class="d-flex gap-2">
				<form class="d-flex" method="get" action="indextransaksi.php">
					<input name="q" value="<?= htmlspecialchars($q) ?>" class="form-control me-2" type="search" placeholder="Cari ID transaksi...">
					<button class="btn btn-outline-secondary" type="submit">Cari</button>
				</form>
				<a href="index.php" class="btn btn-secondary">← Kembali</a>
				<a href="tambahtransaksi.php" class="btn btn-primary">+ Tambah Transaksi</a>
			</div>
		</div>

		<?php if (!$transaksi || mysqli_num_rows($transaksi) == 0): ?>
			<div class="alert alert-info">Belum ada transaksi.</div>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table align-middle">
					<thead class="table-light">
						<tr>
							<th>ID</th>
							<th>Tanggal</th>
							<th>Pembeli</th>
							<th>Barang</th>
							<th>Total</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody>
					<?php while ($row = mysqli_fetch_assoc($transaksi)): ?>
						<tr>
							<td><?= htmlspecialchars($row['id_transaksi'] ?? $row['id'] ?? '-') ?></td>
							<td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
							<td><?= htmlspecialchars($row['nama_pembeli'] ?? $row['id_pembeli'] ?? '-') ?></td>
							<td><?= htmlspecialchars($row['nama_barang'] ?? '-') ?></td>
							<td>Rp <?= number_format($row['total_harga'] ?? 0,0,',','.') ?></td>
							<td>
								<a href="edittransaksi.php?id=<?= $row['id_transaksi'] ?? $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
								<a href="hapustransaksi.php?id=<?= $row['id_transaksi'] ?? $row['id'] ?>" class="btn btn-sm btn-danger ms-2" onclick="return confirm('Yakin hapus transaksi ini?')">Hapus</a>
							</td>
						</tr>
					<?php endwhile; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

	</main>

	<footer class="site-footer py-4 mt-5">
		<div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
			<div class="text-muted">© <?=date('Y')?> Toko Fandi</div>
			<div class="mt-2 mt-md-0">
				<a href="#" class="text-muted me-3">Syarat</a>
				<a href="#" class="text-muted">Kebijakan Privasi</a>
			</div>
		</div>
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
