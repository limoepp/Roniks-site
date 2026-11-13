<?php
$s = settings();
$cats = categories();
$promos = promos_live();
$masters = masters();
$chips = ['Стрижка', 'Окрашивание', 'Маникюр', 'Педикюр', 'Массаж', 'Брови'];
?>
<section class="hero">
  <div class="wrap hero__grid">
    <div class="hero__main">
      <h1 class="hero__title">Салон красоты на&nbsp;Университетском проспекте</h1>
      <p class="hero__lead">Стрижки, окрашивание, маникюр, педикюр и&nbsp;массаж с&nbsp;<?= e($s['since']) ?>&nbsp;года. Все цены&nbsp;— на сайте, запись&nbsp;— в&nbsp;пару касаний.</p>

      <form class="finder" action="<?= e(url('/ceny/')) ?>" method="get" role="search" data-finder>
        <label class="sr" for="finder-q">Найти услугу и цену</label>
        <div class="finder__box">
          <?= icon('search', 'ico finder__ico') ?>
          <input class="finder__input" id="finder-q" name="q" type="search" placeholder="Например, педикюр" autocomplete="off" aria-controls="finder-results" aria-describedby="finder-hint">
          <button class="btn btn--berry finder__btn" type="submit">Найти</button>
        </div>
        <p class="sr" id="finder-hint">Результаты появляются под полем по мере ввода</p>
        <div class="finder__chips" aria-label="Популярные запросы">
          <?php foreach ($chips as $c): ?><button class="chip chip--dark" type="button" data-chip="<?= e($c) ?>"><?= e($c) ?></button><?php endforeach; ?>
        </div>
        <div class="finder__results" id="finder-results" data-finder-results hidden></div>
      </form>

      <?php $st0 = open_status(); if ($st0['text']): ?>
        <p class="status<?= $st0['open'] ? ' is-open' : '' ?>"><?= e($st0['text']) ?></p>
      <?php endif; ?>
      <?php if (!empty($s['rating_value'])): ?>
        <a class="rating" href="<?= e($s['rating_link']) ?>" target="_blank" rel="noopener">
          <?= icon('star', 'ico rating__star') ?><b><?= e($s['rating_value']) ?></b>
          <span><?= e($s['rating_count']) ?> на Яндекс Картах<?php if (str_contains($s['rating_note'] ?? '', 'Хорошее место')): ?>, «Хорошее место 2026»<?php endif; ?></span>
        </a>
      <?php endif; ?>
    </div>

    <aside class="hero__side" aria-labelledby="promo-h">
      <?php if ($promos): ?>
        <div class="offer">
          <div class="offer__head">
            <h2 class="offer__title" id="promo-h"><?= e(month_promos_title()) ?></h2>
            <a class="link" href="<?= e(url('/akcii/')) ?>">Все акции</a>
          </div>
          <div class="offer__list">
            <?php foreach (array_slice($promos, 0, 3) as $p): ?>
              <?= view('partials/promo', ['p' => $p, 'size' => 'compact']) ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php else: ?>
        <div class="offer">
          <h2 class="offer__title" id="promo-h">Запишитесь удобным способом</h2>
          <p>Позвоните администратору или напишите в мессенджер — подберём мастера и время.</p>
          <a class="btn btn--ink" href="<?= e(tel_href(main_phone())) ?>"><?= icon('phone') ?><?= e(main_phone()) ?></a>
        </div>
      <?php endif; ?>
    </aside>
  </div>
  <script type="application/json" id="svc-index"><?= json_encode(services_index(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</section>

<section class="section dirs" aria-labelledby="dirs-h">
  <div class="wrap">
    <div class="section__head">
      <h2 class="section__title" id="dirs-h">Услуги и цены</h2>
      <a class="link" href="<?= e(url('/ceny/')) ?>">Весь прайс одной страницей</a>
    </div>
    <ul class="dirs__list">
      <?php foreach ($cats as $c): $st = category_stats($c); ?>
        <li>
          <a class="dir" href="<?= e(url('/uslugi/' . $c['slug'] . '/')) ?>">
            <?php if (!empty($c['image'])): ?>
              <img class="dir__img" src="<?= e(upload_url($c['image'])) ?>" alt="" loading="lazy">
            <?php else: ?>
              <span class="dir__ico"><?= icon($c['icon'] ?? 'spark') ?></span>
            <?php endif; ?>
            <span class="dir__text">
              <span class="dir__title"><?= e($c['title']) ?></span>
              <span class="dir__short"><?= e($c['short'] ?? '') ?></span>
            </span>
            <span class="dir__meta">
              <?php if ($st['min'] !== null): ?><span class="dir__price">от <?= e(money($st['min'])) ?></span><?php endif; ?>
              <span class="dir__count"><?= $st['count'] ?> <?= plural($st['count'], 'услуга', 'услуги', 'услуг') ?></span>
            </span>
            <?= icon('chevron', 'ico dir__arrow') ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="section section--white ways" aria-labelledby="ways-h">
  <div class="wrap">
    <h2 class="section__title" id="ways-h">Как записаться</h2>
    <div class="ways__grid">
      <div class="ways__item">
        <span class="ways__ico"><?= icon('phone') ?></span>
        <h3>Позвонить администратору</h3>
        <p><?= e(hours_short()) ?></p>
        <a class="ways__big" href="<?= e(tel_href(main_phone())) ?>"><?= e(main_phone()) ?></a>
      </div>
      <div class="ways__item">
        <span class="ways__ico"><?= icon('chat') ?></span>
        <h3>Написать в мессенджер</h3>
        <p>Ответим в рабочее время, можно прислать фото желаемого результата.</p>
        <div class="ways__links">
          <?php if (wa_href()): ?><a class="btn btn--ghost btn--small" href="<?= e(wa_href('Здравствуйте! Хочу записаться')) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?>WhatsApp</a><?php endif; ?>
          <?php if (tg_href()): ?><a class="btn btn--ghost btn--small" href="<?= e(tg_href()) ?>" target="_blank" rel="noopener"><?= icon('telegram') ?>Telegram</a><?php endif; ?>
        </div>
      </div>
      <div class="ways__item">
        <span class="ways__ico"><?= icon('calendar') ?></span>
        <h3><?= !empty($s['booking_url']) ? 'Выбрать время онлайн' : 'Оставить заявку' ?></h3>
        <p><?= !empty($s['booking_url']) ? 'Свободные окна мастеров — в онлайн-записи.' : 'Оставьте номер — перезвоним и подберём удобное время.' ?></p>
        <button class="btn btn--berry btn--small" type="button" data-book="">Записаться</button>
      </div>
    </div>
  </div>
</section>

<section class="section about-strip" aria-labelledby="about-h">
  <div class="wrap about-strip__grid">
    <div>
      <h2 class="section__title" id="about-h"><?= e($s['about_title']) ?></h2>
      <?php foreach (array_slice(preg_split('/\n\s*\n/', trim($s['about_text'])), 0, 2) as $para): ?>
        <p class="prose"><?= nl2br(e($para)) ?></p>
      <?php endforeach; ?>
      <a class="link" href="<?= e(url('/o-salone/')) ?>">Подробнее о салоне</a>
    </div>
    <div>
      <dl class="facts">
        <?php foreach ($s['facts'] ?? [] as $f): ?>
          <div class="facts__item"><dt><?= e($f['value']) ?></dt><dd><?= e($f['label']) ?></dd></div>
        <?php endforeach; ?>
      </dl>
      <?php if (!empty($s['brands'])): ?>
        <p class="brands"><span class="brands__label">Косметика, с которой работаем:</span> <?= e(implode(', ', $s['brands'])) ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ($masters): ?>
<section class="section section--white" aria-labelledby="team-h">
  <div class="wrap">
    <div class="section__head">
      <h2 class="section__title" id="team-h">Мастера</h2>
      <a class="link" href="<?= e(url('/o-salone/#team')) ?>">Все мастера</a>
    </div>
    <?= view('partials/masters', ['masters' => array_slice($masters, 0, 4)]) ?>
  </div>
</section>
<?php endif; ?>

<?= view('partials/visit') ?>
