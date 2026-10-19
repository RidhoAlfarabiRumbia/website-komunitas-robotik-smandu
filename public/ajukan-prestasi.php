<?php
/**
 * Ajukan Prestasi (PRD 8.7).
 * Hanya anggota. Prestasi langsung tayang setelah disimpan (pasca-moderasi),
 * jadi form divalidasi ketat sebelum disimpan.
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';

Auth::requireRole('anggota');

$errors = [];
$old = [
    'nama' => '',
    'lomba_id' => '',
    'tanggal' => date('Y-m-d'),
    'tingkat' => 'Sekolah',
    'hasil' => '',
    'deskripsi' => '',
    'anggota_ids' => [],
];

$lombaList = Lomba::all('tanggal DESC');
$anggotaList = Anggota::all('nama ASC');
$idAnggotaValid = array_map('intval', array_column($anggotaList, 'id'));
// Opsi tingkat dipakai form (baris ~160) SEBELUM blok POST berjalan, jadi
// harus didefinisikan di sini — bukan hanya di dalam blok POST.
$tingkatOpsi = ['Sekolah', 'Kota', 'Provinsi', 'Nasional', 'Internasional'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();

    $old = [
        'nama' => nilai_teks($_POST, 'nama'),
        'lomba_id' => nilai_int($_POST, 'lomba_id'),
        'tanggal' => nilai_teks($_POST, 'tanggal'),
        'tingkat' => nilai_teks($_POST, 'tingkat'),
        'hasil' => nilai_teks($_POST, 'hasil'),
        'deskripsi' => nilai_teks($_POST, 'deskripsi'),
        'anggota_ids' => array_map('intval', (array) ($_POST['anggota_ids'] ?? [])),
    ];

    // Validasi berkas unggahan sebelum disimpan
    foreach (['foto' => ['jpg', 'jpeg', 'png', 'webp'], 'sertifikat' => ['pdf', 'jpg', 'jpeg', 'png']] as $input => $exts) {
        if (empty($_FILES[$input]['name'])) continue;
        if (!is_string($_FILES[$input]['name'])) { $errors[$input] = 'Format berkas tidak didukung.'; continue; }
        $ext = strtolower(pathinfo($_FILES[$input]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $exts, true)) $errors[$input] = 'Format berkas tidak didukung.';
        elseif ($_FILES[$input]['size'] > 2 * 1024 * 1024) $errors[$input] = 'Ukuran berkas maksimal 2MB.';
    }

    if ($old['nama'] === '') {
        $errors['nama'] = 'Nama prestasi wajib diisi.';
    } elseif (mb_strlen($old['nama']) > 160) {
        $errors['nama'] = 'Maksimal 160 karakter.';
    }

    if ($old['tanggal'] === '') {
        $errors['tanggal'] = 'Tanggal wajib diisi.';
    }

    if (!in_array($old['tingkat'], $tingkatOpsi, true)) {
        $errors['tingkat'] = 'Pilih tingkat yang valid.';
    }

    if ($old['hasil'] === '') {
        $errors['hasil'] = 'Hasil wajib diisi, misalnya Juara 1.';
    } elseif (mb_strlen($old['hasil']) > 80) {
        $errors['hasil'] = 'Maksimal 80 karakter.';
    }

    $idLombaValid = array_map('intval', array_column($lombaList, 'id'));
    if ($old['lomba_id'] > 0 && !in_array($old['lomba_id'], $idLombaValid, true)) {
        $errors['lomba_id'] = 'Lomba tidak ditemukan.';
    }

    if (!$old['anggota_ids']) {
        $errors['anggota_ids'] = 'Pilih minimal satu anggota.';
    } elseif (count($old['anggota_ids']) !== count(array_unique($old['anggota_ids']))) {
        $errors['anggota_ids'] = 'Anggota yang dipilih duplikat.';
    } else {
        foreach ($old['anggota_ids'] as $aid) {
            if (!in_array($aid, $idAnggotaValid, true)) {
                $errors['anggota_ids'] = 'Anggota terpilih tidak valid.';
                break;
            }
        }
    }

    if (!$errors) {
        $sertifikat = simpan_upload('sertifikat', 'sertifikat', ['pdf', 'jpg', 'jpeg', 'png'], 2 * 1024 * 1024, 'PDF atau gambar', '2MB');
        $foto = simpan_upload('foto', 'content', ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024, 'JPG, PNG, atau WEBP', '2MB');

        $idBaru = Prestasi::create([
            'nama' => $old['nama'],
            'lomba_id' => $old['lomba_id'] ?: null,
            'tanggal' => $old['tanggal'],
            'tingkat' => $old['tingkat'],
            'hasil' => $old['hasil'],
            'deskripsi' => $old['deskripsi'] ?: null,
            'foto' => $foto,
            'sertifikat' => $sertifikat,
            'status' => 'Tayang',
        ]);

        Prestasi::simpanTim((int) $idBaru, $old['anggota_ids']);

        flash_set('success', 'Prestasi berhasil diterbitkan dan langsung tampil di portal.');
        redirect('prestasi-detail.php?id=' . (int) $idBaru);
    }

    flash_set('error', 'Periksa kembali isian yang ditandai.');
}

$pageTitle = 'Ajukan Prestasi';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <a class="back-link" href="prestasi.php"><i class="bi bi-arrow-left"></i> Kembali ke daftar prestasi</a>

  <div class="form-wrap" style="max-width:760px;">
    <div class="page-head" style="margin-bottom:20px;">
      <div>
        <div class="eyebrow">PENCAPAIAN</div>
        <h1>Ajukan Prestasi</h1>
        <p class="desc">Prestasi langsung tayang setelah dikirim. Admin meninjau kembali dan dapat menyembunyikanya bila tidak valid.</p>
      </div>
    </div>

    <div class="card">
      <form method="post" enctype="multipart/form-data" class="form-grid">
        <?= Csrf::field() ?>

        <div class="field" style="grid-column:1/-1;">
          <label for="nama">Nama Prestasi <span class="req">*</span></label>
          <input type="text" id="nama" name="nama" value="<?= e($old['nama']) ?>" required maxlength="160" class="<?= isset($errors['nama']) ? 'invalid' : '' ?>">
          <?php if (isset($errors['nama'])): ?><span class="err"><?= e($errors['nama']) ?></span><?php endif; ?>
        </div>

        <div class="field">
          <label for="lomba_id">Lomba / Kegiatan</label>
          <select id="lomba_id" name="lomba_id">
            <option value="0">Tidak terkait lomba</option>
            <?php foreach ($lombaList as $l): ?>
              <option value="<?= (int) $l['id'] ?>" <?= $old['lomba_id'] === (int) $l['id'] ? 'selected' : '' ?>>
                <?= e($l['nama']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['lomba_id'])): ?><span class="err"><?= e($errors['lomba_id']) ?></span><?php endif; ?>
        </div>

        <div class="field">
          <label for="tanggal">Tanggal <span class="req">*</span></label>
          <input type="date" id="tanggal" name="tanggal" value="<?= e($old['tanggal']) ?>" required class="<?= isset($errors['tanggal']) ? 'invalid' : '' ?>">
          <?php if (isset($errors['tanggal'])): ?><span class="err"><?= e($errors['tanggal']) ?></span><?php endif; ?>
        </div>

        <div class="field">
          <label for="tingkat">Tingkat <span class="req">*</span></label>
          <select id="tingkat" name="tingkat" class="<?= isset($errors['tingkat']) ? 'invalid' : '' ?>">
            <?php foreach ($tingkatOpsi as $t): ?>
              <option value="<?= e($t) ?>" <?= $old['tingkat'] === $t ? 'selected' : '' ?>><?= e($t) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['tingkat'])): ?><span class="err"><?= e($errors['tingkat']) ?></span><?php endif; ?>
        </div>

        <div class="field">
          <label for="hasil">Hasil <span class="req">*</span></label>
          <input type="text" id="hasil" name="hasil" value="<?= e($old['hasil']) ?>" required maxlength="80" placeholder="Juara 1, Podium 2, Finalis..." class="<?= isset($errors['hasil']) ? 'invalid' : '' ?>">
          <?php if (isset($errors['hasil'])): ?><span class="err"><?= e($errors['hasil']) ?></span><?php endif; ?>
        </div>

        <div class="field" style="grid-column:1/-1;">
          <label for="deskripsi">Ceritakan singkat</label>
          <textarea id="deskripsi" name="deskripsi" rows="4"><?= e($old['deskripsi']) ?></textarea>
        </div>

        <div class="field" style="grid-column:1/-1;">
          <label for="anggota_ids">Peraih Prestasi <span class="req">*</span></label>
          <select id="anggota_ids" name="anggota_ids[]" multiple size="6" class="<?= isset($errors['anggota_ids']) ? 'invalid' : '' ?>">
            <?php foreach ($anggotaList as $a): ?>
              <option value="<?= (int) $a['id'] ?>" <?= in_array((int) $a['id'], $old['anggota_ids'], true) ? 'selected' : '' ?>>
                <?= e($a['nama']) ?><?= $a['kelas'] ? ' - ' . e($a['kelas']) : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
          <span class="hint">Tahan Ctrl / Cmd untuk memilih lebih dari satu anggota.</span>
          <?php if (isset($errors['anggota_ids'])): ?><span class="err"><?= e($errors['anggota_ids']) ?></span><?php endif; ?>
        </div>

        <div class="field">
          <label for="foto">Foto</label>
          <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png,.webp" class="<?= isset($errors['foto']) ? 'invalid' : '' ?>">
          <span class="hint">Maks 2MB.</span>
          <?php if (isset($errors['foto'])): ?><span class="err"><?= e($errors['foto']) ?></span><?php endif; ?>
        </div>

        <div class="field">
          <label for="sertifikat">Sertifikat</label>
          <input type="file" id="sertifikat" name="sertifikat" accept=".pdf,.jpg,.jpeg,.png" class="<?= isset($errors['sertifikat']) ? 'invalid' : '' ?>">
          <span class="hint">PDF atau gambar, maks 2MB.</span>
          <?php if (isset($errors['sertifikat'])): ?><span class="err"><?= e($errors['sertifikat']) ?></span><?php endif; ?>
        </div>

        <div class="form-actions" style="grid-column:1/-1;">
          <a class="btn btn-outline" href="prestasi.php">Batal</a>
          <button class="btn btn-primary" type="submit"><i class="bi bi-trophy"></i> Terbitkan Prestasi</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>