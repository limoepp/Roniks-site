<h1 class="adm-h1">Пароль для входа</h1>
<?php if (!empty($error)): ?><p class="adm-flash adm-flash--error" role="alert"><?= e($error) ?></p><?php endif; ?>
<form class="adm-card adm-form adm-form--narrow" method="post">
  <?= csrf_field() ?>
  <label class="field"><span class="field__label">Текущий пароль</span><input type="password" name="current" autocomplete="current-password" required></label>
  <label class="field"><span class="field__label">Новый пароль</span><input type="password" name="new" autocomplete="new-password" minlength="10" required><span class="adm-hint">Минимум 10 символов. Удобно взять 3–4 слова через дефис.</span></label>
  <label class="field"><span class="field__label">Новый пароль ещё раз</span><input type="password" name="repeat" autocomplete="new-password" minlength="10" required></label>
  <button class="btn btn--berry" type="submit">Сменить пароль</button>
</form>
