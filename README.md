# SIM Sistem Informasi Meter

**SIM Sistem Informasi Meter** adalah aplikasi web untuk monitoring, pencatatan, dan pengelolaan data kWh meter secara terpadu. Aplikasi ini mendukung pemantauan status meter secara real-time, pengelolaan master data kelistrikan, load profile, konfigurasi meter per periode, serta import/export data.

Dibangun dengan **Laravel** sebagai backend API dan **Vue 3 + Vite** sebagai single-page application (SPA).

---

## Fitur Utama

- **Dashboard real-time** — ringkasan total gardu induk, titik ukur, meter, status online/offline, dan konsumsi.
- **Master data** — Gardu Induk, Titik Ukur, Jenis kWh Meter, dan kWh Meter (termasuk soft delete, restore, dan force delete).
- **Konfigurasi meter** — pemetaan meter per titik ukur dan periode, riwayat penggantian meter, serta unggah dokumen PDF.
- **Load profile** — monitoring harian/mingguan, availability data, dan agregasi konsumsi.
- **Import & export CSV** — untuk seluruh modul master data dan konfigurasi.
- **Manajemen pengguna** — role `admin`, `operator`, `viewer`, serta pembatasan akses per gardu induk (unit scope).
- **Keamanan** — JWT authentication, captcha matematika, 2FA (Google Authenticator), session fingerprint, pembatasan percobaan login, dan activity log.
- **Realtime** — broadcast perubahan data dan indikator editing melalui Laravel Reverb.
- **Pengaturan pengguna** — profil, ganti password, linked devices, preferensi notifikasi, dan tampilan terang/gelap.

---

## Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | Vue 3, Vue Router, Vite, Tabler UI |
| Autentikasi | JWT (`php-open-source-saver/jwt-auth`), Google2FA |
| Realtime | Laravel Reverb + Laravel Echo |
| Database | MySQL |
| Penyimpanan dokumen | MinIO / S3-compatible storage |
| PDF processing | `smalot/pdfparser` + pemindaian heuristik |

---

## Kebutuhan Sistem

- PHP **8.3+** dengan ekstensi standar Laravel
- Composer **2.x**
- Node.js **20+** dan npm
- MySQL **8.x**
- MinIO (opsional, untuk penyimpanan dokumen konfigurasi)
- Laravel Reverb (opsional, untuk fitur realtime)

---

## Instalasi

```bash
# 1. Clone repository
git clone https://github.com/karamoik/sim-sistem-informasi-meter.git
cd sim-sistem-informasi-meter

# 2. Install dependency backend
composer install

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# 4. Sesuaikan koneksi database, MinIO, dan Reverb di .env

# 5. Jalankan migrasi dan seeder
php artisan migrate --seed

# 6. Install dependency frontend
npm install
```

---

## Menjalankan Aplikasi

Jalankan setiap proses di terminal terpisah:

```bash
# Backend API
php artisan serve

# Frontend (hot reload)
npm run dev

# Realtime server (opsional)
php artisan reverb:start

# Queue worker (opsional)
php artisan queue:work
```

Aplikasi dapat diakses melalui `APP_URL` yang diatur di `.env` (default `http://localhost`).

---

## Build untuk Produksi

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Pastikan nilai berikut sudah benar di environment produksi:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
```

---

## Konfigurasi Environment Penting

| Variabel | Keterangan |
|---|---|
| `APP_NAME` | Nama aplikasi, default `SIM Sistem Informasi Meter` |
| `APP_URL` | URL publik aplikasi |
| `DB_*` | Koneksi database MySQL |
| `JWT_SECRET` | Secret penandatangan token JWT |
| `JWT_TTL` | Masa berlaku token (menit) |
| `BROADCAST_CONNECTION` | `reverb` untuk realtime, `log` untuk development |
| `REVERB_*` | Konfigurasi host, port, dan kredensial Reverb |
| `MINIO_*` | Kredensial dan endpoint penyimpanan dokumen |
| `FILESYSTEM_DISK` | Disk penyimpanan dokumen (`minio` untuk produksi) |

> **Penting:** jangan commit file `.env`. File tersebut sudah masuk `.gitignore`.

---

## Struktur Project

```text
app/
├── Events/                 # Event realtime (Reverb)
├── Http/
│   ├── Controllers/Api/    # Controller API
│   ├── Middleware/         # Role, unit scope, session
│   └── Requests/           # Form request validation
├── Models/                 # Model Eloquent
├── Services/               # Auth, 2FA, PDF scanner, reset password
└── Console/Commands/       # Command artisan (demo load profile)

database/
├── migrations/             # Skema database
└── seeders/                # Data awal/demo

resources/
├── css/app.css             # Entry CSS
├── js/
│   ├── pages/              # Halaman Vue
│   ├── components/         # Komponen Vue
│   ├── composables/        # Composable Vue
│   ├── router/             # Vue Router
│   └── services/           # Axios API client
└── views/welcome.blade.php # Entry SPA

routes/
├── api.php                 # Route API
└── web.php                 # Route SPA fallback
```

---

## Autentikasi API

1. Ambil captcha dari `GET /api/captcha`.
2. Kirim kredensial ke `POST /api/auth/login` beserta `captcha_token`, `captcha_answer`, dan `fingerprint_id`.
3. Simpan `access_token` dan kirim sebagai header `Authorization: Bearer <token>`.
4. Sertakan header `X-Session-Fingerprint` pada setiap request terautentikasi.
5. Jika 2FA aktif, verifikasi melalui `POST /api/auth/2fa/verify`.

Semua response API menggunakan format konsisten:

```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
```

---

## Data Awal (Seeder)

`php artisan migrate --seed` akan membuat akun dan data demo, termasuk akun administrator default. **Segera ubah password akun default dan hapus akun demo sebelum digunakan di produksi.**

Untuk produksi, disarankan membuat akun admin pertama secara manual atau melalui command khusus, lalu menonaktifkan registrasi publik.

---

## Checklist Keamanan Produksi

- [ ] Set `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] Ganti semua password akun default dari seeder.
- [ ] Tutup atau batasi endpoint registrasi publik.
- [ ] Gunakan HTTPS dan batasi `config/cors.php` ke origin frontend.
- [ ] Rotasi `APP_KEY` dan `JWT_SECRET` bila pernah terekspos.
- [ ] Batasi akses jaringan ke MinIO dan gunakan kredensial kuat.
- [ ] Jalankan `composer audit` dan `npm audit` secara berkala.
- [ ] Aktifkan backup database dan dokumen secara rutin.
- [ ] Pastikan queue worker dan Reverb dijalankan sebagai service.

---

## Lisensi

Project ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).
