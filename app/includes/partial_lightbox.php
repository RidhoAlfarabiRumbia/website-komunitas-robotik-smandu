<?php
/**
 * Lightbox foto: menampilkan gambar ukuran penuh saat thumbnail diklik.
 *
 * Cara pakai: sisipkan partial ini satu kali per halaman (dekat footer),
 * lalu tandai foto yang bisa diperbesar dengan
 *   <span class="work-thumb" data-lightbox="<url>" tabindex="0" role="button">
 *
 * JS memakai event delegation, jadi foto yang dirender belakangan (mis. oleh
 * loop atau AJAX) tetap berfungsi tanpa perlu bind ulang.
 */
?>
<div class="lightbox" id="lightbox" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-label="Pratinjau foto">
  <div class="lightbox-inner">
    <img class="lightbox-img" id="lightboxImg" src="" alt="">
    <div class="lightbox-cap" id="lightboxCap"></div>
  </div>
  <button type="button" class="lightbox-close" id="lightboxClose" aria-label="Tutup pratinjau foto"><i class="bi bi-x-lg"></i></button>
</div>
<script>
(function(){
  var lb = document.getElementById('lightbox');
  if (!lb) return;
  var img = document.getElementById('lightboxImg');
  var cap = document.getElementById('lightboxCap');
  var tutup = document.getElementById('lightboxClose');
  var fokusSebelum = null;

  function buka(src, alt) {
    fokusSebelum = document.activeElement;
    img.src = src;
    img.alt = alt || '';
    cap.textContent = alt || '';
    lb.hidden = false;
    lb.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    tutup.focus();
  }

  function close() {
    lb.hidden = true;
    lb.setAttribute('aria-hidden', 'true');
    img.src = '';
    cap.textContent = '';
    document.body.style.overflow = '';
    // Kembalikan fokus ke elemen yang diklik, supaya keyboard user tidak
    // "hilang" di awal dokumen.
    if (fokusSebelum && typeof fokusSebelum.focus === 'function') fokusSebelum.focus();
  }

  // Delegasi: satu listener untuk semua foto, termasuk yang dirender belakangan.
  document.addEventListener('click', function (e) {
    var pemicu = e.target.closest('[data-lightbox]');
    if (!pemicu) return;
    // Foto ini berada di dalam <a> kartu: cegah agar halaman tidak ikut terbuka.
    e.preventDefault();
    var g = pemicu.querySelector('img') || pemicu;
    buka(g.getAttribute('src') || pemicu.getAttribute('data-lightbox'), g.getAttribute('alt') || '');
  });

  document.addEventListener('keydown', function (e) {
    // Ketik Enter/Space pada foto yang difokuskan untuk membuka pratinjau.
    if (!lb.hidden) {
      if (e.key === 'Escape') close();
      return;
    }
    if (e.key !== 'Enter' && e.key !== ' ') return;
    var pemicu = document.activeElement;
    if (!pemicu || !pemicu.hasAttribute || !pemicu.hasAttribute('data-lightbox')) return;
    e.preventDefault();
    var g = pemicu.querySelector('img') || pemicu;
    buka(g.getAttribute('src') || pemicu.getAttribute('data-lightbox'), g.getAttribute('alt') || '');
  });

  tutup.addEventListener('click', close);
  // Klik pada area gelap (di luar gambar) menutup pratinjau.
  lb.addEventListener('click', function (e) {
    if (e.target === lb) close();
  });
})();
</script>