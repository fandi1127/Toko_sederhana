<?php
include "db.php";

$q = '';
if (isset($_GET['q'])) {
	$q = mysqli_real_escape_string($koneksi, trim($_GET['q']));
}

if ($q !== '') {
	$sql = "SELECT * FROM pembeli WHERE nama_pembeli LIKE '%$q%' ORDER BY id_pembeli DESC";
} else {
	$sql = "SELECT * FROM pembeli ORDER BY id_pembeli DESC";
}
$pembeli = @mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Pembeli - Toko Fandi</title>
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
					<li class="nav-item"><a class="nav-link active" href="indexpembeli.php">Pembeli</a></li>
				</ul>
			</div>
		</div>
	</nav>

	<main class="container py-5">
		<div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4">
			<div>
				<h2 class="h4">Daftar Pembeli</h2>
				<p class="text-muted small">Kelola data pembeli yang terdaftar.</p>
			</div>
			<div class="d-flex gap-2">
				<form class="d-flex" method="get" action="indexpembeli.php">
					<input name="q" value="<?= htmlspecialchars($q) ?>" class="form-control me-2" type="search" placeholder="Cari nama pembeli...">
					<button class="btn btn-outline-secondary" type="submit">Cari</button>
				</form>
				<a href="tambahpembeli.php" class="btn btn-primary">+ Tambah Pembeli</a>
				<a href="index.php" class="btn btn-secondary">Kembali</a>
			</div>
		</div>

		<?php if (!$pembeli || mysqli_num_rows($pembeli) == 0): ?>
			<div class="alert alert-info">Tidak ada data pembeli.</div>
		<?php else: ?>
			<div class="row g-4">
				<?php while ($row = mysqli_fetch_assoc($pembeli)): ?>
					<div class="col-12 col-md-6 col-lg-4">
						<div class="card h-100">
							<div class="card-body d-flex flex-column">
								<h5 class="card-title mb-1"><?= htmlspecialchars($row['nama_pembeli'] ?? 'Tanpa Nama') ?></h5>
								<p class="card-text text-muted small mb-3">Alamat: <?= htmlspecialchars($row['alamat'] ?? '-') ?></p>
								<div class="mt-auto d-flex justify-content-between align-items-center">
									<div class="text-muted small">ID: <?= htmlspecialchars($row['id_pembeli'] ?? '-') ?></div>
									<div>
										<a href="editpembeli.php?id=<?= $row['id_pembeli'] ?>" class="btn btn-warning btn-sm">Edit</a>
										<a href="hapuspembeli.php?id=<?= $row['id_pembeli'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus pembeli ini?')">Hapus</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endwhile; ?>
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
