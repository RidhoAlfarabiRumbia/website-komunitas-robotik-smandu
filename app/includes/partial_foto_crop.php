<?php
/**
 * Markup modal Penyesuaian Foto (foto-crop.js) + boot-nya.
 * Pastikan sama selektornya dengan public/assets/js/foto-crop.js:
 * #cropModal, .crop-frame, .crop-canvas, .crop-stage, .crop-zoom-slider,
 * .crop-tutup, .crop-reset, .crop-batal, .crop-gunakan, .crop-zoom-plus,
 * .crop-zoom-min. Frame mengikuti rasio dari data-crop-aspect pada input
 * foto zona terkait; tombol .js-crop-edit di form membuka foto tersimpan.
 * Muat persis sekali per halaman (sebelum include footer.php).
 */
?>
<div class="crop-modal" id="cropModal" aria-hidden="true">
  <div class="crop-card" role="dialog" aria-modal="true" aria-label="Penyesuaian foto">
    <div class="crop-head">
      <h3><i class="bi bi-crop"></i> Penyesuaian Foto</h3>
      <button type="button" class="crop-tutup" aria-label="Tutup penyesuaian foto"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="crop-stage">
      <div class="crop-frame">
        <canvas class="crop-canvas" width="360" height="480" aria-label="Pratinjau foto"></canvas>
      </div>
    </div>

    <div class="crop-tools">
      <div class="crop-zoom">
        <button type="button" class="crop-zoom-btn crop-zoom-min" aria-label="Perkecil"><i class="bi bi-dash-lg"></i></button>
        <input type="range" class="crop-zoom-slider" min="0" max="1000" value="0" aria-label="Tingkat zoom">
        <button type="button" class="crop-zoom-btn crop-zoom-plus" aria-label="Perbesar"><i class="bi bi-plus-lg"></i></button>
      </div>
    </div>

    <div class="crop-actions">
      <button type="button" class="btn btn-outline btn-sm crop-reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
      <button type="button" class="btn btn-danger-outline btn-sm crop-batal">Batalkan</button>
      <button type="button" class="btn btn-primary crop-gunakan"><i class="bi bi-check2"></i> Gunakan Foto Ini</button>
    </div>

    <p class="crop-hint">Geser foto langsung memakai mouse untuk menata posisi. Zoom memakai roda mouse, tombol, atau geser nilai di bar di tengah. Frame sudah mengikuti bentuk tempat foto ini akan tampil, lalu tekan "Gunakan Foto Ini".</p>
  </div>
</div>
<script src="<?= base_url('assets/js/foto-crop.js?v=2') ?>"></script>
