<?php
/* Kartu demo foto: mengisi slot pada carousel saat item nyata belum mencapai
   tiga buah. Berupa panel ikon gambar abu-abu, tanpa teks. Variabel dari
   pemanggil: $demoBungkus (opsional — true bila kartu butuh pembungkus
   card-wrap seperti karya/galeri). */
?>
<?php if (!empty($demoBungkus)): ?><div class="card-wrap"><?php endif; ?>
<div class="work-card demo-card">
  <span class="work-thumb demo-thumb" aria-hidden="true"><i class="bi bi-image"></i></span>
  <div class="work-body"></div>
</div>
<?php if (!empty($demoBungkus)): ?></div><?php endif; ?>
