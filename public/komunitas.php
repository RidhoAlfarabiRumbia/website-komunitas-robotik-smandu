<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';

$profil = komunitas_profil();
$heroData = Hero::getSliderData();
$semuaAnggota = Anggota::all('nama ASC');
$semuaPrestasi = Prestasi::tayang();
$anggotaTampil = array_slice($semuaAnggota, 0, 6);
$prestasiTampil = array_slice($semuaPrestasi, 0, 3);

$misi = array_values(array_filter(
    array_map('trim', preg_split('/\r\n|\r|\n/', $profil['komunitas_misi']) ?: []),
    static fn(string $baris): bool => $baris !== ''
));
$gate = pendaftaran_gate();
$fotoPembina = $profil['pembina_foto'];
$adaFotoPembina = $fotoPembina !== '' && storage_path($fotoPembina) !== null;
$emailKomunitas = trim($profil['komunitas_email']);
$emailAman = $emailKomunitas !== '' && filter_var($emailKomunitas, FILTER_VALIDATE_EMAIL) !== false;
$instagram = trim($profil['komunitas_instagram']);
$skemaInstagram = strtolower((string) parse_url($instagram, PHP_URL_SCHEME));
$instagramAman = $instagram !== ''
    && filter_var($instagram, FILTER_VALIDATE_URL) !== false
    && in_array($skemaInstagram, ['http', 'https'], true);

