<?php

/**
 * Konfigurasi field per entitas untuk halaman admin/crud.php.
 * type: text | textarea | select | date | file | video | doc_file | anggota_select
 *       | akun_username | password | pelatihan_multi | anggota_multi
 */
function crud_config(): array
{
    return [
        'anggota' => [
            'label' => 'Anggota', 'model' => Anggota::class,
            'fields' => [
                'nama' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
                'kelas' => ['label' => 'Kelas', 'type' => 'text', 'required' => true],
                'fokus' => ['label' => 'Fokus', 'type' => 'text'],
                'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea'],
                'foto' => ['label' => 'Foto', 'type' => 'file', 'subfolder' => 'anggota'],
                'username' => ['label' => 'Username Login', 'type' => 'akun_username'],
                'password' => ['label' => 'Kata Sandi Login', 'type' => 'password'],
            ],
            'list' => ['nama', 'kelas', 'fokus', 'username'],
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
                'link_url' => ['label' => 'Tautan Link', 'type' => 'text'],
                'pelatihan_ids' => ['label' => 'Pelatihan Tertaut', 'type' => 'pelatihan_multi'],
            ],
            'list' => ['judul', 'topik'],
        ],
        'referensi' => [
            'label' => 'Referensi', 'model' => Referensi::class,
            'fields' => [
                'nama' => ['label' => 'Nama Referensi', 'type' => 'text', 'required' => true],
                'url' => ['label' => 'URL', 'type' => 'text', 'required' => true],
                'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea'],
            ],
            'list' => ['nama', 'url', 'deskripsi'],
        ],
        'karya' => [
            'label' => 'Karya', 'model' => Karya::class,
            'fields' => [
                'judul' => ['label' => 'Judul', 'type' => 'text', 'required' => true],
                'kategori' => ['label' => 'Kategori', 'type' => 'select', 'options' => ['Robot','IoT','Programming','AI','Elektronika','Lainnya']],
                'deskripsi' => ['label' => 'Deskripsi', 'type' => 'textarea'],
                'progres' => ['label' => 'Progres', 'type' => 'select', 'options' => ['Sedang Dikerjakan','Selesai']],
                'publikasi' => ['label' => 'Publikasi', 'type' => 'select', 'options' => ['Pending','Approved','Rejected']],
                'pembuat_id' => ['label' => 'Pembuat', 'type' => 'anggota_select', 'required' => true],
                'teknologi' => ['label' => 'Teknologi (pisahkan koma)', 'type' => 'text'],
                'link_demo' => ['label' => 'Tautan Proyek', 'type' => 'text'],
                'foto' => ['label' => 'Foto', 'type' => 'file', 'subfolder' => 'karya'],
            ],
            'list' => ['judul', 'kategori', 'progres', 'publikasi'],
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
        ],
        'lomba' => [
            'label' => 'Lomba', 'model' => Lomba::class,
            'fields' => [
                'nama' => ['label' => 'Nama', 'type' => 'text', 'required' => true],
                'penyelenggara' => ['label' => 'Penyelenggara', 'type' => 'text'],
                'tanggal' => ['label' => 'Tanggal', 'type' => 'date', 'required' => true],
                'lokasi' => ['label' => 'Lokasi', 'type' => 'text'],
                'kategori' => ['label' => 'Kategori', 'type' => 'text'],
                'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Akan Datang','Selesai']],
                'hasil' => ['label' => 'Hasil', 'type' => 'text'],
            ],
            'list' => ['nama', 'tanggal', 'status', 'hasil'],
        ],
        'pengumuman' => [
            'label' => 'Pengumuman', 'model' => Pengumuman::class,
            'fields' => [
                'judul' => ['label' => 'Judul', 'type' => 'text', 'required' => true],
                'isi' => ['label' => 'Isi', 'type' => 'textarea', 'required' => true],
                'tanggal' => ['label' => 'Tanggal', 'type' => 'date', 'required' => true],
            ],
            'list' => ['judul', 'tanggal'],
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
                'kuota' => ['label' => 'Kuota Peserta', 'type' => 'text', 'required' => true],
                'status' => ['label' => 'Status Pendaftaran', 'type' => 'select', 'options' => ['Dibuka','Ditutup']],
                'akses' => ['label' => 'Sifat Akses', 'type' => 'select', 'options' => ['Anggota','Umum']],
            ],
            'list' => ['nama', 'tanggal_mulai', 'kuota', 'status', 'akses'],
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
            'column_labels' => ['lomba_nama' => 'Lomba'],
        ],
    ];
}
