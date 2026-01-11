# Rekomendasi Film (Bootstrap 5) - Simple PHP + MySQL Project

## Fitur
- Register, Login, Logout (user & admin)
- Dashboard admin untuk CRUD rekomendasi
- Filter genre di halaman publik & user
- Rating 1-5 bintang + optional review (user hanya bisa satu rating per rekomendasi)
- Bootstrap 5 untuk tampilan

## Cara pasang
1. Import `sql/init.sql` ke MySQL.
2. Edit `db.php` jika perlu (DB user/password).
3. Letakkan file ke folder server (mis: `/var/www/html/rekomendasi`).
4. Buat akun lewat `register.php`. Untuk menjadikan admin: jalankan SQL:
   `UPDATE users SET is_admin = 1 WHERE email = 'youremail@example.com';`

## Catatan keamanan
- Tambahkan HTTPS di produksi.
- Jangan gunakan user root tanpa password di produksi.
- Pertimbangkan CSRF token dan validasi input tambahan.
