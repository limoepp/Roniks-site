<?php $s = settings(); ?>
<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Навигация"><a href="<?= e(url('/')) ?>">Главная</a><?= icon('chevron', 'ico ico--s') ?><span aria-current="page">Контакты</span></nav>
    <h1 class="page-title">Контакты</h1>
    <p class="page-lead"><?= e($s['city'] . ', ' . $s['address']) ?>. <?= e(hours_short()) ?>.</p>
  </div>
</div>
<?= view('partials/visit') ?>
<?php if (!empty($s['legal_name']) || !empty($s['inn'])): ?>
<section class="section section--white">
  <div class="wrap">
    <h2 class="section__title">Реквизиты</h2>
    <dl class="hours">
      <?php if (!empty($s['legal_name'])): ?><div><dt>Организация</dt><dd><?= e($s['legal_name']) ?></dd></div><?php endif; ?>
      <?php if (!empty($s['inn'])): ?><div><dt>ИНН</dt><dd><?= e($s['inn']) ?></dd></div><?php endif; ?>
      <?php if (!empty($s['ogrn'])): ?><div><dt>ОГРН</dt><dd><?= e($s['ogrn']) ?></dd></div><?php endif; ?>
      <?php if (!empty($s['legal_address'])): ?><div><dt>Юридический адрес</dt><dd><?= e($s['legal_address']) ?></dd></div><?php endif; ?>
    </dl>
  </div>
</section>
<?php endif; ?>
