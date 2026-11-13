<?php
/**
 * Прайс одной категории.
 * @var array $cat
 * @var bool $showCatTitle
 */
$showCatTitle = $showCatTitle ?? false;
$hTag = $showCatTitle ? 'h3' : 'h2';
?>
<section class="pl" id="cat-<?= e($cat['id']) ?>" data-cat="<?= e($cat['id']) ?>">
  <?php if ($showCatTitle): ?>
    <h2 class="pl__cat"><a href="<?= e(url('/uslugi/' . $cat['slug'] . '/')) ?>"><?= icon($cat['icon'] ?? 'spark') ?><?= e($cat['title']) ?></a></h2>
    <div class="price-columns" aria-hidden="true"><span>Наименование услуги</span><span>Варианты и цена</span><span>Запись</span></div>
  <?php endif; ?>
  <?php foreach ($cat['groups'] as $g): ?>
    <div class="pl__group<?= !empty($g['extra']) ? ' pl__group--extra' : '' ?>" id="<?= e($g['id']) ?>" data-group>
      <<?= $hTag ?> class="pl__title"><?= e($g['title']) ?></<?= $hTag ?>>
      <?php if (!empty($g['note'])): ?><p class="pl__note"><?= e($g['note']) ?></p><?php endif; ?>
      <ul class="pl__list">
        <?php foreach ($g['items'] as $it):
            $variants = $it['variants'] ?? [];
            $search = mb_strtolower($cat['title'] . ' ' . $g['title'] . ' ' . $it['name'] . ' ' . ($it['note'] ?? '') . ' ' . implode(' ', array_column($variants, 'label')));
            $bookName = $it['name'];
        ?>
          <li class="row" id="<?= e($it['id']) ?>" data-row data-search="<?= e($search) ?>">
            <div class="row__name">
              <span class="row__title"><?= e($it['name']) ?></span>
              <?php if (!empty($it['note']) || !empty($it['duration'])): ?>
                <span class="row__note"><?= e(implode(' · ', array_filter([$it['duration'] ?? '', $it['note'] ?? '']))) ?></span>
              <?php endif; ?>
            </div>
            <div class="row__price">
              <?php if ($variants): ?>
                <dl class="vars">
                  <?php foreach ($variants as $v): ?>
                    <div class="vars__row"><dt><?= e($v['label']) ?></dt><dd><?= e(price_label($v['price'] ?? null, !empty($it['from']), $it['unit'] ?? '')) ?></dd></div>
                  <?php endforeach; ?>
                </dl>
              <?php else: ?>
                <span class="row__single"><?= e(price_label($it['price'] ?? null, !empty($it['from']), $it['unit'] ?? '')) ?></span>
              <?php endif; ?>
            </div>
            <?php if (empty($g['extra'])): ?>
              <button class="row__book" type="button" data-book="<?= e($bookName) ?>">Записаться<span class="sr"> на «<?= e($bookName) ?>»</span></button>
            <?php else: ?>
              <span class="row__book row__book--none" aria-hidden="true"></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>
</section>
