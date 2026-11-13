<?php
/** @var array $s */
$rep = static function (string $key, array $rows, array $fields, int $max, string $addLabel): string {
    $rowHtml = static function ($row, $i) use ($key, $fields): string {
        $h = '<div class="rep__row" data-rep-row>';
        foreach ($fields as $f => [$label, $ph, $w]) {
            $h .= '<label class="field" style="flex:' . $w . '"><span class="field__label">' . e($label) . '</span><input type="text" data-n="' . e($f) . '" name="' . e($key . '[' . $i . '][' . $f . ']') . '" value="' . e($row[$f] ?? '') . '" placeholder="' . e($ph) . '"></label>';
        }
        return $h . '<button class="icon-btn icon-btn--danger" type="button" data-act="rep-del" aria-label="Удалить строку">✕</button></div>';
    };
    $h = '<div class="rep" data-rep="' . e($key) . '" data-max="' . $max . '">';
    foreach ($rows as $i => $row) {
        $h .= $rowHtml($row, $i);
    }
    $h .= '<template>' . $rowHtml([], 0) . '</template>';
    $h .= '<button class="btn btn--ghost btn--small" type="button" data-act="rep-add">' . e($addLabel) . '</button></div>';
    return $h;
};
$t = static fn(string $k, string $label, string $hint = '', string $ph = '', string $type = 'text') =>
    '<label class="field"><span class="field__label">' . e($label) . '</span><input type="' . $type . '" name="' . e($k) . '" value="' . e((string) ($s[$k] ?? '')) . '" placeholder="' . e($ph) . '">' . ($hint ? '<span class="adm-hint">' . $hint . '</span>' : '') . '</label>';
?>
<div class="adm-head">
  <h1 class="adm-h1">Контакты и настройки</h1>
  <p class="adm-muted">Эти данные выводятся в шапке, подвале, на странице контактов и в форме записи.</p>
</div>

