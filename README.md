# Portal Komunitas Robotik SMA Negeri 2

Source code lengkap (native PHP OOP + MySQL, tanpa framework/MVC, Bootstrap-style layout kustom) sesuai PRD.

## Cara Menjalankan (XAMPP / Laragon / server PHP+MySQL apa pun)

1. **Import database**
   - Buka phpMyAdmin → New → Import → pilih file `database.sql`.
   - Ini akan membuat database `db_robotik` beserta seluruh tabel dan data contoh.

2. **Salin konfigurasi**
   - Duplikat file `.env.example` menjadi `.env`.
   - Sesuaikan `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` dengan pengaturan MySQL lokal Anda.

3. **Letakkan folder project**
   - Salin seluruh folder ini ke `htdocs` (XAMPP) atau document root server Anda, misalnya `htdocs/portal-robotik`.

4. **Akses di browser**
   - Buka `http://localhost/portal-robotik/public/`
   - Anda akan diarahkan ke halaman login.
   - `public/` adalah document root aplikasi. Untuk produksi, arahkan document root server ke folder `public/` (kode aplikasi di `app/` dan `.env` tidak perlu dapat diakses langsung dari browser).

5. **Performa (opsional, disarankan untuk XAMPP)**
   - Di `apache/conf/httpd.conf`, aktifkan (hapus `#`) `deflate_module`, `expires_module`, dan `filter_module`, lalu restart Apache. `public/.htaccess` memakainya untuk gzip + cache aset (CSS/JS 1 tahun dengan cache-bust `?v=filemtime`, gambar upload 1 bulan karena nama file selalu unik). Tanpa modul-modul itu situs tetap jalan, hanya kompresi/header cache yang tidak aktif.
   - Di `php.ini`, aktifkan OPcache (`zend_extension=opcache` dan `opcache.enable=1`) lalu restart Apache, agar runtime PHP lebih ringan.
   - Aktifkan juga ekstensi **GD** (`extension=gd`) lalu restart Apache: setiap unggahan foto otomatis dibuat varian ringan (maks 1600px, WebP) agar halaman cepat dimuat — file asli tetap tersimpan sebagai arsip. Tanpa GD, unggahan tetap jalan memakai file asli apa adanya.

## Akun Default

Semua akun memakai kata sandi **`demo123`**.

| Peran   | Username         | Keterangan              |
|---------|------------------|--------------------------|
| Admin   | `admin`          | Akses penuh area admin  |
| Anggota | `ahmad.fauzan`   | Ketua Komunitas         |
| Anggota | `nabila.putri`   | Koordinator IoT         |
| Anggota | `rafi.ardiansyah`| Anggota baru            |
| Anggota | `zahra.amelia`   | Anggota                 |
| Anggota | `bagas.dwi`      | Anggota                 |
| Anggota | `keisha.ayu`     | Koordinator AI          |

## Struktur Folder

```
public/      -> document root — seluruh file yang diakses browser
  *.php      -> halaman anggota (beranda, karya, galeri, materi, profil, dst.)
admin/     -> halaman area admin (dashboard, moderasi, CRUD generik)
  assets/    -> CSS, ilustrasi SVG ( buatan sendiri, bukan foto berhak cipta)
  uploads/   -> tujuan upload foto karya/galeri/anggota + template Pakta Integritas
app/         -> kode aplikasi (tidak diakses langsung dari browser)
  config/    -> loader .env, autoload kelas, koneksi database (PDO)
  core/      -> kelas dasar: Model, Auth, Csrf, helpers, konfigurasi CRUD
  models/    -> kelas entitas (Anggota, Karya, Galeri, Kegiatan, Lomba, Pengumuman, Materi, Referensi, Pengaturan)
  includes/  -> bootstrap, header/footer/navbar & partial tampilan yang dipakai berulang
storage/     -> berkas privat yang hanya dilayani lewat kode PHP (mis. scan Pakta Integritas anggota)
database.sql -> skema + data contoh
.env         -> konfigurasi lokal (jangan masuk repository)
```

## Catatan Implementasi

- **Tanpa framework/MVC** sesuai PRD — tiap halaman adalah skrip PHP mandiri yang memakai kelas-kelas OOP di `core/` dan `models/`.
- **CRUD admin generik**: satu file `public/admin/crud.php` melayani 8 entitas (anggota, materi, referensi, karya, galeri, kegiatan, lomba, pengumuman) lewat parameter `?entity=`, dikonfigurasi di `app/core/CrudConfig.php` — supaya kode tidak berulang untuk tiap entitas. Sejak fitur Referensi digabung ke Materi, menu Referensi dihapus: pengelolaannya lewat panel di halaman `crud.php?entity=materi`, dan `?entity=referensi` otomatis dialihkan ke sana.
- **Referensi di dalam Materi**: di sisi anggota, daftar Referensi Belajar tampil sebagai bagian di `public/materi.php#referensi` (tautan beranda juga mengarah ke sana); tidak ada lagi halaman `public/referensi.php` maupun menu Referensi di navbar.
- **Menu admin = menu anggota**: halaman Karya, Lomba, dan Materi memakai `Auth::requireMember()` (anggota + admin), dan navbar hanya menyembunyikan menu tersebut untuk pengunjung yang belum login — sehingga admin melihat seluruh menu anggota sesuai PRD tanpa dead link. Tamu tetap hanya melihat Beranda, Galeri, Pelatihan, Prestasi, dan Pengumuman.
- **Keamanan dasar**: password di-hash (bcrypt via `password_hash`), proteksi CSRF token di setiap form POST, query database memakai prepared statement (PDO), validasi tipe & ukuran file upload.
- **Moderasi Karya**: alur Pending → Approved/Rejected ada di `public/admin/moderasi.php`, termasuk catatan alasan penolakan yang akan tampil ke anggota di halaman Profil Saya.
- **Gambar/ilustrasi**: karena ini data contoh, foto anggota/karya/galeri memakai ilustrasi SVG flat buatan sendiri (folder `assets/img/`) — bukan foto asli — agar bebas hak cipta dan mudah diganti nanti dengan foto sungguhan.
- **Template Pakta Integritas**: formulir kosong diunggah admin lewat `public/admin/pakta-template.php` (PDF/DOC/DOCX, maks 5MB) dan path-nya disimpan di tabel `pengaturan` (kunci `pakta_template`). Halaman publik `public/pakta-template.php` men-stream berkas tersebut tanpa login, jadi bisa ditaruh sebagai tombol unduh di `public/daftar.php`. Selama template belum diunggah, halaman unduhan menampilkan pemberitahuan "template belum tersedia". Mengganti atau menghapus template juga menghapus berkas lama di `public/uploads/pakta-integritas/`.
- Sudah diuji end-to-end di lingkungan PHP 8.3 + MariaDB: login, seluruh halaman anggota & admin, ajukan karya → moderasi approve/reject → tampil di portal, serta CRUD (tambah/ubah/hapus).

## Mengganti Gambar dengan Foto Asli

Cukup unggah foto lewat form admin (field "Foto") pada tiap entitas, atau ganti manual file di `assets/img/` dan `uploads/` lalu perbarui kolom `foto` di database.
