<?php
include 'db.php';

// Query Total Transaksi
$q_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) AS total_transaksi FROM transaksi");
$data_transaksi = mysqli_fetch_assoc($q_transaksi);
$total_transaksi = $data_transaksi['total_transaksi'] ?? 0;

// Query Total Pendapatan
$q_pendapatan = mysqli_query($koneksi, "SELECT SUM(total_harga) AS total_pendapatan FROM transaksi");
$data_pendapatan = mysqli_fetch_assoc($q_pendapatan);
$total_pendapatan = $data_pendapatan['total_pendapatan'] ?? 0;

// Query Barang Terlaris
$q_terlaris = mysqli_query($koneksi, "
    SELECT b.nama_barang, SUM(t.jumlah) AS total_terjual
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    GROUP BY b.id_barang
    ORDER BY total_terjual DESC
    LIMIT 1
");
$data_terlaris = mysqli_fetch_assoc($q_terlaris);
$barang_terlaris = $data_terlaris['nama_barang'] ?? 'Belum Ada Transaksi';
$total_terjual = $data_terlaris['total_terjual'] ?? 0;

// Query Produk Unggulan berdasarkan barang yang sering dibeli
$q_produk = mysqli_query($koneksi, "
    SELECT b.nama_barang, b.harga, b.stok, SUM(t.jumlah) AS total_terjual
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    GROUP BY b.id_barang
    ORDER BY total_terjual DESC
    LIMIT 4
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Toko Fandi — Belanja Praktis & Murah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .card:hover { cursor: pointer; background-color: #f8f9fa; transition: .3s; }
    </style>
</head>
<body>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <a class="navbar-brand" href="#">Toko Fandi</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navMenu">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="indexbarang.php">Barang</a></li>
                        <li class="nav-item"><a class="nav-link" href="indexpembeli.php">Pembeli</a></li>
                        <li class="nav-item"><a class="nav-link" href="indextransaksi.php">Transaksi</a></li>
                        <li class="nav-item"><a class="nav-link" href="laporanindex.php">Laporan</a></li>
                        <li class="nav-item"><a class="btn btn-outline-primary ms-3" href="https://wa.me/6289616923707" target="_blank">Hubungi</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="hero">
            <div class="container hero-inner text-center text-white">
                <h1 class="display-5 fw-bold">Belanja Mudah, Harga Bersahabat</h1>
                <p class="lead">Pilihan produk berkualitas untuk kebutuhan sehari-hari. Cepat, aman, dan terpercaya.</p>
                <form class="search-form d-flex justify-content-center mt-3" action="indexbarang.php" method="get">
                    <input name="q" class="form-control me-2 search-input" type="search" placeholder="Cari produk favoritmu..." aria-label="Search">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </form>
            </div>
        </div>
    </header>

    <main class="py-5">
        <div class="container">
            <!-- Ringkasan Penjualan -->
            <section class="section-intro mb-5 text-center">
                <h2 class="h3">Ringkasan Penjualan</h2>
                <p class="text-muted">Data transaksi dan performa toko terkini.</p>
                <div class="row mt-4">
                    <div class="col-12 col-md-4 mb-3">
                        <div class="card category-card text-center p-4 shadow-sm" onclick="window.location.href='laporanindex.php'">
                            <div class="icon fs-2 mb-2">💰</div>
                            <div class="fw-semibold mb-1">Total Pendapatan</div>
                            <div class="fs-5 text-primary">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <div class="card category-card text-center p-4 shadow-sm" onclick="window.location.href='indextransaksi.php'">
                            <div class="icon fs-2 mb-2">🧾</div>
                            <div class="fw-semibold mb-1">Total Transaksi</div>
                            <div class="fs-5 text-success"><?= $total_transaksi ?> transaksi</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <div class="card category-card text-center p-4 shadow-sm" onclick="window.location.href='indexbarang.php?sort=terlaris'">
                            <div class="icon fs-2 mb-2">⭐</div>
                            <div class="fw-semibold mb-1">Barang Terlaris</div>
                            <div class="fs-6 text-dark"><?= htmlspecialchars($barang_terlaris) ?> — <?= $total_terjual ?> kali terjual</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Produk Unggulan -->
            <section id="unggulan" class="mb-5">
                <h2 class="h3 mb-3">Produk Unggulan</h2>
                <div class="row g-4">
                    <?php if (mysqli_num_rows($q_produk) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($q_produk)): ?>
                            <div class="col-6 col-md-3">
                                <div class="card product-card h-100 p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <h5 class="card-title mb-2"><?= htmlspecialchars($row['nama_barang']) ?></h5>
                                        <div class="text-muted small mb-2">
                                            Rp <?= number_format($row['harga'], 0, ',', '.') ?> • Stok: <?= $row['stok'] ?>
                                        </div>
                                        <div class="small text-success">Terjual: <?= $row['total_terjual'] ?>x</div>
                                    </div>
                                    <div class="mt-2 text-end">
                                        <a href="indexbarang.php" class="btn btn-sm btn-primary">Beli</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">Belum ada produk unggulan saat ini.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="text-center py-4 bg-light rounded-3">
                <h3 class="h5">Ingin membuka toko di platform ini?</h3>
                <p class="mb-3 text-muted">Hubungi kami untuk pendaftaran dan penempatan produk.</p>
                <a id="kontak" href="https://wa.me/6289616923707" target="_blank" class="btn btn-primary">Hubungi Kami</a>
            </section>
        </div>
    </main>

    <footer class="site-footer py-4">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="text-muted">© <?=date('Y')?> Toko Fandi. Semua hak cipta dilindungi.</div>
            <div class="mt-2 mt-md-0">
                <a href="#" class="text-muted me-3">Syarat</a>
                <a href="#" class="text-muted">Kebijakan Privasi</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