<form class="adm-form" method="post" data-settings-form>
  <?= csrf_field() ?>

  <fieldset class="adm-card">
    <legend>Телефоны и мессенджеры</legend>
    <?= $rep('phones', $s['phones'] ?? [], ['number' => ['Номер', '+7 (495) 000-00-00', 2], 'label' => ['Подпись', 'Администратор', 2]], 6, '+ Добавить телефон') ?>
    <p class="adm-hint">Первый номер — главный: он на кнопке «Позвонить» и в шапке.</p>
    <div class="adm-grid2">
      <?= $t('whatsapp', 'WhatsApp', 'Номер, на который приходят сообщения с кнопки «WhatsApp»', '+7 (985) 000-00-00') ?>
      <?= $t('telegram', 'Telegram', 'Имя без @ или ссылка', 'roniks_salon') ?>
      <?= $t('vk', 'ВКонтакте', 'Ссылка https://vk.com/…') ?>
      <?= $t('max', 'MAX', 'Ссылка https://max.ru/…') ?>
    </div>
  </fieldset>

  <fieldset class="adm-card">
    <legend>Часы работы</legend>
    <?= $rep('hours', $s['hours'] ?? [], ['days' => ['Дни', 'Пн–Пт', 1], 'time' => ['Время', '9:00–21:00', 1]], 7, '+ Добавить строку') ?>
    <p class="adm-hint">Пишите дни как «Пн–Сб», «Вс» или «Ежедневно», время — «9:00–21:00». По этим строкам сайт показывает «Сейчас открыто».</p>
  </fieldset>

  <fieldset class="adm-card">
    <legend>Онлайн-запись</legend>
    <div class="adm-grid2">
      <?= $t('booking_url', 'Ссылка на онлайн-запись', 'YCLIENTS, DIKIDI, Яндекс и т.п. Пусто — кнопка онлайн-записи не показывается.', 'https://…') ?>
      <?= $t('booking_label', 'Текст кнопки', '', 'Онлайн-запись') ?>
    </div>
  </fieldset>

  <fieldset class="adm-card">
    <legend>Заявки «Перезвоните мне»</legend>
    <p class="adm-hint">Заявки всегда видны в разделе «Заявки». Дополнительно их можно получать на почту или в Telegram.</p>
    <div class="adm-grid2">
      <?= $t('lead_email', 'Почта для заявок', '', 'salon@example.ru', 'email') ?>
      <span></span>
      <?= $t('tg_bot_token', 'Telegram-бот: токен', 'Создайте бота у @BotFather и вставьте токен') ?>
      <?= $t('tg_chat_id', 'Telegram: ID чата', 'Куда бот присылает заявки') ?>
    </div>
  </fieldset>

  <fieldset class="adm-card">
    <legend>Адрес и карта</legend>
    <div class="adm-grid2">
      <?= $t('city', 'Город') ?>
      <?= $t('address', 'Адрес') ?>
      <label class="field adm-span2"><span class="field__label">Как добраться</span><input type="text" name="address_note" value="<?= e($s['address_note'] ?? '') ?>"></label>
      <?= $t('map_link', 'Ссылка на Яндекс Карты') ?>
      <?= $t('yandex_org_id', 'ID организации в Яндекс Картах', 'Число из ссылки на карточку') ?>
      <?= $t('map_ll', 'Координаты центра карты', 'долгота,широта') ?>
    </div>
  </fieldset>

  <fieldset class="adm-card">
    <legend>О салоне</legend>
    <div class="adm-grid2">
      <?= $t('name', 'Короткое название (логотип)') ?>
      <?= $t('full_name', 'Полное название') ?>
      <?= $t('tagline', 'Подзаголовок на странице «О салоне»') ?>
      <?= $t('since', 'Год открытия', '', '', 'number') ?>
      <?= $t('about_title', 'Заголовок блока о салоне') ?>
      <span></span>
      <label class="field adm-span2"><span class="field__label">Текст о салоне</span><textarea name="about_text" rows="7"><?= e($s['about_text'] ?? '') ?></textarea><span class="adm-hint">Абзацы разделяйте пустой строкой. На главной показываются первые два.</span></label>
      <label class="field adm-span2"><span class="field__label">Бренды косметики</span><input type="text" name="brands" value="<?= e(implode(', ', $s['brands'] ?? [])) ?>"><span class="adm-hint">Через запятую</span></label>
    </div>
    <h3 class="adm-h3">Цифры о салоне</h3>
    <?= $rep('facts', $s['facts'] ?? [], ['value' => ['Цифра', 'с 1998', 1], 'label' => ['Подпись', 'работаем на Университетском', 3]], 4, '+ Добавить') ?>
  </fieldset>

  <fieldset class="adm-card">
    <legend>Рейтинг на Яндекс Картах</legend>
    <div class="adm-grid2">
      <?= $t('rating_value', 'Оценка', 'Пусто — блок не показывается', '4,7') ?>
      <?= $t('rating_count', 'Количество оценок', '', '199 оценок') ?>
      <?= $t('rating_note', 'Примечание') ?>
      <?= $t('rating_link', 'Ссылка на отзывы') ?>
    </div>
  </fieldset>

  <fieldset class="adm-card">
    <legend>Реквизиты и документы</legend>
    <div class="adm-grid2">
      <?= $t('legal_name', 'Юридическое лицо или ИП', 'Выводится в подвале и политике конфиденциальности', 'ООО «…»') ?>
      <?= $t('inn', 'ИНН') ?>
      <?= $t('ogrn', 'ОГРН / ОГРНИП') ?>
      <?= $t('legal_address', 'Юридический адрес') ?>
      <label class="field adm-span2"><span class="field__label">Лицензия</span><input type="text" name="license" value="<?= e($s['license'] ?? '') ?>" placeholder="Мед. лицензия № … от … — если есть"></label>
    </div>
  </fieldset>

  <fieldset class="adm-card">
    <legend>Ссылки на другие заведения</legend>
    <?= $rep('sister_links', $s['sister_links'] ?? [], ['title' => ['Название', 'Клиника «Кэри»', 2], 'url' => ['Ссылка', 'https://…', 2], 'note' => ['Пояснение', '', 2]], 4, '+ Добавить ссылку') ?>
  </fieldset>

  <fieldset class="adm-card">
    <legend>Поисковики</legend>
    <div class="adm-grid2">
      <label class="field adm-span2"><span class="field__label">Заголовок главной страницы</span><input type="text" name="seo_title" value="<?= e($s['seo_title'] ?? '') ?>" maxlength="160"></label>
      <label class="field adm-span2"><span class="field__label">Описание для поисковиков</span><textarea name="seo_description" rows="2" maxlength="300"><?= e($s['seo_description'] ?? '') ?></textarea></label>
    </div>
  </fieldset>

  <div class="savebar savebar--static">
    <button class="btn btn--berry" type="submit">Сохранить настройки</button>
  </div>
</form>
