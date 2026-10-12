PRODUCT REQUIREMENTS DOCUMENT (PRD)
Portal Komunitas Robotik SMA Negeri 2
Revisi 1.1 — berdasarkan hasil wawancara pembina komunitas, Oktober 2026
0. Riwayat Revisi
Versi	Tanggal	Perubahan
1.0	September 2026	Draft awal PRD berdasarkan diskusi awal tim.
1.1	Oktober 2026	Revisi berdasarkan hasil wawancara dengan pembina komunitas. Perubahan utama:
•	Akses Materi dibatasi per-Pelatihan/Workshop, tidak otomatis terbuka untuk semua anggota
•	Penambahan modul Pelatihan/Workshop (jadwal, pendaftaran peserta, kontrol akses materi)
•	Penambahan Pendaftaran Anggota dengan Pakta Integritas (formulir + tanda tangan/meterai)
•	Prestasi dipisahkan dari Galeri menjadi modul tersendiri dengan bukti sertifikat dan publikasi langsung (pasca-moderasi)
•	Dukungan upload video untuk dokumentasi kegiatan, selain foto
•	Penegasan prioritas tampilan mobile/Android
•	Penyesuaian hak akses, struktur navigasi, dan prioritas pengembangan

1. Informasi Produk
Item	Keterangan
Nama Produk	Portal Komunitas Robotik SMA Negeri 2
Jenis	Website komunitas dan portal aktivitas siswa
Platform	Web (prioritas tampilan mobile/Android, tetap kompatibel desktop)
Target Pengguna	Admin/Pengelola (2 akun: pembina komunitas dan 1 murid yang ditugaskan), Anggota Komunitas Robotik, dan Pengunjung Umum (guru, kepala sekolah, siswa luar sekolah, mitra — akses terbatas tanpa login)
Teknologi Rencana	Native PHP OOP, MySQL, Bootstrap, PDO, .env

