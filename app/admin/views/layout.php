<?php
$bare = $bare ?? false;
$nav = $nav ?? '';
$f = flash();
$newLeads = count(array_filter(json_read('leads', ['items' => []])['items'], fn($l) => ($l['status'] ?? '') === 'new'));
$items = [
    'prices' => ['Цены и услуги', '/prices'],
    'promos' => ['Акции', '/promos'],
    'leads' => ['Заявки', '/leads'],
    'settings' => ['Контакты и настройки', '/settings'],
    'masters' => ['Мастера', '/masters'],
    'gallery' => ['Фото салона', '/gallery'],
    'backups' => ['Резервные копии', '/backups'],
    'password' => ['Пароль', '/password'],
];
?><!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e(($title ?? 'Вход') . ' — управление сайтом «' . settings()['name'] . '»') ?></title>
<link rel="icon" href="<?= e(asset('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('assets/css/site.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="adm<?= $bare ? ' adm--bare' : '' ?>">
<?php if (!$bare): ?>
<header class="adm-top">
  <a class="adm-top__brand" href="<?= e(admin_url('/prices')) ?>"><span class="logo__word"><?= e(settings()['name']) ?></span><span>управление сайтом</span></a>
  <button class="adm-top__menu burger" type="button" aria-expanded="false" aria-controls="adm-nav" data-adm-burger aria-label="Меню"><?= icon('menu') ?></button>
  <div class="adm-top__right">
    <a class="btn btn--ghost btn--small" href="<?= e(url('/')) ?>" target="_blank" rel="noopener">Открыть сайт</a>
    <form method="post" action="<?= e(admin_url('/logout')) ?>"><?= csrf_field() ?><button class="btn btn--small adm-link" type="submit">Выйти</button></form>
  </div>
</header>
<div class="adm-shell">
  <nav class="adm-nav" id="adm-nav" aria-label="Разделы управления">
    <ul>
      <?php foreach ($items as $key => [$label, $href]): ?>
        <li><a href="<?= e(admin_url($href)) ?>"<?= $nav === $key ? ' aria-current="page" class="is-current"' : '' ?>><?= e($label) ?><?php if ($key === 'leads' && $newLeads): ?><span class="nav__count"><?= $newLeads ?></span><?php endif; ?></a></li>
      <?php endforeach; ?>
    </ul>
    <p class="adm-nav__help">Все изменения сохраняются в резервные копии — любую правку можно отменить.</p>
  </nav>
  <main class="adm-main" id="main">
    <?php if ($f): ?><div class="adm-flash adm-flash--<?= e($f['type']) ?>" role="status"><?= e($f['msg']) ?></div><?php endif; ?>
    <?= $content ?>
  </main>
</div>
<?php else: ?>
<main class="adm-bare" id="main"><?= $content ?></main>
<?php endif; ?>
<script src="<?= e(asset('assets/js/admin.js')) ?>" defer></script>
</body>
</html>
