# Tahap 3: Autentikasi

## Cara memasang
1. Tutup `php artisan serve` / `npm run dev` kalau sedang jalan.
2. Ekstrak zip ini DI DALAM folder proyek Anda
   (`ifs24017-pabwe2026-sk-p8-laravel-inertia-main`), pilih "Replace / Timpa semua".
3. Jalankan:

    composer dump-autoload
    php artisan optimize:clear
    php artisan migrate:fresh --seed
    npm run build
    php artisan serve

## Cara memeriksa
- http://127.0.0.1:8000/login -> login `admin` / `admin` -> masuk `/admin/users`
- Keluar, login `member` / `member` -> masuk `/dashboard/posts`
- Member membuka `/admin/users` -> 403
- Belum login membuka `/dashboard/posts` -> dialihkan ke `/login`
- Sudah login membuka `/login` -> dialihkan ke panel
- `/register` membuat akun member baru
- ID `login-username`, `login-password`, `login-submit` ada di form login
