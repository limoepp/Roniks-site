<form class="adm-login" method="post" action="<?= e(admin_url('/login')) ?>">
  <?= csrf_field() ?>
  <p class="logo"><span class="logo__word"><?= e(settings()['name']) ?></span><span class="logo__sub">управление сайтом</span></p>
  <h1 class="adm-h1">Вход</h1>
  <?php if (!empty($error)): ?><p class="adm-flash adm-flash--error" role="alert"><?= e($error) ?></p><?php endif; ?>
  <label class="field">
    <span class="field__label">Пароль</span>
    <input type="password" name="password" autocomplete="current-password" required autofocus>
  </label>
  <button class="btn btn--berry btn--wide" type="submit">Войти</button>
  <p class="adm-muted">Забыли пароль? Его можно сбросить на хостинге — инструкция в README.md.</p>
</form>
