<?php
// Tombol Kelola (Ubah / Hapus) untuk satu item — hanya untuk admin.
//
// Variabel yang dibutuhkan (diisi halaman induk sebelum include):
//   $crudQs   query penanda mode kelola, mis. '?manage=karya'
//   $itemId   id baris yang akan diubah/dihapus
//   $qs       kata kunci pencarian aktif (boleh tidak diisi)
// Admin bukan admin: tidak menghasilkan apa pun.
if (!Auth::isAdmin()) {
    return;
}
$qsItem = $qs ?? '';
?>
<div class="admin-item-actions">
  <a class="icon-btn" href="<?= e($crudQs) ?>&amp;action=edit&amp;id=<?= (int) $itemId ?><?= e($qsItem) ?>" title="Ubah data ini"><i class="bi bi-pencil"></i></a>
  <form method="post" action="<?= e($crudQs) ?>&amp;action=delete&amp;id=<?= (int) $itemId ?><?= e($qsItem) ?>" style="display:flex;"
        onsubmit="return confirm('Hapus data ini?')">
    <?= Csrf::field() ?>
    <button type="submit" class="icon-btn" title="Hapus data ini"><i class="bi bi-trash"></i></button>
  </form>
</div>