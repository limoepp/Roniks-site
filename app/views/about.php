<?php
$s = settings();
$masters = masters();
$photos = gallery();
?>
<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Навигация"><a href="<?= e(url('/')) ?>">Главная</a><?= icon('chevron', 'ico ico--s') ?><span aria-current="page">О салоне</span></nav>
    <h1 class="page-title"><?= e($s['full_name']) ?></h1>
    <p class="page-lead"><?= e($s['tagline']) ?></p>
  </div>
</div>

<section class="section">
  <div class="wrap about-strip__grid">
    <div>
      <?php foreach (preg_split('/\n\s*\n/', trim($s['about_text'])) as $para): ?>
        <p class="prose"><?= nl2br(e($para)) ?></p>
      <?php endforeach; ?>
    </div>
    <div>
      <dl class="facts">
        <?php foreach ($s['facts'] ?? [] as $f): ?><div class="facts__item"><dt><?= e($f['value']) ?></dt><dd><?= e($f['label']) ?></dd></div><?php endforeach; ?>
      </dl>
      <?php if (!empty($s['brands'])): ?><p class="brands"><span class="brands__label">Косметика, с которой работаем:</span> <?= e(implode(', ', $s['brands'])) ?></p><?php endif; ?>
      <?php if (!empty($s['rating_value'])): ?>
        <a class="rating rating--light" href="<?= e($s['rating_link']) ?>" target="_blank" rel="noopener"><?= icon('star', 'ico rating__star') ?><b><?= e($s['rating_value']) ?></b><span><?= e($s['rating_count']) ?> на Яндекс Картах — читать отзывы</span></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ($masters): ?>
<section class="section section--white" id="team" aria-labelledby="team-h">
  <div class="wrap">
    <h2 class="section__title" id="team-h">Мастера</h2>
    <?= view('partials/masters', ['masters' => $masters]) ?>
  </div>
</section>
<?php endif; ?>

<section class="section interior-section" id="gallery" aria-labelledby="gal-h">
  <div class="wrap">
    <div class="section__head"><h2 class="section__title" id="gal-h">Пространство салона</h2></div>
    <p class="section-intro">Зона встречи гостей, рабочие места и детали интерьера — знакомимся до первого визита.</p>
    <?php if ($photos): ?>
    <ul class="gallery"><?php foreach ($photos as $ph): ?><li><img src="<?= e(upload_url($ph['file'])) ?>" alt="<?= e($ph['alt'] ?? 'Интерьер салона') ?>" loading="lazy"></li><?php endforeach; ?></ul>
    <?php else: ?>
    <div class="interior-grid"><?php foreach (['Зона встречи гостей', 'Парикмахерский зал', 'Зона маникюра'] as $i => $label): ?><?= view('partials/salon-photo', ['index' => $i, 'label' => $label]) ?><?php endforeach; ?></div>
    <?php endif; ?>
  </div>
</section>

<?= view('partials/visit') ?>
