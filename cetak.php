<?php
session_start();

// PERBAIKAN: Ubah 'login' menjadi 'status' agar sesuai dengan index.php
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") { 
    header("Location: login.php"); 
    exit; 
}

include 'koneksi.php';

// Ambil semua data barang
$query = mysqli_query($conn, "SELECT * FROM barang ORDER BY nama_barang ASC");

// Hitung statistik ringkas
$stats_query = mysqli_query($conn, "SELECT SUM(stok) as total_stok, SUM(stok * harga) as total_aset FROM barang");
$stats = mysqli_fetch_assoc($stats_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Barang - Toko Makanan</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; text-transform: uppercase; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 50px; text-align: right; float: right; width: 200px; }
        
        /* Tombol Navigasi */
        .no-print { 
            background: #f4f4f4; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            border: 1px solid #ddd;
        }
        .btn {
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 4px;
            border: none;
            font-weight: bold;
        }
        .btn-print { background: #28a745; color: white; }
        .btn-close { background: #dc3545; color: white; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
        <button class="btn btn-close" onclick="window.close()">Tutup Halaman</button>
        <p style="margin-top: 10px; font-size: 11px; color: #666;">*Gunakan Ctrl+P jika tombol tidak berfungsi</p>
    </div>

    <div class="header">
        <h1 style="margin: 0; letter-spacing: 2px;">LAPORAN DATA STOK MAKANAN</h1>
        <p style="margin: 5px 0; font-size: 14px;">Toko Makanan Jeprianto Zai</p>
        <p style="margin: 0;">Tanggal Laporan: <?= date('d/m/Y H:i'); ?> | Dicetak oleh: <?= htmlspecialchars($_SESSION['username']); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th>Nama Makanan</th>
                <th width="15%" class="text-center">Stok</th>
                <th width="20%" class="text-right">Harga Satuan</th>
                <th width="25%" class="text-right">Subtotal Aset</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($query) > 0) {
                while($row = mysqli_fetch_assoc($query)) : 
                    $subtotal = $row['stok'] * $row['harga'];
            ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                <td class="text-center"><?= $row['stok']; ?></td>
                <td class="text-right">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                <td class="text-right">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
            </tr>
            <?php 
                endwhile; 
            } else {
                echo "<tr><td colspan='5' class='text-center'>Data tidak ditemukan</td></tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #eee; font-weight: bold;">
                <td colspan="2" class="text-right">TOTAL KESELURUHAN</td>
                <td class="text-center"><?= number_format($stats['total_stok'] ?? 0, 0, ',', '.'); ?></td>
                <td></td>
                <td class="text-right">Rp <?= number_format($stats['total_aset'] ?? 0, 0, ',', '.'); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Bekasi, <?= date('d F Y'); ?></p>
        <br><br><br>
        <p><strong>( ____________________ )</strong></p>
        <p>Admin Toko (<?= htmlspecialchars($_SESSION['username']); ?>)</p>
    </div>

</body>
</html>