<?php $cats = categories(); ?>
<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Навигация"><a href="<?= e(url('/')) ?>">Главная</a><?= icon('chevron', 'ico ico--s') ?><span aria-current="page">Услуги</span></nav>
    <h1 class="page-title">Услуги салона</h1>
    <p class="page-lead">Выберите направление, чтобы посмотреть услуги и цены, или найдите услугу по названию в <a href="<?= e(url('/ceny/')) ?>">общем прайсе</a>.</p>
  </div>
</div>
<div class="wrap section svc">
  <?php foreach ($cats as $c): $st = category_stats($c); ?>
    <section class="svc__cat" aria-labelledby="svc-<?= e($c['id']) ?>">
      <div class="svc__head">
        <span class="dir__ico"><?= icon($c['icon'] ?? 'spark') ?></span>
        <div>
          <h2 class="svc__title" id="svc-<?= e($c['id']) ?>"><a href="<?= e(url('/uslugi/' . $c['slug'] . '/')) ?>"><?= e($c['title']) ?></a></h2>
          <p class="svc__short"><?= e($c['short'] ?? '') ?><?php if ($st['min'] !== null): ?>. Цены от <?= e(money($st['min'])) ?><?php endif; ?></p>
        </div>
      </div>
      <ul class="svc__groups">
        <?php foreach ($c['groups'] as $g): ?>
          <li><a href="<?= e(url('/uslugi/' . $c['slug'] . '/#' . $g['id'])) ?>"><?= e($g['title']) ?></a> <span><?= count($g['items']) ?></span></li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endforeach; ?>
</div>
