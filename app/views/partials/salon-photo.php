<?php
/** Public gallery images are shared with the existing admin gallery. */
$photos = gallery();
$index = $index ?? 0;
$label = $label ?? 'Интерьер салона';
$photo = $photos[$index] ?? null;
?>
<figure class="salon-photo<?= $photo ? '' : ' salon-photo--placeholder' ?>">
  <?php if ($photo): ?>
    <img src="<?= e(upload_url($photo['file'])) ?>" alt="<?= e(($photo['alt'] ?? '') ?: $label) ?>" loading="<?= !empty($priority) ? 'eager' : 'lazy' ?>">
  <?php else: ?>
    <svg class="salon-photo__drawing" viewBox="0 0 600 420" fill="none" aria-hidden="true">
      <path d="M60 350V70h480v280M60 315h480M175 70v245M425 70v245M60 120h115M425 120h115"/>
      <rect x="225" y="102" width="150" height="170" rx="75"/>
      <path d="M240 117c28-12 57-12 87-3M238 284h126M252 284v42M350 284v42M266 239h70v44h-70zM298 284v51m-25 0h50M107 315v-63m0 20c-32 0-35-36-35-36s35 0 35 36zm0-17c30 0 34-34 34-34s-34 0-34 34zM469 315v-64m-17 64h35"/>
    </svg>
    <figcaption><span>Фото салона</span><strong><?= e($label) ?></strong><small>Место для настоящей фотографии «Роникса»</small></figcaption>
  <?php endif; ?>
</figure>
