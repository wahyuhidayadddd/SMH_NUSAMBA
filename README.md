# Website NUSAMBA

Website ini menggunakan Laravel 12. Halaman website lama disimpan sebagai konten HTML yang dapat dikelola melalui CMS, sementara aset statis berada di `public/assets`.

## Persiapan

Kebutuhan: PHP 8.2+, Composer, ekstensi PHP `pdo_mysql`, dan MySQL/MariaDB.

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
```

Buat database kosong untuk CMS, lalu isi `.env` dengan koneksi MySQL/MariaDB dan kredensial admin:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nusamba_cms
DB_USERNAME=nama-pengguna-mysql
DB_PASSWORD=kata-sandi-mysql
CMS_ADMIN_EMAIL=admin@example.com
CMS_ADMIN_PASSWORD=ganti-dengan-kata-sandi-kuat
```

Jalankan migrasi dan impor halaman awal:

```powershell
php artisan config:clear
php artisan migrate --seed
```

CMS dan konten website menggunakan database tersebut. Pengujian otomatis tetap memakai SQLite in-memory dan tidak menyentuh database lokal. Jangan gunakan akun MySQL tanpa kata sandi atau kata sandi admin sederhana di lingkungan produksi; setelah mengubah `.env`, jalankan `php artisan config:clear`.

## Menjalankan

```powershell
php artisan serve
```

Website tersedia di `http://127.0.0.1:8000` dan CMS di `http://127.0.0.1:8000/admin`. CMS memakai satu layar bertab untuk semua halaman. Pilih halaman, klik teks pada pratinjau untuk mengubahnya, gunakan toolbar format, atau pilih gambar untuk mengganti gambar dan teks alternatifnya. Atur judul, deskripsi pencarian, serta status publikasi, lalu simpan. Pratinjau dapat diubah ke ukuran desktop, tablet, atau ponsel. Gambar yang diunggah disimpan di `public/assets/uploads/`.

## Struktur

- `app/`, `routes/`, `database/`: logika aplikasi Laravel, routing, dan penyimpanan CMS.
- `resources/content/pages/`: sumber HTML awal yang dikelompokkan ke folder `beranda/`, `layanan/`, `publikasi/`, `tentang/`, dan kategori lainnya.
- `config/cms-pages.php`: pemetaan sumber HTML yang menjaga path halaman CMS dan URL website tetap sama meskipun nama atau folder sumber dirapikan.
- `resources/content/blog.json`: data blog asli.
- `resources/content/original-source.zip`: arsip sumber sebelum migrasi.
- `public/assets/`: CSS, JavaScript, gambar, video, dan aset publik lainnya.
- `resources/views/cms/`: antarmuka admin.

Untuk mengimpor ulang halaman dari sumber HTML, jalankan `php artisan db:seed --class=Database\\Seeders\\CmsPageSeeder`. Perintah ini menyinkronkan halaman berdasarkan path lama yang dipertahankan oleh pemetaan `config/cms-pages.php` dan akan menimpa perubahan CMS untuk halaman yang diimpor.
