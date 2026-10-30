<?php
$heroSlides = $heroData['slides'] ?? [];
$heroTunda = $heroData['deferred'] ?? [];
$nSlides = (int) ($heroData['total'] ?? 0);
$sliderAdminHref = $sliderAdminHref ?? null;
?>
<?php if ($heroSlides): ?>
  <div class="slider">
    <?php foreach ($heroSlides as $i => $h): ?>
      <?php
        $foto = $h['img'] ?? 'assets/img/hero-illustration.svg';
        $fotoKiri = $h['kiri'] ?? $foto;
        $fotoKanan = $h['kanan'] ?? $foto;
        if ($i > 0) continue;
      ?>
      <div class="slide active" style="--slide-img:url('<?= base_url(e($foto)) ?>')">
        <span class="slide-photos">
          <?php if ($nSlides > 1): ?><img class="ph ph-side" src="<?= base_url(e($fotoKiri)) ?>" alt="" aria-hidden="true" fetchpriority="high" decoding="async"><?php endif; ?>
          <img class="ph ph-main" src="<?= base_url(e($foto)) ?>" alt="Gambar slider" fetchpriority="high" decoding="async">
          <?php if ($nSlides > 1): ?><img class="ph ph-side" src="<?= base_url(e($fotoKanan)) ?>" alt="" aria-hidden="true" fetchpriority="high" decoding="async"><?php endif; ?>
        </span>
      </div>
    <?php endforeach; ?>
    <?php if ($sliderAdminHref || $nSlides > 1): ?>
      <div class="dots">
        <?php if ($sliderAdminHref): ?>
          <a class="chip-admin" href="<?= e($sliderAdminHref) ?>" title="Kelola gambar slider"><i class="bi bi-gear"></i> Kelola Slider</a>
        <?php endif; ?>
        <?php if ($nSlides > 1): ?>
          <?php for ($i = 0; $i < $nSlides; $i++): ?><button type="button" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Ke slide <?= $i + 1 ?>"></button><?php endfor; ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="hero-illustration"><img src="<?= base_url('assets/img/hero-illustration.svg') ?>" alt="Ilustrasi robot"></div>
<?php endif; ?>
<?php if ($sliderAdminHref && !$heroSlides): ?>
  <a class="chip-admin" href="<?= e($sliderAdminHref) ?>" title="Kelola gambar slider"><i class="bi bi-gear"></i> Kelola Slider</a>
<?php endif; ?>
<?php if (!empty($heroTunda)): ?>
<script type="application/json" id="hero-slides-data"><?= json_encode(['total' => $nSlides, 'slides' => $heroTunda], JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endif; ?>
<?php if ($heroSlides): ?>
<script>
(function(){
  var slider = document.querySelector('.slider');
  if (!slider) return;
  var INTERVAL = 7000;
  var dots = slider.querySelectorAll('.dots button');
  var dataEl = document.getElementById('hero-slides-data');
  var tunda = null;
  if (dataEl) { try { tunda = JSON.parse(dataEl.textContent); } catch (err) { tunda = null; } }
  var slidesData = tunda && tunda.slides ? tunda.slides : [];
  var total = (tunda && tunda.total) || slider.querySelectorAll('.slide').length;
  var dotsWrap = slider.querySelector('.dots');
  var slideEls = new Array(total);
  Array.prototype.forEach.call(slider.querySelectorAll('.slide'), function(el, k){ slideEls[k] = el; });
  var i = 0;

  function mkImg(cls, src, alt, hidden){
    var im = document.createElement('img');
    im.className = cls;
    im.src = src;
    im.alt = alt;
    if (hidden) im.setAttribute('aria-hidden', 'true');
    im.setAttribute('fetchpriority', 'low');
    im.setAttribute('decoding', 'async');
    return im;
  }
  function ensure(n){
    if (slideEls[n]) return slideEls[n];
    var d = slidesData[n - 1];
    if (!d) return null;
    var el = document.createElement('div');
    el.className = 'slide';
    el.style.setProperty('--slide-img', 'url("' + d.img + '")');
    var ph = document.createElement('span');
    ph.className = 'slide-photos';
    if (total > 1) ph.appendChild(mkImg('ph ph-side', d.kiri, '', true));
    ph.appendChild(mkImg('ph ph-main', d.img, 'Gambar slider', false));
    if (total > 1) ph.appendChild(mkImg('ph ph-side', d.kanan, '', true));
    el.appendChild(ph);
    slider.insertBefore(el, dotsWrap || null);
    slideEls[n] = el;
    return el;
  }
  function go(n){
    if (slideEls[i]) slideEls[i].classList.remove('active');
    if (dots.length && dots[i]) dots[i].classList.remove('active');
    i = (n + total) % total;
    var nxt = ensure(i);
    if (nxt) nxt.classList.add('active');
    if (dots.length && dots[i]) dots[i].classList.add('active');
  }
  function centerOf(el){
    if (!el) return '';
    var img = el.querySelector('.ph-main');
    return img ? (img.getAttribute('src') || '') : '';
  }
  function nextIdx(){
    var cur = centerOf(slideEls[i]);
    for (var k = 1; k <= total; k++){
      var idx = (i + k) % total;
      if (centerOf(slideEls[idx]) !== cur) return idx;
    }
    return (i + 1) % total;
  }
  function adv(){ go(nextIdx()); }
  var timer = null;
  function restart(){
    clearInterval(timer);
    if (total > 1) timer = setInterval(adv, INTERVAL);
  }
  restart();
  dots.forEach(function(d, j){
    d.addEventListener('click', function(){ go(j); restart(); });
  });
  slider.addEventListener('click', function(e){
    if (e.target.closest('.dots')) return;
    if (total < 2) return;
    adv();
    restart();
  });
  if (slidesData.length){
    var kick = function(){
      var idle = window.requestIdleCallback || function(fn){ setTimeout(fn, 100); };
      idle(function(){ for (var n = 1; n < total; n++) ensure(n); });
    };
    if (document.readyState === 'complete') kick();
    else window.addEventListener('load', kick);
  }
})();
</script>
<?php endif; ?>
