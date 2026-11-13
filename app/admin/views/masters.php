<?php /** @var array $items */
$form = static function (array $m, ?int $i = null, int $total = 0): string {
    ob_start(); ?>
    <form class="adm-card master" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= e($m['id'] ?? '') ?>">
      <div class="master__photo">
        <?php if (!empty($m['photo'])): ?><img src="<?= e(upload_url($m['photo'])) ?>" alt=""><?php else: ?><span><?= e(mb_substr($m['name'] ?? '+', 0, 1) ?: '+') ?></span><?php endif; ?>
      </div>
      <div class="adm-grid2 master__fields">
        <label class="field"><span class="field__label">Имя</span><input type="text" name="name" value="<?= e($m['name'] ?? '') ?>" maxlength="80" required></label>
        <label class="field"><span class="field__label">Специализация</span><input type="text" name="role" value="<?= e($m['role'] ?? '') ?>" maxlength="120" placeholder="Мастер маникюра и педикюра"></label>
        <label class="field"><span class="field__label">Опыт</span><input type="text" name="experience" value="<?= e($m['experience'] ?? '') ?>" maxlength="120" placeholder="Стаж 12 лет"></label>
        <label class="field"><span class="field__label">Фото</span><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"><span class="adm-hint">Вертикальное 4:5</span></label>
        <label class="check"><input type="checkbox" name="hidden" value="1"<?= !empty($m['hidden']) ? ' checked' : '' ?>><span>Скрыть с сайта</span></label>
      </div>
      <div class="adm-form__actions">
        <button class="btn btn--berry btn--small" type="submit" name="action" value="save"><?= empty($m['id']) ? 'Добавить мастера' : 'Сохранить' ?></button>
        <?php if (!empty($m['id'])): ?>
          <button class="icon-btn" type="submit" name="action" value="up" <?= $i === 0 ? 'disabled' : '' ?> aria-label="Выше" formnovalidate>↑</button>
          <button class="icon-btn" type="submit" name="action" value="down" <?= $i === $total - 1 ? 'disabled' : '' ?> aria-label="Ниже" formnovalidate>↓</button>
          <button class="btn btn--small adm-link adm-link--danger" type="submit" name="action" value="delete" formnovalidate data-confirm-btn="Удалить мастера <?= e($m['name']) ?>?">Удалить</button>
        <?php endif; ?>
      </div>
    </form>
    <?php return (string) ob_get_clean();
};
?>
<div class="adm-head">
  <h1 class="adm-h1">Мастера</h1>
  <p class="adm-muted">Пока мастеров нет, раздел на сайте не показывается. Добавьте мастеров с фото — блок появится на главной и на странице «О салоне».</p>
</div>
<?php foreach ($items as $i => $m): ?><?= $form($m, $i, count($items)) ?><?php endforeach; ?>
<h2 class="adm-h3">Новый мастер</h2>
<?= $form([]) ?>
