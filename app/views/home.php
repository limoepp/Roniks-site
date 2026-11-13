<?php
$s = settings();
$cats = categories();
$promos = promos_live();
$masters = masters();
$chips = ['Стрижка', 'Окрашивание', 'Маникюр', 'Педикюр', 'Массаж'];
?>
<section class="feature-banner" aria-label="Салон и предложения" data-banner>
  <div class="feature-slide" data-slide>
    <div class="feature-copy">
      <p class="eyebrow">Красота начинается с заботы о себе</p>
      <h1>Салон красоты<br>«Роникс»</h1>
      <p class="feature-lead">Ваше место красоты на Университетском проспекте. Стрижки, окрашивание, маникюр и уход с <?= e($s['since']) ?> года.</p>
      <button class="btn btn--berry" type="button" data-book="">Записаться в салон <span aria-hidden="true">→</span></button>
    </div>
    <?= view('partials/salon-photo', ['index' => 0, 'label' => 'Первое знакомство с салоном', 'priority' => true]) ?>
  </div>
  <?php foreach (array_slice($promos, 0, 3) as $p): ?>
  <div class="feature-slide" data-slide hidden>
    <div class="feature-copy">
      <p class="eyebrow"><?= e(month_promos_title()) ?></p>
      <h2><?= e($p['title']) ?></h2>
      <p class="feature-lead"><?= e($p['text'] ?? '') ?></p>
      <a class="btn btn--berry" href="<?= e(url('/akcii/')) ?>">Посмотреть условия <span aria-hidden="true">→</span></a>
    </div>
    <?php if (!empty($p['image'])): ?><figure class="salon-photo"><img src="<?= e(upload_url($p['image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy"></figure><?php else: ?><?= view('partials/salon-photo', ['index' => 1, 'label' => 'Фото услуги для баннера']) ?><?php endif; ?>
  </div>
  <?php endforeach; ?>
  <div class="banner-controls">
    <div class="banner-dots" role="group" aria-label="Выбор баннера">
      <?php for ($i = 0; $i <= min(3, count($promos)); $i++): ?><button type="button" data-slide-to="<?= $i ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Баннер <?= $i + 1 ?>"></button><?php endfor; ?>
    </div>
    <div class="banner-arrows"><button type="button" data-slide-prev aria-label="Предыдущий баннер">←</button><button type="button" data-slide-next aria-label="Следующий баннер">→</button></div>
  </div>
</section>

<section class="section keri-directions" aria-labelledby="dirs-h">
  <div class="wrap">
    <div class="section__head"><h2 class="section__title" id="dirs-h">Наши направления</h2><a class="link" href="<?= e(url('/ceny/')) ?>">Все услуги и цены →</a></div>
    <ul class="direction-grid">
      <?php foreach ($cats as $i => $c): $st = category_stats($c); ?>
      <li><a class="direction-card direction-card--<?= $i % 4 ?>" href="<?= e(url('/uslugi/' . $c['slug'] . '/')) ?>">
        <?php if (!empty($c['image'])): ?><img src="<?= e(upload_url($c['image'])) ?>" alt="" loading="lazy"><?php else: ?><span class="direction-card__art" aria-hidden="true"><?= icon($c['icon'] ?? 'spark') ?></span><span class="direction-card__photo">Фото направления</span><?php endif; ?>
        <span class="direction-card__top">Подробнее <span aria-hidden="true">↗</span></span>
        <span class="direction-card__bottom"><span class="direction-card__title"><?= e($c['title']) ?></span><span class="direction-card__price"><?php if ($st['min'] !== null): ?>от <?= e(money($st['min'])) ?><?php endif; ?></span></span>
      </a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="section salon-intro" aria-labelledby="about-h">
  <div class="wrap salon-intro__grid">
    <?= view('partials/salon-photo', ['index' => 1, 'label' => 'Интерьер и атмосфера']) ?>
    <div>
      <p class="eyebrow">Знакомство с «Рониксом»</p>
      <h2 class="section__title" id="about-h">Место, куда приятно возвращаться</h2>
      <?php foreach (array_slice(preg_split('/\n\s*\n/', trim($s['about_text'])), 0, 2) as $para): ?><p class="prose"><?= nl2br(e($para)) ?></p><?php endforeach; ?>
      <a class="link" href="<?= e(url('/o-salone/')) ?>">Подробнее о салоне →</a>
    </div>
  </div>
</section>

<section class="section interior-section" aria-labelledby="interior-h">
  <div class="wrap">
    <div class="section__head"><h2 class="section__title" id="interior-h">Пространство салона</h2><a class="link" href="<?= e(url('/o-salone/#gallery')) ?>">Посмотреть салон →</a></div>
    <p class="section-intro">Знакомимся ещё до первого визита: интерьер, рабочие места и детали атмосферы.</p>
    <div class="interior-grid">
      <?php foreach (['Зона встречи гостей', 'Парикмахерский зал', 'Зона маникюра'] as $i => $label): ?><?= view('partials/salon-photo', ['index' => $i, 'label' => $label]) ?><?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($promos): ?>
<section class="section" aria-labelledby="promos-h"><div class="wrap">
  <div class="section__head"><h2 class="section__title" id="promos-h"><?= e(month_promos_title()) ?></h2><a class="link" href="<?= e(url('/akcii/')) ?>">Все предложения →</a></div>
  <div class="promo-grid"><?php foreach (array_slice($promos, 0, 3) as $p): ?><?= view('partials/promo', ['p' => $p]) ?><?php endforeach; ?></div>
</div></section>
<?php endif; ?>

<section class="section quick-search" aria-labelledby="search-h"><div class="wrap">
  <div class="section__head"><h2 class="section__title" id="search-h">Найдите свою услугу</h2><a class="link" href="<?= e(url('/ceny/')) ?>">Открыть прайс →</a></div>
  <form class="finder" action="<?= e(url('/ceny/')) ?>" method="get" role="search" data-finder>
    <label class="sr" for="finder-q">Найти услугу и цену</label>
    <div class="finder__box"><?= icon('search', 'ico finder__ico') ?><input class="finder__input" id="finder-q" name="q" type="search" placeholder="Поиск по всем услугам…" autocomplete="off" aria-controls="finder-results" aria-describedby="finder-hint"><button class="btn btn--berry finder__btn" type="submit">Найти</button></div>
    <p class="sr" id="finder-hint">Результаты появляются под полем по мере ввода</p>
    <div class="finder__chips" aria-label="Популярные запросы"><?php foreach ($chips as $chip): ?><button class="chip" type="button" data-chip="<?= e($chip) ?>"><?= e($chip) ?></button><?php endforeach; ?></div>
    <div class="finder__results" id="finder-results" data-finder-results hidden></div>
  </form>
  <script type="application/json" id="svc-index"><?= json_encode(services_index(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</div></section>

<section class="section booking-strip"><div class="wrap booking-strip__in"><div><h2 class="section__title">Время для себя</h2><p>Выберите услугу — поможем подобрать мастера и удобное время.</p></div><button class="btn btn--berry" type="button" data-book="">Записаться в салон →</button></div></section>
<?php if ($masters): ?><section class="section"><div class="wrap"><div class="section__head"><h2 class="section__title">Мастера салона</h2><a class="link" href="<?= e(url('/o-salone/#team')) ?>">Вся команда →</a></div><?= view('partials/masters', ['masters' => array_slice($masters, 0, 4)]) ?></div></section><?php endif; ?>
<?= view('partials/visit') ?>
