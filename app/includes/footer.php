<footer class="footer">
  <div class="footer-inner">
    <span class="footer-brand"><span class="brand-dot"></span> Portal Komunitas Robotik · SMA Negeri 2</span>
    <span>&copy; <?= date('Y') ?> Komunitas Robotik Smandu</span>
  </div>
</footer>
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

/* Scroll reveal */
(function(){
  var els=document.querySelectorAll('.reveal');
  if(!els.length)return;
  if(!('IntersectionObserver' in window)){els.forEach(function(e){e.classList.add('visible');});return;}
  var obs=new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if(entry.isIntersecting){
        entry.target.classList.add('visible');
        obs.unobserve(entry.target);
      }
    });
  },{threshold:0.1,rootMargin:'0px 0px -40px 0px'});
  els.forEach(function(e){obs.observe(e);});
})();
</script>
</body>
</html>
