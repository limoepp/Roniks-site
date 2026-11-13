<?php
/** @var string $content */
$s = settings();
$cats = categories();
$route = $route ?? '';
$pageTitle = ($route === '/' || empty($title)) ? $s['seo_title'] : $title . ' — ' . $s['full_name'];
$desc = $description ?? $s['seo_description'];
$nav = [
    '/uslugi/' => 'Услуги',
    '/ceny/' => 'Цены',
    '/akcii/' => 'Акции',
    '/o-salone/' => 'О салоне',
    '/o-salone/#gallery' => 'Фото салона',
    '/kontakty/' => 'Контакты',
];
$livePromos = count(promos_live());
$ts = time();
$formTs = $ts . '.' . hash_hmac('sha256', (string) $ts, app_secret());
$phone = main_phone();
$wa = wa_href('Здравствуйте! Хочу записаться');
$tg = tg_href();
?><!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="ru_RU">
<meta name="theme-color" content="#ffffff">
<link rel="icon" href="<?= e(asset('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="preload" href="<?= e(url('/assets/fonts/montserrat-cyrillic.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('assets/css/site.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/keri.css')) ?>">
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BeautySalon',
    'name' => $s['full_name'],
    'telephone' => '+' . digits($phone),
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => $s['address'], 'addressLocality' => $s['city'], 'addressCountry' => 'RU'],
    'url' => site_origin() . url('/'),
    'foundingDate' => (string) $s['since'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
</head>
<body class="keri-theme <?= $route === '/' ? 'is-home' : '' ?>">
<a class="skip" href="#main">Перейти к содержанию</a>

<header class="site-header">
  <div class="wrap identity-bar">
    <a class="logo" href="<?= e(url('/')) ?>" aria-label="<?= e($s['full_name']) ?> — на главную"><span class="logo__sub">САЛОН КРАСОТЫ</span><span class="logo__word"><?= e($s['name']) ?></span></a>
    <div class="header-address"><span><?= e($s['city'] . ', ' . $s['address']) ?></span><a href="<?= e($s['map_link']) ?>" target="_blank" rel="noopener">Посмотреть на карте</a></div>
    <div class="header-hours"><span><?= e(hours_short()) ?></span><small>Ждём вас в салоне</small></div>
    <div class="header-phone"><a href="<?= e(tel_href($phone)) ?>"><?= e($phone) ?></a><button type="button" data-book="">Заказать звонок</button></div>
    <div class="header-social">
      <?php if ($tg): ?><a href="<?= e($tg) ?>" target="_blank" rel="noopener" aria-label="Написать в Telegram"><?= icon('telegram') ?></a><?php endif; ?>
      <?php if ($wa): ?><a href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="Написать в WhatsApp"><?= icon('whatsapp') ?></a><?php endif; ?>
    </div>
    <button class="btn header-book" type="button" data-book="">Запись в салон <span aria-hidden="true">→</span></button>
  </div>
  <div class="mainbar">
    <div class="wrap mainbar__in">
      <a class="logo mobile-logo" href="<?= e(url('/')) ?>" aria-label="Роникс — на главную"><span class="logo__sub">САЛОН КРАСОТЫ</span><span class="logo__word"><?= e($s['name']) ?></span></a>
      <nav class="nav" id="site-nav" aria-label="Основное меню">
        <ul class="nav__list">
          <li class="nav__item nav__item--mega">
            <a class="nav__link<?= $route === '/uslugi/' ? ' is-current' : '' ?>" href="<?= e(url('/uslugi/')) ?>" aria-haspopup="true">Услуги <?= icon('down', 'ico ico--s') ?></a>
            <div class="mega" role="group" aria-label="Направления">
              <div class="mega__in">
                <?php foreach ($cats as $c): ?>
                  <div class="mega__col">
                    <a class="mega__title" href="<?= e(url('/uslugi/' . $c['slug'] . '/')) ?>"><?= icon($c['icon'] ?? 'spark') ?><?= e($c['title']) ?></a>
                    <?php if (count($c['groups']) > 1): ?>
                    <ul>
                      <?php foreach ($c['groups'] as $g): if (!empty($g['extra'])) continue; ?>
                        <li><a href="<?= e(url('/uslugi/' . $c['slug'] . '/#' . $g['id'])) ?>"><?= e($g['title']) ?></a></li>
                      <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </li>
          <?php foreach (array_slice($nav, 1, null, true) as $href => $label): ?>
            <li class="nav__item"><a class="nav__link<?= $route === $href ? ' is-current' : '' ?>" href="<?= e(url($href)) ?>"<?= $route === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?><?php if ($href === '/akcii/' && $livePromos): ?><span class="nav__count"><?= $livePromos ?></span><?php endif; ?></a></li>
          <?php endforeach; ?>
        </ul>
        <div class="nav__mobile-extra">
          <a href="<?= e(tel_href($phone)) ?>" class="nav__phone"><?= icon('phone') ?><?= e($phone) ?></a>
          <p><?= e(hours_short()) ?></p>
        </div>
      </nav>
      <a class="mainbar__search" href="<?= e(url('/ceny/')) ?>" aria-label="Найти услугу в прайсе"><?= icon('search') ?></a>

      <button class="burger" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Открыть меню"><?= icon('menu') ?></button>
    </div>
  </div>
</header>

<main id="main">
<?= $content ?>
</main>

<footer class="site-footer">
  <div class="wrap footer__grid">
    <div class="footer__brand">
      <a class="logo logo--light" href="<?= e(url('/')) ?>"><span class="logo__word"><?= e($s['name']) ?></span><span class="logo__sub">салон красоты с <?= e($s['since']) ?> года</span></a>
      <p class="footer__addr"><?= e($s['city'] . ', ' . $s['address']) ?><br><?= e($s['address_note']) ?></p>
      <?php foreach ($s['hours'] as $h): ?><p class="footer__hours"><span><?= e($h['days']) ?></span> <?= e($h['time']) ?></p><?php endforeach; ?>
    </div>
    <nav class="footer__col" aria-label="Услуги">
      <h2 class="footer__h">Услуги</h2>
      <ul>
        <?php foreach ($cats as $c): ?><li><a href="<?= e(url('/uslugi/' . $c['slug'] . '/')) ?>"><?= e($c['title']) ?></a></li><?php endforeach; ?>
      </ul>
    </nav>
    <nav class="footer__col" aria-label="Салон">
      <h2 class="footer__h">Салон</h2>
      <ul>
        <li><a href="<?= e(url('/ceny/')) ?>">Цены</a></li>
        <li><a href="<?= e(url('/akcii/')) ?>">Акции</a></li>
        <li><a href="<?= e(url('/o-salone/')) ?>">О салоне</a></li>
        <li><a href="<?= e(url('/o-salone/#gallery')) ?>">Фото салона</a></li>
        <li><a href="<?= e(url('/kontakty/')) ?>">Контакты</a></li>
        <?php foreach ($s['sister_links'] ?? [] as $l): if (empty($l['url'])) continue; ?>
          <li><a href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><?= e($l['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="footer__col">
      <h2 class="footer__h">Запись</h2>
      <ul class="footer__phones">
        <?php foreach ($s['phones'] as $p): ?><li><a href="<?= e(tel_href($p['number'])) ?>"><?= e($p['number']) ?></a></li><?php endforeach; ?>
      </ul>
      <div class="footer__msg">
        <?php if ($wa): ?><a href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?>WhatsApp</a><?php endif; ?>
        <?php if ($tg): ?><a href="<?= e($tg) ?>" target="_blank" rel="noopener"><?= icon('telegram') ?>Telegram</a><?php endif; ?>
        <?php if (!empty($s['vk'])): ?><a href="<?= e($s['vk']) ?>" target="_blank" rel="noopener"><?= icon('vk') ?>ВКонтакте</a><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="wrap footer__legal">
    <p>
      © <?= e($s['since']) ?>–<?= date('Y') ?> <?= e($s['full_name']) ?>
      <?php if (!empty($s['legal_name'])): ?> · <?= e($s['legal_name']) ?><?php endif; ?>
      <?php if (!empty($s['inn'])): ?> · ИНН <?= e($s['inn']) ?><?php endif; ?>
      <?php if (!empty($s['ogrn'])): ?> · ОГРН <?= e($s['ogrn']) ?><?php endif; ?>
    </p>
    <?php if (!empty($s['license'])): ?><p><?= e($s['license']) ?></p><?php endif; ?>
    <p>Цены на сайте не являются публичной офертой. Окончательную стоимость мастер называет после консультации. <a href="<?= e(url('/politika-konfidencialnosti/')) ?>">Политика обработки персональных данных</a></p>
  </div>
</footer>

<div class="dock" aria-label="Быстрая связь">
  <a class="dock__btn" href="<?= e(tel_href($phone)) ?>"><?= icon('phone') ?><span>Позвонить</span></a>
  <?php if ($wa): ?><a class="dock__btn" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?><span>WhatsApp</span></a><?php endif; ?>
  <button class="dock__btn dock__btn--main" type="button" data-book=""><?= icon('calendar') ?><span>Записаться</span></button>
</div>

<?= view('partials/booking', ['formTs' => $formTs]) ?>

<div class="cookie" id="cookie" hidden>
  <p>Сайт использует файлы cookie, чтобы карта и формы работали корректно. <a href="<?= e(url('/politika-konfidencialnosti/')) ?>">Подробнее</a></p>
  <button class="btn btn--small btn--ink" type="button" id="cookie-ok">Понятно</button>
</div>

<script src="<?= e(asset('assets/js/site.js')) ?>" defer></script>
<script src="<?= e(asset('assets/js/keri.js')) ?>" defer></script>
</body>
</html>
