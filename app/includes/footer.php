<?php
// Footer hanya untuk halaman member/publik. Halaman admin (public/admin/)
// tidak menampilkan footer; script di bawah & penutup dokumen tetap ikut.
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$sedangAdmin = str_ends_with($scriptDir, '/admin');
if (!$sedangAdmin):
?>
<footer class="footer">
  <div class="footer-main">
    <div class="footer-col footer-about">
      <a href="<?= base_url('beranda.php') ?>" class="footer-brand"><span class="brand-dot"></span> Robotik Smandu</a>
      <p class="footer-desc">Komunitas robotik SMA Negeri 2 Kota Ternate. Wadah belajar, berkarya, dan berkompetisi.</p>
    </div>
    <div class="footer-col">
      <h3>Jelajahi</h3>
      <ul>
        <li><a href="<?= base_url('beranda.php') ?>">Beranda</a></li>
        <li><a href="<?= base_url('karya.php') ?>">Karya</a></li>
        <li><a href="<?= base_url('galeri.php') ?>">Galeri</a></li>
        <li><a href="<?= base_url('prestasi.php') ?>">Prestasi</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h3>Komunitas</h3>
      <ul>
        <li><a href="<?= base_url('pelatihan.php') ?>">Pelatihan</a></li>
        <li><a href="<?= base_url('lomba.php') ?>">Lomba</a></li>
        <li><a href="<?= base_url('kegiatan.php') ?>">Kegiatan</a></li>
        <li><a href="<?= base_url('anggota.php') ?>">Anggota</a></li>
        <li><a href="<?= base_url('pengumuman.php') ?>">Pengumuman</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h3>Terhubung</h3>
      <ul>
        <li><a href="<?= base_url('daftar.php') ?>">Daftar Anggota</a></li>
        <li><a href="<?= base_url('login.php') ?>">Masuk Portal</a></li>
      </ul>
      <div class="footer-social">
        <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
        <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
        <a href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
        <a href="https://www.tiktok.com/" target="_blank" rel="noopener noreferrer" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <span>&copy; <?= date('Y') ?> Komunitas Robotik SMA Negeri 2 Kota Ternate. Hak cipta dilindungi.</span>
  </div>
</footer>
<?php endif; ?>
<script>
function toggleTheme(){
  var html=document.documentElement;
  var btn=document.querySelector('.theme-toggle');
  if(btn){btn.style.pointerEvents='none';}
  var isDark=!html.classList.contains('dark');
  localStorage.setItem('theme',isDark?'dark':'light');
  function applyTheme(){
    html.classList.toggle('dark',isDark);
    html.classList.add('theme-snap');
    syncThemeIcon(isDark);
  }
  function unlock(){
    html.classList.remove('theme-snap');
    if(btn){setTimeout(function(){btn.style.pointerEvents='';},400);}
  }
  if(document.startViewTransition){
    document.startViewTransition(applyTheme).finished.then(unlock,unlock);
  }else{
    applyTheme();
    unlock();
  }
}
function syncThemeIcon(isDark){
  var d=document.getElementById('themeIconDark');
  var l=document.getElementById('themeIconLight');
  if(d&&l){d.style.display=isDark?'none':'';l.style.display=isDark?'':'none';}
}
syncThemeIcon(document.documentElement.classList.contains('dark'));

/* Drawer layar kecil: sidebar admin & menu utama member */
function bindDrawer(toggleId, panelId, openClass, closeId, backdropId){
  var btn=document.getElementById(toggleId);
  if(!btn)return;
  var panel=document.getElementById(panelId);
  var bd=document.getElementById(backdropId);
  var cl=closeId?document.getElementById(closeId):null;
  function open(v){
    document.body.classList.toggle(openClass,v);
    btn.setAttribute('aria-expanded',v?'true':'false');
  }
  btn.addEventListener('click',function(e){
    e.stopPropagation();
    open(!document.body.classList.contains(openClass));
  });
  if(bd)bd.addEventListener('click',function(){open(false);});
  if(cl)cl.addEventListener('click',function(e){e.stopPropagation();open(false);btn.focus();});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')open(false);});
  if(panel)panel.addEventListener('click',function(e){if(e.target.closest('a'))open(false);});
}
bindDrawer('sidebarToggle','adminSidebar','sidebar-open','sidebarClose','sidebarBackdrop');
bindDrawer('navToggle','navMenu','nav-open','navClose','navBackdrop');

/* Scroll reveal — wajib gagal-aman.
   .reveal punya opacity:0 di CSS, jadi kalau observer ini tidak pernah memicu,
   area konten terlihat BLANK (hanya header & footer). Karena itu:
   1) threshold 0 (bukan 0.1) + rootMargin kecil → memicu begitu 1px terlihat,
      termasuk pada elemen yang lebih tinggi daripada layar;
   2) jaring pengaman berbasis posisi: element yang sudah berada di layar
      langsung ditampilkan setelah 1,2 detik, dan juga saat halaman dipulihkan
      dari cache (pageshow). */
(function(){
  var els=document.querySelectorAll('.reveal');
  if(!els.length)return;
  function tampilkanYangSudahDilihat(){
    var tinggi=window.innerHeight||document.documentElement.clientHeight||0;
    for(var i=0;i<els.length;i++){
      var el=els[i];
      if(el.classList.contains('visible'))continue;
      // Tampilkan yang sudah masuk layar ATAU sudah dilewati scroll ke atas
      // (mis. posisi scroll dipulihkan saat halaman dimuat ulang). Yang di bawah
      // layar tetap menunggu animasi saat digulir.
      if(el.getBoundingClientRect().top<tinggi)el.classList.add('visible');
    }
  }
  if(!('IntersectionObserver' in window)){tampilkanYangSudahDilihat();return;}
  var obs=new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if(entry.isIntersecting){
        entry.target.classList.add('visible');
        obs.unobserve(entry.target);
      }
    });
  },{threshold:0,rootMargin:'0px 0px -10px 0px'});
  els.forEach(function(e){obs.observe(e);});
setTimeout(tampilkanYangSudahDilihat,1200);
window.addEventListener('pageshow',tampilkanYangSudahDilihat);
})();
</script>
</body>
</html>
