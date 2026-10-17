<?php
// Form tambah / ubah konten untuk admin. Memakai variabel dari crud_handle.php
// ($crudCfg, $editing, $crudEntitas, $q, $qs). Dipakai oleh public/admin/crud.php
// dan oleh halaman fitur publik ketika admin mengelola konten di sana.
//
// Tanpa atribut action: form dikirim ke URL yang sedang dibuka, dan URL itu
// selalu sudah memuat ?entity=... atau ?manage=... supaya crud_handle.php tahu
// entitas yang dikirim.

$hasWhen = false;
?>
<form class="crud-form" method="post" enctype="multipart/form-data">
  <?= Csrf::field() ?>
  <input type="hidden" name="manage" value="<?= e($crudEntitas) ?>">
  <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>
  <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
  <div class="card crud-form-main">
    <div class="form-grid">
    <?php foreach ($crudCfg['fields'] as $key => $f):
      if ($key === 'foto' && !($crudCfg['foto_inline'] ?? false)) continue; // panel kanan, di luar kartu
      $val = $editing[$key] ?? '';
      if ($key === 'username') $val = $akunUsername;
      $full = in_array($f['type'], ['textarea', 'pelatihan_multi', 'anggota_multi', 'peraih']) ? ' field-full' : '';
      if ($key === 'foto' && ($crudCfg['foto_inline'] ?? false)) $full = ' field-full';
      $whenAttr = '';
      if (!empty($f['show_when'])) {
          $hasWhen = true;
          $wKey = array_key_first($f['show_when']);
          $whenAttr = ' data-when-show="' . e($wKey . ':' . $f['show_when'][$wKey]) . '"';
          $wCur = (string) ($editing[$wKey] ?? ($crudCfg['fields'][$wKey]['options'][0] ?? ''));
          if ($wCur !== $f['show_when'][$wKey]) $whenAttr .= ' style="display:none;"';
      }
    ?>
      <div class="field<?= $full ?>"<?= $whenAttr ?>>
        <label><?= e($f['label']) ?><?= !empty($f['required']) ? ' *' : '' ?></label>
        <?php if ($f['type'] === 'textarea'): ?>
          <textarea name="<?= $key ?>" rows="4" <?= !empty($f['required']) ? 'required' : '' ?>><?= e($val) ?></textarea>
        <?php elseif ($f['type'] === 'select'): ?>
          <select name="<?= $key ?>">
            <?php foreach ($f['options'] as $opt): ?>
              <option <?= $val === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
            <?php endforeach; ?>
          </select>
        <?php elseif ($f['type'] === 'anggota_select'): ?>
          <select name="<?= $key ?>">
            <?php foreach ($anggotaOptions as $a): ?>
              <option value="<?= $a['id'] ?>" <?= (int) $val === (int) $a['id'] ? 'selected' : '' ?>><?= e($a['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        <?php elseif ($f['type'] === 'video'): ?>
          <input name="<?= $key ?>" value="<?= e($val) ?>" placeholder="https://youtube.com/">
          <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Kosongkan bila tidak ada video. Bisa juga mengunggah berkas video.</div>
          <input type="file" name="<?= $key ?>_file" accept=".mp4,.webm,.mov,.m4v" style="margin-top:6px;">
          <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Unggahan: MP4/WEBM/MOV/M4V, maks 40MB.</div>
          <?php if ($val): ?><div style="font-size:12px; color:var(--teal); margin-top:4px;">Saat ini: <?= e($val) ?></div><?php endif; ?>
        <?php elseif ($f['type'] === 'peraih'): ?>
          <div class="peraih-mode-row" style="display:flex; gap:18px; margin-bottom:8px;">
            <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
              <input type="radio" name="peraih_mode" value="anggota" <?= $peraihMode === 'anggota' ? 'checked' : '' ?> onchange="peraihModeChange(this.value)"> Pilih dari anggota
            </label>
            <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
              <input type="radio" name="peraih_mode" value="manual" <?= $peraihMode === 'manual' ? 'checked' : '' ?> onchange="peraihModeChange(this.value)"> Manual
            </label>
          </div>
          <div id="peraihAnggotaWrap"<?= $peraihMode === 'anggota' ? '' : ' style="display:none;"' ?>>
            <?php if ($anggotaOptions): ?>
              <div class="peraih-check-list">
                <?php foreach ($anggotaOptions as $a): ?>
                  <label class="peraih-check-item">
                    <input type="checkbox" name="anggota_ids[]" value="<?= (int) $a['id'] ?>" <?= in_array((int) $a['id'], $terpilihAnggota, true) ? 'checked' : '' ?>>
                    <span><?= e($a['nama']) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
              <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Centang satu atau lebih anggota.</div>
            <?php else: ?>
              <div style="font-size:12px; color:var(--text-muted);">Belum ada data anggota.</div>
            <?php endif; ?>
          </div>
          <div id="peraihManualWrap"<?= $peraihMode === 'manual' ? '' : ' style="display:none;"' ?>>
            <textarea name="peraih_manual" rows="3" placeholder="Budi Santoso, Siti Aminah"><?= e($peraihManualTeks) ?></textarea>
            <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Pisahkan beberapa nama dengan koma.</div>
          </div>
          <script>
          function peraihModeChange(m){
            document.getElementById('peraihAnggotaWrap').style.display = m === 'anggota' ? '' : 'none';
            document.getElementById('peraihManualWrap').style.display = m === 'manual' ? '' : 'none';
          }
          </script>
        <?php elseif ($f['type'] === 'pelatihan_multi'): ?>
          <select name="<?= $key ?>[]" multiple size="5">
            <?php foreach ($pelatihanOptions as $pl): ?>
              <option value="<?= (int) $pl['id'] ?>" <?= in_array((int) $pl['id'], $terpilihPelatihan, true) ? 'selected' : '' ?>><?= e($pl['nama']) ?></option>
            <?php endforeach; ?>
          </select>
          <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Tahan Ctrl / Cmd untuk memilih beberapa. Materi hanya bisa diunduh peserta pelatihan ini.</div>
        <?php elseif ($f['type'] === 'anggota_multi'): ?>
          <select name="<?= $key ?>[]" multiple size="5">
            <?php foreach ($anggotaOptions as $a): ?>
              <option value="<?= (int) $a['id'] ?>" <?= in_array((int) $a['id'], $terpilihAnggota, true) ? 'selected' : '' ?>><?= e($a['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        <?php elseif ($f['type'] === 'akun_username'): ?>
          <input name="<?= $key ?>" value="<?= e($val) ?>" required autocomplete="off">
        <?php elseif ($f['type'] === 'password'): ?>
          <input type="password" name="<?= $key ?>" <?= $editing ? '' : 'required' ?> minlength="8" autocomplete="new-password">
          <div style="font-size:12px; color:var(--text-muted); margin-top:4px;"><?= $editing ? 'Kosongkan bila tidak ingin mengubah kata sandi.' : 'Minimal 8 karakter.' ?></div>
        <?php elseif ($f['type'] === 'date'): ?>
          <input type="date" name="<?= $key ?>" value="<?= e($val ? date('Y-m-d', strtotime($val)) : '') ?>" <?= !empty($f['required']) ? 'required' : '' ?>>
        <?php elseif ($f['type'] === 'file'): ?>
          <input type="file" name="<?= $key ?>" accept=".jpg,.jpeg,.png,.webp">
          <span class="hint">Format JPG, JPEG, PNG, atau WEBP. Ukuran maksimal 2MB file yang melebihi batas akan ditolak.</span>
          <?php if ($val): ?>
            <div style="margin-top:8px;"><img src="<?= base_url(e($val)) ?>" alt="Preview" style="display:block; width:100%; max-width:260px; height:auto; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface-2);"></div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Saat ini: <?= e($val) ?></div>
          <?php endif; ?>
        <?php elseif ($f['type'] === 'doc_file'): ?>
          <input type="file" name="<?= $key ?>" accept=".pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx,.txt,.zip">
          <?php if (!empty($f['restricted'])): ?>
            <span class="hint">Berkas ini terbatas: disimpan di luar document root dan hanya bisa diunduh anggota yang berhak lewat halaman Materi.</span>
          <?php endif; ?>
          <?php if ($val): ?><div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Saat ini: <?= e($val) ?></div><?php endif; ?>
        <?php else: ?>
          <input name="<?= $key ?>" value="<?= e($val) ?>" <?= !empty($f['required']) ? 'required' : '' ?>>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    </div>
    <?php if ($hasWhen): ?>
    <script>
    document.querySelectorAll('.crud-form [data-when-show]').forEach(function (wrap) {
      var parts = wrap.getAttribute('data-when-show').split(':');
      var ctrl = document.querySelector('.crud-form [name="' + parts[0] + '"]');
      if (!ctrl) return;
      var sync = function () { wrap.style.display = ctrl.value === parts[1] ? '' : 'none'; };
      ctrl.addEventListener('change', sync);
      sync();
    });
    </script>
    <?php endif; ?>
    <div style="display:flex; gap:10px; margin-top:10px;">
      <button class="btn btn-primary" type="submit"><?= $editing ? 'Simpan Perubahan' : 'Tambah Data' ?></button>
      <a class="btn btn-outline" href="<?= e($crudBase . $qs) ?>">Batal</a>
    </div>
  </div>
  <?php if (isset($crudCfg['fields']['foto']) && !($crudCfg['foto_inline'] ?? false)): $valFoto = $editing['foto'] ?? ''; ?>
    <aside class="card crud-form-foto">
      <div class="field" style="margin-bottom:0;">
        <label><?= e($crudCfg['fields']['foto']['label']) ?></label>
        <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">
        <span class="hint">Format JPG, JPEG, PNG, atau WEBP. Ukuran maksimal 2MB file yang melebihi batas akan ditolak.</span>
        <?php if ($valFoto): ?>
          <div style="margin-top:10px;"><img src="<?= base_url(e($valFoto)) ?>" alt="Preview" style="display:block; width:100%; height:170px; object-fit:cover; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface-2);"></div>
          <span class="hint">Saat ini: <?= e($valFoto) ?></span>
        <?php endif; ?>
      </div>
    </aside>
  <?php endif; ?>
</form>