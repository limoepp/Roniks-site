<?php /** @var array $cats */ ?>
<div class="adm-head">
  <h1 class="adm-h1">Цены и услуги</h1>
  <p class="adm-muted">Выберите раздел, чтобы изменить цены. Или найдите услугу по названию.</p>
</div>

<div class="adm-card">
  <div class="search">
    <?= icon('search', 'ico search__ico') ?>
    <label class="sr" for="adm-q">Найти услугу</label>
    <input class="search__input" id="adm-q" type="search" placeholder="Найти услугу, например «педикюр»" data-adm-search autocomplete="off">
  </div>
  <ul class="adm-found" data-adm-found hidden>
    <?php foreach ($cats as $c): foreach ($c['groups'] as $g): foreach ($g['items'] as $it): ?>
      <li data-adm-item data-s="<?= e(mb_strtolower($it['name'] . ' ' . $g['title'] . ' ' . $c['title'])) ?>" hidden>
        <a href="<?= e(admin_url('/prices/' . $c['id']) . '#' . $it['id']) ?>"><b><?= e($it['name']) ?></b> <span><?= e($c['title'] . ' — ' . $g['title']) ?></span></a>
        <span class="adm-found__price"><?= e(item_short_price($it)) ?></span>
      </li>
    <?php endforeach; endforeach; endforeach; ?>
    <li class="adm-muted" data-adm-none hidden>Ничего не найдено</li>
  </ul>
</div>

<table class="adm-table">
  <thead><tr><th scope="col">Раздел</th><th scope="col">Услуг</th><th scope="col">На сайте</th><th scope="col"><span class="sr">Порядок</span></th></tr></thead>
  <tbody>
  <?php foreach ($cats as $i => $c):
      $n = 0; foreach ($c['groups'] as $g) { $n += count($g['items']); } ?>
    <tr>
      <td><a class="adm-strong" href="<?= e(admin_url('/prices/' . $c['id'])) ?>"><?= e($c['title']) ?></a><br><span class="adm-muted"><?= count($c['groups']) ?> <?= plural(count($c['groups']), 'подраздел', 'подраздела', 'подразделов') ?></span></td>
      <td><?= $n ?></td>
      <td><?= !empty($c['hidden']) ? '<span class="tag tag--off">скрыт</span>' : '<span class="tag tag--live">показан</span>' ?></td>
      <td class="adm-actions">
        <form method="post" action="<?= e(admin_url('/prices/order')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($c['id']) ?>"><input type="hidden" name="dir" value="up"><button class="icon-btn" type="submit" <?= $i === 0 ? 'disabled' : '' ?> aria-label="Выше: <?= e($c['title']) ?>">↑</button></form>
        <form method="post" action="<?= e(admin_url('/prices/order')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($c['id']) ?>"><input type="hidden" name="dir" value="down"><button class="icon-btn" type="submit" <?= $i === count($cats) - 1 ? 'disabled' : '' ?> aria-label="Ниже: <?= e($c['title']) ?>">↓</button></form>
        <a class="btn btn--ink btn--small" href="<?= e(admin_url('/prices/' . $c['id'])) ?>">Изменить</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<form class="adm-card adm-inline" method="post" action="<?= e(admin_url('/prices/new')) ?>">
  <?= csrf_field() ?>
  <label class="field"><span class="field__label">Новый раздел услуг</span><input type="text" name="title" placeholder="Например, «Косметология»" maxlength="80" required></label>
  <button class="btn btn--ghost" type="submit">Добавить раздел</button>
</form>
