<?php
/**
 * Panel pengelolaan Referensi, disematkan di halaman Materi
 * (public/admin/crud.php?entity=materi) karena fitur Referensi digabung ke Materi.
 */
// Partial berada di document root: tolak akses langsung tanpa sesi admin.
if (session_status() !== PHP_SESSION_ACTIVE || !Auth::isAdmin()) {
    http_response_code(403);
    exit('Akses ditolak.');
}
$refCfg = $config['referensi'];
$refRows = Referensi::all('nama ASC');
$refId = (int) ($_GET['id'] ?? 0);
$refEditing = (($_GET['action'] ?? '') === 'edit' && $refId) ? Referensi::find($refId) : null;
$refBack = '?entity=materi&sub=referensi&action=' . ($refEditing ? 'edit&id=' . $refId : 'create');
?>
<section class="card" id="referensi" style="margin-top:26px; scroll-margin-top:86px;">
  <div class="section-head" style="margin-bottom:14px;">
    <div>
      <div class="eyebrow">BELAJAR</div>
      <h2 style="font-size:17px;">Referensi Belajar</h2>
      <p style="font-size:13px; color:var(--text-muted); margin-top:4px;">Tautan sumber belajar eksternal yang tampil di halaman Materi untuk seluruh anggota.</p>
    </div>
  </div>

  <form class="card" style="max-width:680px; margin-bottom:18px;" method="post" action="?entity=materi">
    <?= Csrf::field() ?>
    <input type="hidden" name="sub_form" value="referensi">
    <?php if ($refEditing): ?><input type="hidden" name="id" value="<?= (int) $refEditing['id'] ?>"><?php endif; ?>
    <div class="form-grid">
      <div class="field">
        <label>Nama Referensi *</label>
        <input name="nama" value="<?= e((string) ($refEditing['nama'] ?? '')) ?>" required>
      </div>
      <div class="field">
        <label>URL *</label>
        <input name="url" value="<?= e((string) ($refEditing['url'] ?? '')) ?>" placeholder="https://..." required>
      </div>
      <div class="field field-full">
        <label>Deskripsi</label>
        <textarea name="deskripsi" rows="3"><?= e((string) ($refEditing['deskripsi'] ?? '')) ?></textarea>
      </div>
    </div>
    <div style="display:flex; gap:10px; margin-top:10px;">
      <button class="btn btn-primary" type="submit"><?= $refEditing ? 'Simpan Perubahan' : 'Tambah Referensi' ?></button>
      <?php if ($refEditing): ?><a class="btn btn-outline" href="?entity=materi">Batal</a><?php endif; ?>
    </div>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <?php foreach ($refCfg['list'] as $col): ?><th><?= e($refCfg['fields'][$col]['label'] ?? ucfirst($col)) ?></th><?php endforeach; ?>
          <th style="width:120px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($refRows as $row): ?>
          <tr>
            <?php foreach ($refCfg['list'] as $col): ?>
              <td><?= e(trunc((string) ($row[$col] ?? '-'), 60)) ?></td>
            <?php endforeach; ?>
            <td>
              <div class="row-actions">
                <a class="icon-btn" href="?entity=materi&sub=referensi&action=edit&id=<?= (int) $row['id'] ?>"><i class="bi bi-pencil"></i></a>
                <form method="post" action="?entity=materi&sub=referensi&action=delete&id=<?= (int) $row['id'] ?>" style="display:flex;"
                      onsubmit="return confirm('Hapus data ini?')">
                  <?= Csrf::field() ?>
                  <button type="submit" class="icon-btn" title="Hapus"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$refRows): ?><tr><td colspan="<?= count($refCfg['list']) + 1 ?>"><div class="empty">Belum ada referensi.</div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
