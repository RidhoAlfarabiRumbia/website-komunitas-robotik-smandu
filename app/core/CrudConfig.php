<?php

/**
 * Konfigurasi field per entitas untuk halaman admin/crud.php.
 * type: text | textarea | select | date | file | video | doc_file | anggota_select
 *       | akun_username | akun_email | password | pelatihan_multi | anggota_multi
 *
 * 'search' (opsional) = kolom yang dicari di kotak pencarian daftar; kolom
 * '_cari_*' diisi sementara oleh crud.php (mis. username, nama pembuat) dan
 * tidak ada di tabel. Entitas tanpa kolom teks (mis. slider) memakai [].
 */
function crud_config(): array
{
    return [
        'anggota' => [
            'label' => 'Anggota', 'model' => Anggota::class,
            'fields' => [
                'nama' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
                'kelas' => ['label' => 'Kelas', 'type' => 'text', 'required' => true],
                'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea'],
                'foto' => ['label' => 'Foto', 'type' => 'file', 'subfolder' => 'anggota'],
                'username' => ['label' => 'Username Login', 'type' => 'akun_username'],
                'akun_email' => ['label' => 'Email Login', 'type' => 'akun_email'],
                'password' => ['label' => 'Kata Sandi Login', 'type' => 'password'],
            ],
            'list' => ['nama', 'kelas', 'username', 'akun_email'],
            'search' => ['nama', 'kelas', 'kontak', 'deskripsi', '_cari_username', '_cari_email'],
        ],
        'materi' => [
            'label' => 'Materi', 'model' => Materi::class,
            'fields' => [
                'judul' => ['label' => 'Judul', 'type' => 'text', 'required' => true],
                'topik' => ['label' => 'Topik', 'type' => 'text', 'required' => true],
                'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea'],
                // Materi bersifat terbatas (hanya peserta pelatihan yang disetujui),
                // jadi berkas WAJIB disimpan di luar document root: restricted => true.
                'file_path' => ['label' => 'File Dokumen', 'type' => 'doc_file', 'subfolder' => 'materi', 'restricted' => true],
                'link_url' => ['label' => 'Tautan Link', 'type' => 'text', 'link' => true],
                'pelatihan_ids' => ['label' => 'Pelatihan Tertaut', 'type' => 'pelatihan_multi'],
            ],
            'list' => ['judul', 'topik'],
            'search' => ['judul', 'topik', 'deskripsi'],
        ],
        'referensi' => [
            'label' => 'Referensi', 'model' => Referensi::class,
            'fields' => [
                'nama' => ['label' => 'Nama Referensi', 'type' => 'text', 'required' => true],
                'url' => ['label' => 'URL', 'type' => 'text', 'required' => true],
                'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea'],
            ],
            'list' => ['nama', 'url', 'deskripsi'],
            'search' => ['nama', 'url', 'deskripsi'],
        ],
        'karya' => [
            'label' => 'Karya', 'model' => Karya::class,
            'fields' => [
                'judul' => ['label' => 'Judul', 'type' => 'text', 'required' => true],
                'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea'],
                'progres' => ['label' => 'Progres', 'type' => 'select', 'options' => ['Sedang Dikerjakan','Selesai']],
                'publikasi' => ['label' => 'Publikasi', 'type' => 'select', 'options' => ['Menunggu','Disetujui','Ditolak']],
                'pembuat_id' => ['label' => 'Pembuat (bisa lebih dari satu)', 'type' => 'anggota_multi', 'required' => true],
                'teknologi' => ['label' => 'Teknologi (pisahkan koma)', 'type' => 'text'],
                // 'link' = wajib berupa tautan http/https (lihat crud_handle.php).
                // Tanpa ini, admin bisa menyimpan javascript: yang dieksekusi
                // saat anggota mengeklik tautannya di karya-detail.php.
                'link_demo' => ['label' => 'Tautan Proyek', 'type' => 'text', 'link' => true],
                'foto' => ['label' => 'Foto', 'type' => 'file', 'subfolder' => 'karya'],
            ],
            'list' => ['judul', 'progres', 'publikasi'],
            'search' => ['judul', 'deskripsi', 'teknologi', '_cari_pembuat'],
        ],
        'galeri' => [
            'label' => 'Galeri', 'model' => Galeri::class,
            'fields' => [
                'judul' => ['label' => 'Judul', 'type' => 'text', 'required' => true],
                'kategori' => ['label' => 'Kategori', 'type' => 'select', 'options' => ['Latihan','Workshop','Lomba','Prestasi','Pameran','Demo Robot','Proses Proyek']],
                'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea'],
                'tanggal' => ['label' => 'Tanggal', 'type' => 'date', 'required' => true],
                'foto' => ['label' => 'Foto', 'type' => 'file', 'subfolder' => 'galeri'],
                'video' => ['label' => 'Link Video', 'type' => 'video'],
            ],
            'list' => ['judul', 'kategori', 'tanggal'],
            'search' => ['judul', 'kategori', 'deskripsi'],
            // Foto dirender di dalam kartu form utama (lebar penuh), bukan
            // panel terpisah di kanan — lihat crud.php.
            'foto_inline' => true,
        ],
        'hero' => [
            'label' => 'Slider', 'model' => Hero::class,
            'fields' => [
                'foto' => ['label' => 'Gambar', 'type' => 'file', 'subfolder' => 'hero'],
            ],
            'list' => [],
            // Slider hanya berisi foto: tidak ada kolom teks, jadi kotak
            // pencarian disembunyikan (lihat crud.php, $cfg['search']).
            'search' => [],
            // Slider hanya punya 1 field (foto), jadi foto dirender di dalam
            // kartu utama — bukan panel terpisah di kanan (lihat crud.php).
            'foto_inline' => true,
            // Kolom judul, deskripsi, urutan, & tautan sudah dihapus dari skema
            // database: slider murni upload gambar.
            // Status tidak jadi field form/list: tombol toggle di kolom Aksi
            // (lihat crud.php, action=toggle_status).
            'toggle_status' => ['label' => 'Status'],
        ],
        'kegiatan' => [
            'label' => 'Kegiatan', 'model' => Kegiatan::class,
            'fields' => [
                'nama' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
                'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea'],
                'tanggal' => ['label' => 'Tanggal', 'type' => 'date', 'required' => true],
                'waktu' => ['label' => 'Waktu', 'type' => 'text'],
                'lokasi' => ['label' => 'Lokasi', 'type' => 'text'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Akan Datang','Selesai']],
                'video' => ['label' => 'Link Video', 'type' => 'video', 'show_when' => ['status' => 'Selesai']],
            ],
            'list' => ['nama', 'tanggal', 'lokasi', 'status'],
            'search' => ['nama', 'deskripsi', 'lokasi', 'waktu'],
        ],
        'lomba' => [
            'label' => 'Lomba', 'model' => Lomba::class,
            'fields' => [
                'nama' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
                'penyelenggara' => ['label' => 'Penyelenggara', 'type' => 'text'],
                'tanggal' => ['label' => 'Tanggal', 'type' => 'date', 'required' => true],
                'lokasi' => ['label' => 'Lokasi', 'type' => 'text'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Akan Datang','Selesai']],
                'hasil' => ['label' => 'Hasil', 'type' => 'text'],
            ],
            'list' => ['nama', 'tanggal', 'status', 'hasil'],
            'search' => ['nama', 'penyelenggara', 'lokasi', 'hasil'],
        ],
        'pengumuman' => [
            'label' => 'Pengumuman', 'model' => Pengumuman::class,
            'fields' => [
                'judul' => ['label' => 'Judul', 'type' => 'text', 'required' => true],
                'isi' => ['label' => 'Isi', 'type' => 'textarea', 'required' => true],
                'tanggal' => ['label' => 'Tanggal', 'type' => 'date', 'required' => true],
            ],
            'list' => ['judul', 'tanggal'],
            'search' => ['judul', 'isi'],
        ],
        'pelatihan' => [
            'label' => 'Pelatihan', 'model' => Pelatihan::class,
            'fields' => [
                'nama' => ['label' => 'Nama Pelatihan', 'type' => 'text', 'required' => true],
                'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea'],
                'tanggal_mulai' => ['label' => 'Tanggal Mulai', 'type' => 'date', 'required' => true],
                'tanggal_selesai' => ['label' => 'Tanggal Selesai', 'type' => 'date'],
                'waktu' => ['label' => 'Waktu', 'type' => 'text'],
                'lokasi' => ['label' => 'Lokasi', 'type' => 'text'],
                'mode' => ['label' => 'Mode Pelaksanaan', 'type' => 'select', 'options' => ['Daring','Luring']],
                'status' => ['label' => 'Status Pendaftaran', 'type' => 'select', 'options' => ['Dibuka','Ditutup']],
                'akses' => ['label' => 'Sifat Akses', 'type' => 'select', 'options' => ['Anggota','Umum']],
            ],
            'list' => ['nama', 'tanggal_mulai', 'mode', 'status', 'akses'],
            'search' => ['nama', 'deskripsi', 'lokasi', 'waktu'],
        ],
        'prestasi' => [
            'label' => 'Prestasi', 'model' => Prestasi::class,
            'fields' => [
                'nama' => ['label' => 'Nama Prestasi', 'type' => 'text', 'required' => true],
                'lomba_teks' => ['label' => 'Nama Lomba', 'type' => 'text'],
                'tanggal' => ['label' => 'Tanggal', 'type' => 'date', 'required' => true],
                'tingkat' => ['label' => 'Tingkat', 'type' => 'select', 'options' => ['Sekolah','Kota','Provinsi','Nasional','Internasional']],
                'hasil' => ['label' => 'Hasil', 'type' => 'text'],
                'deskripsi' => ['label' => 'Ceritakan Singkat', 'type' => 'textarea'],
                'foto' => ['label' => 'Foto', 'type' => 'file', 'subfolder' => 'content'],
                'sertifikat' => ['label' => 'Sertifikat', 'type' => 'doc_file', 'subfolder' => 'sertifikat'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Tayang','Disembunyikan']],
                'peraih' => ['label' => 'Peraih Prestasi', 'type' => 'peraih'],
            ],
            'list' => ['nama', 'lomba_nama', 'tingkat', 'hasil', 'tanggal', 'status'],
            'search' => ['nama', 'lomba_teks', 'lomba_nama', 'hasil', 'deskripsi'],
            'column_labels' => ['lomba_nama' => 'Lomba'],
        ],
    ];
}