2. Ringkasan Produk
Portal digital yang menjadi pusat informasi, pembelajaran, aktivitas, karya, dan dokumentasi komunitas robotik. Anggota tidak hanya menjadi pengguna informasi, tetapi juga dapat berkontribusi melalui pengajuan dan publikasi karya. Sebagian materi pembelajaran bersifat terbatas dan hanya terbuka bagi anggota yang terdaftar pada Pelatihan/Workshop tertentu, sebagai bentuk komitmen dan keberlanjutan pendanaan komunitas.
Siklus utama: Belajar → Berkarya → Berkompetisi → Berbagi → Regenerasi
3. Latar Belakang
•	Materi dan referensi pembelajaran robotik tersebar di berbagai platform seperti Scratch, PictoBlox, Arduino, Tinkercad, GitHub, dan platform lainnya.
•	Anggota baru dapat kesulitan menentukan materi atau sumber belajar yang tepat untuk memulai.
•	Karya siswa dan dokumentasi kegiatan berisiko tercecer atau hilang ketika terjadi pergantian generasi.
•	Informasi kegiatan, lomba, dan pencapaian komunitas belum terpusat.
•	Publikasi kegiatan sebelumnya hanya melalui media sosial pribadi (WhatsApp, Facebook, Instagram, TikTok) yang dinilai kurang efektif, sulit diukur, dan sulit diverifikasi oleh pihak luar seperti sekolah maupun Kementerian Pendidikan.
•	Diperlukan media yang menjadi pusat informasi sekaligus wadah menampilkan karya anggota dan bukti pencapaian (prestasi) secara resmi.
4. Tujuan Produk
•	Menyediakan pusat informasi digital bagi komunitas robotik.
•	Menyediakan materi pembelajaran dan referensi yang mudah diakses oleh anggota yang berhak.
•	Mendokumentasikan karya anggota secara terstruktur.
•	Memberikan ruang bagi anggota untuk memamerkan karya.
•	Mendokumentasikan kegiatan komunitas, prestasi, dan pengalaman mengikuti lomba.
•	Menyimpan riwayat pencapaian dan aktivitas sebagai referensi generasi berikutnya.
•	Mendukung regenerasi melalui dokumentasi pengetahuan dan karya.
•	Menjadi sarana publikasi resmi yang dapat diakses pihak luar (guru, kepala sekolah, mitra) untuk mendukung kredibilitas dan keberlanjutan program di hadapan sekolah maupun pemerintah.
•	Membuka akses belajar bagi guru dan siswa di luar sekolah melalui pendaftaran pelatihan/webinar yang dibuka untuk umum.
5. Pengguna Sistem
5.1 Admin
Terdapat dua akun Admin: pembina komunitas dan satu murid yang ditugaskan membantu pengelolaan. Keduanya memiliki hak akses admin yang setara.
•	Mengelola anggota, materi, referensi, karya, pelatihan/workshop, prestasi, galeri, kegiatan, lomba, dan pengumuman.
•	Memoderasi pengajuan karya dari anggota.
•	Mengelola pendaftaran peserta pelatihan/workshop dan menentukan akses materi terkait.
•	Memverifikasi pendaftaran anggota baru beserta dokumen Pakta Integritas.
•	Meninjau kembali (post-moderation) publikasi prestasi apabila ditemukan indikasi tidak valid.
5.2 Anggota
•	Melihat beranda, materi & referensi, karya, galeri, prestasi, kegiatan, lomba, pengumuman, dan daftar anggota.
•	Mendaftar sebagai peserta Pelatihan/Workshop untuk mendapatkan akses materi terkait selama periode berlangsung.
•	Melihat/mengunduh Materi hanya untuk pelatihan yang diikuti dan disetujui.
•	Mengelola profil sendiri.
•	Mengisi formulir pendaftaran dan Pakta Integritas saat bergabung sebagai anggota.
•	Mengajukan karya untuk ditampilkan di portal (melalui moderasi admin).
•	Mengunggah prestasi/pencapaian beserta bukti sertifikat (tayang langsung tanpa moderasi).
•	Melihat riwayat karya yang pernah diajukan.
5.3 Pengunjung (Publik, tanpa login)
Meliputi guru, kepala sekolah, siswa di luar sekolah, dan mitra eksternal. Dapat mengakses Beranda, Prestasi, Galeri, Pengumuman, dan daftar Pelatihan/Workshop yang ditandai terbuka untuk umum, tanpa perlu membuat akun.
6. Prinsip UX
Website tidak boleh terasa seperti aplikasi CRUD atau sistem administrasi. Tampilan utama harus terasa sebagai portal komunitas yang menonjolkan konten, aktivitas, karya, dan identitas komunitas.
•	Fokus anggota: Konten → Aktivitas → Karya → Komunitas.
•	Fokus admin: Kelola → Moderasi → Perbarui → Dokumentasikan.
•	Informasi penting mudah ditemukan dari beranda.
•	Tampilan sederhana, responsif, konsisten, dan mudah dipahami siswa.
•	Prioritas utama desain adalah pengalaman di layar smartphone (Android), karena mayoritas anggota mengakses melalui HP, bukan laptop; tampilan desktop tetap didukung sebagai pelengkap.
7. Struktur Navigasi
Anggota:
•	Beranda
•	Belajar: Materi (akses sesuai pendaftaran Pelatihan) + Referensi (terbuka, tampil di dalam halaman Materi)
•	Pelatihan / Workshop
•	Karya
•	Prestasi
•	Galeri
•	Aktivitas: Kegiatan, Lomba
•	Pengumuman
•	Komunitas: Anggota
•	Profil Saya: Karya Saya, Pakta Integritas
Admin:
•	Seluruh menu anggota.
•	Kelola: Anggota, Materi (termasuk panel Referensi), Karya, Pelatihan/Workshop (+ peserta), Prestasi, Galeri, Kegiatan, Lomba, Pengumuman.
•	Moderasi: Pengajuan Karya, Peninjauan Prestasi (bila diperlukan).
Pengunjung (tanpa login):
•	Beranda, Prestasi, Galeri, Pengumuman, dan Pelatihan/Workshop yang ditandai terbuka untuk umum.
8. Kebutuhan Fitur
8.1 Beranda
•	Identitas dan deskripsi singkat komunitas.
•	Aktivitas terbaru, karya terbaru, pengumuman terbaru, informasi lomba, prestasi terbaru, dan dokumentasi galeri.
•	Statistik komunitas bila diperlukan.
•	Dapat diakses tanpa login oleh pengunjung umum.
8.2 Materi
•	Materi Arduino, IoT, Python, AI, robotik, pemrograman, dan elektronika dasar.
•	Akses dibatasi: materi hanya dapat dilihat/diunduh oleh anggota yang terdaftar dan disetujui sebagai peserta pada Pelatihan/Workshop terkait, selama periode pelatihan berlangsung — tidak otomatis terbuka untuk seluruh anggota.
•	Admin dapat CRUD materi dan mengaitkan materi dengan satu atau lebih Pelatihan/Workshop.
•	Referensi (lihat 8.3) digabung ke dalam fitur ini: tampil sebagai bagian terpisah di halaman Materi dan dikelola lewat panel di halaman Materi admin.
8.3 Referensi (digabung ke Materi)
•	Sumber eksternal seperti Arduino, Tinkercad, PictoBlox, Scratch, GitHub, dan Python.
•	Setiap referensi memiliki nama, deskripsi, dan URL.
•	Tetap terbuka untuk seluruh anggota karena berupa tautan ke sumber gratis di luar sistem — tanpa kontrol akses Pelatihan.
•	Admin mengelola referensi lewat panel Referensi di halaman Materi; tidak ada menu/entitas Referensi terpisah lagi.
8.4 Pelatihan / Workshop (Baru)
Modul baru untuk mengelola kegiatan belajar terjadwal (mis. "Workshop Mini") yang menjadi syarat akses Materi tertentu.
•	Informasi: nama, deskripsi, jadwal, kuota peserta, status (Dibuka/Ditutup), dan penanda "Terbuka untuk Umum" atau "Khusus Anggota".
•	Anggota (atau pengunjung, jika ditandai terbuka untuk umum) dapat mendaftar sebagai peserta.
•	Admin menyetujui/menolak pendaftaran peserta sesuai kuota.
•	Materi yang dikaitkan dengan pelatihan hanya terbuka bagi peserta yang disetujui, selama status pelatihan aktif.
•	Riwayat pelatihan yang pernah diikuti tersimpan pada profil anggota.
8.5 Karya
•	Karya individu maupun tim.
•	Informasi: judul, deskripsi, pembuat, tanggal, status progres, foto, dokumentasi, dan tautan proyek/demo bila ada.
•	Status progres: Idea, In Progress, Completed, Showcased.
•	Pengajuan anggota harus melalui moderasi (pra-publikasi).
8.6 Pengajuan Karya
•	Anggota mengisi informasi karya.
•	Status awal Pending.
•	Admin review lalu Approved atau Rejected.
•	Karya Approved dapat ditampilkan pada portal.
8.7 Prestasi (Baru, terpisah dari Galeri)
Modul khusus untuk mendokumentasikan pencapaian/kejuaraan komunitas sebagai bukti fisik yang juga dibutuhkan sekolah.
•	Informasi: nama pencapaian/lomba, anggota atau tim terkait, tanggal, tingkat (sekolah/kota/provinsi/nasional/internasional), foto kegiatan, dan file bukti sertifikat (wajib diunggah).
•	Publikasi langsung (pasca-moderasi): tidak memerlukan persetujuan admin sebelum tayang. Admin hanya meninjau dan dapat menyembunyikan/menghapus apabila kemudian ditemukan tidak valid (fiktif).
•	Berbeda dengan Karya, yang wajib disetujui admin terlebih dahulu sebelum tayang.
8.8 Galeri
•	Dokumentasi umum perjalanan komunitas: latihan, workshop, demo robot, pameran, dan proses proyek.
•	Memiliki judul, deskripsi, tanggal, serta relasi ke kegiatan/lomba/karya bila diperlukan.
•	Dokumentasi prestasi/sertifikat tidak lagi ditampung di sini, melainkan pada modul Prestasi (lihat 8.7).
8.9 Kegiatan
•	Latihan, workshop, rapat, demo, dan persiapan lomba.
•	Informasi: nama, deskripsi, tanggal, waktu, lokasi.
8.10 Lomba
•	Informasi kompetisi yang diikuti atau menjadi target komunitas.
•	Nama, penyelenggara, tanggal, lokasi, kategori, status, hasil, dan peserta.
•	Menjadi rekam jejak kompetisi komunitas.
8.11 Pengumuman
•	Informasi pendaftaran, perubahan jadwal, deadline proyek, dan informasi komunitas.
•	Admin mengelola; anggota dan pengunjung dapat melihat.
8.12 Pendaftaran Anggota dan Pakta Integritas (Baru)
•	Calon anggota mengisi formulir pendaftaran (data diri, kelas, kontak).
•	Pakta Integritas: pernyataan komitmen keanggotaan yang diisi dan ditandatangani (bila perlu bermeterai), kemudian diunggah sebagai dokumen/hasil pindai.
•	Admin memverifikasi formulir dan dokumen sebelum akun anggota diaktifkan.
•	Status Pakta Integritas (sudah/belum lengkap) terlihat pada profil anggota.
8.13 Profil Anggota
•	Nama, kelas, deskripsi singkat, jumlah karya, jumlah lomba, jumlah kegiatan, riwayat pelatihan yang diikuti, dan daftar karya.
•	Menampilkan status kelengkapan Pakta Integritas.
•	Berfungsi sebagai rekam jejak kontribusi anggota.
8.14 Upload Media (Baru)
•	Mendukung unggah foto (karya, galeri, prestasi) dan video (dokumentasi/laporan kegiatan).
•	Video laporan kegiatan dapat berdurasi lebih panjang (kurang lebih hingga 15–20 menit), berbeda dari video promosi singkat (1–4 menit); batas ukuran file video dibuat lebih besar dibanding foto.
•	Validasi tipe dan ukuran file tetap diterapkan untuk setiap jenis upload.
9. Perbedaan Galeri, Karya, Prestasi, dan Profil
Fitur	Fokus
Galeri	Apa yang dilakukan komunitas? Dokumentasi umum perjalanan dan aktivitas (latihan, workshop, demo, proses proyek).
Karya	Apa yang dibuat anggota? Hasil karya individu atau tim, wajib melalui moderasi admin sebelum tayang.
Prestasi	Apa yang dicapai/dimenangkan? Bukti pencapaian/kejuaraan beserta sertifikat, tayang langsung tanpa menunggu moderasi.
Profil	Siapa anggotanya dan apa kontribusinya? Rekam jejak individu, termasuk status Pakta Integritas.

