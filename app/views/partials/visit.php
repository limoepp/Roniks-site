<?php
$s = settings();
$withForm = $withForm ?? false;
$mapSrc = 'https://yandex.ru/map-widget/v1/?ll=' . rawurlencode($s['map_ll']) . '&z=16&mode=search&oid=' . rawurlencode($s['yandex_org_id']) . '&ol=biz';
?>
<section class="section visit" id="visit" aria-labelledby="visit-h">
  <div class="wrap visit__grid">
    <div class="visit__info">
      <h2 class="section__title" id="visit-h">Как нас найти</h2>
      <p class="visit__addr"><?= e($s['city'] . ', ' . $s['address']) ?></p>
      <p class="visit__note"><?= e($s['address_note']) ?></p>
      <a class="link" href="<?= e($s['map_link']) ?>" target="_blank" rel="noopener">Построить маршрут в Яндекс Картах</a>

      <h3 class="visit__h">Часы работы</h3>
      <dl class="hours">
        <?php foreach ($s['hours'] as $h): ?><div><dt><?= e($h['days']) ?></dt><dd><?= e($h['time']) ?></dd></div><?php endforeach; ?>
      </dl>

      <h3 class="visit__h">Телефоны</h3>
      <ul class="visit__phones">
        <?php foreach ($s['phones'] as $p): ?>
          <li><a href="<?= e(tel_href($p['number'])) ?>"><?= e($p['number']) ?></a><?php if (!empty($p['label'])): ?> <span><?= e($p['label']) ?></span><?php endif; ?></li>
        <?php endforeach; ?>
        <?php if (!empty($s['whatsapp'])): ?><li><a href="<?= e(wa_href()) ?>" target="_blank" rel="noopener"><?= e($s['whatsapp']) ?></a> <span>WhatsApp</span></li><?php endif; ?>
      </ul>
      <button class="btn btn--berry" type="button" data-book="">Записаться</button>
    </div>
    <div class="visit__map" data-map data-src="<?= e($mapSrc) ?>">
      <button class="visit__map-btn" type="button" data-map-load>
        <?= icon('pin') ?>
        <span>Показать карту</span>
        <small>Карта загрузится с Яндекса</small>
      </button>
    </div>
  </div>
</section>
