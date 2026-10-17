<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';
$isLoggedIn = Auth::check();
$isAdmin = $isLoggedIn && Auth::role() === 'admin';
$isAnggota = Auth::role() === 'anggota';
// Admin juga melihat konten anggota di beranda (PRD 7: seluruh menu anggota).
$kontenAnggota = $isAnggota || $isAdmin;

// ---- Mode kelola admin: tambah / ubah / sembunyikan gambar slider beranda ----
$crudEntitas = 'hero';
$crudItemEntity = 'hero';                           // tombol Ubah/Hapus di tiap slide
$crudQs = '?manage=hero';
$crudAktif = $isAdmin && (($_GET['manage'] ?? $_POST['manage'] ?? '') === $crudEntitas);
$crudForm = false;
if ($crudAktif) {
    $crudPath = 'beranda.php';
    $crudParam = 'manage';
    include __DIR__ . '/../app/includes/crud_handle.php';
    $crudForm = $showForm;
}

$heroData = Hero::getSliderData();
$heroSlides = $heroData['slides'];      // first slide for initial render
$heroTunda = $heroData['deferred'];     // remaining slides for lazy load
$nSlides = $heroData['total'] ?? 0;

// Konten publik (PRD 8.3)
$galeriTerbaru = Galeri::all('tanggal DESC', 3);
$pengumumanTerbaru = Pengumuman::all('tanggal DESC', 3);
$pelatihanTerbaru = Pelatihan::untukPengunjung(3);
$prestasiTerbaru = Prestasi::tayang(3);

// Konten khusus anggota & admin
// Karya yang sudah tayang (Approved) ditampilkan juga untuk tamu —
// halaman karya.php kini juga publik.
$karyaTerbaru = Karya::approved(3);
// Lomba & agenda kegiatan ditampilkan juga untuk tamu di beranda (ringkasan
// 3 terbaru); halaman penuhnya (lomba.php, kegiatan.php) kini juga publik.
$kegiatanTerbaru = Kegiatan::all('tanggal DESC', 3);
$lombaTerbaru = Lomba::all('tanggal DESC', 3);
$materiTerbaru = $kontenAnggota
    ? ($isAdmin
        ? Materi::semuaWithPelatihan('topik ASC, judul ASC', 3)
        : Pelatihan::materiUntukAnggota((int) Auth::anggotaId(), true, 3))
    : [];

$fitur = [
    ['bi-mortarboard', 'teal', 'Pelatihan & Workshop', 'Jadwal pelatihan terjadwal dengan kuota dan persetujuan peserta.', 'pelatihan.php', 'Lihat Pelatihan'],
    ['bi-award', 'amber', 'Prestasi Anggota', 'Jeak juara dan penghargaan yang dicapai anggota komunitas.', 'prestasi.php', 'Lihat Prestasi'],
    ['bi-images', 'blue', 'Galeri Kegiatan', 'Dokumentasi latihan, workshop, lomba, dan pameran karya komunitas.', 'galeri.php', 'Buka Galeri'],
    ['bi-megaphone', 'red', 'Pengumuman', 'Informasi terbaru soal pendaftaran, jadwal, dan pengumuman resmi komunitas.', 'pengumuman.php', 'Baca Pengumuman'],
    ['bi-journal-bookmark', 'teal', 'Materi Terstruktur', 'Materi pelatihan bagi peserta yang telah disetujui admin.', 'materi.php', 'Lihat Materi'],
    ['bi-cpu', 'amber', 'Karya Anggota', 'Karya robot, IoT, AI, dan elektronika karya anggota yang sudah tayang.', 'karya.php', 'Jelajahi Karya'],
    ['bi-calendar-event', 'blue', 'Agenda Kegiatan', 'Rangkaian kegiatan komunitas beserta tanggal, waktu, dan lokasinya.', 'kegiatan.php', 'Lihat Agenda'],
    ['bi-trophy', 'red', 'Lomba & Kompetisi', 'Jadwal lomba, informasi pendaftaran, dan informasi kompetisi.', 'lomba.php', 'Lihat Lomba'],
    ['bi-link-45deg', 'teal', 'Referensi Belajar', 'Sumber eksternal yang biasa dipakai anggota untuk belajar dan mengerjakan proyek.', 'materi.php#referensi', 'Lihat Referensi'],
];

