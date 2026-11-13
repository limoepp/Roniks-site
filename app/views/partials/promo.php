<?php
/** @var array $p  @var string $size  compact|full */
$size = $size ?? 'full';
$htag = $htag ?? 'h3';
$cat = !empty($p['category']) ? category_by_id($p['category']) : null;
$book = trim(($p['service'] ?? '') !== '' ? $p['service'] . ' — по акции' : $p['title']);
$unit = $p['price_unit'] ?? '';
?>
<article class="promo promo--<?= e($size) ?>">
  <?php if (!empty($p['image']) && $size === 'full'): ?>
    <img class="promo__img" src="<?= e(upload_url($p['image'])) ?>" alt="" loading="lazy">
  <?php endif; ?>
  <div class="promo__body">
    <?php if (!empty($p['badge'])): ?><span class="promo__badge"><?= e($p['badge']) ?></span><?php endif; ?>
    <<?= $htag ?> class="promo__title"><?= e($p['title']) ?></<?= $htag ?>>
    <?php if ($size === 'full' && !empty($p['text'])): ?><p class="promo__text"><?= e($p['text']) ?></p><?php endif; ?>
    <?php if (!empty($p['price_new'])): ?>
      <p class="promo__price">
        <span class="promo__new"><?= e(price_label($p['price_new'], false, $unit)) ?></span>
        <?php if (!empty($p['price_old'])): ?><s class="promo__old"><span class="sr">вместо </span><?= e(price_label($p['price_old'], false, $unit)) ?></s><?php endif; ?>
      </p>
    <?php endif; ?>
    <p class="promo__date"><?= icon('calendar', 'ico ico--s') ?><?= e(ucf(promo_period($p))) ?></p>
    <div class="promo__actions">
      <button class="btn btn--berry<?= $size === 'compact' ? ' btn--small' : '' ?>" type="button" data-book="<?= e($book) ?>"><?= e($p['button'] ?: 'Записаться по акции') ?></button>
      <?php if ($cat && $size === 'full'): ?><a class="link" href="<?= e(url('/uslugi/' . $cat['slug'] . '/')) ?>">Все цены: <?= e(mb_strtolower($cat['title'])) ?></a><?php endif; ?>
    </div>
  </div>
</article>