10. Hak Akses
Fitur	Admin	Anggota	Pengunjung
Beranda	Lihat	Lihat	Lihat
Materi + Referensi	CRUD materi; CRUD referensi lewat panel Materi	Lihat/Unduh materi (jika peserta Pelatihan terkait disetujui); Lihat referensi	-
Pelatihan/Workshop	CRUD + approve peserta	Lihat + Daftar	Lihat + Daftar (jika terbuka umum)
Karya	CRUD + Moderasi	Lihat + Submit	-
Prestasi	Kelola (post-moderation)	Lihat + Submit (auto-publish)	Lihat
Galeri	CRUD	Lihat	Lihat
Kegiatan	CRUD	Lihat	-
Lomba	CRUD	Lihat	-
Pengumuman	CRUD	Lihat	Lihat
Anggota	CRUD + verifikasi pendaftaran	Lihat	-
Pakta Integritas	Verifikasi	Isi + Upload (saat pendaftaran)	-
Profil	Kelola	Edit sendiri	-
Akun	Kelola	Edit sendiri	-
Moderasi	Ya	Tidak	Tidak

11. Alur Moderasi Karya
1. Anggota membuka Profil Saya / Karya Saya.
2. Memilih Tambah Karya dan mengisi informasi.
3. Sistem menyimpan pengajuan dengan status Pending.
4. Admin melakukan review.
5. Admin memilih Approved atau Rejected.
6. Jika Approved, karya ditampilkan pada portal.
7. Jika Rejected, karya tidak dipublikasikan dan dapat diperbaiki/diajukan kembali.
11.1 Alur Publikasi Prestasi (Baru)
1. Anggota mengunggah data prestasi beserta foto kegiatan dan file bukti sertifikat.
2. Sistem langsung menayangkan prestasi tersebut pada portal tanpa menunggu persetujuan admin.
3. Admin dapat meninjau kembali sewaktu-waktu.
4. Jika ditemukan tidak valid/fiktif, admin menyembunyikan atau menghapus data prestasi tersebut.
11.2 Alur Pendaftaran Anggota dan Pakta Integritas (Baru)
1. Calon anggota mengisi formulir pendaftaran.
2. Calon anggota mengisi dan menandatangani Pakta Integritas, lalu mengunggah hasil pindai.
3. Admin memverifikasi kelengkapan formulir dan dokumen.
4. Jika lengkap dan sesuai, akun anggota diaktifkan.
11.3 Alur Pendaftaran Pelatihan/Workshop (Baru)
1. Admin membuat Pelatihan/Workshop baru: jadwal, kuota, dan status (khusus anggota atau terbuka umum).
2. Anggota (atau pengunjung, jika terbuka umum) mendaftar sebagai peserta.
3. Admin menyetujui peserta sesuai kuota.
4. Peserta yang disetujui mendapat akses ke Materi yang dikaitkan dengan pelatihan tersebut, selama periode berlangsung.
12. Kebutuhan Non-Fungsional
12.1 Keamanan
•	Password menggunakan hashing.
•	Session untuk autentikasi.
•	Role-based access untuk admin, anggota, dan pengunjung.
•	CSRF pada operasi penting.
•	Validasi input.
•	Validasi tipe dan ukuran file upload (foto maupun video).
•	Dokumen Pakta Integritas (hasil pindai/tanda tangan) disimpan sebagai file dengan akses terbatas hanya untuk admin.
•	Konfigurasi sensitif disimpan di .env dan tidak masuk repository.
12.2 Antarmuka
•	Prioritas utama: tampilan dioptimalkan untuk layar smartphone (Android), karena mayoritas anggota mengakses lewat HP.
•	Tetap responsif dan dapat digunakan dengan baik di desktop.
•	Menggunakan Bootstrap.
•	Tampilan konsisten dan identitas visual komunitas robotik.
•	Konten komunitas menjadi fokus, bukan tabel administrasi.
12.3 Teknologi
•	Native PHP OOP.
•	MySQL dan PDO.
•	.env untuk konfigurasi.
•	Tidak menggunakan Laravel/CodeIgniter/framework PHP.
•	Tidak menggunakan pola MVC.
•	Flutter/API dapat dikembangkan kemudian dengan backend dan database yang sama.
12.4 Media dan Penyimpanan (Baru)
•	Mendukung upload foto dan video.
•	Batas ukuran file video dibuat lebih besar dibanding foto untuk mengakomodasi video laporan kegiatan (± 15–20 menit).
•	Batas ukuran spesifik ditentukan pada tahap implementasi sesuai kapasitas hosting.
12.5 Operasional (Baru)
•	Hosting direncanakan terpisah dari domain utama sekolah (mis. path/subdomain khusus komunitas), dibiayai oleh pembina/komunitas, bukan oleh anggaran sekolah.
•	Perkiraan biaya hosting berlangganan sekitar Rp300.000–Rp500.000 per bulan, menyesuaikan kapasitas dan kecepatan server.
13. Prioritas Pengembangan
Prioritas	Fitur	Keterangan
P1	Login, Pendaftaran Anggota + Pakta Integritas, Beranda, Anggota, Materi (dengan kontrol akses Pelatihan, termasuk Referensi), Pelatihan/Workshop, Karya, Prestasi, Galeri, Kegiatan, Lomba, Pengumuman, Profil, Moderasi Karya	Fungsi inti — tanpa ini sistem belum dapat dipakai pembina
P2	Upload foto/video, tautan proyek, riwayat karya, relasi anggota-karya, peserta lomba, kuota & approval peserta Pelatihan, halaman publik untuk Pengunjung	Pendukung pengalaman portal
P3	Komentar, like, notifikasi, chat, forum, search, tag, activity feed	Ditunda agar scope tetap sederhana

