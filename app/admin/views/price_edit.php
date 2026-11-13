<?php
/** @var array $cat */
$fmt = static fn($v) => $v === null || $v === '' ? '' : (string) $v;

$itemHtml = static function (array $it) use ($fmt): string {
    $vars = $it['variants'] ?? [];
    ob_start(); ?>
    <li class="it<?= !empty($it['hidden']) ? ' is-hidden' : '' ?>" data-item data-id="<?= e($it['id'] ?? '') ?>" id="<?= e($it['id'] ?? '') ?>">
      <div class="it__main">
        <input class="it__name" type="text" data-f="name" value="<?= e($it['name'] ?? '') ?>" placeholder="Название услуги" aria-label="Название услуги" maxlength="200">
        <label class="it__price" data-single<?= $vars ? ' hidden' : '' ?>><span class="sr">Цена</span><input type="text" inputmode="decimal" data-f="price" data-price value="<?= e($fmt($it['price'] ?? null)) ?>" placeholder="0"><span>₽</span></label>
        <span class="it__varhint" data-varhint<?= $vars ? '' : ' hidden' ?>>цены в вариантах ↓</span>
        <span class="it__btns">
          <button class="icon-btn" type="button" data-act="more" aria-expanded="false" title="Примечание, длительность, «от», скрыть">⋯</button>
          <button class="icon-btn" type="button" data-act="up" title="Выше" aria-label="Переместить выше">↑</button>
          <button class="icon-btn" type="button" data-act="down" title="Ниже" aria-label="Переместить ниже">↓</button>
          <button class="icon-btn icon-btn--danger" type="button" data-act="del" title="Удалить услугу" aria-label="Удалить услугу">✕</button>
        </span>
      </div>
      <div class="it__vars" data-vars>
        <?php foreach ($vars as $v): ?>
          <div class="var" data-var>
            <input type="text" data-f="label" value="<?= e($v['label'] ?? '') ?>" placeholder="Например, короткие" aria-label="Вариант" maxlength="80">
            <label class="it__price"><span class="sr">Цена варианта</span><input type="text" inputmode="decimal" data-f="price" data-price value="<?= e($fmt($v['price'] ?? null)) ?>" placeholder="0"><span>₽</span></label>
            <button class="icon-btn icon-btn--danger" type="button" data-act="del-var" aria-label="Удалить вариант">✕</button>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="it__add" type="button" data-act="add-var">+ вариант цены</button>
      <div class="it__more" data-more hidden>
        <label class="field"><span class="field__label">Примечание</span><input type="text" data-f="note" value="<?= e($it['note'] ?? '') ?>" placeholder="Например, «без мытья»" maxlength="200"></label>
        <label class="field"><span class="field__label">Длительность</span><input type="text" data-f="duration" value="<?= e($it['duration'] ?? '') ?>" placeholder="60 мин" maxlength="40"></label>
        <label class="field"><span class="field__label">Единица</span><input type="text" data-f="unit" value="<?= e($it['unit'] ?? '') ?>" placeholder="за 1 ноготь" maxlength="40"></label>
        <label class="check"><input type="checkbox" data-f="from"<?= !empty($it['from']) ? ' checked' : '' ?>><span>Писать «от» перед ценой</span></label>
        <label class="check"><input type="checkbox" data-f="hidden"<?= !empty($it['hidden']) ? ' checked' : '' ?>><span>Скрыть с сайта</span></label>
      </div>
    </li>
    <?php return (string) ob_get_clean();
};

$groupHtml = static function (array $g, string $itemsHtml): string {
    ob_start(); ?>
    <section class="g" data-group data-id="<?= e($g['id'] ?? '') ?>" id="<?= e($g['id'] ?? '') ?>">
      <div class="g__head">
        <input class="g__title" type="text" data-f="title" value="<?= e($g['title'] ?? '') ?>" placeholder="Название подраздела" aria-label="Название подраздела" maxlength="120">
        <span class="it__btns">
          <button class="icon-btn" type="button" data-act="g-up" aria-label="Подраздел выше">↑</button>
          <button class="icon-btn" type="button" data-act="g-down" aria-label="Подраздел ниже">↓</button>
          <button class="icon-btn icon-btn--danger" type="button" data-act="g-del" aria-label="Удалить подраздел">✕</button>
        </span>
      </div>
      <input class="g__note" type="text" data-f="note" value="<?= e($g['note'] ?? '') ?>" placeholder="Пояснение к подразделу (необязательно)" aria-label="Пояснение к подразделу" maxlength="300">
      <label class="check g__extra"><input type="checkbox" data-f="extra"<?= !empty($g['extra']) ? ' checked' : '' ?>><span>Доплаты и материалы: без кнопки «Записаться», не влияют на «от … ₽»</span></label>
      <ol class="items" data-items><?= $itemsHtml ?></ol>
      <button class="btn btn--ghost btn--small" type="button" data-act="add-item">+ Добавить услугу</button>
    </section>
    <?php return (string) ob_get_clean();
};
?>
<div class="adm-head">
  <nav class="crumbs" aria-label="Навигация"><a href="<?= e(admin_url('/prices')) ?>">Цены и услуги</a><?= icon('chevron', 'ico ico--s') ?><span><?= e($cat['title']) ?></span></nav>
  <div class="adm-head__row">
    <h1 class="adm-h1"><?= e($cat['title']) ?></h1>
    <?= !empty($cat['hidden']) ? '<span class="tag tag--off">скрыт с сайта</span>' : '<a class="link" href="' . e(url('/uslugi/' . $cat['slug'] . '/')) . '" target="_blank" rel="noopener">Открыть на сайте</a>' ?>
  </div>
  <p class="adm-muted">Меняйте цены прямо в полях. Если у услуги несколько цен (по длине волос, по времени) — добавьте варианты. Изменения появятся на сайте после кнопки «Сохранить».</p>
