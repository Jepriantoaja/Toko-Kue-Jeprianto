<?php
// 1. Memulai session agar sistem tahu session mana yang akan di hapus
session_start();

// 2. Menghapus semua data session yang tersimpan
session_destroy();

// 3. Mengarahkan pengguna kembali ke Halaman
echo "<script>
       alert('Anda telah berhasil Log out');
       window.location.href='login.php';
       </script>";
exit();
?>