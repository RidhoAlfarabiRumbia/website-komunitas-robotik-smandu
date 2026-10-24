# Update Terbaru

Ringkasan perubahan pada branch `update-terbaru`.

## 25 Oktober 2026

### Fitur baru

- Lupa password melalui email dengan token reset sekali pakai dan masa berlaku terbatas.
- Pengelolaan email akun di Profil anggota, Profil admin, CRUD Anggota, dan verifikasi pendaftaran.
- Email kini wajib diisi saat pendaftaran anggota dan saat membuat atau memperbarui akun.
- Pratinjau foto karya ukuran penuh.
- Lightbox untuk memperbesar foto galeri.
- Popup notifikasi pengajuan karya saat admin masuk ke dashboard.
- Informasi mode pelatihan daring/luring menggantikan informasi kuota.

### Perbaikan

- Logout tetap berhasil dari halaman lama, sesi yang sudah berakhir, atau halaman yang token keamanannya sudah berubah.
- Status publikasi karya dan lomba menggunakan Bahasa Indonesia.
- Kategori pada karya dan lomba dihapus agar struktur data lebih sederhana.
- Mode gelap menggunakan palet hitam netral tanpa bayangan.
- Scrollbar dibuat stabil saat halaman berubah.
- Perbaikan keamanan pada validasi input, pengelolaan akun, dan tautan.
- Perbaikan pembentukan URL agar tetap benar di lingkungan Windows.

### Konfigurasi dan migrasi

- `.env.example` dan README diperbarui dengan `APP_URL` serta konfigurasi SMTP.
- Migrasi yang perlu dijalankan bila belum tersedia:
  - `migrate_password_reset.sql`
  - `migrate_pelatihan_mode.sql`
  - `migrate_hapus_kategori_karya.sql`
  - `migrate_hapus_kategori_lomba.sql`
  - `migrate_publikasi_karya_indonesia.sql`

### Catatan

- Email dipakai untuk reset kata sandi dan tidak ditampilkan ke publik.
- Token reset hanya disimpan sebagai hash, berlaku 30 menit, dan tidak dapat dipakai ulang.
- Pengiriman email membutuhkan konfigurasi SMTP yang benar di `.env`.
- Untuk produksi, gunakan email domain resmi dan penyedia SMTP yang sesuai, bukan akun Gmail pribadi.
