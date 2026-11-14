# BlogVerse Pro

Aplikasi blog multi-penulis dengan **Laravel 13 + Inertia.js 2 + React + Tailwind CSS 4** dan database **PostgreSQL**.
Dibuat sebagai tugas PABWE 2026 (Pertemuan 8).

## Fitur
- **Publik (tanpa login):** beranda galeri artikel, daftar artikel, pencarian, filter kategori, pembaca artikel Markdown.
- **Autentikasi:** login, registrasi (otomatis role `member`), logout, middleware `EnsureRole`.
- **Dashboard penulis:** CRUD artikel, toggle terbit/draf, foto sampul, statistik, modal full-screen, editor TOAST UI.
- **Excel:** ekspor dan impor artikel (`.xlsx`) lewat PhpSpreadsheet.
- **AI Generator:** membuat draf artikel dengan OpenAI (butuh `OPENAI_API_KEY`).
- **Profil:** ubah nama, email, password, dan foto.
- **Panel admin (hanya-baca):** daftar pengguna, artikel per pengguna, semua artikel, ekspor Excel.

## Arsitektur
Kode dikelompokkan per fitur di `app/Features/*` (Admin, Ai, Auth, Post, Profile) dengan pola
Controller -> Service -> Repository (interface di `Contracts`).

## Instalasi
```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```
Buat database PostgreSQL `pabwe_p8_blog`, sesuaikan `DB_PASSWORD` di `.env`, lalu:
```powershell
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
php artisan serve
```
Buka http://127.0.0.1:8000.

## Akun demo
| Username | Password | Role |
|---|---|---|
| admin | admin | admin |
| member | member | member |

## OpenAI (opsional)
Isi `OPENAI_API_KEY` di `.env`. Tanpa kunci, fitur AI menampilkan pesan bahwa kunci belum diatur.
**Jangan** meng-commit file `.env`.

## Pengujian
```powershell
$env:XDEBUG_MODE = "coverage"
php artisan test --coverage --min=100
```
atau jalankan `test.bat`. Hasil ringkas tersimpan di `coverage.txt`.

Pemeriksaan keamanan dependensi:
```powershell
composer audit
```

## Daftar rute
| Method | URI | Akses |
|---|---|---|
| GET | `/`, `/articles`, `/articles/{slug}` | Publik |
| GET/POST | `/login`, `/register` | Tamu |
| POST | `/logout` | Login |
| GET/POST | `/dashboard/posts` | Member |
| GET/PUT/DELETE | `/dashboard/posts/{post}` | Pemilik |
| PATCH | `/dashboard/posts/{post}/publish` | Pemilik |
| POST/DELETE | `/dashboard/posts/{post}/cover` | Pemilik |
| GET/POST | `/dashboard/posts/export`, `/dashboard/posts/import` | Member |
| POST | `/dashboard/posts/ai-generate`, `/dashboard/posts/ai-save` | Member |
| GET/PUT | `/profile` | Login |
| GET | `/admin/users`, `/admin/users/{user}/posts`, `/admin/posts` (+ `/export`) | Admin |
