<?php
session_start();
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Ambil data terbaru
$query = "SELECT p.*, b.nama_barang 
          FROM penjualan p 
          JOIN barang b ON p.id_barang = b.id 
          ORDER BY p.tanggal_transaksi DESC, p.id DESC"; 
$riwayat = mysqli_query($conn, $query);

if (!$riwayat) {
    die("Query Error: " . mysqli_error($conn));
}

// Kueri total omzet
$query_total = "SELECT SUM(total_harga) as grand_total FROM penjualan";
$result_total = mysqli_query($conn, $query_total);
$data_total = mysqli_fetch_assoc($result_total);
$grand_total = $data_total['grand_total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Penjualan - Toko Kue Jeprianto Zai</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { 
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('Roti.jpg'); 
            background-size: cover; background-position: center; background-attachment: fixed; 
            min-height: 100vh; padding: 40px 20px; font-family: 'Segoe UI', sans-serif;
        }

        .main-container { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px; 
            padding: 40px; 
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4); 
            max-width: 1100px; margin: auto;
        }

        .header-title { border-bottom: 2px solid rgba(0,0,0,0.05); padding-bottom: 20px; margin-bottom: 30px; }
        
        .table-responsive {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            overflow: hidden;
        }

        .table thead th {
            background-color: rgba(33, 37, 41, 0.9); color: #ffffff;
            text-transform: uppercase; font-size: 11px; letter-spacing: 1px; padding: 15px; border: none;
        }

        .total-section {
            background: rgba(255, 255, 255, 0.6); 
            backdrop-filter: blur(5px);
            border-radius: 12px; padding: 15px 25px;
            margin-top: 20px; border-left: 5px solid #198754;
        }

        .btn-nota {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            padding: 6px 15px; border-radius: 6px; transition: 0.3s;
        }

        .badge-payment { padding: 5px 12px; border-radius: 4px; font-size: 10px; font-weight: 700; }
        .bg-cash { background-color: rgba(227, 252, 239, 0.9); color: #006644; }
        .bg-qris { background-color: rgba(224, 242, 254, 0.9); color: #0369a1; }
        
        @media print {
            .btn, .btn-nota, .header-title a, .no-print { display: none !important; }
            body { background: white !important; padding: 0; }
            .main-container { box-shadow: none; border: none; width: 100%; max-width: 100%; background: white; }
        }
    </style>
</head>
<body>

<div class="main-container shadow-lg">
    <div class="header-title d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark m-0">Riwayat Transaksi</h2>
            <p class="text-secondary small mb-0">Laporan aktivitas penjualan <strong>Toko Kue Jeprianto Zai</strong></p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm rounded-pill px-4">
                Cetak Laporan
            </button>
            <a href="index.php" class="btn btn-dark btn-sm rounded-pill px-4">
                Dashboard
            </a>
        </div>
    </div>

    <div class="table-responsive shadow-sm">
        <table class="table align-middle text-center table-hover m-0">
            <thead>
                <tr>
                    <th>NO</th>
                    <th class="text-start">TANGGAL</th>
                    <th class="text-start">NAMA PRODUK</th>
                    <th>KUANTITAS</th>
                    <th>TOTAL</th>
                    <th>METODE</th>
                    <th class="no-print">OPSI</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if (mysqli_num_rows($riwayat) > 0) :
                    while($row = mysqli_fetch_assoc($riwayat)) : 
                        $metode = strtolower($row['metode_pembayaran']);
                        $badgeClass = ($metode == 'qris') ? 'bg-qris' : 'bg-cash';
                        
                        $raw_date = $row['tanggal_transaksi'] ?? $row['tanggal_jual'] ?? date('Y-m-d H:i:s');
                        $tanggal = date('d M Y, H:i', strtotime($raw_date));
                ?>
                <tr>
                    <td class="text-muted fw-bold"><?= $no++; ?></td>
                    <td class="text-start small text-dark"><?= $tanggal; ?></td>
                    <td class="fw-bold text-start text-dark"><?= htmlspecialchars($row['nama_barang']); ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $row['jumlah_jual']; ?> Unit</span></td>
                    <td class="fw-bold text-dark">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                    <td><span class="badge-payment <?= $badgeClass; ?>"><?= strtoupper($metode); ?></span></td>
                    <td class="no-print">
                        <a href="cetak_nota.php?id=<?= $row['id']; ?>" target="_blank" class="btn btn-outline-primary btn-nota">
                            Nota
                        </a>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else : 
                ?>
                <tr>
                    <td colspan="7" class="py-5 text-muted fst-italic">Belum ada transaksi terekam untuk Toko Kue Jeprianto Zai.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <div class="total-section d-flex align-items-center gap-4 shadow-sm">
            <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">Total Omzet :</span>
            <h4 class="fw-bold text-success m-0">Rp <?= number_format($grand_total, 0, ',', '.'); ?></h4>
        </div>
    </div>
</div>

</body>
</html>