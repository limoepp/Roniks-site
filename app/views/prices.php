<?php
$cats = categories();
$q = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 60);
$updated = json_read('services')['updated_at'] ?? '';
?>
<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Навигация"><a href="<?= e(url('/')) ?>">Главная</a><?= icon('chevron', 'ico ico--s') ?><span aria-current="page">Цены</span></nav>
    <h1 class="page-title">Цены</h1>
    <p class="page-lead">Все услуги салона на одной странице. Начните вводить название — список отфильтруется.<?php if ($updated): ?> Цены обновлены <?= e(date_human(substr($updated, 0, 10))) ?>.<?php endif; ?></p>
  </div>
</div>

<div class="wrap price-layout">
  <aside class="price-sidebar" aria-label="Направления услуг">
    <h2 class="price-sidebar__title">Направления</h2>
    <div class="chips price-sidebar__filters" role="group" aria-label="Фильтр направлений">
      <button class="chip is-active" type="button" data-filter="" aria-pressed="true">Все услуги</button>
      <?php foreach ($cats as $c): ?>
        <button class="chip" type="button" data-filter="<?= e($c['id']) ?>" aria-pressed="false"><?= e($c['title']) ?></button>
      <?php endforeach; ?>
    </div>
    <p class="price-sidebar__help">Нужна помощь с выбором?</p>
    <button class="link link--btn" type="button" data-book="">Связаться с салоном</button>
  </aside>
  <div class="price-content">
  <div class="pricebar" data-pricebar>
    <div class="pricebar__in">
    <div class="search">
      <?= icon('search', 'ico search__ico') ?>
      <label class="sr" for="price-q">Поиск по прайсу</label>
      <input class="search__input" id="price-q" type="search" placeholder="Например: педикюр, мелирование, массаж спины" value="<?= e($q) ?>" autocomplete="off" data-price-search>
      <button class="search__clear" type="button" data-search-clear aria-label="Очистить поиск" hidden><?= icon('close') ?></button>
    </div>
    </div>
  </div>

<div class="prices" data-prices>
  <p class="prices__count" data-count role="status" aria-live="polite"></p>
  <?php foreach ($cats as $c): ?>
    <?= view('partials/pricelist', ['cat' => $c, 'showCatTitle' => true]) ?>
  <?php endforeach; ?>
  <div class="empty" data-empty hidden>
    <h2>Не нашли такую услугу</h2>
    <p>Возможно, она называется иначе. Позвоните администратору — подскажем, есть ли она у нас и сколько стоит.</p>
    <p class="empty__actions">
      <a class="btn btn--ink" href="<?= e(tel_href(main_phone())) ?>"><?= icon('phone') ?><?= e(main_phone()) ?></a>
      <button class="btn btn--ghost" type="button" data-search-clear>Показать все услуги</button>
    </p>
  </div>
  <p class="prices__disclaimer">Цены указаны в рублях. Итоговую стоимость мастер называет после консультации: она зависит от длины и густоты волос и объёма работы.</p>
</div>
  </div>
</div>
