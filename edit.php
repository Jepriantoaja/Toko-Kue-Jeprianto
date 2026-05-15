<?php
session_start();
include 'koneksi.php';

$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM barang WHERE id = $id");
$data = mysqli_fetch_assoc($query);

if (isset($_POST['update'])) {
    $nama = $_POST['nama'];
    $stok = $_POST['stok'];
    $harga = $_POST['harga'];
    mysqli_query($conn, "UPDATE barang SET nama_barang='$nama', stok='$stok', harga='$harga' WHERE id=$id");
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Barang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .card-edit { max-width: 500px; margin: 50px auto; border-radius: 12px; }
        .card-header { background-color: #ffc107; font-weight: bold; text-align: center; padding: 15px; border-radius: 12px 12px 0 0 !important; }
    </style>
</head>
<body class="bg-light">
    <div class="card card-edit shadow-sm">
        <div class="card-header">Edit Data Barang</div> 
        <div class="card-body p-4">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Barang</label>
                    <input type="text" name="nama" class="form-control" value="<?= $data['nama_barang']; ?>" required>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold">Stok</label>
                        <input type="number" name="stok" class="form-control" value="<?= $data['stok']; ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">Harga</label>
                        <input type="number" name="harga" class="form-control" value="<?= $data['harga']; ?>" required>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="update" class="btn btn-primary fw-bold">Update Data</button>
                    <a href="index.php" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>