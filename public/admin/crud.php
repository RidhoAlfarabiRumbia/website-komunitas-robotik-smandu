<?php
require_once __DIR__ . '/../../app/includes/bootstrap.php';
require_once __DIR__ . '/../../app/core/CrudConfig.php';
Auth::requireRole('admin');

// Logika CRUD + pengumpulan data: app/includes/crud_handle.php
// (dipakai bersama dengan halaman fitur publik).
$crudEntitas = $_GET['entity'] ?? 'anggota';
$crudPath = 'admin/crud.php';
$crudReferensi = true;
include __DIR__ . '/../../app/includes/crud_handle.php';

$pageTitle = 'Kelola ' . $crudCfg['label'];
include __DIR__ . '/../../app/includes/header.php';
?>
<div class="topbar"><div class="topbar-inner admin-topbar">
  <?php include __DIR__ . '/_hamburger.php'; ?>
  <a href="dashboard.php" class="brand"><span class="brand-dot"></span><span class="brand-name"><span>Robotik</span><span>Smandu</span></span><span class="brand-admin">/ Admin</span></a>
  <div style="flex:1;"></div>
  <div class="user-chip">
    <a class="btn btn-outline btn-sm" href="<?= base_url('beranda.php') ?>" title="Lihat Beranda"><i class="bi bi-eye"></i> Beranda</a>
    <?php include __DIR__ . '/../../app/includes/partial_pengajuan_popup.php'; ?>
    <button type="button" class="theme-toggle" onclick="toggleTheme()" title="Mode gelap/terang" aria-label="Toggle dark mode">
      <i class="bi bi-moon-stars-fill" id="themeIconDark"></i>
      <i class="bi bi-sun-fill" id="themeIconLight" style="display:none;"></i>
    </button>
    <a class="profile-icon" href="<?= base_url('admin/profil.php') ?>" title="Profil &amp; Kata Sandi"><i class="bi bi-person-circle"></i></a>
    <form method="post" action="<?= base_url('logout.php') ?>" style="display:flex;"><input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>"><button type="submit" class="logout-link" title="Keluar"><i class="bi bi-box-arrow-right"></i></button></form>
  </div>
</div></div>
<div class="admin-body">
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="admin-main">
    <div class="page-head">
      <?php if ($refPanel): ?>
        <div><div class="eyebrow">MATERI</div><h1>Kelola Referensi Belajar</h1><p class="desc">Tautan sumber belajar yang tampil di halaman Materi untuk seluruh anggota.</p></div>
        <a class="btn btn-outline" href="?entity=materi"><i class="bi bi-arrow-left"></i> Kembali ke Materi</a>
      <?php else: ?>
        <div><div class="eyebrow">KELOLA</div><h1>Kelola <?= e($crudCfg['label']) ?></h1><p class="desc">Tambah, ubah, atau hapus data <?= strtolower(e($crudCfg['label'])) ?> pada portal.</p></div>
      <?php endif; ?>
    </div>

    <?php if ($msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>

    <?php if ($refPanel): ?>
      <?php include __DIR__ . '/_referensi_panel.php'; ?>
    <?php elseif ($showForm): ?>
      <?php include __DIR__ . '/../../app/includes/crud_form.php'; ?>
    <?php else: ?>
      <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; justify-content:flex-end; margin-bottom:14px;">
        <?php if ($searchCols): ?><?= cari_form(['entity' => $crudEntitas], $q, 'Cari ' . strtolower($crudCfg['label']) . '...', null, null, 'cari-inline') ?><?php endif; ?>
        <a class="btn btn-primary" href="?entity=<?= e($crudEntitas) ?>&amp;action=create<?= e($qs) ?>"><i class="bi bi-plus-circle"></i> Tambah</a>
      </div>
      <?php if ($searchCols): ?>
        <div class="cari-info"><?= $q === '' ? e($totalRows . ' data') : e('Menampilkan ' . count($rows) . ' dari ' . $totalRows . ' data') ?></div>
      <?php endif; ?>
      <div class="table-wrap">
        <table>
          <thead><tr><?php if ($listHasFoto): ?><th style="width:112px;">Foto</th><?php endif; ?><?php foreach ($crudCfg['list'] as $col): ?><th><?= e(($crudCfg['column_labels'] ?? [])[$col] ?? $crudCfg['fields'][$col]['label'] ?? ucfirst($col)) ?></th><?php endforeach; ?><th style="width:120px;">Aksi</th></tr></thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
              <tr>
                <?php if ($listHasFoto): ?>
                  <td><?php if (!empty($row['foto'])): ?><img src="<?= base_url(e($row['foto'])) ?>" alt="" loading="lazy" decoding="async" style="display:block; width:96px; height:96px; object-fit:cover; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface-2);"><?php else: ?><span style="color:var(--text-dim); font-size:12px;">-</span><?php endif; ?></td>
                <?php endif; ?>
                <?php foreach ($crudCfg['list'] as $col): ?>
                  <td><?php
                    if ($col === 'username') {
                        echo e($akunList[(int) $row['id']] ?? '-');
                    } elseif ($col === 'akun_email') {
                        echo e(trunc((string) ($akunEmailList[(int) $row['id']] ?? '-'), 40));
                    } else {
                        echo e(trunc((string) ($row[$col] ?? '-'), 60));
                    }
                  ?></td>
                <?php endforeach; ?>
                <td>
                  <div class="row-actions">
                    <?php if (!empty($crudCfg['toggle_status'])): $tampil = ($row['status'] ?? '') === 'Tampil'; ?>
                      <form method="post" action="?entity=<?= e($crudEntitas) ?>&amp;action=toggle_status&amp;id=<?= (int) $row['id'] ?><?= e($qs) ?>" style="display:flex;">
                        <?= Csrf::field() ?>
                        <button type="submit" class="icon-btn<?= $tampil ? ' is-on' : '' ?>"
                                title="<?= $tampil ? 'Sembunyikan dari slider' : 'Tampilkan di slider' ?>"
                                aria-label="<?= $tampil ? 'Sembunyikan' : 'Tampilkan' ?> slider">
                          <i class="bi <?= $tampil ? 'bi-eye' : 'bi-eye-slash' ?>"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                    <?php $itemId = (int) $row['id']; include __DIR__ . '/../../app/includes/partial_admin_actions.php'; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="<?= count($crudCfg['list']) + ($listHasFoto ? 2 : 1) ?>"><div class="empty"><?= $q !== '' ? 'Data tidak ditemukan.' : 'Belum ada data.' ?></div></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php if ($crudEntitas === 'materi' && !$refPanel): ?>
        <div style="margin-top:18px;">
          <a class="btn btn-outline" href="?entity=materi&amp;sub=referensi"><i class="bi bi-link-45deg"></i> Kelola Referensi Belajar</a>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../../app/includes/footer.php'; ?>