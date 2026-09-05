<p align="center">
  <strong style="font-size: 1.6rem;">SISTEM AKADEMIK SMK NURUL ULUM</strong>
</p>

<p align="center">
Sistem informasi akademik sekolah yang modern, lengkap, aman, responsif, dan mudah dikembangkan.
</p>

---

## Fitur Utama

- **Manajemen Data Master** — tahun ajaran, semester, jurusan, kompetensi keahlian, kelas/rombel, ruangan, mata pelajaran, kurikulum, CP & TP
- **Manajemen Pengguna** — 8 role (Super Admin, Admin Sekolah, Kepala Sekolah, Wakil Kepala Sekolah, Guru, Wali Kelas, Siswa, Orang Tua/Wali) dengan authorization penuh (Policy/Gate)
- **Akademik** — siswa, guru, pegawai, jadwal pelajaran, absensi, jurnal guru, wali kelas
- **Bank Soal & Ujian** — quiz, pre/post test, PTS, PAS, ujian praktik/proyek; jenis soal pilihan ganda (termasuk kompleks), benar/salah, menjodohkan, isian singkat, essay, upload file, praktik
- **Sistem Ujian Aman** — timer server-side, token ujian, batas percobaan, randomisasi soal & opsi, question pool, autosave, logging IP & user agent
- **Penilaian & Rapor** — grading config, leger, ranking, rapor, remedial, analisis hasil ujian
- **Laporan** — rekap kehadiran, rekap nilai, hasil ujian, jurnal guru (export PDF / Excel / CSV)

## Tech Stack

- **Laravel** 13 (PHP ^8.3) — MVC, Eloquent ORM, Form Request, Service/Action class
- **MySQL** 8 — database utama (lihat `database/smknulum.sql`)
- **Blade + Tailwind** — UI responsif

## Persyaratan

- PHP ^8.3 (dengan ekstensi `pdo_mysql`)
- Composer
- MySQL 8.x
- Node.js & NPM (untuk aset frontend)

## Instalasi

```bash
git clone https://github.com/Gading20/sistem-akademik.git
cd sistem-akademik
composer install
cp .env.example .env
php artisan key:generate
```

### Database (MySQL)

**Opsi A — import file SQL lengkap** (sudah termasuk data awal seeder):

```bash
mysql -u USERNAME -p < database/smknulum.sql
```

File ini membuat database `smknulum` (40 tabel + data dummy). Sesuaikan kredensial di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smknulum
DB_USERNAME=...
DB_PASSWORD=...
```

**Opsi B — jalankan migration & seeder:**

```bash
php artisan migrate --seed
```

### Aset Frontend & Menjalankan Aplikasi

```bash
npm install
npm run build
php artisan serve
```

## Akun Default (data seeder)

| Role          | Email                        | Password  |
|---------------|------------------------------|-----------|
| Super Admin   | admin@smknurululum.sch.id    | password  |

> ⚠️ Segera ganti semua password default sebelum digunakan di produksi (lihat `SECURITY.md`).

## Testing

```bash
php artisan test
```

## Keamanan

Lihat [SECURITY.md](SECURITY.md) untuk kebijakan keamanan, pelaporan kerentanan, dan checklist deployment.

## Lisensi

Proyek internal SMK Nurul Ulum — hak cipta pengembang.