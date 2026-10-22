<?php
/**
 * Notifikasi pengajuan admin.
 * Dirender di dalam .user-chip pada topbar halaman admin (sebelah tombol
 * Beranda): tombol lonceng + modal di tengah layar berisi SATU daftar
 * gabungan semua pengajuan yang menunggu tindakan, dengan urutan kategori
 * tetap: Karya paling atas, lalu Peserta Pelatihan, lalu Pendaftaran Anggota
 * (di dalam tiap kategori diurutkan terbaru).
 *   - Karya menunggu moderasi
 *   - Peserta pelatihan menunggu persetujuan
 *   - Pendaftaran anggota baru
 *
 * Modal ditutup lewat tombol X, Esc, atau klik area gelap. Setelah login
 * admin, modal terbuka otomatis sekali (flag $_SESSION['popup_pengajuan']
 * dari login.php lalu dihapus di sini).
 */
if (!Auth::isAdmin()) {
    return;
}

$notifKarya = Karya::byStatus('Pending');
$notifPeserta = Pelatihan::semuaPeserta('Pending');
$notifDaftar = Pendaftaran::semua('Baru');

$notifItems = [];
foreach ($notifKarya as $k) {
    $notifItems[] = [
        'tanggal' => $k['dibuat_pada'],
        'tipe' => 'Karya',
        'judul' => $k['judul'],
        'meta' => 'oleh ' . $k['pembuat_nama'],
        'url' => 'moderasi.php',
        'ikon' => 'bi-check2-square',
        'warna' => 'teal',
    ];
}
foreach ($notifPeserta as $p) {
    $notifItems[] = [
        'tanggal' => $p['dibuat_pada'],
        'tipe' => 'Peserta Pelatihan',
        'judul' => $p['nama'],
        'meta' => $p['pelatihan_nama'] ?? 'Pelatihan',
        'url' => 'pelatihan-peserta.php',
        'ikon' => 'bi-person-check',
        'warna' => 'amber',
    ];
}
foreach ($notifDaftar as $d) {
    $notifItems[] = [
        'tanggal' => $d['dibuat_pada'],
        'tipe' => 'Pendaftaran Anggota',
        'judul' => $d['nama'],
        'meta' => $d['kelas'] ?: 'Calon anggota',
        'url' => 'verifikasi.php',
        'ikon' => 'bi-file-earmark-check',
        'warna' => 'blue',
    ];
}
// Urutan kategori dipertahankan: Karya dulu, lalu Peserta Pelatihan, lalu
// Pendaftaran Anggota. Di dalam kategori, yang terbaru tampil lebih dulu.
$urutanKategori = ['Karya' => 0, 'Peserta Pelatihan' => 1, 'Pendaftaran Anggota' => 2];
usort($notifItems, static function (array $a, array $b) use ($urutanKategori): int {
    $kategoriA = $urutanKategori[$a['tipe']] ?? 99;
    $kategoriB = $urutanKategori[$b['tipe']] ?? 99;
    if ($kategoriA !== $kategoriB) {
        return $kategoriA <=> $kategoriB;
    }
    return strcmp((string) $b['tanggal'], (string) $a['tanggal']);
});
$notifTotal = count($notifItems);

$notifAutoOpen = !empty($_SESSION['popup_pengajuan']);
if ($notifAutoOpen) {
    unset($_SESSION['popup_pengajuan']);
}
?>
<button type="button" class="notif-btn" id="notifToggle" aria-label="Notifikasi pengajuan" aria-expanded="false" aria-controls="notifModal" title="Notifikasi pengajuan">
  <i class="bi bi-bell"></i>
  <?php if ($notifTotal > 0): ?><span class="notif-badge"><?= $notifTotal > 99 ? '99+' : (int) $notifTotal ?></span><?php endif; ?>
