# Sistem Absensi Karyawan Berbasis Website

Aplikasi web untuk mengelola absensi karyawan menggunakan QR Code dan GPS. Dibangun dengan **Laravel** dan **MySQL** (XAMPP). Implementasi lengkap meliputi **Bagian 1: Dasar Sistem & Admin**, **Bagian 2: Proses Absensi Karyawan**, dan **Bagian 3: Laporan, Export, Statistik & Keamanan**.

---

## Fitur Bagian 1 (Selesai)

- Autentikasi Admin & Karyawan (login, logout, session, password hashing)
- Role & authorization: `admin` dan `employee` (karyawan/user biasa ditolak akses ke `/admin/*` dengan 403)
- Dashboard Admin (total karyawan, karyawan aktif, total lokasi, lokasi aktif, statistik Hadir/Terlambat/Tidak Hadir hari ini, lokasi aktif terbaru, karyawan terbaru)
- CRUD Data Karyawan (search nama/nomor/email, filter status, pagination, aktivasi/nonaktif, hapus)
- CRUD Lokasi Absensi (nama, alamat, latitude, longitude, radius, status)
- QR Code dibuat langsung oleh Laravel (`endroid/qr-code`) — format SVG
- Public token acak & unik (40 karakter, bukan ID database)
- Generate QR Baru → token diganti, token lama langsung tidak berlaku, data lokasi & riwayat tetap ada
- Aktif/Nonaktif lokasi
- Halaman QR yang dapat dicetak (print), tampilan perbesar (modal)
- CRUD Jadwal Kerja (nama, hari, jam mulai/selesai, toleransi, status)
- Halaman Profil Admin (ubah nama, email, password dengan verifikasi password lama)
- Halaman publik `/absensi/scan/{public_token}` — menampilkan informasi lokasi

## Fitur Upgrade Lokasi Absensi (Peta Interaktif)

- **GPS Otomatis**: tombol **"📍 Deteksi Lokasi Saya"** memakai Browser Geolocation API (`enableHighAccuracy`, timeout 10 dtk); koordinat Admin langsung menjadi center peta. Penanganan error lengkap: permission ditolak, posisi tidak tersedia, timeout, browser tidak mendukung — dengan tombol ulangi (`Coba Lagi`) dan indikator akurasi (`Akurasi GPS: ±X meter` + peringatan bila akurasi buruk).
- **Peta Leaflet + OpenStreetMap**: peta interaktif, tile OSM dengan atribusi resmi `© OpenStreetMap contributors`, tanpa Google Maps / API key.
- **Marker Draggable**: marker dapat digeser; **klik peta** juga memindahkan marker; Latitude/Longitude otomatis diperbarui di form (read-only, dikendalikan peta/GPS).
- **Circle Radius**: lingkaran radius tampil di peta dan **berubah real-time** saat input Radius diubah.
- **Reverse Geocoding (Opsional)**: setelah GPS/marker selesai digeser (debounce 700 ms), alamat dicoba diisi otomatis dari **Nominatim/OSM**. Jika gagal, form tetap bisa disimpan — alamat diisi manual, karena **koordinat adalah sumber utama radius**.
- **Edit Lokasi**: peta memuat koordinat lama dari database; tombol **"↩ Kembalikan Lokasi Tersimpan"** (reset frontend) dan **"⦿ Fokus Lokasi"** (center ke marker).
- **Detail Lokasi**: peta read-only (marker + lingkaran radius) tanpa tombol GPS.
- **QR & token aman**: koordinat dipindahkan **tidak** mengubah `public_token`; QR lama tetap valid; `is_active=false` tetap memblokir absensi.
- Karyawan (GPS) tidak berubah: validasi jarak Haversine tetap memakai `latitude`/`longitude`/`radius` **terbaru dari database** — tidak ada koordinat hard-code di JS/controller/service/blade.
- Catatan: Geolocation API umumnya butuh **secure context** (HTTPS). Di `http://127.0.0.1:8000` lokal tetap bekerja sesuai kemampuan browser; di production gunakan HTTPS.

