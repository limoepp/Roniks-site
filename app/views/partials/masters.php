<?php /** @var array $masters */ ?>
<ul class="team">
  <?php foreach ($masters as $m): ?>
    <li class="team__item">
      <?php if (!empty($m['photo'])): ?>
        <img class="team__photo" src="<?= e(upload_url($m['photo'])) ?>" alt="<?= e($m['name']) ?>" loading="lazy">
      <?php else: ?>
        <span class="team__photo team__photo--empty" aria-hidden="true"><?= e(mb_substr($m['name'], 0, 1)) ?></span>
      <?php endif; ?>
      <h3 class="team__name"><?= e($m['name']) ?></h3>
      <p class="team__role"><?= e($m['role'] ?? '') ?></p>
      <?php if (!empty($m['experience'])): ?><p class="team__exp"><?= e($m['experience']) ?></p><?php endif; ?>
      <button class="link link--btn" type="button" data-book="<?= e('к мастеру ' . $m['name']) ?>">Записаться к мастеру</button>
    </li>
  <?php endforeach; ?>
</ul>
