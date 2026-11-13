<?php /** @var array $items */ ?>
<div class="adm-head">
  <h1 class="adm-h1">Фото салона</h1>
  <p class="adm-muted">Фото интерьера и работ показываются на странице «О салоне». Фото автоматически уменьшаются до 1600 px.</p>
</div>
<form class="adm-card adm-inline" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <label class="field"><span class="field__label">Добавить фото</span><input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required></label>
  <button class="btn btn--berry" type="submit" name="action" value="upload">Загрузить</button>
</form>
<?php if ($items): ?>
<form method="post" class="adm-gallery">
  <?= csrf_field() ?>
  <?php foreach ($items as $p): ?>
    <div class="adm-gallery__item">
      <img src="<?= e(upload_url($p['file'])) ?>" alt="">
      <label class="field"><span class="field__label">Подпись</span><input type="text" name="alt[<?= e($p['file']) ?>]" value="<?= e($p['alt'] ?? '') ?>" maxlength="160"></label>
      <button class="btn btn--small adm-link adm-link--danger" type="submit" name="file" value="<?= e($p['file']) ?>" formaction="<?= e(admin_url('/gallery')) ?>" data-gallery-del>Удалить</button>
    </div>
  <?php endforeach; ?>
  <input type="hidden" name="action" value="alt" data-gallery-action>
  <div class="savebar savebar--static"><button class="btn btn--ghost" type="submit">Сохранить подписи</button></div>
</form>
<?php endif; ?>
