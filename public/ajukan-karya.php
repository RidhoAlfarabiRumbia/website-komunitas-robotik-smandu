<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';
Auth::requireRole('anggota');
$anggota = Auth::anggota();

$errors = [];
$form = [
    'judul' => '',
    'deskripsi' => '',
    'progres' => 'Sedang Dikerjakan',
    'teknologi' => '',
    'link_demo' => '',
    'pembuat_lain' => [],
];

$progresOpsi = ['Sedang Dikerjakan', 'Selesai'];
$anggotaLain = array_values(array_filter(
    Anggota::all('nama ASC'),
    static fn(array $a): bool => (int) $a['id'] !== (int) $anggota['id']
));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();

    $form = [
        'judul' => nilai_teks($_POST, 'judul'),
        'deskripsi' => nilai_teks($_POST, 'deskripsi'),
        'progres' => nilai_teks($_POST, 'progres'),
        'teknologi' => nilai_teks($_POST, 'teknologi'),
        'link_demo' => nilai_teks($_POST, 'link_demo'),
        'pembuat_lain' => array_values(array_filter(array_map('intval', (array) ($_POST['pembuat_lain'] ?? [])))),
    ];

    if ($form['judul'] === '') $errors[] = 'Judul karya wajib diisi.';
    if ($form['deskripsi'] === '') $errors[] = 'Deskripsi wajib diisi.';
    if (!in_array($form['progres'], $progresOpsi, true)) $errors[] = 'Status progres tidak valid.';
    if ($form['link_demo'] !== '' && !filter_var($form['link_demo'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Tautan proyek tidak valid.';
    }

    if (!$errors) {
        $foto = simpan_upload('foto', 'karya', ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024, 'JPG, PNG, atau WEBP', '2MB');

        // Jika user mencoba upload tapi gagal (simpan_upload return null + sudah set flash)
        if ($foto === null && !empty($_FILES['foto']['name'])) {
            redirect('ajukan-karya.php');
        }

        $karyaId = Karya::create([
            'judul' => $form['judul'],
            'deskripsi' => $form['deskripsi'],
            'progres' => $form['progres'],
            'publikasi' => 'Menunggu',
            'pembuat_id' => $anggota['id'],
            'teknologi' => $form['teknologi'],
            'link_demo' => $form['link_demo'] ?: null,
            'foto' => $foto ?: 'assets/img/karya-robot.svg',
        ]);
        Karya::simpanPembuat($karyaId, array_merge([(int) $anggota['id']], $form['pembuat_lain']));

        flash_set('success', 'Karya berhasil diajukan dan menunggu review admin.');
        redirect('profil.php');
    }

    flash_set('error', 'Periksa kembali isian yang ditandai.');
}

$pageTitle = 'Ajukan Karya';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="breadcrumb"><a href="profil.php">Profil Saya</a> / Ajukan Karya</div>
  <div class="page-head">
    <div><div class="eyebrow">BERKARYA</div><h1>Ajukan Karya Baru</h1><p class="desc">Karya yang diajukan akan berstatus Menunggu hingga direview oleh admin.</p></div>
  </div>
  <?php if ($errors): foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; endif; ?>
  <form class="card" style="max-width:640px;" method="post" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <div class="form-grid">
      <div class="field field-full"><label>Judul karya</label><input name="judul" required maxlength="150" value="<?= e($form['judul']) ?>" placeholder="Contoh: Robot Penyiram Tanaman Otomatis"></div>
      <div class="field">
        <label>Status progres</label>
        <select name="progres">
          <?php foreach ($progresOpsi as $p): ?><option <?= $form['progres'] === $p ? 'selected' : '' ?>><?= e($p) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field field-full"><label>Deskripsi</label><textarea name="deskripsi" rows="4" required placeholder="Jelaskan singkat karya dan cara kerjanya"><?= e($form['deskripsi']) ?></textarea></div>
      <div class="field field-full">
        <label>Pembuat lain (opsional)</label>
        <?php if ($anggotaLain): ?>
          <div class="peraih-check-list">
            <?php foreach ($anggotaLain as $a): ?>
              <label class="peraih-check-item">
                <input type="checkbox" name="pembuat_lain[]" value="<?= (int) $a['id'] ?>" <?= in_array((int) $a['id'], $form['pembuat_lain'], true) ? 'checked' : '' ?>>
                <span><?= e($a['nama']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Centang anggota lain yang ikut membuat karya ini. Kamu otomatis tercatat sebagai pembuat.</div>
        <?php else: ?>
          <div style="font-size:12px; color:var(--text-muted);">Belum ada anggota lain.</div>
        <?php endif; ?>
      </div>
      <div class="field"><label>Teknologi yang digunakan</label><input name="teknologi" maxlength="255" value="<?= e($form['teknologi']) ?>" placeholder="Contoh: Arduino, Sensor Ultrasonik"></div>
      <div class="field"><label>Tautan proyek (opsional)</label><input name="link_demo" maxlength="191" value="<?= e($form['link_demo']) ?>" placeholder="https://..."></div>
      <div class="field field-full"><label>Foto/dokumentasi (JPG/PNG/WEBP, maks 2MB)</label><input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp"></div>
    </div>
    <div style="display:flex; gap:10px; margin-top:16px;">
      <button class="btn btn-primary" type="submit">Kirim untuk direview</button>
      <a class="btn btn-outline" href="profil.php">Batal</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>