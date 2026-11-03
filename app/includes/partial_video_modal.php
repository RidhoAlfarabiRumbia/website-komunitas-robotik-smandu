<div class="video-modal" id="videoModal" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="videoModalTitle">
  <div class="video-modal-inner">
    <div class="video-modal-head">
      <h2 id="videoModalTitle">Dokumentasi Kegiatan</h2>
      <button type="button" class="video-modal-close" id="videoModalClose" aria-label="Tutup dokumentasi"><i class="bi bi-x-lg"></i></button>
    </div>
    <video id="videoModalPlayer" controls playsinline preload="none"></video>
  </div>
</div>
<script>
(function(){
  var modal = document.getElementById('videoModal');
  if (!modal) return;
  var player = document.getElementById('videoModalPlayer');
  var title = document.getElementById('videoModalTitle');
  var close = document.getElementById('videoModalClose');
  var fokusSebelum = null;

  function buka(src, label) {
    if (!src) return;
    fokusSebelum = document.activeElement;
    title.textContent = label || 'Dokumentasi Kegiatan';
    player.src = src;
    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    var putar = player.play();
    if (putar && typeof putar.catch === 'function') putar.catch(function(){});
    close.focus();
  }

  function tutup() {
    player.pause();
    player.removeAttribute('src');
    player.load();
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (fokusSebelum && typeof fokusSebelum.focus === 'function') fokusSebelum.focus();
  }

  document.addEventListener('click', function(e){
    var pemicu = e.target.closest('[data-video-modal]');
    if (pemicu) {
      e.preventDefault();
      buka(pemicu.getAttribute('data-video-modal'), pemicu.getAttribute('data-video-title'));
      return;
    }
    if (e.target === modal) tutup();
  });

  close.addEventListener('click', tutup);
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && !modal.hidden) tutup();
  });
})();
</script>
