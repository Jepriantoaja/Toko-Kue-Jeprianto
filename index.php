<?php
session_start();

// 1. Periksa Login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") { 
    header("Location: login.php"); 
    exit; 
}

include 'koneksi.php';

// Path library QR
$lib_path = "phpqrcode/qrlib.php";
if (file_exists($lib_path)) {
    include $lib_path;
}

$user_logged = $_SESSION['username'] ?? 'User';

if (!file_exists('temp')) { mkdir('temp', 0777, true); }

// --- FITUR TAMBAH ---
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']); 
    $stok = max(0, (int)$_POST['stok']); 
    $harga = max(0, (int)$_POST['harga']); 
    
    $query = "INSERT INTO barang (nama_barang, stok, harga) VALUES ('$nama', '$stok', '$harga')";
    if (mysqli_query($conn, $query)) { 
        header("Location: index.php?status=tambah_sukses"); 
        exit;
    }
}

// --- FITUR JUAL ---
if (isset($_POST['jual'])) {
    $id_barang = (int)$_POST['id_barang'];
    $jumlah_jual = (int)$_POST['jumlah_jual'];
    $metode = $_POST['metode_pembayaran']; 
    
    mysqli_begin_transaction($conn);
    try {
        $stmt = $conn->prepare("SELECT stok, harga FROM barang WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $id_barang);
        $stmt->execute();
        $result = $stmt->get_result();
        $data_barang = $result->fetch_assoc();

        if (!$data_barang) { throw new Exception("Barang tidak ditemukan!"); }

        if ($data_barang['stok'] >= $jumlah_jual && $jumlah_jual > 0) {
            $total_harga = $data_barang['harga'] * $jumlah_jual;
            
            $stmt_ins = $conn->prepare("INSERT INTO penjualan (id_barang, jumlah_jual, total_harga, metode_pembayaran, tanggal_transaksi) VALUES (?, ?, ?, ?, NOW())");
            $stmt_ins->bind_param("iiss", $id_barang, $jumlah_jual, $total_harga, $metode);
            $stmt_ins->execute();
            $id_transaksi = $conn->insert_id;

            $stmt_upd = $conn->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?");
            $stmt_upd->bind_param("ii", $jumlah_jual, $id_barang);
            $stmt_upd->execute();

            mysqli_commit($conn);
            header("Location: index.php?status=jual_sukses&trx_id=$id_transaksi");
            exit;
        } else {
            throw new Exception("Stok tidak mencukupi!");
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: index.php?status=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
}

// --- DATA DASHBOARD ---
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : "";
$query_sql = "SELECT * FROM barang WHERE nama_barang LIKE '%$keyword%' ORDER BY nama_barang ASC";
$barang_query = mysqli_query($conn, $query_sql);

$barang_data_array = [];
$labels = [];
$stok_data = [];
$colors = [];

while($row = mysqli_fetch_assoc($barang_query)) {
    $barang_data_array[] = $row;
    $labels[] = $row['nama_barang'];
    $stok_data[] = $row['stok'];
    $colors[] = ($row['stok'] < 10) ? 'rgba(255, 71, 87, 0.8)' : 'rgba(46, 213, 115, 0.8)';
}

$total_jenis = count($barang_data_array);
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stok) as total_stok, SUM(stok * harga) as total_aset FROM barang"));
$pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) as total_masuk FROM penjualan"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Kue Jeprianto Zai - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
    <style>
        body { 
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('Roti.jpg'); 
            background-size: cover; background-position: center; background-attachment: fixed; 
            min-height: 100vh; padding: 30px 15px; font-family: 'Segoe UI', sans-serif;
        }
        .main-container { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(15px); border-radius: 24px; padding: 40px; 
            border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            position: relative;
        }
        /* FIX MODAL STACKING ISSUE */
        .modal { z-index: 2050 !important; }
        .modal-backdrop { z-index: 2040 !important; }
        
        .card-stats { 
            background: rgba(255, 255, 255, 0.4) !important;
            backdrop-filter: blur(5px); border-radius: 18px; color: #1a1a1a !important; 
            transition: all 0.3s ease; border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .card-stats:hover { transform: translateY(-8px); background: rgba(255, 255, 255, 0.6) !important; }
        .bg-glass-blue { border-left: 6px solid #007bff; }
        .bg-glass-green { border-left: 6px solid #28a745; }
        .bg-glass-cyan { border-left: 6px solid #17a2b8; }
        .bg-glass-orange { border-left: 6px solid #fd7e14; }
        .table-glass { background: rgba(255, 255, 255, 0.5); border-radius: 15px; overflow: hidden; }
        .qr-area { display: none; text-align: center; padding: 20px; background: white; border-radius: 15px; }
        
        /* Form Control Fix */
        .form-control-lg {
            background-color: #fff !important;
            border: 2px solid #dee2e6 !important;
        }
    </style>
</head>
<body>

<div class="container main-container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="fw-bold text-dark m-0">Toko Kue <span class="text-primary">Jeprianto Zai</span></h1>
            <p class="text-secondary mb-0">Manajemen Inventaris & Penjualan</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i> Produk
            </button>
            <a href="riwayat_penjualan.php" class="btn btn-primary rounded-pill px-4 shadow-sm">Riwayat</a>
            <a href="logout.php" class="btn btn-outline-danger rounded-pill px-4" onclick="return confirm('Yakin ingin keluar?')">Logout</a>
        </div>
    </div>

    <div class="row mb-4 g-3 text-center">
        <div class="col-md-3">
            <div class="card card-stats bg-glass-blue p-3 shadow-sm">
                <small class="text-uppercase fw-bold opacity-75">Total Jenis</small>
                <h2 class="m-0 fw-bold"><?= $total_jenis; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats bg-glass-green p-3 shadow-sm">
                <small class="text-uppercase fw-bold opacity-75">Total Stok</small>
                <h2 class="m-0 fw-bold"><?= $stats['total_stok'] ?? 0; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats bg-glass-cyan p-3 shadow-sm">
                <small class="text-uppercase fw-bold opacity-75">Nilai Aset</small>
                <h4 class="m-0 fw-bold">Rp <?= number_format($stats['total_aset'] ?? 0, 0, ',', '.'); ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats bg-glass-orange p-3 shadow-sm">
                <small class="text-uppercase fw-bold opacity-75">Uang Masuk</small>
                <h4 class="m-0 fw-bold text-success">Rp <?= number_format($pendapatan['total_masuk'] ?? 0, 0, ',', '.'); ?></h4>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card p-4 shadow-sm border-0" style="background: rgba(255,255,255,0.7); border-radius: 20px;">
                <h5 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Monitor Persediaan</h5>
                <div style="height: 280px;"><canvas id="stokChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6 ms-auto">
            <form method="GET" class="d-flex gap-2">
                <div class="input-group shadow-sm" style="border-radius: 30px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="keyword" class="form-control border-0 shadow-none" placeholder="Cari kue favorit..." value="<?= htmlspecialchars($keyword); ?>">
                    <button type="submit" class="btn btn-dark px-4">Cari</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-glass shadow-sm">
        <table class="table table-hover align-middle m-0 text-center">
            <thead class="table-dark opacity-90">
                <tr>
                    <th class="py-3">No</th>
                    <th class="text-start">Nama Produk</th>
                    <th>Sisa Stok</th>
                    <th>Harga</th>
                    <th>Kelola</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach($barang_data_array as $row) : ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td class="text-start fw-bold"><?= htmlspecialchars($row['nama_barang']); ?></td>
                    <td><span class="badge rounded-pill p-2 px-3 <?= ($row['stok'] < 10) ? 'bg-danger' : 'bg-success'; ?>"><?= $row['stok']; ?> unit</span></td>
                    <td class="fw-semibold">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                    <td>
                        <div class="btn-group shadow-sm">
                            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalJual<?= $row['id']; ?>">Jual</button>
                            <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm px-3 text-white">Edit</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php foreach($barang_data_array as $row) : ?>
<div class="modal fade" id="modalJual<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 bg-primary text-white p-4" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold">Konfirmasi Penjualan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4 text-start">
                    <input type="hidden" name="id_barang" value="<?= $row['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Item: <span class="text-primary"><?= $row['nama_barang']; ?></span></label>
                        <input type="number" name="jumlah_jual" id="jumlah<?= $row['id']; ?>" class="form-control form-control-lg" min="1" max="<?= $row['stok']; ?>" required oninput="updateQR(<?= $row['id']; ?>, <?= $row['harga']; ?>)">
                        <small class="text-muted">Stok tersedia: <?= $row['stok']; ?> unit</small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Metode Bayar</label>
                        <select name="metode_pembayaran" class="form-select form-select-lg" id="metode<?= $row['id']; ?>" onchange="updateQR(<?= $row['id']; ?>, <?= $row['harga']; ?>)">
                            <option value="cash">Tunai (Cash)</option>
                            <option value="qris">QRIS / Digital Payment</option>
                        </select>
                    </div>
                    <div id="qrArea<?= $row['id']; ?>" class="qr-area shadow-sm border mb-3">
                        <p class="fw-bold text-muted small mb-2">SCAN UNTUK PEMBAYARAN</p>
                        <?php 
                            if (class_exists('QRcode')) {
                                $qr_file = "temp/qr_".$row['id'].".png";
                                QRcode::png("TRX-".$row['id']."-".time(), $qr_file, QR_ECLEVEL_L, 4);
                                echo '<img src="'.$qr_file.'?v='.time().'" class="img-fluid border mb-2" style="max-width:140px;">';
                            }
                        ?>
                        <div class="p-2 rounded bg-light border">
                            <h3 class="text-success fw-bold m-0" id="totalTampil<?= $row['id']; ?>">Rp 0</h3>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" name="jual" class="btn btn-success btn-lg w-100 fw-bold rounded-pill">PROSES SEKARANG</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 bg-success text-white p-4" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold">Tambah Produk Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Nama Kue/Roti</label>
                        <input type="text" name="nama" class="form-control form-control-lg" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-secondary">Stok Awal</label>
                            <input type="number" name="stok" class="form-control form-control-lg" min="0" value="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-secondary">Harga Jual</label>
                            <input type="number" name="harga" class="form-control form-control-lg" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" name="tambah" class="btn btn-success btn-lg w-100 fw-bold rounded-pill">SIMPAN PRODUK</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'jual_sukses') {
        Swal.fire({
            icon: 'success', title: 'Transaksi Berhasil!', text: 'Ingin cetak nota sekarang?',
            showCancelButton: true, confirmButtonText: 'Ya, Cetak'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = 'cetak_nota.php?id=' + urlParams.get('trx_id');
        });
    }

    if (urlParams.get('status') === 'tambah_sukses') Swal.fire('Berhasil!', 'Produk telah ditambahkan.', 'success');
    if (urlParams.get('status') === 'error') Swal.fire('Oops...', decodeURIComponent(urlParams.get('msg')), 'error');

    // Chart
    const ctx = document.getElementById('stokChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels); ?>,
            datasets: [{
                label: 'Unit Persediaan',
                data: <?= json_encode($stok_data); ?>,
                backgroundColor: <?= json_encode($colors); ?>,
                borderRadius: 10, barThickness: 30,
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    function updateQR(id, harga) {
        let jml = document.getElementById('jumlah' + id).value;
        let metode = document.getElementById('metode' + id).value;
        let qrArea = document.getElementById('qrArea' + id);
        let total = (jml > 0) ? jml * harga : 0;
        let format = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(total);
        document.getElementById('totalTampil' + id).innerText = format;
        qrArea.style.display = (metode === 'qris' && jml > 0) ? 'block' : 'none';
    }
</script>
</body>
</html>