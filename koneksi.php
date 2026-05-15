<?php
// Gunakan port 3307 sesuai pengaturan XAMPP kamu
$koneksi = mysqli_connect("localhost:3307", "root", "", "db_uas");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Tambahkan baris ini agar variabel $conn di index.php tidak error lagi
$conn = $koneksi; 
?>