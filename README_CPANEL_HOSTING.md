# Panduan Hosting ke cPanel (Frontend React + Backend PHP)

Panduan ini menjelaskan cara meng-hosting aplikasi Cemilan KasirPOS menggunakan **Backend PHP Native** (Rekomendasi Utama) ke shared hosting cPanel.

> **Catatan:** Jika Anda ingin menggunakan Backend Node.js, silakan lihat bagian [Alternatif: Backend Node.js](#alternatif-backend-nodejs) di bawah.

## 📋 Prasyarat

1.  Akses ke cPanel hosting.
2.  Hosting mendukung **PHP 7.4** atau **PHP 8.x**.
3.  Domain atau subdomain yang aktif (misal: `tokocemilan.com`).
4.  Database MySQL yang sudah dibuat di cPanel.

## 🏗️ Langkah 1: Persiapan Database

1.  Login ke cPanel.
2.  Buka **MySQL Database Wizard**.
3.  Buat database baru (contoh: `u12345_cemilan`).
4.  Buat user database baru (contoh: `u12345_admin`) dan passwordnya.
5.  **PENTING**: Berikan hak akses **ALL PRIVILEGES** user tersebut ke database yang baru dibuat.
6.  Buka **phpMyAdmin**, pilih database tadi, lalu Import file `cemilankasirpos.sql` (atau file sql terbaru) yang ada di folder proyek ini.

## ⚙️ Langkah 2: Upload Backend (PHP)

1.  Buka **File Manager** di cPanel.
2.  Masuk ke folder `public_html`.
3.  Buat folder baru bernama `api`.
4.  Upload semua file dari folder `php_server` di komputer Anda ke dalam folder `public_html/api` tersebut.
    *   Pastikan file `index.php`, `config.php`, `auth.php`, dll terupload.
5.  **Konfigurasi Database**:
    *   Edit file `config.php` di dalam folder `api` tersebut.
    *   Sesuaikan bagian berikut dengan database yang Anda buat di Langkah 1:
    ```php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u12345_cemilan'); // Sesuaikan nama DB
    define('DB_USER', 'u12345_admin');   // Sesuaikan user DB
    define('DB_PASS', 'password_anda');  // Sesuaikan password
    ```
    *   Simpan perubahan.

## 🖥️ Langkah 3: Build & Upload Frontend (React)

1.  **Edit Environment Variable Frontend**:
    *   Buka file `.env.production` di komputer Anda (atau buat jika belum ada).
    *   Ubah `VITE_API_URL` agar mengarah ke folder API PHP Anda.
    *   Contoh: `VITE_API_URL=https://tokocemilan.com/api`
    *   *Catatan: Pastikan URL ini benar dan bisa diakses.*

2.  **Build Project**:
    *   Buka terminal di root project.
    *   Jalankan: `npm run build`.
    *   Folder `dist` akan terupdate dengan file hasil build.

3.  **Upload ke cPanel**:
    *   Buka **File Manager**.
    *   Masuk ke folder `public_html`.
    *   Upload **semua isi** dari folder `dist` ke sini (sejajar dengan folder `api` yang tadi dibuat).
    *   Struktur folder Anda di `public_html` akan terlihat seperti ini:
        *   `/api` (Folder backend PHP)
        *   `/assets` (Folder aset frontend)
        *   `index.html` (File utama frontend)
        *   `vite.svg`
        *   ...

4.  **Konfigurasi .htaccess untuk React Router**:
    *   Buat atau edit file `.htaccess` di folder `public_html`.
    *   Isi dengan kode berikut agar refresh halaman tidak 404:
    ```apache
    <IfModule mod_rewrite.c>
      RewriteEngine On
      RewriteBase /
      RewriteRule ^index\.html$ - [L]
      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteCond %{REQUEST_FILENAME} !-d
      RewriteCond %{REQUEST_URI} !^/api/ [NC]
      RewriteRule . /index.html [L]
    </IfModule>
    ```
    *   *Perhatikan baris `RewriteCond %{REQUEST_URI} !^/api/ [NC]`: Ini penting agar request ke folder `/api` tidak dialihkan ke React.*

## ✅ Langkah 4: Testing

1.  Buka website frontend Anda (misal: `https://tokocemilan.com`).
2.  Coba login (Default: `superadmin` / `password`).
3.  Jika berhasil login dan data tampil, berarti Frontend sukses berkomunikasi dengan Backend PHP.

---

# <a id="alternatif-backend-nodejs"></a>Alternatif: Backend Node.js

Jika Anda lebih memilih menggunakan Node.js (memerlukan fitur "Setup Node.js App" di cPanel), ikuti langkah berikut:

## ⚙️ Langkah 1 (Node.js): Upload Backend

1.  Buka **File Manager** di cPanel.
2.  Buat folder baru di luar `public_html` agar lebih aman (misal: `/home/u12345/cemilan-backend-node`).
3.  Upload semua isi dari folder `server` di komputer Anda ke dalam folder tersebut.
    *   **JANGAN** upload folder `node_modules`.
4.  Buat file `.env` di dalam folder backend tersebut dan isi konfigurasi database.

## 🚀 Langkah 2 (Node.js): Konfigurasi di cPanel

1.  Di dashboard cPanel, cari dan buka menu **Setup Node.js App**.
2.  Klik **Create Application**.
3.  Isi form:
    *   **Application Root**: `cemilan-backend-node`.
    *   **Application URL**: `api.tokocemilan.com` (atau subfolder).
    *   **Startup File**: `index.js`.
4.  Klik **Create** lalu **Run NPM Install**.

## 🖥️ Langkah 3 (Node.js): Build Frontend

1.  Ubah `.env.production` di lokal: `VITE_API_URL=https://api.tokocemilan.com` (sesuaikan dengan URL Node.js App Anda).
2.  Jalankan `npm run build`.
3.  Upload isi folder `dist` ke `public_html`.
4.  Setup `.htaccess` standar React (tanpa pengecualian `/api` jika API beda domain).

## 🛡️ Troubleshooting

1.  **API Error / Network Error**:
    *   Cek Console browser (F12).
    *   Coba akses URL API langsung di browser (misal: `https://tokocemilan.com/api/index.php`). Jika muncul JSON error atau blank, berarti PHP jalan. Jika 404, cek letak folder.
2.  **Database Connection Error**:
    *   Cek file `php_error.log` di dalam folder `api` (jika ada).
    *   Pastikan user DB dan password di `config.php` sudah benar.