$langkah = [
    ['Isi Formulir', 'Lengkapi formulir pendaftaran anggota dan unggah dokumen Pakta Integritas melalui halaman pendaftaran.'],
    ['Tunggu Verifikasi', 'Admin memeriksa dokumen Pakta Integritas lalu mengaktifkan akun anggota kamu.'],
    ['Masuk dan Berkarya', 'Masuk ke portal, ikuti pelatihan, ajukan karya, dan daftarkan prestasi atau lomba.'],
];

// Untuk anggota yang sudah masuk, alur "bergabung" tidak relevan —
// section yang sama diganti menjadi panduan berkarya.
$langkahAnggota = [
    ['Ikuti Pelatihan', 'Daftar pelatihan atau workshop yang sesuai minatmu dan tunggu persetujuan admin agar kamu bisa mengikutinya.'],
    ['Pelajari Materi', 'Unduh materi pelatihan yang sudah tersedia dari halaman Materi sebagai bahan belajar dan latihan.'],
    ['Ajukan Karya', 'Unggah karyamu beserta foto dan deskripsi, lalu admin memoderasi sebelum karya tayang di halaman Karya.'],
];

$pageTitle = 'Beranda';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="hero-frame">
  <section class="welcome-hero">
    <div class="welcome-media hero-media">
      <?php if ($heroSlides): ?>
        <div class="slider">
          <?php $nSlides = $heroData['total'] ?? count($heroSlides); ?>
          <?php foreach ($heroSlides as $i => $h): ?>
            <?php
              $foto = $h['img'] ?? 'assets/img/hero-illustration.svg';
              $fotoKiri = $h['kiri'] ?? $foto;
              $fotoKanan = $h['kanan'] ?? $foto;
              if ($i > 0) continue;
            ?>
            <div class="slide active" style="--slide-img:url('<?= base_url(e($foto)) ?>')">
                <span class="slide-photos">
                  <?php if ($nSlides > 1): ?><img class="ph ph-side" src="<?= base_url(e($fotoKiri)) ?>" alt="" aria-hidden="true" fetchpriority="high" decoding="async"><?php endif; ?>
                  <img class="ph ph-main" src="<?= base_url(e($foto)) ?>" alt="Gambar slider" fetchpriority="high" decoding="async">
                  <?php if ($nSlides > 1): ?><img class="ph ph-side" src="<?= base_url(e($fotoKanan)) ?>" alt="" aria-hidden="true" fetchpriority="high" decoding="async"><?php endif; ?>
                </span>
            </div>
          <?php endforeach; ?>
          <?php if ($isAdmin || $nSlides > 1): ?>
            <div class="dots">
              <?php if ($isAdmin): ?>
                <a class="chip-admin" href="?manage=hero" title="Kelola gambar slider"><i class="bi bi-gear"></i> Kelola Slider</a>
              <?php endif; ?>
              <?php if ($nSlides > 1): ?>
                <?php for ($i = 0; $i < $nSlides; $i++): ?><button type="button" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Ke slide <?= $i + 1 ?>"></button><?php endfor; ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="hero-illustration"><img src="<?= base_url('assets/img/hero-illustration.svg') ?>" alt="Ilustrasi robot"></div>
      <?php endif; ?>
      <?php if ($isAdmin && !$heroSlides): ?>
        <a class="chip-admin" href="?manage=hero" title="Kelola gambar slider"><i class="bi bi-gear"></i> Kelola Slider</a>
      <?php endif; ?>
    </div>
    <div class="welcome-scrim" aria-hidden="true"></div>
    <div class="welcome-copy">
      <?php
        // Sapaan personal hanya untuk anggota (bukan admin/tamu): "Halo {nama}, ..."
        $sapaan = '';
        if ($isAnggota) {
            $a = Auth::anggota();
            $namaAnggota = trim((string) ($a['nama'] ?? ''));
            if ($namaAnggota !== '') {
                $sapaan = 'Halo ' . $namaAnggota . ', ';
            }
        }
      ?>
      <h1><?= e($sapaan) ?>Selamat datang di Komunitas Robotik SMA Negeri 2 Kota Ternate</h1>
      <p>
        Ruang belajar, berkarya, dan berkompetisi.
      </p>
      <div class="welcome-actions">
      <?php if (!$isLoggedIn): ?>
        <a class="btn btn-primary" href="<?= base_url('daftar.php') ?>"><i class="bi bi-person-plus"></i> Daftar Anggota</a>
        <a class="btn btn-outline" href="<?= base_url('login.php') ?>"><i class="bi bi-box-arrow-in-right"></i> Masuk</a>
      <?php elseif ($isAdmin): ?>
        <a class="btn btn-primary" href="<?= base_url('admin/dashboard.php') ?>"><i class="bi bi-speedometer2"></i> Dashboard Admin</a>
        <a class="btn btn-outline" href="<?= base_url('anggota.php') ?>" title="Tambah, ubah, atau hapus anggota langsung di halaman Anggota"><i class="bi bi-people"></i> Kelola Anggota</a>
      <?php else: ?>
        <a class="btn btn-primary" href="<?= base_url('profil.php') ?>"><i class="bi bi-person-circle"></i> Lihat Profil Saya</a>
        <a class="btn btn-outline" href="<?= base_url('ajukan-karya.php') ?>"><i class="bi bi-plus-circle"></i> Ajukan Karya</a>
      <?php endif; ?>
      </div>
      </div>
    </section>
  </div>

  <?php if ($crudAktif): ?>
    <?php if ($msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>
    <section class="section">
      <div class="section-head">
        <div>
          <div class="eyebrow">KELOLA</div>
          <h2><?= $crudForm ? ($editing ? 'Ubah' : 'Tambah') . ' Gambar Slider' : 'Gambar Slider Beranda' ?></h2>
          <p class="desc">Gambar slider yang tampil di bagian atas beranda. Slide yang disembunyikan tidak muncul untuk pengunjung.</p>
        </div>
        <?php if (!$crudForm): ?>
          <a class="btn btn-primary" href="?manage=hero&amp;action=create"><i class="bi bi-plus-circle"></i> Tambah Gambar</a>
        <?php endif; ?>
      </div>

      <?php if ($crudForm): ?>
        <?php include __DIR__ . '/../app/includes/crud_form.php'; ?>
      <?php elseif ($rows): ?>
        <div class="table-wrap">
          <table>
            <thead><tr><th style="width:140px;">Gambar</th><th>Status</th><th style="width:150px;">Aksi</th></tr></thead>
            <tbody>
              <?php foreach ($rows as $row): $tampil = ($row['status'] ?? '') === 'Tampil'; ?>
                <tr>
                  <td><img src="<?= base_url(e($row['foto'] ?? 'assets/img/hero-illustration.svg')) ?>" alt="" loading="lazy" decoding="async" style="display:block; width:120px; height:68px; object-fit:cover; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface-2);"></td>
                  <td><span class="badge <?= $tampil ? 'badge-teal' : 'badge-muted' ?>"><?= $tampil ? 'Tampil' : 'Disembunyikan' ?></span></td>
                  <td>
                    <div class="row-actions">
                      <form method="post" action="<?= e($crudQs) ?>&amp;action=toggle_status&amp;id=<?= (int) $row['id'] ?>" style="display:flex;">
                        <?= Csrf::field() ?>
                        <button type="submit" class="icon-btn<?= $tampil ? ' is-on' : '' ?>" title="<?= $tampil ? 'Sembunyikan dari beranda' : 'Tampilkan di beranda' ?>">
                          <i class="bi <?= $tampil ? 'bi-eye' : 'bi-eye-slash' ?>"></i>
                        </button>
                      </form>
                      <?php $itemId = (int) $row['id']; include __DIR__ . '/../app/includes/partial_admin_actions.php'; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty">Belum ada gambar slider. Klik "Tambah Gambar" untuk mengunggah.</div>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <?php if (!$isAnggota): ?>
  <section class="section reveal">
    <div class="section-head"><h2>Yang bisa kamu temukan di sini</h2></div>
    <div class="grid grid-3 grid-stagger">
      <?php foreach ($fitur as [$icon, $warna, $judul, $deskripsi, $href, $cta]): ?>
        <a class="card feature-card" href="<?= base_url($href) ?>">
          <div class="f-ico" style="background:var(--<?= $warna ?>-dim); color:var(--<?= $warna ?>);"><i class="bi <?= $icon ?>"></i></div>
          <div class="f-ttl"><?= e($judul) ?></div>
          <p class="f-desc"><?= e($deskripsi) ?></p>
          <span class="f-cta"><?= e($cta) ?> <i class="bi bi-arrow-right"></i></span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php
    /* Tiap section dirender sekali ke variabel, lalu dicetak dengan urutan
       berbeda: tamu mendapat urutan Prestasi → Lomba → Karya → Galeri →
       Agenda → Pelatihan → Pengumuman (tanpa Materi); anggota tetap struktur
       lama (grid 2 kolom + Galeri/Karya di bawah). */
    ob_start();
  ?>
  <section class="section reveal">
    <div class="section-head">
      <h2>Prestasi terbaru</h2>
      <a class="see-all" href="<?= base_url('prestasi.php') ?>">Lihat semua prestasi <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if ($prestasiTerbaru): ?>
      <div class="grid grid-3 grid-stagger">
        <?php foreach ($prestasiTerbaru as $p): ?>
          <a href="prestasi-detail.php?id=<?= (int) $p['id'] ?>" class="work-card">
            <div class="work-thumb"><img src="<?= base_url(e($p['foto'] ?? 'assets/img/galeri-prestasi.svg')) ?>" alt="<?= e($p['nama']) ?>" loading="lazy" decoding="async"></div>
            <div class="work-body">
              <span class="badge badge-amber"><i class="bi bi-trophy"></i> <?= e($p['hasil']) ?></span>
              <div class="work-title" style="font-size:14px;"><?= e($p['nama']) ?></div>
              <div class="work-meta"><?= e($p['lomba_nama'] ?? '-') ?> · <?= tanggal_indo($p['tanggal']) ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card"><div class="empty">Belum ada prestasi tercatat.</div></div>
    <?php endif; ?>
  </section>
  <?php $secPrestasi = ob_get_clean(); ?>

  <?php ob_start(); ?>
  <section class="section reveal">
    <div class="section-head">
      <h2>Lomba terdekat</h2>
      <a class="see-all" href="<?= base_url('lomba.php') ?>">Lihat semua lomba <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if ($lombaTerbaru): ?>
      <div class="table-wrap">
        <?php foreach ($lombaTerbaru as $l): ?>
          <div class="list-item list-item-pad">
            <div class="list-thumb"><i class="bi bi-trophy" style="font-size:18px; color:var(--amber);"></i></div>
            <div class="grow">
              <div class="ttl"><?= e($l['nama']) ?></div>
              <div class="meta"><?= tanggal_indo($l['tanggal']) ?> · <?= e($l['lokasi']) ?></div>
            </div>
            <?php if ($l['hasil']): ?><span class="badge badge-amber"><i class="bi bi-trophy"></i> <?= e($l['hasil']) ?></span><?php endif; ?>
            <span class="badge <?= $l['status'] === 'Selesai' ? 'badge-muted' : ($l['status'] === 'Akan Datang' ? 'badge-blue' : 'badge-teal') ?>"><?= e($l['status']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card"><div class="empty">Belum ada lomba tercatat.</div></div>
    <?php endif; ?>
  </section>
  <?php $secLomba = ob_get_clean(); ?>

  <?php ob_start(); ?>
  <section class="section reveal">
    <div class="section-head">
      <h2>Karya terbaru</h2>
      <a class="see-all" href="<?= base_url('karya.php') ?>">Lihat semua karya <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if ($karyaTerbaru): ?>
      <div class="grid grid-3 grid-stagger">
        <?php foreach ($karyaTerbaru as $k): ?>
          <?php include __DIR__ . '/../app/includes/partial_karya_card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card"><div class="empty">Belum ada karya yang tayang. Jadilah yang pertama!</div></div>
    <?php endif; ?>
  </section>
  <?php $secKarya = ob_get_clean(); ?>

  <?php ob_start(); ?>
  <section class="section reveal">
    <div class="section-head">
      <h2>Galeri terbaru</h2>
      <a class="see-all" href="<?= base_url('galeri.php') ?>">Lihat semua galeri <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if ($galeriTerbaru): ?>
      <div class="grid grid-3 grid-stagger">
        <?php foreach ($galeriTerbaru as $g): ?>
          <?php include __DIR__ . '/../app/includes/partial_galeri_card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card"><div class="empty">Belum ada dokumentasi galeri.</div></div>
    <?php endif; ?>
  </section>
  <?php $secGaleri = ob_get_clean(); ?>

  <?php ob_start(); ?>
  <section class="section reveal">
    <div class="section-head">
      <h2>Agenda kegiatan terdekat</h2>
      <a class="see-all" href="<?= base_url('kegiatan.php') ?>">Lihat semua agenda <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if ($kegiatanTerbaru): ?>
      <div class="table-wrap">
        <?php foreach ($kegiatanTerbaru as $k): ?>
          <div class="list-item list-item-pad">
            <div class="list-thumb"><i class="bi bi-calendar-event" style="font-size:19px; color:var(--teal);"></i></div>
            <div class="grow">
              <div class="ttl"><?= e($k['nama']) ?></div>
              <div class="meta"><?= tanggal_indo($k['tanggal']) ?> · <?= e($k['waktu']) ?> · <?= e($k['lokasi']) ?></div>
            </div>
            <span class="badge <?= $k['status'] === 'Selesai' ? 'badge-muted' : 'badge-teal' ?>"><?= e($k['status']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card"><div class="empty">Belum ada agenda terjadwal.</div></div>
    <?php endif; ?>
  </section>
  <?php $secAgenda = ob_get_clean(); ?>

  <?php ob_start(); ?>
  <section class="section reveal">
    <div class="section-head">
      <h2>Pelatihan &amp; Workshop mendatang</h2>
      <a class="see-all" href="<?= base_url('pelatihan.php') ?>">Lihat semua pelatihan <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if ($pelatihanTerbaru): ?>
      <div class="table-wrap">
        <?php foreach ($pelatihanTerbaru as $p): ?>
          <div class="list-item list-item-pad">
            <div class="list-thumb"><i class="bi bi-mortarboard" style="font-size:18px; color:var(--teal);"></i></div>
            <div class="grow">
              <a href="<?= base_url('pelatihan-detail.php?id=' . (int) $p['id']) ?>" class="ttl"><?= e($p['nama']) ?></a>
              <div class="meta"><?= e(tanggal_rentang($p['tanggal_mulai'], $p['tanggal_selesai'])) ?> · <?= e($p['lokasi'] ?: '-') ?></div>
            </div>
            <span class="badge <?= $p['akses'] === 'Umum' ? 'badge-blue' : 'badge-amber' ?>"><?= $p['akses'] === 'Umum' ? 'Terbuka Umum' : 'Khusus Anggota' ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card"><div class="empty">Belum ada pelatihan yang dijadwalkan.</div></div>
    <?php endif; ?>
  </section>
  <?php $secPelatihan = ob_get_clean(); ?>

  <?php ob_start(); ?>
  <section class="section reveal home-span">
    <div class="section-head">
      <h2>Pengumuman terbaru</h2>
      <a class="see-all" href="<?= base_url('pengumuman.php') ?>">Lihat semua pengumuman <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if ($pengumumanTerbaru): ?>
      <div class="table-wrap">
        <?php foreach ($pengumumanTerbaru as $p): ?>
          <div class="list-item list-item-pad">
            <div class="list-thumb"><i class="bi bi-megaphone" style="font-size:18px; color:var(--blue);"></i></div>
            <div class="grow">
              <div class="ttl"><?= e($p['judul']) ?></div>
              <div class="meta"><?= tanggal_indo($p['tanggal']) ?></div>
              <div style="font-size:13px; color:var(--text-muted); margin-top:6px;"><?= e(trunc($p['isi'], 90)) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card"><div class="empty">Belum ada pengumuman.</div></div>
    <?php endif; ?>
  </section>
  <?php $secPengumuman = ob_get_clean(); ?>

  <?php if ($isAnggota): ?>
  <!-- Beranda anggota: section daftar ditata grid 2 kolom. Tiap baris = 1 section
   kiri + 1 section kanan, jadi puncak kedua kolom selalu sejajar walau tinggi
   isi section berbeda. Section lebar penuh tetap berada di luar grid. -->
  <div class="home-cols">
    <?= $secPelatihan ?>
    <?= $secLomba ?>
    <?= $secPrestasi ?>
    <?= $secAgenda ?>
    <?= $secPengumuman ?>
  </div>
  <?= $secGaleri ?>
  <?= $secKarya ?>
  <?php else: ?>
  <!-- Urutan beranda tamu -->
  <?= $secPrestasi ?>
  <?= $secLomba ?>
  <?= $secKarya ?>
  <?= $secGaleri ?>
  <?= $secAgenda ?>
  <?= $secPelatihan ?>
  <?= $secPengumuman ?>
  <?php endif; ?>

  <?php if ($kontenAnggota): ?>
  <section class="section reveal">
    <div class="section-head">
      <h2>Materi untukmu</h2>
      <a class="see-all" href="<?= base_url('materi.php') ?>">Lihat semua materi <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if ($materiTerbaru): ?>
      <div class="table-wrap">
        <?php foreach ($materiTerbaru as $m): ?>
          <div class="list-item list-item-pad">
            <div class="list-thumb"><i class="bi bi-journal-bookmark" style="font-size:18px; color:var(--teal);"></i></div>
            <div class="grow">
              <div class="ttl"><?= e($m['judul']) ?></div>
              <div class="meta"><?= e($m['pelatihan_nama'] ?? (trunc((string) ($m['pelatihan_terkait'] ?? ''), 60) ?: (string) $m['topik'])) ?></div>
            </div>
            <a class="btn btn-outline btn-sm" href="<?= base_url('berkas.php?jenis=materi&id=' . (int) $m['id']) ?>"><i class="bi bi-download"></i> Unduh</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card"><div class="empty"><?= $isAdmin ? 'Belum ada materi pelatihan.' : 'Materi tersedia setelah kamu disetujui pada pelatihan terkait.' ?></div></div>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <section class="section reveal">
    <div class="section-head">
      <h2><?= $isAnggota ? 'Cara Berkarya' : 'Cara bergabung' ?></h2>
    </div>
    <div class="steps">
      <?php foreach (($isAnggota ? $langkahAnggota : $langkah) as $i => [$judul, $deskripsi]): ?>
        <div class="step">
          <div class="n"><?= $i + 1 ?></div>
          <div class="f-ttl"><?= e($judul) ?></div>
          <p class="f-desc"><?= e($deskripsi) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="cta-panel reveal">
    <div>
      <div class="eyebrow" style="color:var(--on-teal);">Ayo Berkarya</div>
      <h2>Siap mulai berkarya di komunitas robotik?</h2>
      <p><?= $isLoggedIn
        ? 'Lihat materi terbaru, ikuti pelatihan berikutnya, dan daftarkan karya pertamamu hari ini.'
        : 'Masuk ke portal, lihat materi terbaru, dan daftarkan karya pertamamu hari ini.' ?></p>
    </div>
    <div class="cta-actions">
      <?php if (!$isLoggedIn): ?>
        <a class="btn btn-light" href="<?= base_url('daftar.php') ?>"><i class="bi bi-person-plus"></i> Daftar Sekarang</a>
        <a class="btn btn-light" href="<?= base_url('login.php') ?>"><i class="bi bi-box-arrow-in-right"></i> Masuk</a>
      <?php else: ?>
        <a class="btn btn-light" href="<?= $isAdmin ? base_url('admin/dashboard.php') : base_url('ajukan-karya.php') ?>">
          <i class="bi bi-arrow-right-circle"></i> <?= $isAdmin ? 'Dashboard Admin' : 'Ajukan Karya' ?>
        </a>
      <?php endif; ?>
    </div>
  </section>
</div>
<?php if (!empty($heroTunda)): ?>
<script type="application/json" id="hero-slides-data"><?= json_encode(['total' => (int) $nSlides, 'slides' => $heroTunda], JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endif; ?>
<script>
(function(){
  var slider = document.querySelector('.slider');
  if (!slider) return;
  var INTERVAL = 7000;
  var dots = slider.querySelectorAll('.dots button');
  var dataEl = document.getElementById('hero-slides-data');
  var tunda = null;
  if (dataEl) { try { tunda = JSON.parse(dataEl.textContent); } catch (err) { tunda = null; } }
  var slidesData = tunda && tunda.slides ? tunda.slides : [];
  var total = (tunda && tunda.total) || slider.querySelectorAll('.slide').length;
  var dotsWrap = slider.querySelector('.dots');
  var slideEls = new Array(total);
  Array.prototype.forEach.call(slider.querySelectorAll('.slide'), function(el, k){ slideEls[k] = el; });
  var i = 0;

  function mkImg(cls, src, alt, hidden){
    var im = document.createElement('img');
    im.className = cls;
    im.src = src;
    im.alt = alt;
    if (hidden) im.setAttribute('aria-hidden', 'true');
    im.setAttribute('fetchpriority', 'low');
    im.setAttribute('decoding', 'async');
    return im;
  }
  /* Slide ke-2 dst dibangun dari JSON hanya saat dibutuhkan (lazy). */
  function ensure(n){
    if (slideEls[n]) return slideEls[n];
    var d = slidesData[n - 1];
    if (!d) return null;
    var el = document.createElement('div');
    el.className = 'slide';
    el.style.setProperty('--slide-img', 'url("' + d.img + '")');
    var ph = document.createElement('span');
    ph.className = 'slide-photos';
    if (total > 1) ph.appendChild(mkImg('ph ph-side', d.kiri, '', true));
    ph.appendChild(mkImg('ph ph-main', d.img, 'Gambar slider', false));
    if (total > 1) ph.appendChild(mkImg('ph ph-side', d.kanan, '', true));
    el.appendChild(ph);
    slider.insertBefore(el, dotsWrap || null);
    slideEls[n] = el;
    return el;
  }
  function go(n){
    if (slideEls[i]) slideEls[i].classList.remove('active');
    if (dots.length && dots[i]) dots[i].classList.remove('active');
    i = (n + total) % total;
    var nxt = ensure(i);
    if (nxt) nxt.classList.add('active');
    if (dots.length && dots[i]) dots[i].classList.add('active');
  }
  function centerOf(el){
    if (!el) return '';
    var img = el.querySelector('.ph-main');
    return img ? (img.getAttribute('src') || '') : '';
  }
  /* Slide berikutnya dipilih yang foto tengahnya berbeda dari yang sedang
     tampil, supaya foto sama tidak muncul lagi dalam waktu dekat. */
  function nextIdx(){
    var cur = centerOf(slideEls[i]);
    for (var k = 1; k <= total; k++){
      var idx = (i + k) % total;
      if (centerOf(slideEls[idx]) !== cur) return idx;
    }
    return (i + 1) % total;
  }
  function adv(){ go(nextIdx()); }
  var timer = null;
  function restart(){
    clearInterval(timer);
    if (total > 1) timer = setInterval(adv, INTERVAL);
  }
  restart();
  dots.forEach(function(d, j){
    d.addEventListener('click', function(){ go(j); restart(); });
  });
  slider.addEventListener('click', function(e){
    if (e.target.closest('.dots')) return;
    if (total < 2) return;
    adv();
    restart();
  });
  /* Setelah halaman termuat, bangun semua slide (unduhan prioritas rendah)
     supaya jauh sebelum pergantian slide pertama (7 dtk) semuanya siap. */
  if (slidesData.length){
    var kick = function(){
      var idle = window.requestIdleCallback || function(fn){ setTimeout(fn, 100); };
      idle(function(){ for (var n = 1; n < total; n++) ensure(n); });
    };
    if (document.readyState === 'complete') kick();
    else window.addEventListener('load', kick);
  }
})();
</script>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>