</div>

<form method="post" enctype="multipart/form-data" data-cat-form>
  <?= csrf_field() ?>
  <input type="hidden" name="payload" value="">

  <div class="adm-card adm-tools">
    <div class="search">
      <?= icon('search', 'ico search__ico') ?>
      <label class="sr" for="cat-q">Найти в разделе</label>
      <input class="search__input" id="cat-q" type="search" placeholder="Найти услугу в разделе" data-cat-search autocomplete="off">
    </div>
    <div class="bulk">
      <label for="bulk-pct">Изменить все цены на</label>
      <input id="bulk-pct" type="number" step="1" min="-90" max="300" placeholder="10" data-bulk-pct>
      <span>%,</span>
      <label class="sr" for="bulk-round">Округление</label>
      <select id="bulk-round" data-bulk-round>
        <option value="10">округлить до 10 ₽</option>
        <option value="50">до 50 ₽</option>
        <option value="100">до 100 ₽</option>
        <option value="1">не округлять</option>
      </select>
      <button class="btn btn--ghost btn--small" type="button" data-act="bulk">Пересчитать</button>
      <span class="adm-hint">Минус — снизить цены. Видимые после поиска услуги меняются только они.</span>
    </div>
  </div>

  <details class="adm-card adm-settings">
    <summary>Настройки раздела: название, описание, адрес страницы, картинка</summary>
    <div class="adm-grid2">
      <label class="field"><span class="field__label">Название</span><input type="text" data-c="title" value="<?= e($cat['title']) ?>" maxlength="80" required></label>
      <label class="field"><span class="field__label">Адрес страницы</span><input type="text" data-c="slug" value="<?= e($cat['slug']) ?>" maxlength="80" pattern="[a-z0-9\-]+"><span class="adm-hint">Латиница и дефисы: <?= e(site_origin() . url('/uslugi/')) ?><b><?= e($cat['slug']) ?></b>/</span></label>
      <label class="field"><span class="field__label">Коротко (для меню и главной)</span><input type="text" data-c="short" value="<?= e($cat['short'] ?? '') ?>" maxlength="200"></label>
      <label class="field"><span class="field__label">Цена «от» на главной, ₽</span><input type="text" inputmode="numeric" data-c="price_from" value="<?= e((string) ($cat['price_from'] ?? '')) ?>" placeholder="считается автоматически"><span class="adm-hint">Пусто — минимальная цена раздела</span></label>
      <label class="field adm-span2"><span class="field__label">Описание раздела</span><textarea data-c="description" rows="3" maxlength="1000"><?= e($cat['description'] ?? '') ?></textarea></label>
      <label class="field"><span class="field__label">Значок</span>
        <select data-c="icon"><?php foreach (ICONS as $k => $label): ?><option value="<?= e($k) ?>"<?= ($cat['icon'] ?? '') === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select>
      </label>
      <div class="field">
        <span class="field__label">Картинка раздела</span>
        <?php if (!empty($cat['image'])): ?>
          <img class="adm-thumb" src="<?= e(upload_url($cat['image'])) ?>" alt="">
          <label class="check"><input type="checkbox" name="remove_image" value="1"><span>Убрать картинку</span></label>
        <?php endif; ?>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
        <span class="adm-hint">JPG, PNG или WebP до 10 МБ. Без картинки показывается значок.</span>
      </div>
      <label class="check adm-span2"><input type="checkbox" data-c="hidden"<?= !empty($cat['hidden']) ? ' checked' : '' ?>><span>Скрыть раздел с сайта</span></label>
    </div>
  </details>

  <div class="groups" data-groups>
    <?php foreach ($cat['groups'] as $g):
        $ih = '';
        foreach ($g['items'] as $it) {
            $ih .= $itemHtml($it);
        }
        echo $groupHtml($g, $ih);
    endforeach; ?>
  </div>
  <button class="btn btn--ghost" type="button" data-act="add-group">+ Добавить подраздел</button>

  <div class="savebar" data-savebar>
    <span class="savebar__status" data-dirty-msg>Изменений нет</span>
    <a class="btn btn--ghost btn--small" href="<?= e(admin_url('/prices/' . $cat['id'])) ?>">Отменить</a>
    <button class="btn btn--berry" type="submit">Сохранить</button>
  </div>

  <template id="tpl-item"><?= $itemHtml(['id' => '', 'name' => '', 'price' => null, 'variants' => []]) ?></template>
  <template id="tpl-var">
    <div class="var" data-var>
      <input type="text" data-f="label" value="" placeholder="Например, короткие" aria-label="Вариант" maxlength="80">
      <label class="it__price"><span class="sr">Цена варианта</span><input type="text" inputmode="decimal" data-f="price" data-price value="" placeholder="0"><span>₽</span></label>
      <button class="icon-btn icon-btn--danger" type="button" data-act="del-var" aria-label="Удалить вариант">✕</button>
    </div>
  </template>
  <template id="tpl-group"><?= $groupHtml(['id' => '', 'title' => '', 'note' => '', 'extra' => false], '') ?></template>
</form>

<form class="adm-danger" method="post" action="<?= e(admin_url('/prices/' . $cat['id'] . '/delete')) ?>" data-confirm="Удалить раздел «<?= e($cat['title']) ?>» со всеми услугами? Вернуть можно из резервной копии.">
  <?= csrf_field() ?>
  <button class="btn btn--small adm-link adm-link--danger" type="submit">Удалить раздел</button>
</form>
