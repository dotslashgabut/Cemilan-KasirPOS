# Implementasi JWT & CORS (Detail Teknis)

Dokumen ini menjelaskan secara rinci penerapan **JSON Web Token (JWT)** untuk autentikasi dan **Cross-Origin Resource Sharing (CORS)** untuk keamanan akses API pada aplikasi Cemilan KasirPOS.

Dokumen ini mencakup panduan konfigurasi untuk dua skenario deployment yang umum:
1.  **Subfolder Deployment** (Satu Domain)
2.  **Cross-Domain Deployment** (Beda Domain/Subdomain)

---

## 🔐 1. Implementasi JWT (JSON Web Token)

JWT digunakan untuk mengamankan komunikasi antara Frontend (React) dan Backend (PHP). Sistem ini bersifat *stateless*, artinya server tidak perlu menyimpan sesi di database untuk setiap request.

### Alur Kerja Autentikasi

1.  **Login**:
    *   User mengirim `username` dan `password` ke endpoint `/login`.
    *   Backend memverifikasi kredensial (mendukung hash `bcrypt`).
    *   Jika valid, Backend membuat **Token JWT** yang berisi data user (ID, Username, Role) dan waktu kadaluarsa (default 24 jam).
    *   Backend mengirim token ini kembali ke Frontend.

2.  **Penyimpanan Token (Frontend)**:
    *   Frontend menerima token dan menyimpannya di **LocalStorage** browser dengan key `pos_token`.
    *   *Catatan: Penyimpanan di LocalStorage memudahkan implementasi namun rentan XSS. Untuk keamanan tingkat tinggi, pertimbangkan HttpOnly Cookies di masa depan.*

3.  **Mengirim Request (Authenticated)**:
    *   Setiap kali Frontend melakukan request ke API (misal: ambil data transaksi), Frontend menyisipkan token ke dalam **Header HTTP**.
    *   Format Header: `Authorization: Bearer <token_jwt_anda>`

4.  **Validasi Token (Backend)**:
    *   Backend (`auth.php`) membaca header `Authorization`.
    *   Token didecode dan divalidasi tanda tangannya menggunakan **JWT Secret**.
    *   Jika valid dan belum expired, request diproses. Jika tidak, server merespon dengan `401 Unauthorized`.

### Konfigurasi JWT di Backend

File terkait: `php_server/auth.php`

**PENTING:** Anda **WAJIB** mengganti `JWT_SECRET` di production agar token tidak bisa dipalsukan.

**Cara Mengganti Secret:**
1.  Buka file `.env` di folder backend.
2.  Tambahkan/Edit baris:
    ```env
    JWT_SECRET=GantiStringIniDenganKarakterAcakYangSangatPanjangDanRumit!@#123
    ```

---

## 🌐 2. Implementasi CORS (Cross-Origin Resource Sharing)

CORS adalah mekanisme keamanan browser yang membatasi bagaimana halaman web di satu domain bisa meminta resource dari domain lain.

File konfigurasi: `php_server/config.php` (membaca dari `.env`)

### Kode Implementasi Saat Ini

Secara default, backend dikonfigurasi untuk fleksibilitas (Development Mode):

```php
// php_server/config.php

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Mengizinkan origin dari mana saja (kurang aman untuk production)
header("Access-Control-Allow-Origin: " . ($origin ? $origin : '*'));
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle Preflight Request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
```

---

## 🚀 3. Skenario Deployment & Konfigurasi

Berikut adalah detail konfigurasi untuk dua skenario penempatan web yang berbeda.

### Skenario A: Penempatan di Subfolder (Satu Domain)

Pada skenario ini, Frontend dan Backend berada di domain yang sama, hanya beda folder.

**Contoh URL:**
*   **Frontend**: `https://tokocemilan.com/app/` (atau di root `https://tokocemilan.com/`)
*   **Backend**: `https://tokocemilan.com/api/`

**Analisis CORS:**
*   Karena Frontend dan Backend memiliki **Origin yang sama** (Protocol, Domain, dan Port sama), maka **CORS tidak terlalu ketat**. Browser menganggap ini "Same-Origin".
*   Namun, konfigurasi CORS tetap disarankan untuk keamanan tambahan atau jika ada aset yang di-load dari subdomain lain.

**Konfigurasi yang Disarankan:**

    1.  **Backend (`php_server/.env`)**:
        Anda bisa membatasi origin secara spesifik.
        ```env
        ALLOWED_ORIGINS=https://tokocemilan.com
        ```

2.  **Frontend (`.env.production`)**:
    Set URL API relatif atau absolut.
    ```env
    VITE_API_URL=https://tokocemilan.com/api
    ```

