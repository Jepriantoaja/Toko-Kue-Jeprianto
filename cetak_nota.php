<?php
include 'koneksi.php';

// Pastikan ada ID yang dikirim
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

// Menggunakan Prepared Statement untuk keamanan extra
$id_jual = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT penjualan.*, barang.nama_barang, barang.harga 
                       FROM penjualan 
                       JOIN barang ON penjualan.id_barang = barang.id 
                       WHERE penjualan.id = ?");
$stmt->bind_param("i", $id_jual);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<script>alert('Transaksi tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota #<?= $data['id']; ?> - Toko Kue Jeprianto Zai</title>
    <style>
        body { 
            font-family: 'Courier New', Courier, monospace; 
            width: 280px; 
            font-size: 12px; 
            margin: 0 auto; 
            padding: 10px; 
            color: #000;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-bottom: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        .item-name { text-transform: uppercase; font-weight: bold; }
        
        /* Tombol Dashboard & Riwayat */
        .no-print { margin-top: 20px; }
        .btn-action { 
            padding: 8px 15px; 
            background: #000; 
            color: #fff; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block;
            font-family: sans-serif;
            font-size: 11px;
            margin: 2px;
        }

        @media print { 
            .no-print { display: none; } 
            body { width: 100%; padding: 0; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center">
        <strong style="font-size: 16px;">TOKO KUE JEPRIANTO ZAI</strong><br>
        Jl. Makanan Enak No. 123<br>
        Telp: 0812-XXXX-XXXX
        <div class="line"></div>
    </div>
    
    <table>
        <tr>
            <td>ID Transaksi:</td>
            <td class="text-right">#INV<?= str_pad($data['id'], 5, '0', STR_PAD_LEFT); ?></td>
        </tr>
        <tr>
            <td>Kasir:</td>
            <td class="text-right">Admin Kasir</td>
        </tr>
        <tr>
            <td>Metode:</td>
            <td class="text-right"><?= strtoupper($data['metode_pembayaran']); ?></td>
        </tr>
        <tr>
            <td>Waktu:</td>
            <td class="text-right">
                <?= date('d/m/Y H:i', strtotime($data['tanggal_transaksi'])); ?>
            </td>
        </tr>
    </table>
    
    <div class="line"></div>
    
    <table>
        <tr>
            <td colspan="2" class="item-name"><?= htmlspecialchars($data['nama_barang']); ?></td>
        </tr>
        <tr>
            <td><?= $data['jumlah_jual']; ?> x Rp <?= number_format($data['harga'], 0, ',', '.'); ?></td>
            <td class="text-right">Rp <?= number_format($data['total_harga'], 0, ',', '.'); ?></td>
        </tr>
    </table>
    
    <div class="line"></div>
    
    <table style="font-weight: bold; font-size: 14px;">
        <tr>
            <td>TOTAL BAYAR</td>
            <td class="text-right">Rp <?= number_format($data['total_harga'], 0, ',', '.'); ?></td>
        </tr>
    </table>
    
    <div class="line"></div>
    
    <div class="text-center" style="margin-top: 15px;">
        <?php 
        // Menampilkan QR Code Transaksi jika filenya ada
        $qr_file = "temp/qr_".$data['id'].".png";
        if(file_exists($qr_file)) {
            echo '<img src="'.$qr_file.'" style="width: 80px; filter: grayscale(100%); opacity: 0.8;"><br>';
        }
        ?>
        Terima Kasih Atas Pembeliannya!<br>
        --- Barang yang sudah dibeli ---<br>
        tidak dapat ditukar/dikembalikan
    </div>

    <div class="no-print text-center">
        <hr>
        <button onclick="window.print()" class="btn-action">Cetak Ulang</button>
        <a href="index.php" class="btn-action" style="background: #0d6efd;">Dashboard</a>
        <a href="riwayat_penjualan.php" class="btn-action" style="background: #6c757d;">Riwayat</a>
    </div>
</body>
</html>