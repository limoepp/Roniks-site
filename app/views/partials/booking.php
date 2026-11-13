<?php
$s = settings();
$phone = main_phone();
$tg = tg_href();
?>
<dialog class="book" id="book" aria-labelledby="book-title">
  <div class="book__in">
    <button class="book__close" type="button" data-close aria-label="Закрыть"><?= icon('close') ?></button>
    <h2 class="book__title" id="book-title">Записаться в салон</h2>
    <p class="book__service" data-book-service hidden></p>

    <div class="book__ways">
      <?php if (!empty($s['booking_url'])): ?>
        <a class="way way--main" href="<?= e($s['booking_url']) ?>" target="_blank" rel="noopener"><?= icon('calendar') ?><span><b><?= e($s['booking_label'] ?: 'Онлайн-запись') ?></b><small>выбрать мастера и время</small></span></a>
      <?php endif; ?>
      <?php if (wa_href()): ?>
        <a class="way way--wa" href="<?= e(wa_href('Здравствуйте! Хочу записаться')) ?>" data-wa target="_blank" rel="noopener"><?= icon('whatsapp') ?><span><b>WhatsApp</b><small>ответим в рабочее время</small></span></a>
      <?php endif; ?>
      <?php if ($tg): ?>
        <a class="way" href="<?= e($tg) ?>" target="_blank" rel="noopener"><?= icon('telegram') ?><span><b>Telegram</b><small>@<?= e(ltrim($s['telegram'], '@')) ?></small></span></a>
      <?php endif; ?>
      <a class="way" href="<?= e(tel_href($phone)) ?>"><?= icon('phone') ?><span><b><?= e($phone) ?></b><small><?= e(hours_short()) ?></small></span></a>
    </div>

    <form class="book__form" data-lead-form action="<?= e(url('/api/lead')) ?>" method="post" novalidate>
      <p class="book__or">Или оставьте номер — администратор перезвонит и подберёт время</p>
      <input type="hidden" name="ts" value="<?= e($formTs) ?>">
      <input type="hidden" name="service" value="" data-service-input>
      <div class="hp" aria-hidden="true"><label>Сайт <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <div class="field-row">
        <label class="field">
          <span class="field__label">Имя</span>
          <input type="text" name="name" autocomplete="given-name" maxlength="80" required>
          <span class="field__error" data-error="name"></span>
        </label>
        <label class="field">
          <span class="field__label">Телефон</span>
          <input type="tel" name="phone" autocomplete="tel" inputmode="tel" placeholder="+7 (___) ___-__-__" maxlength="30" required>
          <span class="field__error" data-error="phone"></span>
        </label>
      </div>
      <label class="field">
        <span class="field__label">Комментарий <span class="field__opt">— необязательно</span></span>
        <textarea name="comment" rows="2" maxlength="600" placeholder="Удобные дни и время, мастер"></textarea>
      </label>
      <label class="check">
        <input type="checkbox" name="consent" value="1" required>
        <span>Согласен на обработку персональных данных по <a href="<?= e(url('/politika-konfidencialnosti/')) ?>" target="_blank">политике салона</a></span>
      </label>
      <span class="field__error" data-error="consent"></span>
      <p class="form-msg" data-form-msg role="status" aria-live="polite"></p>
      <button class="btn btn--berry btn--wide" type="submit">Жду звонка</button>
    </form>
  </div>
</dialog>
