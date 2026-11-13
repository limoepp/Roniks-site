/* Сайт салона «Роникс»: меню, запись, поиск по прайсу. Без зависимостей. */
(function () {
  'use strict';

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

  /* ---------- Поиск: нормализация и синонимы ---------- */
  var ALT = {
    'покрас': ['окраш', 'тонир', 'краситель'],
    'краск': ['окраш', 'краситель'],
    'шеллак': ['shellac'], 'шилак': ['shellac'], 'шелак': ['shellac'],
    'гельлак': ['гель лак'],
    'ногт': ['маникюр', 'педикюр'],
    'ножн': ['педикюр'],
    'ступн': ['стоп', 'педикюр'],
    'загар': ['солярий', 'коллагенарий'],
    'солярий': ['солярий', 'коллагенарий'],
    'лебел': ['lebel'], 'матрикс': ['matrix'], 'грейми': ['greymy'], 'игора': ['igora'],
    'спа': ['spa'],
    'кератин': ['keratin', 'кератин'],
    'седин': ['окраш', 'тонир'],
    'омбр': ['омбре'], 'шату': ['шатуш'],
    'балаяж': ['шатуш', 'омбре', 'мелир'],
    'коллаген': ['коллаген', 'hyperfill'],
    'волос': ['волос', 'стрижк', 'окраш', 'уклад'],
    'спин': ['спин', 'воротник'],
    'шея': ['шейно'], 'шеи': ['шейно'],
    'бород': ['бород'],
    'усы': ['усов'],
    'реснич': ['ресниц']
  };

  function norm(s) {
    return (s || '').toLowerCase().replace(/ё/g, 'е').replace(/[«»"'’`.,:;!?()]/g, ' ').replace(/[\-–—/]+/g, ' ').replace(/\s+/g, ' ').trim();
  }
  function stem(t) {
    if (t.length >= 7) return t.slice(0, t.length - 2);
    if (t.length >= 5) return t.slice(0, t.length - 1);
    return t;
  }
  function buildQuery(q) {
    var words = norm(q).split(' ').filter(function (w) { return w.length > 1; });
    return words.map(function (w) {
      var opts = [stem(w)];
      Object.keys(ALT).forEach(function (k) {
        if (w.indexOf(k) === 0 || k.indexOf(w) === 0 && w.length >= 4) opts = opts.concat(ALT[k]);
      });
      return opts;
    });
  }
  function matches(hay, query) {
    for (var i = 0; i < query.length; i++) {
      var ok = false;
      for (var j = 0; j < query[i].length; j++) {
        if (hay.indexOf(query[i][j]) !== -1) { ok = true; break; }
      }
      if (!ok) return false;
    }
    return true;
  }
  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function highlight(text, query) {
    var safe = escapeHtml(text);
    var stems = [];
    query.forEach(function (opts) { stems.push(opts[0]); });
    stems = stems.filter(function (s) { return s.length > 1; });
    if (!stems.length) return safe;
    // подсвечиваем слово целиком, а не обрезанную основу
    var re = new RegExp('(' + stems.map(function (s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&').replace(/е/g, '[её]') + '[^\\s()«»,.:;—–\\-]*'; }).join('|') + ')', 'gi');
    return safe.replace(re, '<mark>$1</mark>');
  }

  /* ---------- Меню на телефоне ---------- */
  var burger = $('.burger');
  var nav = $('#site-nav');
  if (burger && nav) {
    burger.addEventListener('click', function () {
      var open = !nav.classList.contains('is-open');
      nav.classList.toggle('is-open', open);
      burger.setAttribute('aria-expanded', String(open));
      burger.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
      document.body.style.overflow = open ? 'hidden' : '';
    });
    nav.addEventListener('click', function (e) {
      if (e.target.closest('a') && nav.classList.contains('is-open')) burger.click();
    });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      var mega = $('.nav__item--mega');
      if (mega && mega.contains(document.activeElement)) { document.activeElement.blur(); }
    }
  });

  /* ---------- Диалог записи ---------- */
  var dlg = $('#book');
  if (dlg) {
    var svcLine = $('[data-book-service]', dlg);
    var svcInput = $('[data-service-input]', dlg);
    var waLink = $('[data-wa]', dlg);
    var form = $('[data-lead-form]', dlg);
    var formHTML = form ? form.innerHTML : '';
    var waBase = waLink ? waLink.getAttribute('href').split('?')[0] : '';

    var openBook = function (service) {
      service = (service || '').trim();
      if (svcLine) {
        svcLine.hidden = !service;
        svcLine.textContent = service ? 'Услуга: ' + service : '';
      }
      if (svcInput) svcInput.value = service;
      if (waLink) {
        var text = 'Здравствуйте! Хочу записаться' + (service ? ': ' + service : '');
        waLink.setAttribute('href', waBase + '?text=' + encodeURIComponent(text));
      }
      if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', '');
    };

    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-book]');
      if (btn) { e.preventDefault(); openBook(btn.getAttribute('data-book')); }
    });
    dlg.addEventListener('click', function (e) {
      if (e.target === dlg || e.target.closest('[data-close]')) dlg.close();
    });

    var bindForm = function () {
      if (!form) return;
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = $('[data-form-msg]', form);
        var btn = form.querySelector('button[type="submit"]');
        $$('[data-error]', form).forEach(function (el) { el.textContent = ''; });
        $$('[aria-invalid]', form).forEach(function (el) { el.removeAttribute('aria-invalid'); });
        msg.textContent = ''; msg.className = 'form-msg';

        var errors = {};
        if (!form.name.value.trim()) errors.name = 'Укажите, как к вам обращаться';
        var d = form.phone.value.replace(/\D/g, '');
        if (d.length < 10 || d.length > 12) errors.phone = 'Проверьте номер: нужно 10–11 цифр';
        if (!form.consent.checked) errors.consent = 'Без согласия мы не сможем перезвонить';
        if (showErrors(errors)) return;

        btn.disabled = true; btn.textContent = 'Отправляем…';
        fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json' } })
          .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
          .then(function (res) {
            if (res.ok) {
              form.innerHTML = '<div class="book__done" tabindex="-1"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg><h3>Заявка отправлена</h3><p>Администратор перезвонит в рабочее время и подберёт удобное время.</p><button class="btn btn--ghost" type="button" data-close>Закрыть</button></div>';
              form.querySelector('.book__done').focus();
              return;
            }
            if (res.errors) { showErrors(res.errors); }
            msg.textContent = res.error || (res.errors ? '' : 'Не получилось отправить. Позвоните нам или напишите в мессенджер.');
            msg.classList.add('is-error');
          })
          .catch(function () {
            msg.textContent = 'Нет связи с сайтом. Позвоните нам или напишите в мессенджер.';
            msg.classList.add('is-error');
          })
          .then(function () { if (btn.isConnected) { btn.disabled = false; btn.textContent = 'Жду звонка'; } });
      });

      function showErrors(errs) {
        var first = null;
        Object.keys(errs).forEach(function (k) {
          var slot = form.querySelector('[data-error="' + k + '"]');
          if (slot) slot.textContent = errs[k];
          var input = form.elements[k];
          if (input && input.setAttribute) {
            input.setAttribute('aria-invalid', 'true');
            if (slot) { slot.id = 'err-' + k; input.setAttribute('aria-describedby', slot.id); }
            if (!first) first = input;
          }
        });
        if (first) first.focus();
        return !!first;
      }
    };
    bindForm();
    dlg.addEventListener('close', function () {
      if (form && form.querySelector('.book__done')) {
        form.innerHTML = formHTML;
        bindForm();
      }
    });
  }

  /* ---------- Поиск на главной ---------- */
  var finder = $('[data-finder]');
  var indexEl = $('#svc-index');
  if (finder && indexEl) {
    var index = [];
    try { index = JSON.parse(indexEl.textContent).map(function (x) { x.h = norm(x.s); return x; }); } catch (err) { index = []; }
    var input = $('.finder__input', finder);
    var box = $('[data-finder-results]', finder);
    var pricesUrl = finder.getAttribute('action');
    var timer;

    var render = function () {
      var q = input.value.trim();
      if (q.length < 2) { box.hidden = true; box.innerHTML = ''; return; }
      var query = buildQuery(q);
      var found = index.filter(function (x) { return matches(x.h, query); });
      var html = '';
      if (!found.length) {
        html = '<p class="fr__empty">По запросу «' + escapeHtml(q) + '» ничего не нашли. Попробуйте другое слово или позвоните администратору.</p>';
      } else {
        found.slice(0, 7).forEach(function (x) {
          html += '<div class="fr__item"><a class="fr__name" href="' + escapeHtml(x.u) + '">' + highlight(x.n, query) + '</a>' +
            '<span class="fr__price">' + escapeHtml(x.p) + '</span>' +
            '<span class="fr__where">' + escapeHtml(x.c + ' — ' + x.g) + '</span>' +
            (x.b ? '<button class="fr__book" type="button" data-book="' + escapeHtml(x.b) + '">Записаться</button>' : '<span></span>') + '</div>';
        });
        html += '<a class="fr__more" href="' + escapeHtml(pricesUrl + '?q=' + encodeURIComponent(q)) + '">' +
          (found.length > 7 ? 'Все ' + found.length + ' результатов в прайсе' : 'Открыть в прайсе') + '</a>';
      }
      box.innerHTML = html;
      box.hidden = false;
    };

    input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(render, 80); });
    input.addEventListener('focus', function () { if (input.value.trim().length >= 2) render(); });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { box.hidden = true; }
      if (e.key === 'ArrowDown') { var first = $('a, button', box); if (first) { e.preventDefault(); first.focus(); } }
    });
    $$('[data-chip]', finder).forEach(function (chip) {
      chip.addEventListener('click', function () {
        input.value = chip.getAttribute('data-chip');
        render();
        input.focus();
      });
    });
    document.addEventListener('click', function (e) {
      if (!finder.contains(e.target)) box.hidden = true;
    });
  }

  /* ---------- Прайс: фильтр и поиск ---------- */
  var prices = $('[data-prices]');
  var search = $('[data-price-search]');
  if (prices && search) {
    var rows = $$('[data-row]', prices).map(function (row) {
      var title = $('.row__title', row);
      return { el: row, hay: norm(row.getAttribute('data-search')), cat: row.closest('[data-cat]').getAttribute('data-cat'), title: title, text: title.textContent };
    });
    var groups = $$('[data-group]', prices);
    var cats = $$('[data-cat]', prices);
    var chips = $$('[data-filter]');
    var counter = $('[data-count]');
    var empty = $('[data-empty]');
    var clearBtns = $$('[data-search-clear]');
    var activeCat = '';
    var total = rows.length;

    var apply = function () {
      var q = search.value.trim();
      var query = buildQuery(q);
      var shown = 0;
      rows.forEach(function (r) {
        var ok = (!activeCat || r.cat === activeCat) && (!query.length || matches(r.hay, query));
        r.el.hidden = !ok;
        r.title.innerHTML = ok && query.length ? highlight(r.text, query) : escapeHtml(r.text);
        if (ok) shown++;
      });
      groups.forEach(function (g) { g.hidden = !$('[data-row]:not([hidden])', g); });
      cats.forEach(function (c) { c.hidden = !$('[data-row]:not([hidden])', c); });
      empty.hidden = shown > 0;
      clearBtns.forEach(function (b) { b.hidden = !q && b.classList.contains('search__clear'); });
      counter.textContent = (query.length || activeCat)
        ? (shown ? 'Найдено: ' + shown + ' из ' + total : '')
        : total + ' услуг в прайсе';
      try {
        var u = new URL(location.href);
        if (q) u.searchParams.set('q', q); else u.searchParams.delete('q');
        history.replaceState(null, '', u.pathname + u.search + u.hash);
      } catch (err) { /* старый браузер */ }
    };

    var t2;
    search.addEventListener('input', function () { clearTimeout(t2); t2 = setTimeout(apply, 60); });
    search.addEventListener('keydown', function (e) { if (e.key === 'Escape') { search.value = ''; apply(); } });
    clearBtns.forEach(function (b) {
      b.addEventListener('click', function () {
        search.value = ''; activeCat = '';
        chips.forEach(function (c) { var on = c.getAttribute('data-filter') === ''; c.classList.toggle('is-active', on); c.setAttribute('aria-pressed', String(on)); });
        apply(); search.focus();
      });
    });
    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        activeCat = chip.getAttribute('data-filter');
        chips.forEach(function (c) { var on = c === chip; c.classList.toggle('is-active', on); c.setAttribute('aria-pressed', String(on)); });
        apply();
        var top = prices.getBoundingClientRect().top + window.scrollY - 160;
        if (window.scrollY > top) window.scrollTo({ top: top });
      });
    });
    apply();
  }

  /* ---------- Подсветка услуги по ссылке #id ---------- */
  var markTarget = function () {
    if (!location.hash || location.hash.length < 2) return;
    var el;
    try { el = document.getElementById(decodeURIComponent(location.hash.slice(1))); } catch (err) { return; }
    if (el && el.classList.contains('row')) {
      el.classList.remove('is-target'); void el.offsetWidth; el.classList.add('is-target');
    }
  };
  window.addEventListener('hashchange', markTarget);
  markTarget();

  /* ---------- Карта по клику ---------- */
  $$('[data-map]').forEach(function (wrap) {
    var btn = $('[data-map-load]', wrap);
    if (!btn) return;
    btn.addEventListener('click', function () {
      var f = document.createElement('iframe');
      f.src = wrap.getAttribute('data-src');
      f.title = 'Салон на Яндекс Картах';
      f.loading = 'lazy';
      f.setAttribute('allowfullscreen', '');
      wrap.appendChild(f);
      btn.remove();
    });
  });

  /* ---------- Cookie ---------- */
  var cookie = $('#cookie');
  if (cookie) {
    var seen = false;
    try { seen = localStorage.getItem('roniks_cookie_ok') === '1'; } catch (err) { seen = false; }
    if (!seen) cookie.hidden = false;
    $('#cookie-ok').addEventListener('click', function () {
      cookie.hidden = true;
      try { localStorage.setItem('roniks_cookie_ok', '1'); } catch (err) { /* приватный режим */ }
    });
  }
})();
