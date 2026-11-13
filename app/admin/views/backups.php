<?php /** @var array $files @var array $labels */ ?>
<div class="adm-head">
  <h1 class="adm-h1">Резервные копии</h1>
  <p class="adm-muted">Перед каждым сохранением сайт запоминает предыдущую версию (хранятся последние 60). Если что-то пошло не так — восстановите нужную.</p>
</div>
<?php if (!$files): ?>
  <div class="adm-card"><p>Копий пока нет — они появятся после первого сохранения.</p></div>
<?php else: ?>
<table class="adm-table">
  <thead><tr><th scope="col">Что</th><th scope="col">Версия от</th><th scope="col"><span class="sr">Действие</span></th></tr></thead>
  <tbody>
  <?php foreach (array_slice($files, 0, 200) as $f):
      $b = basename($f);
      if (!preg_match('/^(\w+)-(\d{4})(\d{2})(\d{2})-(\d{2})(\d{2})(\d{2})/', $b, $m) || !isset($labels[$m[1]])) continue; ?>
    <tr>
      <td><?= e($labels[$m[1]]) ?></td>
      <td class="adm-nowrap"><?= e("{$m[4]}.{$m[3]}.{$m[2]} {$m[5]}:{$m[6]}:{$m[7]}") ?></td>
      <td class="adm-actions"><form method="post" data-confirm="Восстановить «<?= e($labels[$m[1]]) ?>» к версии от <?= e("{$m[4]}.{$m[3]} {$m[5]}:{$m[6]}") ?>?"><?= csrf_field() ?><input type="hidden" name="file" value="<?= e($b) ?>"><button class="btn btn--ghost btn--small" type="submit">Восстановить</button></form></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
