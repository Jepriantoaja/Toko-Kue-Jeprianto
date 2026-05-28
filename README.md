# Sistem Inventaris Toko Kue Jeprianto

Aplikasi berbasis web untuk mengelola sistem inventarisasi dan penjualan pada Toko Kue. Proyek ini dibuat menggunakan bahasa pemrograman PHP native untuk memenuhi tugas pemrograman Web.

Fitur Utama
* **Autentikasi Pengguna:** Sistem Login, Logout, dan Lupa Password yang aman (`login.php`, `lupa_password.php`).
* **Manajemen Profil:** Pengaturan profil pengguna (`profil.php`).
* **Sistem Penjualan & Inventaris:** Pencatatan dan monitoring stok barang dagangan (Kue/Roti).
* **Riwayat Transaksi:** Halaman log atau riwayat penjualan yang terstruktur (`riwayat_penjualan.php`).
* **Cetak Nota & Laporan:** Fitur untuk mencetak nota transaksi secara langsung (`cetak.php`, `cetak_nota.php`).
* **Integrasi QR Code:** Menggunakan pustaka PHP QR Code untuk sistem identifikasi atau pelacakan cepat (`phpqrcode`).

Teknologi yang Digunakan
* **Bahasa Pemrograman:** PHP (Native)
* **Database:** MySQL
* **Antarmuka (Frontend):** HTML, CSS, JavaScript
* **Library Tambahan:** phpqrcode

Cara Menjalankan Proyek di Lokal
1. Download atau *clone* repositori ini.
2. Pindahkan folder proyek ke dalam direktori server lokal Anda (misal: `xampp/htdocs/`).
3. Import database MySQL (pastikan untuk memeriksa konfigurasi di `koneksi.php`).
4. Jalankan aplikasi melalui browser dengan mengakses `http://localhost/Toko-Kue-Jeprianto/login.php`.
