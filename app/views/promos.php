<?php $promos = promos_live(); ?>
<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Навигация"><a href="<?= e(url('/')) ?>">Главная</a><?= icon('chevron', 'ico ico--s') ?><span aria-current="page">Акции</span></nav>
    <h1 class="page-title"><?= e(month_promos_title()) ?></h1>
    <p class="page-lead">Специальные цены этого месяца. Скажите администратору, что записываетесь по акции.</p>
  </div>
</div>
<div class="wrap section">
  <?php if ($promos): ?>
    <div class="promo-grid">
      <?php foreach ($promos as $p): ?><?= view('partials/promo', ['p' => $p, 'size' => 'full', 'htag' => 'h2']) ?><?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">
      <h2>Сейчас акций нет</h2>
      <p>Новые предложения появляются в начале месяца. Пока можно посмотреть цены на все услуги.</p>
      <p class="empty__actions"><a class="btn btn--ink" href="<?= e(url('/ceny/')) ?>">Открыть прайс</a></p>
    </div>
  <?php endif; ?>
</div>
