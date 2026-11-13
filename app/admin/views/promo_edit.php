<?php /** @var array $promo @var array $cats */ ?>
<div class="adm-head">
  <nav class="crumbs" aria-label="Навигация"><a href="<?= e(admin_url('/promos')) ?>">Акции</a><?= icon('chevron', 'ico ico--s') ?><span><?= $promo['id'] ? e($promo['title']) : 'Новая акция' ?></span></nav>
  <h1 class="adm-h1"><?= $promo['id'] ? 'Изменить акцию' : 'Новая акция' ?></h1>
</div>
<?php if (!empty($error)): ?><p class="adm-flash adm-flash--error" role="alert"><?= e($error) ?></p><?php endif; ?>

<form class="adm-card adm-form" method="post" enctype="multipart/form-data" action="<?= e(admin_url('/promos/edit')) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= e($promo['id']) ?>">
  <div class="adm-grid2">
    <label class="field adm-span2"><span class="field__label">Заголовок</span><input type="text" name="title" value="<?= e($promo['title']) ?>" maxlength="140" required placeholder="Например, «Педикюр со скидкой 15%»"></label>
    <label class="field adm-span2"><span class="field__label">Описание <span class="field__opt">— одно-два предложения</span></span><textarea name="text" rows="2" maxlength="600"><?= e($promo['text']) ?></textarea></label>
    <label class="field"><span class="field__label">Цена по акции, ₽</span><input type="text" inputmode="decimal" name="price_new" value="<?= e((string) ($promo['price_new'] ?? '')) ?>"></label>
    <label class="field"><span class="field__label">Обычная цена, ₽ <span class="field__opt">— будет зачёркнута</span></span><input type="text" inputmode="decimal" name="price_old" value="<?= e((string) ($promo['price_old'] ?? '')) ?>"></label>
    <label class="field"><span class="field__label">Плашка</span><input type="text" name="badge" value="<?= e($promo['badge']) ?>" maxlength="20" placeholder="−20%"><span class="adm-hint">Пусто — процент посчитается из цен</span></label>
    <label class="field"><span class="field__label">Единица цены</span><input type="text" name="price_unit" value="<?= e($promo['price_unit'] ?? '') ?>" maxlength="30" placeholder="за 1 мин"></label>
    <label class="field"><span class="field__label">Начало</span><input type="date" name="date_start" value="<?= e($promo['date_start']) ?>"></label>
    <label class="field"><span class="field__label">Окончание</span><input type="date" name="date_end" value="<?= e($promo['date_end']) ?>"><span class="adm-hint">Последний день, когда акция видна</span></label>
    <label class="field"><span class="field__label">Направление</span>
      <select name="category"><option value="">— не выбрано —</option><?php foreach ($cats as $c): ?><option value="<?= e($c['id']) ?>"<?= $promo['category'] === $c['id'] ? ' selected' : '' ?>><?= e($c['title']) ?></option><?php endforeach; ?></select>
      <span class="adm-hint">Акция покажется и на странице этого направления</span>
    </label>
    <label class="field"><span class="field__label">Услуга для записи</span><input type="text" name="service" value="<?= e($promo['service']) ?>" maxlength="200" placeholder="Попадёт в сообщение WhatsApp и заявку"></label>
    <label class="field"><span class="field__label">Текст кнопки</span><input type="text" name="button" value="<?= e($promo['button']) ?>" maxlength="40"></label>
    <div class="field">
      <span class="field__label">Картинка <span class="field__opt">— необязательно</span></span>
      <?php if (!empty($promo['image'])): ?>
        <img class="adm-thumb" src="<?= e(upload_url($promo['image'])) ?>" alt="">
        <label class="check"><input type="checkbox" name="remove_image" value="1"><span>Убрать картинку</span></label>
      <?php endif; ?>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
      <span class="adm-hint">Горизонтальная, 16:9, показывается на странице «Акции»</span>
    </div>
    <label class="check adm-span2"><input type="checkbox" name="active" value="1"<?= !empty($promo['active']) ? ' checked' : '' ?>><span>Акция включена</span></label>
  </div>
  <div class="adm-form__actions">
    <button class="btn btn--berry" type="submit">Сохранить акцию</button>
    <a class="btn btn--ghost" href="<?= e(admin_url('/promos')) ?>">Отмена</a>
  </div>
</form>
