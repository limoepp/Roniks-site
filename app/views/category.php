<?php
/** @var array $cat */
$promos = promos_live($cat['id']);
$st = category_stats($cat);
?>
<div class="page-head page-head--cat">
  <div class="wrap page-head__grid">
    <div>
      <nav class="crumbs" aria-label="Навигация"><a href="<?= e(url('/')) ?>">Главная</a><?= icon('chevron', 'ico ico--s') ?><a href="<?= e(url('/uslugi/')) ?>">Услуги</a><?= icon('chevron', 'ico ico--s') ?><span aria-current="page"><?= e($cat['title']) ?></span></nav>
      <h1 class="page-title"><?= e($cat['title']) ?></h1>
      <p class="page-lead"><?= e($cat['description'] ?? $cat['short'] ?? '') ?></p>
      <div class="page-head__actions">
        <button class="btn btn--berry" type="button" data-book="<?= e($cat['title']) ?>">Записаться</button>
        <?php if ($st['min'] !== null): ?><span class="page-head__from">Цены от <?= e(money($st['min'])) ?></span><?php endif; ?>
      </div>
    </div>
    <?php if (!empty($cat['image'])): ?>
      <img class="page-head__img" src="<?= e(upload_url($cat['image'])) ?>" alt="<?= e($cat['title']) ?>">
    <?php endif; ?>
  </div>
</div>

<?php if (count($cat['groups']) > 1): ?>
<nav class="subnav" aria-label="Разделы">
  <div class="wrap subnav__in">
    <?php foreach ($cat['groups'] as $g): ?>
      <a class="chip" href="#<?= e($g['id']) ?>"><?= e($g['title']) ?></a>
    <?php endforeach; ?>
  </div>
</nav>
<?php endif; ?>

<div class="wrap cat-layout">
  <div class="prices prices--cat">
    <?= view('partials/pricelist', ['cat' => $cat]) ?>
    <p class="prices__disclaimer">Итоговую стоимость мастер называет после консультации.</p>
  </div>
  <?php if ($promos): ?>
    <aside class="cat-aside" aria-label="Акции">
      <h2 class="cat-aside__title"><?= e(month_promos_title()) ?></h2>
      <?php foreach ($promos as $p): ?><?= view('partials/promo', ['p' => $p, 'size' => 'compact']) ?><?php endforeach; ?>
    </aside>
  <?php endif; ?>
</div>

<?php
$others = array_filter(categories(), fn($c) => $c['id'] !== $cat['id']);
if ($others): ?>
<section class="section section--white" aria-labelledby="more-h">
  <div class="wrap">
    <h2 class="section__title" id="more-h">Другие направления</h2>
    <ul class="dirs__list dirs__list--compact">
      <?php foreach ($others as $c): $s2 = category_stats($c); ?>
        <li><a class="dir" href="<?= e(url('/uslugi/' . $c['slug'] . '/')) ?>"><span class="dir__ico"><?= icon($c['icon'] ?? 'spark') ?></span><span class="dir__text"><span class="dir__title"><?= e($c['title']) ?></span></span><span class="dir__meta"><?php if ($s2['min'] !== null): ?><span class="dir__price">от <?= e(money($s2['min'])) ?></span><?php endif; ?></span><?= icon('chevron', 'ico dir__arrow') ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>
