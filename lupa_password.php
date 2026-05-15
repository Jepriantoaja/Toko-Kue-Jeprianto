<?php
include 'koneksi.php';

$step = 1; // Langkah 1: Verifikasi User
$user_id = "";
$error = "";

// PROSES LANGKAH 1: CEK USERNAME
if (isset($_POST['cek_user'])) {
    // Gunakan $koneksi, bukan $conn atau yang lain
    $username = mysqli_real_escape_string($koneksi, $_POST['username']); 
    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    
    // Tambahkan pengecekan ini agar tidak fatal error jika query salah
    if ($result) {
        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            $user_id = $row['id'];
            $step = 2;
        } else {
            $error = "Username tidak ditemukan di sistem!";
        }
    } else {
        $error = "Error Query: " . mysqli_error($koneksi);
    }
}
// PROSES LANGKAH 2: UPDATE PASSWORD
if (isset($_POST['reset_password'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $password_baru = mysqli_real_escape_string($conn, $_POST['password_baru']);
    $konfirmasi = mysqli_real_escape_string($conn, $_POST['konfirmasi']);

    if ($password_baru === $konfirmasi) {
        // Update database
        $update = mysqli_query($koneksi, "UPDATE users SET password = '$password_baru' WHERE id = '$id'");
        
        if($update) {
            echo "<script>
                    alert('Password berhasil diperbarui! Silakan login kembali.');
                    window.location.href='login.php';
                  </script>";
            exit; 
        } else {
            $error = "Gagal memperbarui database.";
        }
    } else {
        $error = "Konfirmasi password tidak cocok!";
        $user_id = $id;
        $step = 2; // Tetap di form password baru
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card-reset { max-width: 400px; border-radius: 12px; border: none; }
        .input-group-text { cursor: pointer; background-color: #fff; }
    </style>
</head>
<body class="d-flex align-items-center" style="height: 100vh;">
    <div class="container">
        <div class="card card-reset shadow-sm mx-auto p-4">
            <h4 class="text-center mb-4 fw-bold">Reset Password</h4>

            <?php if($error) : ?>
                <div class='alert alert-danger small text-center'><?= $error; ?></div>
            <?php endif; ?>

            <?php if($step == 1) : ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Masukkan Username Anda</label>
                        <input type="text" name="username" class="form-control" placeholder="Username" required autocomplete="off">
                    </div>
                    <button type="submit" name="cek_user" class="btn btn-primary w-100 fw-bold">Cek Akun</button>
                </form>

            <?php else : ?>
                <form method="post">
                    <input type="hidden" name="id" value="<?= $user_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="password_baru" id="pass1" class="form-control" placeholder="Password Baru" required>
                            <span class="input-group-text" onclick="togglePass('pass1', 'icon1')">
                                <i class="fa fa-eye" id="icon1"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="konfirmasi" id="pass2" class="form-control" placeholder="Ulangi Password" required>
                            <span class="input-group-text" onclick="togglePass('pass2', 'icon2')">
                                <i class="fa fa-eye" id="icon2"></i>
                            </span>
                        </div>
                    </div>
                    
                    <button type="submit" name="reset_password" class="btn btn-success w-100 fw-bold">Perbarui Password</button>
                </form>
            <?php endif; ?>

            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none small">Kembali ke Login</a>
            </div>
        </div>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
</body>
</html>