</button>
<div class="notif-modal" id="notifModal" hidden role="dialog" aria-modal="true" aria-labelledby="notifJudul">
  <div class="notif-card">
    <button type="button" class="notif-close" id="notifClose" aria-label="Tutup notifikasi"><i class="bi bi-x-lg"></i></button>
    <div class="notif-head">
      <div class="notif-eyebrow"><i class="bi bi-bell"></i> NOTIFIKASI ADMIN</div>
      <h2 id="notifJudul">Pengajuan terbaru</h2>
      <p class="notif-desc">
        <?= $notifTotal > 0 ? (int) $notifTotal . ' pengajuan menunggu tindakan, urut kategori (Karya di atas).' : 'Tidak ada pengajuan yang menunggu tindakan.' ?>
      </p>
    </div>
    <div class="notif-list-wrap">
      <div class="notif-list" id="notifList">
        <?php if ($notifItems): ?>
          <?php foreach ($notifItems as $item): ?>
            <a class="notif-item" href="<?= e($item['url']) ?>">
              <span class="notif-ico notif-ico-<?= e($item['warna']) ?>"><i class="bi <?= e($item['ikon']) ?>"></i></span>
              <span class="notif-item-body">
                <span class="notif-item-tipe"><?= e($item['tipe']) ?></span>
                <span class="notif-item-title"><?= e($item['judul']) ?></span>
                <span class="notif-item-meta"><?= e($item['meta']) ?> &middot; <?= e(tanggal_indo($item['tanggal'])) ?></span>
              </span>
              <i class="bi bi-chevron-right"></i>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="notif-empty"><i class="bi bi-check2-circle"></i> Semua pengajuan sudah ditindaklanjuti.</div>
        <?php endif; ?>
      </div>
      <?php /* Scrollbar kustom untuk Firefox (native-nya memakai tombol panah). */ ?>
      <span class="notif-scrollbar" id="notifScrollbar" aria-hidden="true"><span class="notif-scrollbar-thumb"></span></span>
    </div>
    <div class="notif-foot">
      <button type="button" class="btn btn-outline btn-sm" id="notifLater">Nanti saja</button>
    </div>
  </div>
</div>
<script>
(function(){
  var tombol = document.getElementById('notifToggle');
  var modal = document.getElementById('notifModal');
  if (!tombol || !modal) return;
  // Pindahkan modal ke <body>: topbar memakai backdrop-filter sehingga
  // position:fixed di dalamnya akan terkurung di area topbar.
  if (modal.parentNode !== document.body) {
    document.body.appendChild(modal);
  }
  var tombolTutup = document.getElementById('notifClose');
  var tombolNanti = document.getElementById('notifLater');
  var daftar = document.getElementById('notifList');
  var bar = document.getElementById('notifScrollbar');
  var thumb = bar ? bar.firstElementChild : null;
  // Firefox tidak mendukung ::-webkit-scrollbar dan scrollbar native-nya
  // menampilkan tombol panah, jadi di sana dipakai scrollbar kustom ini.
  var firefox = /Firefox\//.test(navigator.userAgent);
  var bukaOtomatis = <?= $notifAutoOpen ? 'true' : 'false' ?>;

  function perbaruiScrollbar(){
    if (!firefox || !daftar || !bar || !thumb) return;
    var maks = daftar.scrollHeight - daftar.clientHeight;
    if (maks <= 1) {
      bar.style.display = 'none';
      return;
    }
    bar.style.display = 'block';
    var track = bar.clientHeight;
    if (track <= 0) return;
    var tinggi = Math.max(24, Math.round(track * daftar.clientHeight / daftar.scrollHeight));
    var atas = Math.round((track - tinggi) * (daftar.scrollTop / maks));
    thumb.style.height = tinggi + 'px';
    thumb.style.transform = 'translateY(' + atas + 'px)';
  }

  function buka(){
    modal.hidden = false;
    tombol.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
    tombolTutup.focus();
    requestAnimationFrame(perbaruiScrollbar);
  }
  function tutup(){
    modal.hidden = true;
    tombol.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    tombol.focus();
  }

  tombol.addEventListener('click', buka);
  tombolTutup.addEventListener('click', tutup);
  if (tombolNanti) tombolNanti.addEventListener('click', tutup);
  // Klik area gelap di luar kartu menutup notifikasi.
  modal.addEventListener('click', function(e){
    if (e.target === modal) tutup();
  });
  document.addEventListener('keydown', function(e){
    if (!modal.hidden && e.key === 'Escape') tutup();
  });
  if (daftar) daftar.addEventListener('scroll', perbaruiScrollbar, { passive: true });
  window.addEventListener('resize', perbaruiScrollbar);
  if (window.ResizeObserver && daftar) {
    new ResizeObserver(perbaruiScrollbar).observe(daftar);
  }

  if (bukaOtomatis) buka();
})();
</script>
