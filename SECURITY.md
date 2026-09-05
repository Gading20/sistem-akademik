# Keamanan Sistem Akademik SMK Nurul Ulum

Dokumen ini merangkum kontrol keamanan yang sudah diterapkan dan langkah-langkah
wajib sebelum aplikasi dipakai di produksi.

## Kontrol yang Sudah Diterapkan

### Transport & Protokol
- **HTTPS dipaksa di produksi** — `URL::forceScheme('https')` dan header
  `Strict-Transport-Security` (HSTS) dikirim saat koneksi HTTPS/produksi.
- **Trusted proxies** dikonfigurasi agar skema HTTPS dari reverse proxy
  (nginx, load balancer) diteruskan dengan benar.

### Header Keamanan HTTP (semua respons)
Dikirim oleh middleware `App\Http\Middleware\SecurityHeaders`:
- `Content-Security-Policy` — membatasi sumber script/style/font/connect ke
  domain yang dipakai aplikasi (Tailwind CDN, Alpine CDN, Google Fonts);
  melarang iframe (`frame-ancestors 'none'`), `object`, dan `base-uri` asing.
- `X-Frame-Options: DENY` — anti clickjacking.
- `X-Content-Type-Options: nosniff` — anti MIME sniffing.
- `Referrer-Policy: strict-origin-when-cross-origin` — membatasi kebocoran
  referrer.
- `Permissions-Policy` — menonaktifkan kamera, mikrofon, geolokasi, pembayaran, USB.

### Otentikasi & Sesi
- **Rate limit login** — maksimal 5 percobaan gagal per akun (lockout sementara)
  **dan** 10 percobaan per menit per IP (middleware `throttle:10,1`).
  Penghitung direset saat login berhasil.
- **Regenerasi sesi** saat login, logout, dan setelah ganti password
  (anti session fixation).
- Cookie sesi: `http_only=true`, `same_site=lax`, serialisasi JSON;
  `SESSION_SECURE_COOKIE=true` di produksi (cookie hanya lewat HTTPS).
- Password di-hash dengan bcrypt (cast `hashed` di model `User`); tidak ada
  password plaintext di database atau kode.
- **Kebijakan password** — minimal 8 karakter + konfirmasi pada pembuatan
  siswa/guru dan ganti password; verifikasi password lama wajib saat ganti.

### Otorisasi
- RBAC multi-level (`CheckRole`) di route + **Policy** per entitas + cek
  kepemilikan di controller/service. Menu tidak hanya disembunyikan —
  backend memverifikasi akses.
- Siswa hanya bisa melihat/mengerjakan ujian untuk kelasnya, dan hanya bisa
  melihat hasil miliknya sendiri.

### Ujian (anti kecurangan terukur — bukan klaim anti-cheat 100%)
- Timer dihitung **di sisi server** (submit otomatis saat waktu habis,
  tidak bergantung JavaScript).
- Token ujian, batas percobaan (attempt limit), dan pengecekan jadwal
  (start/end) di server.
- Jawaban **dipastikan milik soal ujian** dan pilihan jawaban **dipastikan
  milik soal tersebut** (anti injeksi jawaban/pilihan dari soal lain).
- Autosave jawaban di-throttle (`throttle:120,1`) untuk mencegah spam endpoint.
- IP address & user agent dicatat di setiap percobaan (`ExamAttempt`).
- Randomisasi soal/pilihan (opsional per ujian) dan activity log.

### Lainnya
- **CSRF** aktif di semua route web (`VerifyCsrfToken` bawaan Laravel).
- **XSS** — Blade meng-escape output secara default.
- **SQL injection** — seluruh query memakai Eloquent/Query Builder.
- **Upload file** divalidasi (tipe & ukuran maksimal) — foto/avatar hanya
  gambar, ukuran ≤ 2MB.
- **Secret tidak pernah di-commit** — `.env` ada di `.gitignore`; tidak ada
  API key/password hardcoded di kode (dipindai ulang pada audit).
- **Dependensi diaudit** — `composer audit` bersih (tidak ada advisories).
- **Audit log** — login/logout, CRUD master, nilai, ujian, dan perubahan
  password tercatat beserta IP & user agent.

## Checklist Wajib Sebelum Produksi

1. **`.env` di server:**
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda
   SESSION_SECURE_COOKIE=true
   ```
2. **`php artisan key:generate`** — pastikan `APP_KEY` terisi dan disimpan aman.
   Jangan pernah membagikan `.env` / `APP_KEY`.
3. **Wajib HTTPS (SSL)** — aplikasi mengirim HSTS di produksi; tanpa HTTPS,
   sesi & password bisa disadap.
4. **Ganti semua password default seeder** (mis. `password`) — buat akun
   admin dengan password kuat, lalu hapus/ubah akun demo.
5. **`php artisan config:cache && route:cache && view:cache`** setelah deploy
   (dan setelah mengubah `.env`).
6. **Batasi akses file** — `chmod 600 .env`, nonaktifkan akses publik ke
   `storage/` dan `.git/`.
7. **Backup rutin** database (`database.sqlite` atau MySQL) dan folder
   `storage/app`.
8. **Update berkala** — `composer update` + `composer audit` untuk patch
   keamanan framework/dependensi.
9. **Nginx/Apache** — nonaktifkan listing direktori, pasang header HTTPS
   redirect, dan batasi ukuran body upload (`client_max_body_size`).
10. **Pantau audit log** (`audit_logs`) secara berkala untuk aktivitas mencurigakan.

## Rekomendasi Pengembangan Berikutnya
- **Two-Factor Authentication (2FA)** untuk akun admin/guru.
- **Password reset via email** (saat ini belum ada mekanisme lupa password).
- **Logout perangkat lain** saat ganti password (`Auth::logoutOtherDevices`).
- Pindah dari CDN Tailwind/Alpine ke **asset lokal** agar CSP bisa diperketat
  (tanpa `'unsafe-inline'`).
- **Brute-force protection terpusat** via package seperti laravel/security
  (rate limit per user + IP secara global).

## Melaporkan Kerentanan
Jangan publish kerentanan di tempat umum. Laporkan langsung ke pengelola
sistem/developer dengan detail langkah reproduksi.