$pageTitle = 'Profil Komunitas';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="hero-frame">
  <section class="komunitas-hero">
    <div class="komunitas-hero-media">
      <?php $sliderAdminHref = null; include __DIR__ . '/../app/includes/partial_hero_slider.php'; ?>
    </div>
    <div class="komunitas-hero-scrim" aria-hidden="true"></div>
    <div class="komunitas-hero-copy">
      <div class="eyebrow">PROFIL KOMUNITAS</div>
      <h1><?= e($profil['komunitas_nama']) ?></h1>
      <p><?= e($profil['komunitas_tagline']) ?></p>
      <div class="komunitas-stats">
        <div class="komunitas-stat"><strong><?= count($semuaAnggota) ?></strong><span>Anggota komunitas</span></div>
        <div class="komunitas-stat"><strong><?= count($semuaPrestasi) ?></strong><span>Prestasi tayang</span></div>
      </div>
      <?php if ($gate['buka'] || $emailAman): ?>
        <div class="komunitas-hero-actions">
          <?php if ($gate['buka']): ?>
            <a class="btn btn-primary" href="<?= base_url('daftar.php') ?>"><i class="bi bi-person-plus"></i> Daftar Anggota</a>
          <?php endif; ?>
          <?php if ($emailAman): ?>
            <a class="btn btn-outline" href="mailto:<?= e($emailKomunitas) ?>"><i class="bi bi-envelope"></i> Hubungi Kami</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php if (Auth::isAdmin()): ?>
      <div class="komunitas-hero-admin">
        <a class="chip-admin" href="<?= base_url('admin/komunitas-pengaturan.php') ?>" title="Kelola profil komunitas"><i class="bi bi-pencil-square"></i> Kelola Profil</a>
      </div>
    <?php endif; ?>
  </section>
  </div>

  <section class="section reveal">
    <div class="section-head"><h2>Tentang komunitas</h2></div>
    <div class="grid grid-2">
      <div class="card">
        <h3 style="font-size:15px; margin-bottom:10px;"><i class="bi bi-info-circle"></i> Cerita singkat</h3>
        <p style="font-size:13.5px; color:var(--text-muted);"><?= nl2br(e($profil['komunitas_deskripsi'])) ?></p>
        <?php if ($profil['komunitas_lokasi'] !== '' || $emailAman || $instagramAman): ?>
          <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:16px;">
            <?php if ($profil['komunitas_lokasi'] !== ''): ?>
              <span class="badge badge-muted"><i class="bi bi-geo-alt"></i> <?= e($profil['komunitas_lokasi']) ?></span>
            <?php endif; ?>
            <?php if ($emailAman): ?>
              <a class="badge badge-teal" href="mailto:<?= e($emailKomunitas) ?>"><i class="bi bi-envelope"></i> Email</a>
            <?php endif; ?>
            <?php if ($instagramAman): ?>
              <a class="badge badge-blue" href="<?= e($instagram) ?>" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i> Instagram</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="card">
        <h3 style="font-size:15px; margin-bottom:10px;"><i class="bi bi-compass"></i> Visi &amp; Misi</h3>
        <?php if ($profil['komunitas_visi'] !== ''): ?>
          <p style="font-size:13.5px; color:var(--text-muted);"><strong>Visi.</strong> <?= nl2br(e($profil['komunitas_visi'])) ?></p>
        <?php endif; ?>
        <?php if ($misi): ?>
          <div style="font-size:13.5px; color:var(--text-muted);">
            <strong>Misi</strong>
            <ul style="margin:8px 0 0; padding-left:20px;">
              <?php foreach ($misi as $poin): ?><li><?= e($poin) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        <?php if ($profil['komunitas_visi'] === '' && !$misi): ?>
          <div class="empty" style="padding:20px 0;">Visi dan misi belum diisi.</div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php if ($profil['pembina_nama'] !== ''): ?>
    <section class="section reveal">
      <div class="section-head"><h2>Pembina</h2></div>
      <div class="card" style="display:flex; gap:18px; align-items:flex-start; flex-wrap:wrap;">
        <div class="avatar" style="width:76px; height:76px;">
          <img src="<?= base_url(e($adaFotoPembina ? $fotoPembina : 'assets/img/avatar-1.svg')) ?>" alt="Foto <?= e($profil['pembina_nama']) ?>">
        </div>
        <div style="flex:1; min-width:220px;">
          <h3 style="font-size:16px; margin-bottom:4px;"><?= e($profil['pembina_nama']) ?></h3>
          <?php if ($profil['pembina_jabatan'] !== ''): ?>
            <div class="meta" style="margin-bottom:8px;"><?= e($profil['pembina_jabatan']) ?></div>
          <?php endif; ?>
          <?php if ($profil['pembina_bio'] !== ''): ?>
            <p style="font-size:13.5px; color:var(--text-muted); margin:0;"><?= nl2br(e($profil['pembina_bio'])) ?></p>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="section reveal">
    <div class="section-head">
      <h2>Ringkasan prestasi</h2>
      <a class="see-all" href="<?= base_url('prestasi.php') ?>">Lihat semua prestasi <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if ($prestasiTampil): ?>
      <div class="grid grid-3 grid-stagger">
        <?php foreach ($prestasiTampil as $p): ?>
          <a href="prestasi-detail.php?id=<?= (int) $p['id'] ?>" class="work-card">
            <span class="work-thumb" data-lightbox="<?= base_url(e($p['foto'] ?? 'assets/img/galeri-prestasi.svg')) ?>" tabindex="0" role="button" aria-label="Perbesar foto prestasi: <?= e($p['nama']) ?>"><img src="<?= base_url(e($p['foto'] ?? 'assets/img/galeri-prestasi.svg')) ?>" alt="<?= e($p['nama']) ?>" loading="lazy" decoding="async"></span>
            <div class="work-body">
              <span class="badge badge-amber"><i class="bi bi-trophy"></i> <?= e($p['hasil']) ?></span>
              <div class="work-title" style="font-size:14px;"><?= e($p['nama']) ?></div>
              <div class="work-meta"><?= e($p['lomba_nama'] ?? '-') ?> &middot; <?= tanggal_indo($p['tanggal']) ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card"><div class="empty">Belum ada prestasi yang tercatat.</div></div>
    <?php endif; ?>
  </section>

  <section class="section reveal">
    <div class="section-head">
      <h2>Anggota komunitas</h2>
      <a class="see-all" href="<?= base_url('anggota.php') ?>">Lihat semua anggota <i class="bi bi-arrow-right"></i></a>
    </div>
    <?php if ($anggotaTampil): ?>
      <div class="grid grid-3 grid-stagger reveal">
        <?php foreach ($anggotaTampil as $a): ?>
          <?php $fotoAnggota = trim((string) ($a['foto'] ?? '')); ?>
          <a href="<?= base_url('anggota-detail.php?id=' . (int) $a['id']) ?>" class="card" style="display:flex; gap:14px; align-items:center; text-decoration:none; color:inherit;">
            <div class="avatar" style="width:46px; height:46px;"><img src="<?= base_url(e($fotoAnggota !== '' ? $fotoAnggota : 'assets/img/avatar-1.svg')) ?>" alt="" loading="lazy" decoding="async"></div>
            <div>
              <div class="ttl"><?= e($a['nama']) ?></div>
              <div class="meta"><?= e($a['kelas']) ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      <?php if (count($semuaAnggota) > count($anggotaTampil)): ?>
        <p style="font-size:12.5px; color:var(--text-muted); margin-top:12px;">Menampilkan <?= count($anggotaTampil) ?> dari <?= count($semuaAnggota) ?> anggota.</p>
      <?php endif; ?>
    <?php else: ?>
      <div class="card"><div class="empty">Belum ada anggota terdaftar.</div></div>
    <?php endif; ?>
  </section>

  <section class="cta-panel reveal">
    <div>
      <div class="eyebrow">Ayo Bergabung</div>
      <h2>Tertarik menjadi bagian komunitas?</h2>
      <p>Belajar bersama, mengerjakan proyek, dan tampilkan pencapaianmu bersama komunitas robotik.</p>
    </div>
    <div class="cta-actions">
      <?php if ($gate['buka']): ?>
        <a class="btn btn-light" href="<?= base_url('daftar.php') ?>"><i class="bi bi-person-plus"></i> Daftar Anggota</a>
      <?php elseif ($emailAman): ?>
        <a class="btn btn-light" href="mailto:<?= e($emailKomunitas) ?>"><i class="bi bi-envelope"></i> Hubungi Kami</a>
      <?php endif; ?>
      <a class="btn btn-light" href="<?= base_url('anggota.php') ?>"><i class="bi bi-people"></i> Kenali Anggota</a>
    </div>
  </section>
</div>
<?php include __DIR__ . '/../app/includes/partial_lightbox.php'; ?>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
