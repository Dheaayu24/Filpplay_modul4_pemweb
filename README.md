# FlipPlay REST API (Laravel 12)

Pengembangan lanjutan **FlipPlay** dari simulasi OOP berbasis terminal (Modul 3) menjadi **REST API** menggunakan **Laravel 12**, Eloquent ORM, Migration, Form Request Validation, API Resource (`whenLoaded`), dan relasi Many-to-Many (`belongsToMany`).

## Struktur Entitas & Relasi
- **`Media`** (`app/Models/Media.php`) — merepresentasikan media (Anime / Drama) dengan atribut `title`, `type`, `genre`, `year`, `episodes`, `country`, accessor `details`, dan query scope `search` & `filterType`.
- **`User`** (`app/Models/User.php`) — merepresentasikan pengguna FlipPlay beserta koleksi media pribadinya.
- **Tabel Pivot `media_user`** — menghubungkan `Media` dan `User` menggunakan relasi `belongsToMany` dengan `cascadeOnDelete()` dan unique constraint `(media_id, user_id)`.

## Cara Menjalankan Project
1. Masuk ke folder project:
   ```bash
   cd flipplay-api
   ```
2. Install dependensi (jika belum):
   ```bash
   composer install
   ```
3. Salin `.env.example` menjadi `.env` (jika belum ada) dan generate app key:
   ```bash
   php artisan key:generate
   ```
   > **Catatan Database**: Secara default `.env` menggunakan SQLite (`database/database.sqlite`) agar langsung dapat dijalankan tanpa konfigurasi server MySQL. Jika ingin menggunakan MySQL (XAMPP/Laragon), nyalakan MySQL, buat database `flipplay`, lalu ubah `DB_CONNECTION=mysql`, `DB_DATABASE=flipplay`, `DB_USERNAME=root`, dan `DB_PASSWORD=` pada `.env`.
4. Jalankan migrasi dan seeder:
   ```bash
   php artisan migrate:fresh --seed
   ```
5. Verifikasi seluruh route API:
   ```bash
   php artisan route:list --path=api
   ```
6. Jalankan server lokal:
   ```bash
   php artisan serve
   ```
   Base URL API: `http://127.0.0.1:8000/api`

## Daftar Endpoint API

| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `GET` | `/api/media` | Menampilkan seluruh media (mendukung filter `?title=...` & `?type=anime\|drama`) |
| `GET` | `/api/media/{media}` | Menampilkan detail satu media beserta pengguna yang mengoleksinya |
| `POST` | `/api/media` | Menambahkan media baru (validasi wajib & anti-duplikat judul) |
| `PATCH` | `/api/media/{media}` | Memperbarui data media (mendukung partial update) |
| `DELETE` | `/api/media/{media}` | Menghapus media |
| `GET` | `/api/users` | Menampilkan daftar pengguna beserta koleksinya |
| `POST` | `/api/users` | Menambahkan pengguna baru |
| `GET` | `/api/users/{user}` | Menampilkan detail pengguna beserta koleksinya |
| `GET` | `/api/users/{user}/collection` | Menampilkan daftar koleksi media milik pengguna |
| `POST` | `/api/users/{user}/collection` | Menambahkan media ke koleksi pengguna (`media_id`, return `409` jika duplikat) |
| `DELETE` | `/api/users/{user}/collection/{media}` | Menghapus media dari koleksi pengguna |

## Pengujian
- **Otomatis (PHPUnit)**:
  ```bash
  php artisan test
  ```
- **Manual (Postman)**:
  Import file `FlipPlay_API.postman_collection.json` ke Postman untuk menguji seluruh skenario *happy path* (`200`, `201`) dan *failure path* (`404`, `409`, `422`).
