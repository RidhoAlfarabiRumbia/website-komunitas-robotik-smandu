# Update Terbaru

Ringkasan perubahan pada branch `update-terbaru`.

## 9 November 2026 (branch `perbaikan-pelatihan-materi`)

### Fitur baru (9 November 2026)

- Dialog konfirmasi modal selaras tema menggantikan `confirm()` bawaan browser di semua titik hapus (CRUD, referensi, peserta pelatihan, pendaftaran, template Pakta, foto pembina, foto profil) dan sebelum mendaftar pelatihan.
- Admin dapat langsung mengunggah satu berkas materi saat menambah Pelatihan baru; materi tersimpan dengan judul nama pelatihan, topik "Pelatihan", dan otomatis terkait pelatihan tersebut.
- Email berisi tautan pembuatan akun dikirim ke calon anggota saat pendaftarannya diverifikasi; username & kata sandi diisi pelamar sendiri lewat tautan tersebut (berlaku 24 jam, sekali pakai).
- Admin dapat mengirim ulang tautan dari halaman verifikasi bila email tidak masuk; tautan lama otomatis tidak berlaku.
- Halaman baru Lengkapi Akun (lengkapi-akun.php) untuk menetapkan username & kata sandi dari tautan email verifikasi.
- Form verifikasi admin disederhanakan tanpa input username/email/kata sandi.
- Tautan "Daftar sebagai anggota" kini tersedia di halaman Masuk.
- Modal Penyesuaian Foto: setelah memilih berkas di form foto apa pun (slider, karya, galeri, prestasi, foto anggota/profil, foto pembina, termasuk formulir Ajukan Karya/Ajukan Prestasi anggota), foto dibuka di frame ber-border dan bisa digeser (drag) serta di-zoom (roda mouse/tombol/slider); rasio frame otomatis mengikuti zona dan tidak lagi ada pilihan rasio. Hasil cut-out menggantikan berkas yang diunggah.
- Tombol "Edit Foto" pada form edit: foto yang sudah tersimpan bisa dibuka kembali di modal penyesuaian foto, digeser/zoom, lalu disimpan sebagai pengganti foto lama.
- Frame foto disinkronkan dengan tampilan: kartu Karya/Galeri/Prestasi kini memakai rasio 16:9 konsisten (termasuk halaman Prestasi), foto Slider hero memakai 3:4 untuk kolom tengah, dan avatar/pembina tetap 1:1.
- Dokumen Pakta Integritas bisa juga diunggah saat admin menambah anggota secara manual (tersimpan sebagai pendaftaran berstatus Diverifikasi milik anggota tersebut).
- Formulir pendaftaran memperjelas bahwa Pakta Integritas boleh berupa dokumen hasil scan (JPG/PNG) atau PDF.
- Padding area konten (body `.main` member dan `.admin-main` admin) diperbesar agar konten lebih lega; header, sidebar, dan footer memakai margin aslinya.

### Perbaikan (9 November 2026)

- Halaman berkedip/flashy saat pindah dan kadang "nyangkut" semi-transparan (paling terasa pada Karya & Galeri) dibereskan. Penyebab: animasi masuk bertumpuk — container halaman fade 0,55s menunggu JS reveal (kartu bisa blank/stuck bila skrip sempat gagal), lalu kartu dianimasikan dua lapis (induk grid + kartu) dengan jeda bertingkat sampai 0,61s. Kini: grid kartu tidak lagi bergantung pada JS (class `reveal` dilepas dari 9 halaman: Karya, Galeri, Anggota, Lomba, Materi, Pelatihan, Prestasi, Komunitas), container halaman fade opacity saja 0,4s tanpa translasi, jeda stagger kartu dipersingkat (maks 0,37s), dan animasi ganda pada kartu dihapus.
- Beranda & hero Komunitas tidak diubah: fade masuk foto slider tetap 0,8 detik seperti semula (fade + naik), supaya gerakannya tetap terasa.
- Hasil "Gunakan Foto Ini" tidak lagi bergeser atau pecah: potongan diambil tepat dari area yang terlihat di modal dengan resolusi foto asli (dibatasi sisi terpanjang 1600px), bukan ukuran frame di layar.
- Modal penyesuaian foto ikut dimuat pada mode manage di halaman fitur (Karya, Galeri, Prestasi, Anggota, Materi, Pelatihan, Kegiatan, Lomba, Pengumuman) sehingga crop, tombol "Edit Foto", dan pratinjau alamat foto bekerja juga bila form edit dibuka dari dashboard admin.
- Pratinjau form langsung ikut berubah setelah menekan "Gunakan Foto Ini" (termasuk pada form yang memakai gambar default). Penyebab semula: CSP `img-src 'self' data:` menolak gambar buatan canvas dari `blob:` — kini CSP mengizinkan `blob:` untuk konten yang dihasilkan sendiri oleh portal.
- Akses materi tidak lagi terkunci tanggal: peserta yang sudah disetujui tetap bisa mengunduh materi walau pelatihan sudah berakhir atau statusnya Ditutup.
- Pendaftaran pelatihan hanya mengikuti status Dibuka/Ditutup; jadwal yang mundur atau digeser tidak lagi menutup daftar secara diam-diam.
- Beranda anggota kembali menampilkan materi dari semua pelatihan yang diikuti, bukan hanya yang belum selesai.

### Tidak perlu migrasi

- Semua perubahan bersifat kode, tanpa perubahan skema database.

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
