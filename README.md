<div align="center">

# Portal Komunitas Robotik SMA Negeri 2

**Website komunitas robotik: tempat belajar, memamerkan karya, mencatat prestasi, dan mengabarkan kegiatan.**

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL%20%2F%20MariaDB-PDO-4479A1?logo=mysql&logoColor=white)
![Framework](https://img.shields.io/badge/framework-none-lightgrey)
![Status](https://img.shields.io/badge/status-fitur%20inti%20selesai-brightgreen)

[Fitur](#fitur) · [Memulai](#memulai) · [Dokumentasi](#dokumentasi) · [Roadmap](#roadmap)

</div>

<!-- Tambahkan screenshot di sini, misalnya:
![Beranda](docs/screenshots/beranda.png)
-->

## Tentang

Materi belajar robotik tersebar di banyak platform, karya dan dokumentasi tercecer di media sosial pribadi, dan pihak luar sulit memverifikasi pencapaian komunitas. Website ini mengumpulkan semuanya di satu tempat resmi, supaya pengetahuan komunitas tidak hilang saat pergantian generasi.

**Siklus komunitas:** Belajar → Berkarya → Berkompetisi → Berbagi → Regenerasi

- **Mobile-first**: dirancang untuk HP Android, responsif di desktop.
- **Mode terang dan gelap**, mengikuti preferensi sistem.
- **Akses materi terkontrol**: hanya peserta pelatihan yang disetujui.
- **Bisa diverifikasi pihak luar**: prestasi dan dokumentasi terbuka untuk umum tanpa akun.

## Daftar Isi

- [Fitur](#fitur)
- [Peran Pengguna](#peran-pengguna)
- [Teknologi](#teknologi)
- [Memulai](#memulai)
- [Struktur Proyek](#struktur-proyek)
- [Keamanan](#keamanan)
- [Dokumentasi](#dokumentasi)
- [Roadmap](#roadmap)
- [Berkontribusi](#berkontribusi)
- [Lisensi](#lisensi)

## Fitur

| Tahap | Fitur |
|---|---|
| **Belajar** | **Materi** (Arduino, IoT, Python, AI, robotik, elektronika) dengan akses per pelatihan · **Referensi** belajar gratis di halaman Materi · **Pelatihan/Workshop** dengan jadwal, mode pelaksanaan (daring/luring), dan persetujuan peserta |
| **Berkarya** | **Karya** anggota (Robot, IoT, Programming, AI, Elektronika) dengan status progres · **Ajukan Karya** dengan moderasi admin dan alasan penolakan |
| **Berkompetisi** | **Lomba** (info, status, hasil, peserta) · **Prestasi** dengan bukti sertifikat, tayang langsung dan bisa ditinjau admin |
| **Berbagi** | **Galeri** foto dan video · **Kegiatan** dengan video laporan · **Pengumuman** · **Slider Beranda** yang dikelola admin |
| **Akun** | Login username · **Lupa password via email** dengan token sekali pakai · Pengelolaan email, kata sandi, dan foto profil di Profil |
| **Regenerasi** | **Pendaftaran Anggota** dengan Pakta Integritas dan **kunci periode** (buka manual, tutup manual, atau jadwal WIT) · **Profil** berisi rekam jejak karya, prestasi, dan pelatihan |

### Aturan akses utama

- **Materi** hanya terbuka bagi peserta pelatihan terkait yang disetujui, selama pelatihan belum berakhir. Admin selalu punya akses penuh.
- **Karya** ditinjau admin **sebelum** tayang. **Prestasi** tayang langsung dan ditinjau **sesudahnya**.
- **Mode pelaksanaan** pelatihan ditandai **Daring (online)** atau **Luring (tatap muka)**; tanpa batas kuota — admin menyetujui peserta secara manual.
- **Dokumen Pakta Integritas** bersifat privat dan hanya bisa dibuka admin.
- **Pendaftaran anggota** default **tertutup** dan hanya bisa diisi saat admin membuka periode melalui **Admin → Kunci Pendaftaran**. Admin dapat memilih buka manual, tutup manual, atau jadwal tanggal WIT.

## Peran Pengguna

| Peran | Siapa | Bisa apa |
|---|---|---|
| **Pengunjung** | Guru, kepala sekolah, siswa luar, mitra (tanpa login) | Melihat prestasi, galeri, pengumuman, pelatihan terbuka; mendaftar anggota; mengunduh template Pakta Integritas |
| **Anggota** | Anggota terverifikasi | Mengakses materi pelatihan yang diikuti, mendaftar pelatihan, mengajukan karya, mengunggah prestasi, mengelola profil |
| **Admin** | Pembina komunitas dan satu murid yang ditugaskan | Mengelola semua konten, mengatur kunci/periode pendaftaran, memoderasi karya, memverifikasi pendaftaran, menyetujui peserta pelatihan |

## Teknologi

| Lapisan | Pilihan |
|---|---|
| Bahasa | PHP 8.3 (OOP), tanpa framework dan tanpa Composer |
| Database | MySQL / MariaDB lewat PDO |
| Tampilan | CSS kustom, JavaScript vanilla, [Bootstrap Icons](https://icons.getbootstrap.com/), Google Fonts (Poppins, Inter) |
| Server | Apache (XAMPP / Laragon / hosting PHP + MySQL) |

## Memulai

### Prasyarat

- PHP 8.3 dengan ekstensi `openssl` (untuk SMTP TLS)
- MySQL atau MariaDB
- Apache (XAMPP atau Laragon memudahkan di lokal)

### Instalasi

```bash
# 1. Salin proyek ke document root server lokal
#    contoh XAMPP: C:\xampp\htdocs\portal-robotik

# 2. Buat konfigurasi
cp .env.example .env
```

3. Impor `database.sql` lewat phpMyAdmin (membuat database `db_robotik`, tabel, dan data contoh).
4. Sesuaikan `.env`:

   ```env
   APP_NAME="Portal Komunitas Robotik SMA Negeri 2"
   APP_URL=http://localhost/portal-robotik/public
   DB_HOST=localhost
   DB_NAME=db_robotik
   DB_USER=isi_username_database
   DB_PASS=isi_password_database

   MAIL_HOST=smtp.example.com
   MAIL_PORT=587
   MAIL_USERNAME=alamat@example.com
   MAIL_PASSWORD=isi_password_smtp
   MAIL_ENCRYPTION=tls
   MAIL_FROM=alamat@example.com
   MAIL_FROM_NAME="Portal Robotik"
   ```

   `APP_URL` dipakai untuk membentuk tautan reset password di email. Isi `MAIL_*` dengan SMTP penyedia email (gunakan app password bila penyedia mewajibkannya). `MAIL_ENCRYPTION` menerima `tls`, `ssl`, atau `none`.

   Untuk pengujian lokal dengan Gmail: aktifkan 2-Step Verification, buat App Password 16 karakter, gunakan `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_ENCRYPTION=tls`, dan isi `MAIL_FROM` dengan alamat Gmail yang sama seperti `MAIL_USERNAME`. Jangan memakai password login Gmail. Buka tautan reset di komputer yang menjalankan server karena `APP_URL` masih `localhost`.

5. Buka `http://localhost/portal-robotik/public/`.

> Data contoh dan akun awal ada di `database.sql`. Ganti atau hapus akun bawaan sebelum dipakai di produksi. Email akun contoh kosong; isi lewat CRUD Anggota atau halaman Profil agar fitur lupa password bisa dipakai.

> Pendaftaran anggota **default tertutup**. Buka melalui **Admin → Kunci Pendaftaran** untuk memilih buka manual, tutup manual, atau jadwal tanggal WIT.

> **Produksi:** arahkan document root server ke folder `public/`. Kode di `app/`, berkas privat di `storage/`, dan `.env` tidak boleh bisa diakses langsung dari browser.

> **Upgrade dari versi lama:** jalankan `migrate.sql` pada database yang sudah ada (cadangkan dulu).
>
> Jika database masih memiliki kolom `karya.kategori` atau `lomba.kategori`, jalankan `migrate_hapus_kategori_karya.sql` / `migrate_hapus_kategori_lomba.sql` (cadangkan dulu; data kategori lama akan dihapus).
>
> Jika status publikasi karya masih `Pending`/`Approved`/`Rejected`, jalankan `migrate_publikasi_karya_indonesia.sql`.
>
> Untuk fitur lupa password, jalankan `migrate_password_reset.sql` (menambah kolom `users.email` dan tabel `password_resets`). Setelah itu isi email akun yang sudah ada lewat CRUD Anggota atau halaman Profil admin/anggota.
>
> Fitur kunci & periode pendaftaran tidak memerlukan migrasi tambahan. Atur melalui **Admin → Kunci Pendaftaran**.

### Opsional: performa

- Aktifkan ekstensi **GD** (`extension=gd`) agar foto yang diunggah otomatis dibuatkan versi ringan (WebP, maks 1600 px). Tanpa GD, unggahan tetap jalan memakai file asli.
- Aktifkan modul Apache `deflate_module`, `expires_module`, `filter_module` untuk kompresi dan cache aset, serta **OPcache** di `php.ini`.

## Struktur Proyek

<details>
<summary>Lihat struktur folder</summary>

```
portal-robotik/
├── public/                 # document root (satu-satunya folder yang diakses browser)
│   ├── *.php               # halaman anggota dan publik
│   ├── berkas.php          # pelayan berkas privat dengan cek hak akses
│   ├── admin/              # halaman admin
│   ├── assets/             # CSS kustom, ilustrasi SVG
│   └── uploads/            # unggahan publik (foto, video, sertifikat, template Pakta)
├── app/                    # kode aplikasi (tidak diakses langsung dari browser)
│   ├── config/             # loader .env, autoloader, koneksi PDO
│   ├── core/               # Model, Auth, Csrf, helpers, konfigurasi CRUD
│   ├── models/             # kelas entitas
│   └── includes/           # bootstrap, header/footer/navbar, partial
├── storage/terbatas/       # berkas privat (materi, Pakta Integritas)
├── database.sql            # instalasi baru: skema + data contoh
├── migrate.sql             # migrasi dari versi sebelumnya
├── migrate_hapus_kategori_karya.sql # migrasi sekali pakai: hapus kolom karya.kategori
├── migrate_hapus_kategori_lomba.sql # migrasi sekali pakai: hapus kolom lomba.kategori
├── migrate_publikasi_karya_indonesia.sql # migrasi status publikasi karya
├── migrate_password_reset.sql # migrasi email akun + token reset password
└── .env                    # konfigurasi lokal (tidak masuk repository)
```

</details>

## Keamanan

- Kata sandi di-hash dengan bcrypt; setiap form POST dilindungi token CSRF.
- Semua query memakai prepared statement (PDO); semua keluaran HTML di-escape.
- Validasi tipe dan ukuran berkas unggahan dilakukan di server.
- Materi dan dokumen Pakta Integritas disimpan **di luar document root** dan hanya dilayani setelah pemeriksaan hak akses.
- Token reset password disimpan sebagai hash SHA-256, berlaku 30 menit, sekali pakai, dan dibatasi per email/IP.
- Konfigurasi sensitif ada di `.env` dan **tidak boleh di-commit**.

Menemukan celah keamanan? Mohon laporkan secara privat kepada pengelola, jangan lewat issue publik.

## Dokumentasi

| Dokumen | Isi |
|---|---|
| [`PRD.md`](./PRD.md) | Kebutuhan produk: fitur, alur, hak akses, model data |
| [`AGENTS.md`](./AGENTS.md) | Panduan untuk AI agent yang mengerjakan kode proyek ini |

## Roadmap

- [x] Fitur inti: login, pendaftaran + Pakta Integritas, materi dengan akses pelatihan, pelatihan, karya, prestasi, galeri, kegiatan, lomba, pengumuman, profil
- [x] Slider Beranda, mode gelap/terang, upload foto dan video
- [x] Lupa password via email, pengelolaan email akun, dan token reset sekali pakai
- [ ] Perbaikan keamanan: batasi percobaan login, hapus lewat POST + CSRF, cek status di halaman detail karya
- [ ] Statistik komunitas di beranda
- [ ] Pengujian otomatis dan linter
- [ ] Header keamanan tambahan (CSP, X-Frame-Options, HSTS) di produksi
- [ ] Ditunda: komentar, like, notifikasi, chat, forum, pencarian global, tag

Rincian lengkap ada di [`PRD.md`](./PRD.md).

## Berkontribusi

Kontribusi dan saran sangat disambut.

1. Fork repo ini dan buat branch: `git checkout -b feat/nama-fitur`
2. Baca [`AGENTS.md`](./AGENTS.md) dan [`PRD.md`](./PRD.md) agar perubahan sesuai aturan proyek
3. Jalankan `php -l` pada setiap file PHP yang diubah, lalu uji alurnya di browser
4. Commit dengan format [Conventional Commits](https://www.conventionalcommits.org/) (`feat:`, `fix:`, `docs:`) dan buka Pull Request

## Lisensi

Belum ditentukan. Tambahkan file `LICENSE` sebelum repo dibuka untuk umum.