14. User Journey Utama
Journey Anggota: Login → Beranda → Daftar Pelatihan/Workshop → (bila disetujui) Akses Materi Pelatihan (termasuk bagian Referensi) → Melihat karya anggota lain → Mengikuti kegiatan → Membuat karya → Mengajukan karya → Review admin → Karya dipublikasikan
Journey Anggota Baru (Baru): Isi Formulir Pendaftaran → Isi & Unggah Pakta Integritas → Verifikasi Admin → Akun Aktif → Login
Journey Pengunjung (Baru): Buka Beranda tanpa login → Lihat Prestasi / Galeri / Pengumuman → (opsional) Daftar Pelatihan yang terbuka untuk umum
15. Siklus Nilai Produk
BELAJAR → MEMBUAT → MEMPUBLIKASIKAN → DILIHAT → MENJADI REFERENSI → GENERASI BERIKUTNYA BELAJAR → MEMBUAT KARYA BARU
16. Batasan Scope
•	Produk difokuskan sebagai portal komunitas, bukan media sosial penuh.
•	Komentar, like, chat, forum, dan notifikasi tidak menjadi bagian versi awal.
•	Pencarian global dan sistem tag ditunda.
•	Calendar terpisah tidak diperlukan pada versi awal.
•	Akses Materi dibatasi berdasarkan pendaftaran dan persetujuan pada Pelatihan/Workshop terkait, bukan terbuka otomatis untuk seluruh anggota.
•	Verifikasi Prestasi bersifat pasca-publikasi (post-moderation), berbeda dari Karya yang wajib melalui moderasi sebelum tayang (pra-publikasi).
•	Arsip tidak dibuat sebagai modul khusus; data historis tetap disimpan melalui fitur terkait.
17. Catatan Pengembangan Lanjutan
Sebelum implementasi database final, struktur data perlu divalidasi kembali terutama untuk: relasi Pelatihan–Peserta–Materi (akses bersyarat), struktur Prestasi (terpisah dari Galeri, dengan status publikasi pasca-moderasi), penyimpanan dokumen Pakta Integritas, Karya, Galeri, Profil Anggota, hubungan karya dengan anggota, hubungan galeri dengan kegiatan/lomba, serta mekanisme status moderasi. Database sebaiknya mengikuti kebutuhan produk, bukan sekadar kebutuhan CRUD.
