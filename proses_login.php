<?php 
session_start();
include 'koneksi.php'; 

if (isset($_POST['login'])) {
    // Mengamankan input username
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // PERBAIKAN: Nama tabel diubah menjadi 'users' (pakai s) sesuai database kamu
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        // Jika data ditemukan, set session dan lempar ke halaman index
        $_SESSION['username'] = $username;
        $_SESSION['status'] = "login";
        header("location:index.php");
        exit(); 
    } else {
        // Jika data tidak cocok atau tabel tidak ditemukan
        echo "<script>alert('Login Gagal! Username atau Password salah.'); window.location='login.php';</script>";
    }
}
?>