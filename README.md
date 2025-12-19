# Sistem_Penjualan_PtWSU
Aplikasi web pencatatan penjualan

Sistem Informasi Pencatatan Penjualan Berbasis Web ini dikembangkan sebagai solusi atas permasalahan pencatatan transaksi penjualan yang sebelumnya masih dilakukan secara manual menggunakan nota kertas dan spreadsheet sederhana. Proses manual tersebut berpotensi menimbulkan berbagai permasalahan, seperti kesalahan pencatatan, keterlambatan penyusunan laporan, serta kesulitan dalam memperoleh informasi penjualan secara cepat dan akurat.

Aplikasi ini dirancang untuk membantu perusahaan dalam mengelola data penjualan secara terkomputerisasi sehingga dapat meningkatkan efisiensi operasional, akurasi data, serta kecepatan dalam penyajian laporan penjualan yang dibutuhkan oleh pihak manajemen sebagai dasar pengambilan keputusan.

## Fitur Sistem
Fitur-fitur utama yang tersedia dalam sistem informasi ini meliputi:
- Autentikasi pengguna (login dan logout)
- Pengelolaan data produk
- Pencatatan transaksi penjualan
- Pengelolaan stok barang
- Penyajian laporan penjualan
- Ekspor laporan penjualan ke dalam format PDF

## Teknologi yang Digunakan
Aplikasi web ini dibangun dengan menggunakan teknologi sebagai berikut:
- Bahasa Pemrograman : PHP
- Basis Data : MySQL
- Antarmuka Pengguna : HTML, CSS, dan JavaScript
- Library Pendukung : FPDF

## Cara Menjalankan Aplikasi
Langkah-langkah untuk menjalankan aplikasi ini adalah sebagai berikut:
1. Pastikan perangkat telah terpasang web server lokal seperti XAMPP
2. Salin seluruh folder aplikasi ke dalam direktori htdocs
3. Lakukan konfigurasi koneksi basis data pada file konfigurasi yang digunakan
4. Jalankan aplikasi melalui browser

## Keterangan Folder Vendor
Aplikasi ini menggunakan library pihak ketiga untuk mendukung pembuatan laporan penjualan dalam format PDF. Folder `vendor` disertakan dalam bentuk file terkompresi (`vendor.zip`) karena keterbatasan unggah file di GitHub.  
Sebelum aplikasi dijalankan, file `vendor.zip` perlu diekstrak terlebih dahulu ke dalam folder `vendor` agar library dapat digunakan dengan baik oleh sistem.

## Tujuan Pengembangan
Pengembangan sistem informasi ini bertujuan untuk:
- Mengurangi kesalahan pencatatan transaksi penjualan
- Mempercepat proses pengolahan dan pelaporan data penjualan
- Meningkatkan akurasi dan keamanan data
- Mendukung pengambilan keputusan manajemen berdasarkan informasi yang dihasilkan oleh sistem

## Keterangan
Repository ini disediakan sebagai pendukung keperluan akademik dan tugas akhir. Seluruh source code bersifat Public agar sesuai dengan ketentuan pengumpulan yang berlaku.
