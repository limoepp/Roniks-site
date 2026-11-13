<?php /** @var array $items */
$st = ['new' => ['Новая', 'soon'], 'called' => ['Перезвонили', 'live'], 'done' => ['Записан', 'off']];
?>
<div class="adm-head adm-head__row">
  <div>
    <h1 class="adm-h1">Заявки</h1>
    <p class="adm-muted">Заявки из формы «Перезвоните мне». Отмечайте, кому уже перезвонили.</p>
  </div>
  <?php if ($items): ?><a class="btn btn--ghost btn--small" href="<?= e(admin_url('/leads') . '?export=csv') ?>">Скачать для Excel</a><?php endif; ?>
</div>
<?php if (!$items): ?>
  <div class="adm-card"><p>Заявок пока нет. Они появятся здесь, когда клиенты оставят номер в форме записи.</p></div>
<?php else: ?>
<table class="adm-table">
  <thead><tr><th scope="col">Когда</th><th scope="col">Клиент</th><th scope="col">Услуга и комментарий</th><th scope="col">Статус</th><th scope="col"><span class="sr">Действия</span></th></tr></thead>
  <tbody>
  <?php foreach ($items as $l): $s0 = $st[$l['status']] ?? $st['new']; ?>
    <tr class="<?= $l['status'] === 'new' ? 'is-new' : '' ?>">
      <td class="adm-nowrap"><?= e(date('d.m H:i', strtotime($l['created']))) ?></td>
      <td><b><?= e($l['name']) ?></b><br><a href="<?= e(tel_href($l['phone'])) ?>"><?= e($l['phone']) ?></a></td>
      <td><?= e($l['service'] ?: '—') ?><?php if ($l['comment']): ?><br><span class="adm-muted"><?= e($l['comment']) ?></span><?php endif; ?></td>
      <td><span class="tag tag--<?= $s0[1] ?>"><?= $s0[0] ?></span></td>
      <td class="adm-actions">
        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($l['id']) ?>">
          <?php if ($l['status'] !== 'called'): ?><button class="btn btn--ghost btn--small" name="action" value="called">Перезвонили</button><?php endif; ?>
          <?php if ($l['status'] !== 'done'): ?><button class="btn btn--ghost btn--small" name="action" value="done">Записан</button><?php endif; ?>
          <button class="icon-btn icon-btn--danger" name="action" value="delete" aria-label="Удалить заявку" data-confirm-btn="Удалить заявку?">✕</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
