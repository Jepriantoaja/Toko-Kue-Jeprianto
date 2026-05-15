<?php
session_start();

// 1. PERBAIKAN LOGIKA SESSION (Disamakan dengan index.php)
// Menggunakan 'status' sesuai dengan pengecekan di index.php
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") { 
    header("Location: login.php"); 
    exit; 
}

include 'koneksi.php';

// Mengambil username dari session yang diset saat login
$username_session = $_SESSION['username'] ?? 'User'; 
$pesan = "";

// --- LOGIKA UBAH PASSWORD ---
if (isset($_POST['update_password'])) {
    // Menggunakan mysqli_real_escape_string untuk keamanan tambahan
    $password_baru = mysqli_real_escape_string($conn, $_POST['password_baru']);
    $konfirmasi = mysqli_real_escape_string($conn, $_POST['konfirmasi_password']);

    if ($password_baru === $konfirmasi) {
        // PERHATIAN: Pastikan di login.php juga menggunakan password_hash. 
        // Jika login.php masih menggunakan teks biasa (MD5 atau tanpa enkripsi), 
        // sesuaikan bagian ini.
        $hash_password = password_hash($password_baru, PASSWORD_DEFAULT);
        
        $query = "UPDATE users SET password = '$hash_password' WHERE username = '$username_session'";
        
        if (mysqli_query($conn, $query)) {
            $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        Password berhasil diperbarui!
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        } else {
            $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        Gagal memperbarui database: " . mysqli_error($conn) . "
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        }
    } else {
        $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    Konfirmasi password tidak cocok!
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - <?= htmlspecialchars($username_session); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { 
            /* Menggunakan tema background yang senada dengan dashboard */
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('Roti.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .profile-card { 
            border-radius: 20px; 
            overflow: hidden; 
            border: none; 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .profile-header { 
            background: #212529; 
            height: 100px; 
        }
        .profile-img { 
            width: 120px; 
            height: 120px; 
            margin-top: -60px; 
            border: 5px solid white; 
            background-color: white; 
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 500px;">
        
        <?= $pesan; ?>

        <div class="card shadow-lg profile-card">
            <div class="profile-header text-end p-3">
                <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Tutup</a>
            </div>

            <div class="card-body text-center pb-5 pt-0">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($username_session); ?>&background=0d6efd&color=fff&size=128" 
                     class="rounded-circle profile-img shadow-sm mb-3">
                
                <h3 class="fw-bold text-dark mb-1"><?= htmlspecialchars($username_session); ?></h3>
                <p class="text-muted mb-4 small">Pengelola Toko Makanan</p>
                
                <div class="text-start px-3">
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold">Username Akun</label>
                        <input type="text" class="form-control bg-light border-0 py-2" value="<?= htmlspecialchars($username_session); ?>" readonly>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small text-muted text-uppercase fw-bold">Keamanan</label>
                        <button type="button" class="btn btn-primary w-100 py-2" data-bs-toggle="modal" data-bs-target="#modalPassword">
                            Ganti Password
                        </button>
                    </div>
                    
                    <div class="d-grid">
                        <a href="index.php" class="btn btn-outline-secondary">
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPassword" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Ganti Password Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password Baru</label>
                            <input type="password" name="password_baru" class="form-control" required placeholder="Min. 6 karakter">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Konfirmasi Password</label>
                            <input type="password" name="konfirmasi_password" class="form-control" required placeholder="Ulangi password">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_password" class="btn btn-primary px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>