<?php
include 'db.php';

// --- FILTER DATA (opsional) ---
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
    <title>Cetak Laporan Transaksi</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            background: white;
            color: black;
            margin: 40px;
        }
        h2, h3 {
            text-align: center;
            margin: 5px 0;
        }
        .info {
            margin: 20px 0;
            border: 1px solid #000;
            padding: 10px;
            border-radius: 6px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 14px;
        }
        table th {
            background: #eee;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right; margin-bottom: 10px;">
        <button onclick="window.print()">🖨️ Cetak Sekarang</button>
    </div>

    <h2>LAPORAN TRANSAKSI TOKO FANDI</h2>
    <h3>Periode: 
        <?= (!empty($_GET['tgl_mulai']) && !empty($_GET['tgl_selesai'])) 
            ? date('d/m/Y', strtotime($_GET['tgl_mulai'])) . " - " . date('d/m/Y', strtotime($_GET['tgl_selesai'])) 
            : "Semua Tanggal"; ?>
    </h3>

    <div class="info">
        <table>
            <tr>
                <td><strong>Total Pendapatan</strong></td>
                <td>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td><strong>Total Transaksi</strong></td>
                <td><?= $total_transaksi ?> transaksi</td>
            </tr>
            <tr>
                <td><strong>Barang Terlaris</strong></td>
                <td><?= strtoupper($nama_terlaris) ?></td>
            </tr>
        </table>
    </div>

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

    <div class="footer">
        <p>Surabaya, <?= date('d F Y') ?></p>
        <br><br><br>
        <p><strong>(_________________)</strong><br>Petugas</p>
    </div>

</body>
</html>
