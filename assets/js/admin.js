/* Админка «Роникс»: редактор прайса, повторяющиеся поля, подтверждения. */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var norm = function (s) { return (s || '').toLowerCase().replace(/ё/g, 'е'); };

  /* Меню на телефоне */
  var burger = $('[data-adm-burger]');
  if (burger) {
    burger.addEventListener('click', function () {
      var nav = $('#adm-nav');
      var open = !nav.classList.contains('is-open');
      nav.classList.toggle('is-open', open);
      burger.setAttribute('aria-expanded', String(open));
    });
  }

  /* Подтверждения */
  $$('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!confirm(f.getAttribute('data-confirm'))) e.preventDefault(); });
  });
  $$('[data-confirm-btn]').forEach(function (b) {
    b.addEventListener('click', function (e) { if (!confirm(b.getAttribute('data-confirm-btn'))) e.preventDefault(); });
  });
  $$('[data-gallery-del]').forEach(function (b) {
    b.addEventListener('click', function (e) {
      if (!confirm('Удалить фото?')) { e.preventDefault(); return; }
      $('[data-gallery-action]').value = 'delete';
    });
  });

  /* Поиск услуг на странице разделов */
  var aq = $('[data-adm-search]');
  if (aq) {
    var list = $('[data-adm-found]');
    var items = $$('[data-adm-item]');
    var none = $('[data-adm-none]');
    aq.addEventListener('input', function () {
      var q = norm(aq.value.trim());
      if (q.length < 2) { list.hidden = true; return; }
      var words = q.split(/\s+/);
      var n = 0;
      items.forEach(function (li) {
        var s = norm(li.getAttribute('data-s'));
        var ok = words.every(function (w) { return s.indexOf(w) !== -1; }) && n < 30;
        li.hidden = !ok;
        if (ok) n++;
      });
      none.hidden = n > 0;
      list.hidden = false;
    });
  }

  /* Повторяющиеся строки (телефоны, часы работы…) */
  $$('[data-rep]').forEach(function (rep) {
    var tpl = $('template', rep);
    var max = parseInt(rep.getAttribute('data-max'), 10) || 10;
    var addBtn = $('[data-act="rep-add"]', rep);
    var sync = function () {
      var rows = $$('[data-rep-row]', rep).filter(function (r) { return !r.closest('template'); });
      rows.forEach(function (row, i) {
        $$('[data-n]', row).forEach(function (inp) {
          inp.name = rep.getAttribute('data-rep') + '[' + i + '][' + inp.getAttribute('data-n') + ']';
        });
      });
      addBtn.hidden = rows.length >= max;
    };
    rep.addEventListener('click', function (e) {
      var del = e.target.closest('[data-act="rep-del"]');
      if (del) { del.closest('[data-rep-row]').remove(); sync(); }
      if (e.target.closest('[data-act="rep-add"]')) {
        var node = tpl.content.firstElementChild.cloneNode(true);
        rep.insertBefore(node, tpl);
        sync();
        var first = $('input', node); if (first) first.focus();
      }
    });
    sync();
  });

  /* ---------- Редактор прайса ---------- */
  var form = $('[data-cat-form]');
  if (!form) return;

  var groupsBox = $('[data-groups]', form);
  var dirty = false;
  var dirtyMsg = $('[data-dirty-msg]', form);
  var setDirty = function () {
    if (dirty) return;
    dirty = true;
    dirtyMsg.textContent = 'Есть несохранённые изменения';
    $('[data-savebar]', form).classList.add('is-dirty');
  };
  form.addEventListener('input', setDirty);
  form.addEventListener('change', setDirty);
  window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

  var tpl = function (id) { return $('#' + id).content.firstElementChild.cloneNode(true); };
  var refreshItem = function (it) {
    var hasVars = $$('[data-var]', it).length > 0;
    $('[data-single]', it).hidden = hasVars;
    $('[data-varhint]', it).hidden = !hasVars;
    it.classList.toggle('is-hidden', $('[data-f="hidden"]', it).checked);
  };
  var move = function (el, dir) {
    if (dir < 0 && el.previousElementSibling) el.parentNode.insertBefore(el, el.previousElementSibling);
    if (dir > 0 && el.nextElementSibling) el.parentNode.insertBefore(el.nextElementSibling, el);
    el.scrollIntoView({ block: 'nearest' });
    setDirty();
  };

  form.addEventListener('click', function (e) {
    var b = e.target.closest('[data-act]');
    if (!b || !form.contains(b)) return;
    var act = b.getAttribute('data-act');
    var it = b.closest('[data-item]');
    var g = b.closest('[data-group]');
    if (act === 'more') {
      var more = $('[data-more]', it);
      more.hidden = !more.hidden;
      b.setAttribute('aria-expanded', String(!more.hidden));
      b.classList.toggle('is-on', !more.hidden);
    } else if (act === 'up' || act === 'down') {
      move(it, act === 'up' ? -1 : 1);
    } else if (act === 'del') {
      var name = $('[data-f="name"]', it).value || 'без названия';
      if (confirm('Удалить услугу «' + name + '»?')) { it.remove(); setDirty(); }
    } else if (act === 'add-var') {
      var v = tpl('tpl-var');
      $('[data-vars]', it).appendChild(v);
      refreshItem(it);
      $('input', v).focus();
      setDirty();
    } else if (act === 'del-var') {
      b.closest('[data-var]').remove();
      refreshItem(it);
      setDirty();
    } else if (act === 'add-item') {
      var ni = tpl('tpl-item');
      $('[data-items]', g).appendChild(ni);
      refreshItem(ni);
      $('[data-f="name"]', ni).focus();
      setDirty();
    } else if (act === 'g-up' || act === 'g-down') {
      move(g, act === 'g-up' ? -1 : 1);
    } else if (act === 'g-del') {
      var cnt = $$('[data-item]', g).length;
      if (confirm('Удалить подраздел «' + ($('[data-f="title"]', g).value || 'без названия') + '»' + (cnt ? ' и ' + cnt + ' услуг в нём' : '') + '?')) { g.remove(); setDirty(); }
    } else if (act === 'add-group') {
      var ng = tpl('tpl-group');
      groupsBox.appendChild(ng);
      $('[data-f="title"]', ng).focus();
      setDirty();
    } else if (act === 'bulk') {
      var pct = parseFloat($('[data-bulk-pct]', form).value);
      var round = parseInt($('[data-bulk-round]', form).value, 10) || 1;
      if (!pct || isNaN(pct)) { alert('Укажите процент, например 10 или −5.'); return; }
      var inputs = $$('[data-price]', groupsBox).filter(function (inp) {
        var row = inp.closest('[data-item]');
        return row && !row.hidden && !inp.closest('[hidden]');
      });
      var changed = 0;
      inputs.forEach(function (inp) {
        var val = parseFloat(String(inp.value).replace(',', '.').replace(/\s/g, ''));
        if (!val) return;
        var nv = Math.max(round, Math.round(val * (1 + pct / 100) / round) * round);
        if (nv !== val) { inp.value = nv; inp.classList.add('is-changed'); changed++; }
      });
      setDirty();
      alert('Пересчитано цен: ' + changed + '. Изменённые поля подсвечены. Проверьте и нажмите «Сохранить».');
    }
  });
  form.addEventListener('change', function (e) {
    if (e.target.matches('[data-f="hidden"]')) refreshItem(e.target.closest('[data-item]'));
  });

  /* Поиск внутри раздела */
  var cq = $('[data-cat-search]', form);
  cq.addEventListener('input', function () {
    var q = norm(cq.value.trim());
    $$('[data-item]', groupsBox).forEach(function (it) {
      var hay = norm($('[data-f="name"]', it).value + ' ' + $$('[data-var] [data-f="label"]', it).map(function (i) { return i.value; }).join(' '));
      it.hidden = q.length > 1 && hay.indexOf(q) === -1;
    });
    $$('[data-group]', groupsBox).forEach(function (g) {
      g.hidden = q.length > 1 && !$('[data-item]:not([hidden])', g);
    });
  });
  cq.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });

  /* Подсветка услуги из поиска (#id) */
  if (location.hash.length > 1) {
    var target = document.getElementById(location.hash.slice(1));
    if (target && target.matches('[data-item]')) {
      target.classList.add('is-target');
      var inp = $('[data-price]:not([hidden] *)', target) || $('[data-f="name"]', target);
      setTimeout(function () { target.scrollIntoView({ block: 'center' }); inp.focus(); }, 50);
    }
  }

  /* Сохранение: собираем форму в JSON */
  form.addEventListener('submit', function (e) {
    var val = function (root, f) { var el = $('[data-f="' + f + '"]', root); return el ? (el.type === 'checkbox' ? el.checked : el.value) : ''; };
    var cval = function (f) { var el = $('[data-c="' + f + '"]', form); return el ? (el.type === 'checkbox' ? el.checked : el.value) : ''; };
    var bad = null;
    var payload = {
      title: cval('title'), slug: cval('slug'), short: cval('short'), description: cval('description'),
      icon: cval('icon'), price_from: cval('price_from'), hidden: cval('hidden'),
      groups: $$('[data-group]', groupsBox).map(function (g) {
        return {
          id: g.getAttribute('data-id'), title: val(g, 'title'), note: $('.g__note', g).value, extra: val(g, 'extra'),
          items: $$('[data-item]', g).map(function (it) {
            var price = $('.it__main [data-f="price"]', it).value.trim();
            var vars = $$('[data-var]', it).map(function (v) { return { label: val(v, 'label'), price: $('[data-f="price"]', v).value.trim() }; });
            [price].concat(vars.map(function (v) { return v.price; })).forEach(function (p) {
              if (p && isNaN(parseFloat(p.replace(',', '.').replace(/\s/g, '')))) bad = bad || it;
            });
            return {
              id: it.getAttribute('data-id'), name: val(it, 'name'), price: price, variants: vars,
              note: val(it, 'note'), duration: val(it, 'duration'), unit: val(it, 'unit'), from: val(it, 'from'), hidden: val(it, 'hidden')
            };
          })
        };
      })
    };
    if (bad) {
      e.preventDefault();
      alert('В цене должны быть только цифры. Проверьте услугу «' + val(bad, 'name') + '».');
      bad.scrollIntoView({ block: 'center' });
      return;
    }
    form.payload.value = JSON.stringify(payload);
    dirty = false;
  });
})();