## Fitur Bagian 2 (Selesai)

- Login karyawan diarahkan otomatis ke Dashboard Karyawan
- Dashboard Karyawan mobile-first: jam realtime, tanggal, status absen hari ini, jadwal hari ini, tombol **ABSEN MASUK** / **ABSEN PULANG**, status terkini, riwayat terakhir
- Halaman **Absensi** (`/employee/attendance`): scanner QR kamera (library `html5-qrcode`), fallback input manual, overlay loading
- **Browser Geolocation API** — mengambil koordinat GPS & akurasi saat scan QR berhasil
- **Validasi server (Haversine)**: jarak dihitung `DistanceService::haversine()` dan dibandingkan dengan `radius` lokasi
- **Pipeline validasi check-in/check-out** (`AttendanceService`): karyawan aktif → token QR valid → lokasi aktif → koordinat valid → jarak ≤ radius → jadwal hari ini → create/update record dalam transaksi
- **Status otomatis**: `Hadir` (≤ start_time + toleransi) atau `Terlambat`
- **Pencegahan absensi ganda**: unique `(employee_id, date)` di `attendance_records` + pengecekan aplikasi
- Check-in ditolak jika: karyawan nonaktif, token invalid, lokasi nonaktif, di luar radius, tanpa jadwal hari ini, sudah check-in
- Check-out ditolak jika: belum check-in, sudah check-out
- Riwayat Absensi karyawan (`/employee/attendance/history`) dengan filter: Semua, Hari Ini, Minggu Ini, Bulan Ini, rentang tanggal + pagination
- Detail absensi karyawan (`/employee/attendance/{id}`) — hanya record milik sendiri (selain → 404)
- Profil Karyawan (ubah nama, email, password dengan verifikasi password lama)
- Navigasi bawah (bottom nav) mobile: Dashboard, Absensi, Riwayat, Profil
- **Data Absensi Admin** (`/admin/attendance`): daftar seluruh record, filter period/rentang + cari nama karyawan, pagination, dan statistik Hadir/Terlambat/Belum Absen hari ini
- Detail absensi Admin (`/admin/attendance/{id}`) — info karyawan, lokasi, koordinat, status, catatan
- Timezone aplikasi **Asia/Jakarta** (waktu dihitung dari server, bukan klien)

## Fitur Bagian 3 (Selesai)

