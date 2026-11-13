<?php
/** @var array $items */
$names = ['live' => ['показывается', 'live'], 'scheduled' => ['запланирована', 'soon'], 'ended' => ['закончилась', 'off'], 'off' => ['выключена', 'off']];
?>
<div class="adm-head adm-head__row">
  <div>
    <h1 class="adm-h1">Акции</h1>
    <p class="adm-muted">Акция сама появляется на сайте в дату начала и исчезает после даты окончания. Чтобы повторить акцию в следующем месяце — нажмите «Повторить».</p>
  </div>
  <a class="btn btn--berry" href="<?= e(admin_url('/promos/edit')) ?>">+ Новая акция</a>
</div>

<?php if (!$items): ?>
  <div class="adm-card"><p>Акций пока нет. Добавьте первую — она появится на главной и на странице «Акции».</p></div>
<?php else: ?>
<table class="adm-table">
  <thead><tr><th scope="col">Акция</th><th scope="col">Срок</th><th scope="col">Статус</th><th scope="col"><span class="sr">Действия</span></th></tr></thead>
  <tbody>
  <?php foreach ($items as $i => $p): $st = promo_status($p); ?>
    <tr>
      <td>
        <a class="adm-strong" href="<?= e(admin_url('/promos/edit?id=' . $p['id'])) ?>"><?= e($p['title']) ?></a>
        <?php if (!empty($p['badge'])): ?> <span class="tag tag--badge"><?= e($p['badge']) ?></span><?php endif; ?>
        <?php if (!empty($p['price_new'])): ?><br><span class="adm-muted"><?= e(price_label($p['price_new'], false, $p['price_unit'] ?? '')) ?><?= !empty($p['price_old']) ? ' вместо ' . e(price_label($p['price_old'])) : '' ?></span><?php endif; ?>
      </td>
      <td class="adm-nowrap"><?= e(ucf(promo_period($p))) ?></td>
      <td><span class="tag tag--<?= $names[$st][1] ?>"><?= $names[$st][0] ?></span></td>
      <td class="adm-actions">
        <form method="post" action="<?= e(admin_url('/promos/move')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($p['id']) ?>"><input type="hidden" name="dir" value="up"><button class="icon-btn" type="submit" <?= $i === 0 ? 'disabled' : '' ?> aria-label="Выше">↑</button></form>
        <form method="post" action="<?= e(admin_url('/promos/move')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($p['id']) ?>"><input type="hidden" name="dir" value="down"><button class="icon-btn" type="submit" <?= $i === count($items) - 1 ? 'disabled' : '' ?> aria-label="Ниже">↓</button></form>
        <form method="post" action="<?= e(admin_url('/promos/toggle')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($p['id']) ?>"><button class="btn btn--ghost btn--small" type="submit"><?= !empty($p['active']) ? 'Выключить' : 'Включить' ?></button></form>
        <form method="post" action="<?= e(admin_url('/promos/copy')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($p['id']) ?>"><button class="btn btn--ghost btn--small" type="submit" title="Копия на следующий месяц">Повторить</button></form>
        <form method="post" action="<?= e(admin_url('/promos/delete')) ?>" data-confirm="Удалить акцию «<?= e($p['title']) ?>»?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($p['id']) ?>"><button class="icon-btn icon-btn--danger" type="submit" aria-label="Удалить">✕</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
