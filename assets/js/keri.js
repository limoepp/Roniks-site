/* Small, manual banner carousel. No autoplay or external dependencies. */
(function () {
  'use strict';
  var banner = document.querySelector('[data-banner]');
  if (!banner) return;
  var slides = Array.from(banner.querySelectorAll('[data-slide]'));
  var dots = Array.from(banner.querySelectorAll('[data-slide-to]'));
  var current = 0;
  function show(index) {
    current = (index + slides.length) % slides.length;
    slides.forEach(function (slide, i) { slide.hidden = i !== current; });
    dots.forEach(function (dot, i) {
      dot.setAttribute('aria-pressed', String(i === current));
    });
  }
  banner.addEventListener('click', function (event) {
    var dot = event.target.closest('[data-slide-to]');
    if (dot) show(Number(dot.dataset.slideTo));
    if (event.target.closest('[data-slide-next]')) show(current + 1);
    if (event.target.closest('[data-slide-prev]')) show(current - 1);
  });
})();
