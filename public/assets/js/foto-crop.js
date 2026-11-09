/**
 * Penyesuaian foto dalam frame (modal) sebelum diunggah.
 *
 * Frame mengikuti rasio tampilana zona (data-crop-aspect pada input, mis.
 * "16/9" untuk kartu Karya/Galeri/Prestasi, "3/4" untuk slider hero, "1/1"
 * untuk avatar) supaya hasil potongan sesuai dengan yang ditampilkan site.
 *
 * Dipicu dari dua jalur:
 *  1) input[type=file].js-foto-crop — setelah berkas dipilih, foto dibuka
 *     di dalam frame, bisa digeser (drag) dan di-zoom (roda mouse, tombol,
 *     atau slider).
 *  2) tombol .js-crop-edit dengan data-crop-src — foto yang sudah tersimpan
 *     dibuka kembali di modal yang sama; hasil penyesuaian menggantikan
 *     berkas pada input sehingga tersimpan sebagai foto baru saat form
 *     dikirim.
 *
 * "Gunakan Foto Ini" memotong foto via canvas dan menggantikan berkas pada
 * input form (DataTransfer) supaya pengiriman form dan pemeriksa server
 * (handle_upload) tetap tidak berubah.
 */
(function () {
  'use strict';

  var WADAH_GAMBAR = ['image/jpeg', 'image/png', 'image/webp'];
  var SISI_MAKS = 1600;
  var KUALITAS = 0.86;

  var modal = document.getElementById('cropModal');
  if (!modal) return;

  var canvas = modal.querySelector('.crop-canvas');
  var frame = modal.querySelector('.crop-frame');
  var stage = modal.querySelector('.crop-stage');
  var slider = modal.querySelector('.crop-zoom-slider');
  var btnTutup = modal.querySelector('.crop-tutup');
  var btnReset = modal.querySelector('.crop-reset');
  var btnBatal = modal.querySelector('.crop-batal');
  var btnGunakan = modal.querySelector('.crop-gunakan');
  var btnZoomPlus = modal.querySelector('.crop-zoom-plus');
  var btnZoomMin = modal.querySelector('.crop-zoom-min');

  var ctx = canvas.getContext('2d');
  var src = null;          // { img, nama }
  var kunci = null;        // { rasio, frameW, frameH, scale, min, max, ox, oy }
  var inputAktif = null;
  var drag = null;         // { x, y, ox, oy }

  function rasioZona(nilai) {
    var m = /^(\d{1,2})\/(\d{1,2})$/.exec(String(nilai || ''));
    if (!m) return 3 / 4;
    var a = +m[1], b = +m[2];
    return (a > 0 && b > 0) ? a / b : 3 / 4;
  }

  function buka(input) {
    inputAktif = input;
    var file = input.files && input.files[0];
    var token = (input.cropToken = (input.cropToken || 0) + 1);
    if (!file || WADAH_GAMBAR.indexOf(file.type) === -1) {
      // Bukan gambar yang didukung modal: biarkan berkas asli terkirim
      // sebagaimana alur upload biasa (server tetap memvalidasi).
      inputAktif = null;
      return;
    }
    var rasioDefault = rasioZona(input.dataset.cropAspect);
    var reader = new FileReader();
    reader.onload = function () {
      var im = new Image();
      im.onload = function () {
        if (token !== input.cropToken) return;
        src = { img: im, nama: file.name };
        bukaModal();
        aturRasio(rasioDefault);
      };
      im.onerror = function () { tutupModal(); };
      im.src = reader.result;
    };
    reader.readAsDataURL(file);
  }

  // Buka modal memakai foto yang sudah tersimpan (tombol "Edit Foto").
  function bukaDariUrl(input, url, rasioAttr) {
    inputAktif = input;
    var token = (input.cropToken = (input.cropToken || 0) + 1);
    var im = new Image();
    im.onload = function () {
      if (token !== input.cropToken) return;
      src = { img: im, nama: 'foto.jpg' };
      bukaModal();
      aturRasio(rasioZona(rasioAttr || input.dataset.cropAspect));
    };
    im.onerror = function () { inputAktif = null; };
    im.src = url;
  }

  function ukuranFrame(rasio) {
    var lebarMaks = Math.max(280, Math.min(stage.clientWidth - 40, 820));
    var tinggiMaks = Math.max(240, Math.min(stage.clientHeight - 36, 520));
    var w = tinggiMaks * rasio;
    var h = tinggiMaks;
    if (w > lebarMaks) { w = lebarMaks; h = w / rasio; }
    return { w: Math.round(w), h: Math.round(h) };
  }

  function aturRasio(rasio) {
    if (!src) return;
    var uk = ukuranFrame(rasio);
    kunci = {
      rasio: rasio,
      frameW: uk.w,
      frameH: uk.h,
      scale: 1, min: 1, max: 4,
      ox: 0, oy: 0
    };
    frame.style.width = uk.w + 'px';
    frame.style.height = uk.h + 'px';
    canvas.width = uk.w;
    canvas.height = uk.h;
    resetZoom();
  }

  function ukuranCakup() {
    var iw = src.img.naturalWidth, ih = src.img.naturalHeight;
    return Math.max(kunci.frameW / iw, kunci.frameH / ih);
  }

  function resetZoom() {
    var cakup = ukuranCakup();
    kunci.min = cakup;
    kunci.max = Math.min(ukuranCakup() * 4, 8);
    kunci.scale = cakup;
    slider.min = '0';
    slider.max = '1000';
    slider.value = '0';
    kunci.ox = 0;
    kunci.oy = 0;
    batasiDanGambar();
  }

  function batasi() {
    var sw = src.img.naturalWidth * kunci.scale;
    var sh = src.img.naturalHeight * kunci.scale;
    var batasX = Math.max(0, (sw - kunci.frameW) / 2);
    var batasY = Math.max(0, (sh - kunci.frameH) / 2);
    kunci.ox = Math.min(batasX, Math.max(-batasX, kunci.ox));
    kunci.oy = Math.min(batasY, Math.max(-batasY, kunci.oy));
  }

  function gambar() {
    if (!src || !kunci) return;
    var iw = src.img.naturalWidth, ih = src.img.naturalHeight;
    var sw = iw * kunci.scale, sh = ih * kunci.scale;
    ctx.clearRect(0, 0, kunci.frameW, kunci.frameH);
    ctx.fillStyle = '#10161f';
    ctx.fillRect(0, 0, kunci.frameW, kunci.frameH);
    ctx.drawImage(src.img,
      (kunci.frameW - sw) / 2 + kunci.ox,
      (kunci.frameH - sh) / 2 + kunci.oy,
      sw, sh);
  }

  function batasiDanGambar() {
    if (!src || !kunci) return;
    batasi();
    gambar();
    var pos = kunci.max > kunci.min
      ? Math.round(1000 * (Math.log(kunci.scale / kunci.min) / Math.log(kunci.max / kunci.min)))
      : 0;
    slider.value = String(Math.min(1000, Math.max(0, pos)));
  }

  function tepatPosDariSlider(v) {
    if (!src || !kunci || kunci.max <= kunci.min) return;
    var t = (+v) / 1000;
    var s = kunci.min * Math.pow(kunci.max / kunci.min, t);
    // Jaga titik tengah tetap: hitung pergeseran supaya pusat gambar berada
    // di posisi yang sama sebelum/ sesudah perubahan zoom.
    var cx = kunci.ox, cy = kunci.oy;
    var swLama = src.img.naturalWidth * kunci.scale, shLama = src.img.naturalHeight * kunci.scale;
    kunci.scale = s;
    var swBaru = src.img.naturalWidth * s, shBaru = src.img.naturalHeight * s;
    kunci.ox = swLama <= kunci.frameW ? 0 : cx * (swBaru / swLama);
    kunci.oy = shLama <= kunci.frameH ? 0 : cy * (shBaru / shLama);
    batasiDanGambar();
  }

  function lonjakanZoom(faktor) {
    if (!src || !kunci) return;
    var cx = kunci.ox, cy = kunci.oy;
    var swLama = src.img.naturalWidth * kunci.scale, shLama = src.img.naturalHeight * kunci.scale;
    kunci.scale = Math.min(kunci.max, Math.max(kunci.min, kunci.scale * faktor));
    var swBaru = src.img.naturalWidth * kunci.scale, shBaru = src.img.naturalHeight * kunci.scale;
    kunci.ox = swLama <= kunci.frameW ? 0 : cx * (swBaru / swLama);
    kunci.oy = shLama <= kunci.frameH ? 0 : cy * (shBaru / shLama);
    batasiDanGambar();
  }

  function bukaModal() {
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function tutupModal() {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  function cariPratinjau(input) {
    var field = input.closest('.field');
    if (!field) return null;
    return field.querySelector('img[src]');
  }

  function gunakan() {
    if (!src || !kunci) { tutupModal(); return; }
    var iw = src.img.naturalWidth, ih = src.img.naturalHeight;
    var sw = iw * kunci.scale, sh = ih * kunci.scale;
    // Area foto yang terlihat dalam frame (koordinat tampilan lalu dibagi
    // zoom supaya balik ke koordinat piksel asli foto).
    var sx = (sw / 2 - kunci.frameW / 2 - kunci.ox) / kunci.scale;
    var sy = (sh / 2 - kunci.frameH / 2 - kunci.oy) / kunci.scale;
    var sisaW = kunci.frameW / kunci.scale;  // lebar crop dalam piksel asli foto
    var sisaH = kunci.frameH / kunci.scale;
    // Kualitas mengikuti piksel asli (dibatasi SISI_MAKS) — bukan ukuran frame
    // di layar, agar hasil tidak pecah.
    var kap = Math.min(1, SISI_MAKS / Math.max(sisaW, sisaH));
    var outW = Math.max(1, Math.round(sisaW * kap));
    var outH = Math.max(1, Math.round(sisaH * kap));
    var out = document.createElement('canvas');
    out.width = outW;
    out.height = outH;
    var octx = out.getContext('2d');
    octx.fillStyle = '#ffffff';
    octx.fillRect(0, 0, outW, outH);
    octx.drawImage(src.img, sx, sy, sisaW, sisaH, 0, 0, outW, outH);
    out.toBlob(function (blob) {
      if (!blob || !inputAktif) { tutupModal(); return; }
      var nama = (src.nama || 'foto').replace(/\.[^.]+$/, '') + '.jpg';
      var fileBaru;
      if (typeof File === 'function') {
        fileBaru = new File([blob], nama, { type: 'image/jpeg', lastModified: Date.now() });
      } else {
        fileBaru = blob;
        fileBaru.name = nama;
      }
      if (typeof DataTransfer === 'function') {
        var dt = new DataTransfer();
        dt.items.add(fileBaru);
        inputAktif.files = dt.files;
      }
      var pv = cariPratinjau(inputAktif);
      if (pv) {
        if (pv.dataset.cropOrig === undefined) pv.dataset.cropOrig = pv.src;
        pv.src = URL.createObjectURL(blob);
      }
      inputAktif.cropPreview = URL.createObjectURL(blob);
      inputAktif.dispatchEvent(new CustomEvent('fotocrop:selesai', { bubbles: true }));
      var tanda = inputAktif.form && inputAktif.form.querySelector('.crop-terpasang[data-kunci="' + inputAktif.name + '"]');
      if (tanda) {
        tanda.hidden = false;
        tanda.textContent = 'Foto baru siap — tersimpan setelah form dikirim.';
      }
      tutupModal();
    }, 'image/jpeg', KUALITAS);
  }

  function batal() {
    if (inputAktif) {
      inputAktif.value = '';
      var tanda = inputAktif.form && inputAktif.form.querySelector('.crop-terpasang[data-kunci="' + inputAktif.name + '"]');
      if (tanda) tanda.hidden = true;
      var pv = cariPratinjau(inputAktif);
      if (pv && pv.dataset.cropOrig) {
        pv.src = pv.dataset.cropOrig;
        delete pv.dataset.cropOrig;
      }
    }
    inputAktif = null;
    tutupModal();
  }

  document.addEventListener('change', function (e) {
    var input = e.target;
    if (!input || input.type !== 'file' || !input.classList.contains('js-foto-crop')) return;
    buka(input);
  }, true);

  // Tombol "Edit Foto": buka foto tersimpan di modal yang sama.
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.js-crop-edit');
    if (!btn) return;
    var wadah = btn.closest('.field') || btn.closest('form');
    var input = wadah ? wadah.querySelector('input[type=file].js-foto-crop') : null;
    var url = btn.dataset.cropSrc;
    if (!input || !url) return;
    if (btn.dataset.cropAspect) input.dataset.cropAspect = btn.dataset.cropAspect;
    bukaDariUrl(input, url, btn.dataset.cropAspect);
  });

  modal.addEventListener('click', function (e) {
    if (e.target === modal) { batal(); }
  });
  btnTutup.addEventListener('click', batal);
  btnBatal.addEventListener('click', batal);
  btnGunakan.addEventListener('click', gunakan);
  btnReset.addEventListener('click', function () { if (src) resetZoom(); });

  btnZoomPlus.addEventListener('click', function () { lonjakanZoom(1.18); });
  btnZoomMin.addEventListener('click', function () { lonjakanZoom(1 / 1.18); });
  slider.addEventListener('input', function () { tepatPosDariSlider(slider.value); });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('open')) { batal(); }
  });

  window.addEventListener('resize', function () {
    if (!modal.classList.contains('open') || !src || !kunci) return;
    aturRasio(kunci.rasio);
  });

  frame.addEventListener('pointerdown', function (e) {
    if (!src || !kunci || e.button !== 0) return;
    e.preventDefault();
    frame.setPointerCapture(e.pointerId);
    frame.classList.add('dragging');
    drag = { x: e.clientX, y: e.clientY, ox: kunci.ox, oy: kunci.oy };
  });
  frame.addEventListener('pointermove', function (e) {
    if (!drag || !kunci) return;
    kunci.ox = drag.ox + (e.clientX - drag.x);
    kunci.oy = drag.oy + (e.clientY - drag.y);
    batasiDanGambar();
  });
  ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(function (ev) {
    frame.addEventListener(ev, function () {
      drag = null;
      frame.classList.remove('dragging');
    });
  });

  frame.addEventListener('wheel', function (e) {
    if (!src) return;
    e.preventDefault();
    lonjakanZoom(e.deltaY > 0 ? (1 / 1.08) : 1.08);
  }, { passive: false });
})();