- **Laporan Harian** (`/admin/reports/daily`): pilih tanggal, rekap Total Karyawan, Hadir, Terlambat, Tidak Hadir + persentase kehadiran, daftar detail absensi
- **Laporan Bulanan** (`/admin/reports/monthly`): pilih bulan/tahun, filter department & karyawan; per-karyawan kolom Total Hari Kerja, Hadir, Terlambat, Tidak Hadir
- **Search & Filter absensi admin**: cari nama/Nomor Karyawan, filter status (Hadir/Terlambat/**Tidak Hadir**), filter karyawan, filter lokasi, periode (Hari Ini/Kemarin/Minggu Ini/Bulan Ini/Rentang)
  - Status **Tidak Hadir** = karyawan aktif yang memiliki jadwal kerja pada periode tersebut tapi belum ada record absensi
- **Pagination** data absensi dengan info "Menampilkan X–Y dari Z"
- **Detail absensi admin**: jarak absen dari titik lokasi (meter/km, titik pusat & radius lokasi ditampilkan)
- **Dashboard Admin**: kartu **Persentase Kehadiran** + grafik batang **Absensi 7 Hari Terakhir** (Hadir/Terlambat, Chart.js)
- **Riwayat karyawan**: tambahan filter bulan (`type=month`) dan status (Hadir/Terlambat)
- **Export Laporan** (CSV ber-BOM UTF-8 agar Excel terbuka dengan benar, Excel `.xlsx`, PDF landscape A4) — mengikuti filter aktif:
  - CSV: `/admin/reports/export/csv`
  - Excel: `/admin/reports/export/excel`
  - PDF: `/admin/reports/export/pdf`
- **Print / Cetak** (`/admin/reports/print`): halaman standalone, toolbar disembunyikan saat dicetak, header tabel berulang per halaman — tersedia juga tombol di halaman Data Absensi
- **Satu sumber query** (`AttendanceReportService`) dipakai tabel, CSV, Excel, PDF, dan print agar hasil konsisten
- **Keamanan**:
  - Semua route di luar `maintenance fallback login` dilindungi `auth` + middleware role (`admin` / `employee`)
  - Token CSRF aktif di semua form (Laravel `VerifyCsrfToken`)
  - Validasi server ketat: form request `EmployeeRequest`, validasi koordinat `between:-90,90` / `between:-180,180`, semua filter divalidasi format tanggal
  - Mass assignment dicegah via `$fillable` (whitelist) di semua model
  - Query memakai Eloquent/Query Builder (anti SQL injection); seluruh konfigurasi sensitif hanya dari `.env` (tidak di-versioning)
  - Error pages kustom: **403, 404, 419 (CSRF), 500**

---

## Requirements

- PHP >= 8.2
- Composer
- Node.js & npm (untuk aset frontend via Vite)
- MySQL (XAMPP) aktif di port 3306

## Instalasi

```bash
composer install
npm install
npm run build
```

## Konfigurasi `.env`

Salin `.env.example` menjadi `.env` lalu atur:

```dotenv
APP_NAME="Sistem Absensi Karyawan"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=metopel
DB_USERNAME=root
DB_PASSWORD=
```

> **Penting:** pastikan database bernama `metopel` sudah dibuat di phpMyAdmin. Jika password MySQL root Anda berbeda, sesuaikan `DB_PASSWORD`.

Bersihkan konfigurasi setelah mengubah `.env`:

```bash
php artisan config:clear
```

## Database XAMPP

- Database: `metopel` (MySQL, XAMPP)
- Periksa koneksi & status migrasi:

```bash
php artisan migrate:status
```

## Migration & Seeder

```bash
php artisan migrate
# atau untuk data demo sekaligus:
php artisan migrate --seed
```

Seeder tidak membuat data duplikat (menggunakan `firstOrCreate`). Data yang di-seed:

- 1 Admin
- 4 Karyawan
- 2 Lokasi absensi (berikut QR token)
- 6 Jadwal kerja
- Relasi jadwal ke karyawan (pivot `employee_work_schedule`)

## Akun Demo

| Role     | Email             | Password  |
|----------|-------------------|-----------|
| Admin    | admin@gmail.com   | 12345678  |
| Karyawan | karyawan@gmail.com | 12345678  |

Karyawan lain: `budi@gmail.com`, `siti@gmail.com`, `andi@gmail.com` (password sama: `12345678`).

## URL

| Halaman             | URL                           |
|---------------------|-------------------------------|
| Login               | `http://127.0.0.1:8000/login` |
| Dashboard Admin     | `http://127.0.0.1:8000/admin/dashboard` |
| Data Absensi Admin  | `http://127.0.0.1:8000/admin/attendance` |
| Laporan Harian      | `http://127.0.0.1:8000/admin/reports/daily` |
| Laporan Bulanan     | `http://127.0.0.1:8000/admin/reports/monthly` |
| Cetak / Print       | `http://127.0.0.1:8000/admin/reports/print` |
| Export CSV/Excel/PDF | `/admin/reports/export/csv` · `/admin/reports/export/excel` · `/admin/reports/export/pdf` |
| Dashboard Karyawan  | `http://127.0.0.1:8000/employee/dashboard` |
| Absensi (scan QR)   | `http://127.0.0.1:8000/employee/attendance` |
| Riwayat Karyawan    | `http://127.0.0.1:8000/employee/attendance/history` |
| Halaman scan publik | `http://127.0.0.1:8000/absensi/scan/{public_token}` |

Menjalankan Laravel:

```bash
php artisan serve
```

lalu buka `http://127.0.0.1:8000`.

> Tips demo: untuk melihat alur absensi, gunakan browser/HP di dekat koordinat lokasi (contoh lokasi "Kantor Pusat" 3.5952, 98.6722 radius 100m). Untuk uji cepat di server, gunakan lokasi + radius kecil.

## Package yang Digunakan

- `laravel/framework` ^12
- `endroid/qr-code` ^6 — generator QR Code (SVG) langsung dari Laravel
- `html5-qrcode` — scanner QR berbasis kamera browser
- `leaflet` ^1.9 — peta interaktif (OpenStreetMap, marker draggable, circle radius)
- `maatwebsite/excel` ^3.1 — export Excel (`.xlsx`)
- `barryvdh/laravel-dompdf` ^3.1 — export PDF
- `chart.js` — grafik dashboard (di-bundle via Vite)
- Frontend: Blade + Tailwind CSS 4 (Vite)

## Struktur Tabel

- `users` — name, email, password, role (`admin`/`employee`)
- `employees` — user_id, employee_number, phone, position, department, join_date, status
- `attendance_locations` — name, address, latitude, longitude, radius, public_token, is_active
- `work_schedules` — name, day, start_time, end_time, late_tolerance, is_active
- `employee_work_schedule` — pivot relasi karyawan ↔ jadwal (unique pasangan)
- `attendance_records` — employee_id, attendance_location_id, date, check_in, check_out, koordinat masuk/keluar, status (Hadir/Terlambat), notes; unique `(employee_id, date)`

## Menjalankan Test

```bash
php artisan test
```

Test memakai SQLite in-memory (tidak menyentuh database `metopel`).

## Fitur yang Belum Dibuat (Bagian 3)

- Laporan harian/bulanan
- Export Excel / CSV / PDF
- Deployment production

## Deployment (GitHub → Vercel → MySQL Online)

Aplikasi siap dideploy ke **Vercel** (PHP Runtime) + **database MySQL terkelola** (mis. MySQL Online / TiDB). Langkah:

1. Push kode ke repo GitHub (folder project tanpa `vendor/`, `node_modules/`, dan `.env`).
2. Buat database online (mis. MySQL Online) dan catat kredensialnya.
3. Di Vercel Project Settings → Environment Variables, isi:
   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=<hasil `key:generate` atau rahasia acak 32-byte base64>
   APP_URL=https://<nama-app>.vercel.app
   DB_CONNECTION=mysql
   DB_HOST=<host mysql online>
   DB_PORT=3306
   DB_DATABASE=<nama database>
   DB_USERNAME=<user>
   DB_PASSWORD=<password>
   SESSION_DRIVER=database
   SESSION_SECURE_COOKIE=false   # ubah true bila https sudah aktif & login header Secure
   ```
4. Build command Vercel: `composer install --no-dev --optimize-autoloader && npm ci && npm run build`
   > Pastikan tanpa `migrate:fresh` — jalankan migrasi sekali (mis. via terminal database/`php artisan migrate` saat build) agar data terpelihara.
5. Detail teknis penting:
   - Tidak ada `localhost`/IP pribadi yang di-hardcode; QR Code dibangun via `route()` sehingga otomatis mengikuti `APP_URL` di production.
   - Lokasi absensi dikelola admin (lat/long/radius) — tidak bergantung IP server.
   - `storage/framework` & cache dibersihkan otomatis per deploy melalui `php artisan optimize:clear`.
   - Gunakan custom `vercel.json` (PHP runtime) dengan dokumentasi Vercel; database kosong dapat di-isi via seeder `php artisan migrate --seed` sekali saja.