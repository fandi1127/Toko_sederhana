<?php
include 'db.php';

// --- FILTER TANGGAL DAN PENCARIAN ---
$where = "";
if (!empty($_GET['tgl_mulai']) && !empty($_GET['tgl_selesai'])) {
    $tgl_mulai = $_GET['tgl_mulai'];
    $tgl_selesai = $_GET['tgl_selesai'];
    $where .= " AND t.tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
}
if (!empty($_GET['cari'])) {
    $cari = $_GET['cari'];
    $where .= " AND p.nama_pembeli LIKE '%$cari%'";
}

// --- DATA RINGKASAN ---
$q_pendapatan = mysqli_query($koneksi, "SELECT SUM(total_harga) AS total FROM transaksi");
$total_pendapatan = mysqli_fetch_assoc($q_pendapatan)['total'] ?? 0;

$q_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM transaksi");
$total_transaksi = mysqli_fetch_assoc($q_transaksi)['total'] ?? 0;

$q_barang_terlaris = mysqli_query($koneksi, "
    SELECT b.nama_barang, SUM(t.jumlah) AS total_jual
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    GROUP BY t.id_barang
    ORDER BY total_jual DESC
    LIMIT 1
");
$barang_terlaris = mysqli_fetch_assoc($q_barang_terlaris);
$nama_terlaris = $barang_terlaris['nama_barang'] ?? '-';

// --- DATA TABEL TRANSAKSI ---
$query = "
    SELECT t.*, p.nama_pembeli, b.nama_barang 
    FROM transaksi t
    JOIN pembeli p ON t.id_pembeli = p.id_pembeli
    JOIN barang b ON t.id_barang = b.id_barang
    WHERE 1=1 $where
    ORDER BY t.tanggal DESC
";
$transaksi = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .stats {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin: 0;
            color: #555;
            font-size: 18px;
        }
        .card p {
            font-size: 22px;
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }
        form {
            background: white;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        form input, form button, form a {
            margin: 5px;
            padding: 6px 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        table th {
            background: #333;
            color: white;
        }
        a.btn {
            background: #007bff;
            color: white;
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 4px;
        }
        a.btn:hover {
            background: #0056b3;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background: #6c757d;
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
        }
        .back-btn:hover {
            background: #565e64;
        }
    </style>
</head>
<body>

    <h2>LAPORAN TRANSAKSI TOKO FANDI</h2>

    <div class="stats">
        <div class="card">
            <h3>Total Pendapatan</h3>
            <p>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
        </div>
        <div class="card">
            <h3>Total Transaksi</h3>
            <p><?= $total_transaksi ?> transaksi</p>
        </div>
        <div class="card">
            <h3>Barang Terlaris</h3>
            <p><?= strtoupper($nama_terlaris) ?></p>
        </div>
    </div>

    <form method="GET">
        <label>Dari: <input type="date" name="tgl_mulai" value="<?= $_GET['tgl_mulai'] ?? '' ?>"></label>
        <label>Sampai: <input type="date" name="tgl_selesai" value="<?= $_GET['tgl_selesai'] ?? '' ?>"></label>
        <input type="text" name="cari" placeholder="Cari nama pembeli" value="<?= $_GET['cari'] ?? '' ?>">
        <button type="submit">Tampilkan</button>
        <a href="cetaklaporan.php?tgl_mulai=<?= $_GET['tgl_mulai'] ?? '' ?>&tgl_selesai=<?= $_GET['tgl_selesai'] ?? '' ?>&cari=<?= $_GET['cari'] ?? '' ?>" target="_blank" class="btn">Cetak Laporan</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Pembeli</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($transaksi) > 0) {
                while ($row = mysqli_fetch_assoc($transaksi)) {
                    echo "<tr>
                        <td>{$no}</td>
                        <td>{$row['tanggal']}</td>
                        <td>{$row['nama_pembeli']}</td>
                        <td>{$row['nama_barang']}</td>
                        <td>{$row['jumlah']}</td>
                        <td>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</td>
                    </tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='6'>Tidak ada data ditemukan.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Tombol Kembali -->
    <a href="index.php" class="back-btn">← Kembali ke Dashboard</a>

</body>
</html>