3.  **Web Server (.htaccess)**:
    Pastikan routing Frontend tidak menabrak routing Backend.
    Lihat panduan di `README_CPANEL_HOSTING.md` bagian `.htaccess` untuk mengecualikan folder `/api` dari routing React.

---

### Skenario B: Beda Subdomain / Domain (Cross-Domain)

Skenario ini memisahkan Frontend dan Backend di domain atau subdomain berbeda. Ini adalah arsitektur modern yang umum.

**Contoh URL:**
*   **Frontend**: `https://app.tokocemilan.com` (atau `https://tokocemilan.vercel.app`)
*   **Backend**: `https://api.tokocemilan.com` (atau `https://backend-saya.com`)

**Analisis CORS:**
*   Ini adalah **Cross-Origin Request**. Browser akan memblokir request jika backend tidak mengirim header CORS yang benar.
*   Browser akan mengirim request `OPTIONS` (Preflight) terlebih dahulu sebelum mengirim request `POST`, `PUT`, atau `DELETE`. Backend harus merespon `OPTIONS` dengan status 200 OK.

**Konfigurasi yang Disarankan:**

1.  **Backend (`php_server/.env`)**:
    **Sangat Disarankan** untuk membatasi origin hanya ke domain Frontend Anda demi keamanan.

    ```env
    # php_server/.env
    
    # Daftar domain yang diizinkan (pisahkan dengan koma)
    ALLOWED_ORIGINS=https://app.tokocemilan.com,https://tokocemilan.vercel.app
    ```

2.  **Frontend (`.env.production`)**:
    Gunakan URL lengkap Backend.
    ```env
    VITE_API_URL=https://api.tokocemilan.com
    ```

3.  **Cookies (Opsional)**:
    Jika Anda berencana menggunakan Cookies di masa depan (bukan LocalStorage), Anda wajib set `Access-Control-Allow-Credentials: true` dan `Access-Control-Allow-Origin` tidak boleh `*`.

---

## ⚠️ Troubleshooting Umum

### 1. Error "CORS Policy: No 'Access-Control-Allow-Origin' header..."
*   **Penyebab**: Backend tidak mengirim header CORS, atau Origin frontend tidak ada di daftar yang diizinkan backend.
*   **Solusi**: Cek `php_server/.env`. Pastikan domain frontend Anda tertulis persis di `ALLOWED_ORIGINS`.

### 2. Error "401 Unauthorized" padahal sudah login
*   **Penyebab**: Token JWT tidak terkirim di header, atau Token expired, atau JWT Secret di backend berubah (misal setelah deploy ulang tanpa .env yang persisten).
*   **Solusi**: Cek tab Network di browser, lihat request header. Pastikan ada `Authorization: Bearer eyJ...`. Jika tidak, coba logout dan login ulang.

### 3. Error Preflight (OPTIONS) 404/500
*   **Penyebab**: Web server (Apache/Nginx) memblokir method `OPTIONS` atau routing PHP tidak menangani method tersebut.
*   **Solusi**: Pastikan blok kode penanganan `OPTIONS` di `config.php` berada di paling atas sebelum logika database atau auth.

```php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
```

---

## 🔒 4. Konfigurasi HTTPS & HSTS

### Apa itu HSTS?
**HTTP Strict Transport Security (HSTS)** adalah mekanisme kebijakan keamanan web yang memaksa browser web untuk berinteraksi dengan situs web hanya menggunakan koneksi HTTPS yang aman, dan tidak pernah melalui protokol HTTP yang tidak aman. Ini membantu mencegah serangan *protocol downgrade* dan *cookie hijacking*.

### Mengapa Penting?
1.  **Keamanan**: Mencegah pengguna mengakses versi HTTP yang tidak aman dari situs Anda.
2.  **Kepercayaan**: Meningkatkan kepercayaan pengguna dan mesin pencari (SEO).
3.  **Kepatuhan**: Memenuhi standar keamanan modern.

### Cara Mengaktifkan HSTS
Di file `php_server/config.php`, cari bagian Security Headers dan aktifkan (uncomment) baris berikut:

```php
// Security Headers
if (isset($_SERVER['REQUEST_METHOD'])) {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    
    // AKTIFKAN BARIS INI (Hapus tanda // di depannya)
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains"); 
}
```

**PENTING:**
*   **Pastikan SSL Aktif**: Jangan aktifkan HSTS jika server Anda belum memiliki sertifikat SSL (HTTPS) yang valid. Jika Anda mengaktifkannya tanpa SSL, situs Anda **tidak akan bisa diakses** sama sekali.
*   **Max-Age**: Nilai `31536000` detik setara dengan 1 tahun. Ini memberitahu browser untuk mengingat aturan ini selama satu tahun.
