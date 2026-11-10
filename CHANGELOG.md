# Update Terbaru

Ringkasan perubahan pada branch `update-terbaru`.

## 10 November 2026 (branch `perbaikan-pelatihan-materi`)

### Fitur baru (10 November 2026)

- Data yang dihapus kini membersihkan berkasnya di server sekaligus: hapus data di CRUD (semua entitas) dan hapus pendaftaran di Verifikasi ikut menghapus berkas di `public/uploads/<subfolder>/` maupun `storage/terbatas/<subfolder>/` lewat helper baru `hapus_upload()` yang memvalidasi path dengan `storage_path()`. Pergantian foto/dokumen saat edit CRUD juga menghapus versi lama agar tidak menumpuk orphan; URL video eksternal dan placeholder otomatis dilewati, dan kegagalan hapus hanya dicatat di `error_log` tanpa menggagalkan aksi. Sampah orphan yang sudah ada sebelum perubahan ini tidak dibersihkan otomatis (bisa dibuat skrip sweep terpisah bila diminta).
- Upload foto kini membiarkan pasangan: begitu varian ringan `<stempel>_opt.webp` (atau fallback JPEG) berhasil dibuat, berkas asli PNG/JPG langsung dihapus — sebelumnya asli ditinggal "arsip" padahal tak pernah dipakai. `hapus_upload()` pun membersihkan pasangan ini: menghapus varian ikut menghapus aslinya (dan sebaliknya), aman untuk berkas materi/pakta PDF yang tidak punya pasangan.
- Protes dobel klik kini dua lapis: form yang menulis data (CRUD semua entitas + panel Referensi, Ajukan Karya/Prestasi, Pendaftaran, Daftar Pelatihan) memakai field `token_kirim` sekali-pakai yang diverifikasi di server — kiriman kedua dengan form yang sama ditolak dengan pesan flash singkat; sementara footer memasang guard global yang mematikan tombol submit segera setelah diklik dan lucut kembali tombolnya 25 detik kemudian bila pengiriman terganggu. Aksi hapus/ubah status yang alurnya sudah aman tidak diberi token.

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
- Perputaran slider hero lebih halus (Beranda & Komunitas): sebelumnya slide baru dan lama sama-sama pudar di atas latar gelap, sehingga di tengah transisi dasar gelap ikut tembus sekitar 25% — terasa seperti kilat/gelap sesaat. Kini slide lama dipertahankan penuh sampai slide baru selesai muncul di atasnya, lalu baru dilepas di bawah slide yang sudah menutupi; lapisan slide juga dipromosikan ke komposit GPU (`will-change`). Lapisan efek gelap (scrim) di atas foto tidak berubah posisi, teks hero tetap kontras.
- Hero slider Beranda dan hero Profil Komunitas kini mengikuti margin horizontal header (24px), sama lebar dengan area konten di atasnya; pada layar ≤720px keduanya otomatis mengikuti margin ponsel (16px).
- Daftar Kegiatan & Pengumuman kini memakai pola kartu yang sama dengan halaman Lomba: tiap item adalah kartu sendiri dalam grid dua kolom yang naik bertahap (stagger), bukan teks yang muncul di dalam satu kotak statis. Tombol ubah/hapus admin kini juga mengapung di pojok kartu seperti di halaman lain.
- Kartu kini terangkat halus saat kursor menyentuhnya (efek hover lift): semua kartu (akses cepat, prestasi, karya, dokumentasi, agenda, dsb.) naik 6px dan bayangannya menggelap dengan transisi lembut 0,3 detik. Mode animasi masuk kartu grid berganti fill "both" → "backwards" supaya nilai transform tidak mengunci efek angkat tersebut, dan preferensi "kurangi gerakan" mematikan angkatannya.
- Tombol "Lihat semua" dilepas dari beranda (prestasi, lomba, karya, galeri, agenda, pelatihan, pengumuman, materi) dan profil komunitas (prestasi, anggota): judul section-nya itulah yang kini langsung jadi tautan ke halaman daftar. Tampilannya tetap polos tanpa panah, dan garis bawah baru muncul saat kursor atau fokus keyboard mengarah kesana. Tiap judul juga kini disertai satu kalimat penjelasan singkat di bawahnya.
- Slot foto di carousel beranda selalu berjumlah tiga: bila data kurang, kartu demo mengisi sisanya — area foto memuat ikon gambar abu-abu ukuran sedang tanpa teks, menggantikan kotak pesan kosong untuk seksi karya & galeri. Khusus prestasi tidak memakai kartu demo: 1-3 kartu berjajar rapi di tengah (lebar responsif 3/2/1 kolom) dan kartu tunggal melebar terbatas (maks 560px), 0 tetap menampilkan pesan kosong, dan lebih dari 3 kembali memakai carousel yang bisa digeser panahnya.- Jarak antar seksi konten diperlonggar agar halaman lebih berbernafas: jarak vertikal antar kartu seksi beranda (kolom kiri-kanan) dan antar seksi di seluruh halaman member naik dari 38px menjadi 112px (di mobile tetap ringkas 28px).
- Latar seksi beranda kini selang-seling abu-abu halus dan putih: seksi genap (fitur, agenda, materi, dsb.) mendapat panel abu kebiruan (#e3ecfa) membulat dengan jarak kisi dalam selebar slider, CTA berwarna tidak ikut dibungkus, dan mode gelap otomatis memakai panel abu gelap kebiruan.
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
