/* Lavanya storefront scripts (no libraries needed) */
(function () {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));

  /* ---------- Logo loader ---------- */
  const loader = $('#loader');
  if (loader) {
    document.body.classList.add('loading');
    let seen = false;
    try { seen = sessionStorage.getItem('lv_loader') === '1'; sessionStorage.setItem('lv_loader', '1'); } catch (e) {}
    const full = parseInt(loader.dataset.time, 10) || 1400;
    const minTime = (seen && loader.dataset.every !== '1') ? 250 : full;
    const start = Date.now();
    const hide = () => {
      setTimeout(() => { loader.classList.add('done'); document.body.classList.remove('loading'); revealCheck(); }, Math.max(0, minTime - (Date.now() - start)));
    };
    if (document.readyState === 'complete') hide(); else window.addEventListener('load', hide);
    setTimeout(hide, Math.max(3000, full + 1000)); // safety (e.g. slow fonts)
  }

  /* ---------- Toasts ---------- */
  function toast(msg, type = 'success') {
    const wrap = $('#toasts'); if (!wrap) return;
    const t = document.createElement('div');
    t.className = 'toast toast-' + type; t.textContent = msg;
    wrap.appendChild(t); autoHide(t);
  }
  function autoHide(t) { setTimeout(() => { t.classList.add('hide'); setTimeout(() => t.remove(), 450); }, 3800); }
  $$('#toasts .toast').forEach(autoHide);

  /* ---------- Header ---------- */
  const header = $('#siteHeader'), toTop = $('#toTop');
  const onScroll = () => {
    const y = window.scrollY;
    header && header.classList.toggle('scrolled', y > 10);
    toTop && toTop.classList.toggle('show', y > 600);
  };
  window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
  toTop && toTop.addEventListener('click', e => { e.preventDefault(); window.scrollTo({ top: 0, behavior: 'smooth' }); });

  /* ---------- Mobile drawer menu (left, 50% width) ---------- */
  const nav = $('#mainNav'), overlay = $('#navOverlay'), burger = $('#burger');
  const isMobile = () => window.matchMedia('(max-width: 920px)').matches;
  let scrollY = 0;
  const openNav = () => {
    scrollY = window.scrollY;
    nav.classList.add('open'); overlay.classList.add('show'); burger.classList.add('is-open');
    document.body.classList.add('nav-locked'); document.body.style.top = -scrollY + 'px';
    burger.setAttribute('aria-expanded', 'true');
  };
  const closeNav = () => {
    if (!nav.classList.contains('open')) return;
    nav.classList.remove('open'); overlay.classList.remove('show'); burger.classList.remove('is-open');
    document.body.classList.remove('nav-locked'); document.body.style.top = ''; window.scrollTo(0, scrollY);
    burger.setAttribute('aria-expanded', 'false');
  };
  burger && burger.addEventListener('click', () => nav.classList.contains('open') ? closeNav() : openNav());
  overlay && overlay.addEventListener('click', closeNav);
  $('#navClose') && $('#navClose').addEventListener('click', closeNav);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeNav(); });
  window.addEventListener('resize', () => { if (!isMobile()) closeNav(); });
  // categories accordion inside the drawer
  $$('.has-drop > .drop-toggle').forEach(a => a.addEventListener('click', e => {
    if (!isMobile()) return;
    e.preventDefault();
    const box = a.parentElement, dd = $('.dropdown', box);
    const open = !box.classList.contains('open');
    box.classList.toggle('open', open);
    dd.style.maxHeight = open ? dd.scrollHeight + 'px' : '0px';
  }));
  // swipe left to close
  if (nav) {
    let x0 = null, dx = 0;
    nav.addEventListener('touchstart', e => { x0 = e.touches[0].clientX; dx = 0; nav.style.transition = 'none'; }, { passive: true });
    nav.addEventListener('touchmove', e => {
      if (x0 === null) return; dx = Math.min(0, e.touches[0].clientX - x0);
      nav.style.transform = `translateX(${dx}px)`;
    }, { passive: true });
    nav.addEventListener('touchend', () => {
      nav.style.transition = ''; nav.style.transform = '';
      if (dx < -60) closeNav(); x0 = null;
    });
  }

  const sBtn = $('#searchToggle'), sBar = $('#searchBar');
  sBtn && sBtn.addEventListener('click', () => { sBar.classList.toggle('open'); if (sBar.classList.contains('open')) setTimeout(() => $('input', sBar).focus(), 200); });

  /* ---------- Tabs ---------- */
  $$('[data-tabs]').forEach(group => {
    const scope = group.parentElement;
    $$('.tab', group).forEach(btn => btn.addEventListener('click', () => {
      $$('.tab', group).forEach(b => b.classList.remove('on'));
      btn.classList.add('on');
      $$(':scope > .tab-pane', scope).forEach(p => p.classList.toggle('on', p.id === btn.dataset.tab));
      revealCheck();
    }));
  });
  $$('[data-open-tab]').forEach(a => a.addEventListener('click', () => {
    const b = $(`.tab[data-tab="${a.dataset.openTab}"]`); b && b.click();
  }));

  /* ---------- Reveal on scroll ---------- */
  let io = null;
  if ('IntersectionObserver' in window) {
    io = new IntersectionObserver(entries => entries.forEach(en => {
      if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
    }), { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
  }
  function revealCheck() {
    $$('.reveal:not(.in)').forEach((el, i) => {
      el.style.transitionDelay = (i % 4) * 70 + 'ms';
      io ? io.observe(el) : el.classList.add('in');
    });
  }
  if (!loader) revealCheck();

  /* ---------- Hero slider ---------- */
  const slides = $$('.hero-slide'), dots = $$('#heroDots button');
  if (slides.length > 1) {
    let i = 0, timer;
    const go = n => { slides[i].classList.remove('on'); dots[i] && dots[i].classList.remove('on'); i = (n + slides.length) % slides.length; slides[i].classList.add('on'); dots[i] && dots[i].classList.add('on'); };
    const ms = parseInt((document.querySelector('.arch-main') || {}).dataset?.interval || '4200', 10) || 4200;
    const play = () => { clearInterval(timer); timer = setInterval(() => go(i + 1), ms); };
    dots.forEach((d, n) => d.addEventListener('click', () => { go(n); play(); }));
    play();
  }

  /* ---------- Countdown ---------- */
  $$('[data-countdown]').forEach(el => {
    const end = new Date(el.dataset.countdown).getTime();
    const pad = n => String(n).padStart(2, '0');
    const tick = () => {
      let s = Math.max(0, Math.floor((end - Date.now()) / 1000));
      $('[data-d]', el).textContent = pad(Math.floor(s / 86400)); s %= 86400;
      $('[data-h]', el).textContent = pad(Math.floor(s / 3600)); s %= 3600;
      $('[data-m]', el).textContent = pad(Math.floor(s / 60));
      $('[data-s]', el).textContent = pad(s % 60);
    };
    tick(); setInterval(tick, 1000);
  });

  /* ---------- Quantity steppers ---------- */
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-qty]'); if (!b) return;
    const input = $('input', b.parentElement);
    const min = parseInt(input.min || '0', 10), max = parseInt(input.max || '999', 10);
    input.value = Math.min(max, Math.max(min, (parseInt(input.value, 10) || 0) + parseInt(b.dataset.qty, 10)));
    input.dispatchEvent(new Event('change', { bubbles: true }));
  });
  $$('[data-autosubmit]').forEach(i => i.addEventListener('change', () => i.form.submit()));

  /* ---------- Ajax add to cart ---------- */
  document.addEventListener('submit', async e => {
    const form = e.target;
    if (!form.classList.contains('js-add-cart')) return;
    if (e.submitter && e.submitter.hasAttribute('data-no-ajax')) {
      if (!$('[name=variant_id]', form).value) { e.preventDefault(); toast('Please select a size / colour first.', 'error'); }
      return;
    }
    e.preventDefault();
    if (!$('[name=variant_id]', form).value) { toast('Please select a size / colour first.', 'error'); return; }
    const btn = e.submitter || $('button', form);
    btn && btn.classList.add('loading');
    try {
      const res = await fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();
      toast(data.message, data.ok ? 'success' : 'error');
      $$('[data-cart-count]').forEach(c => { c.textContent = data.count; c.hidden = !data.count; c.classList.remove('bump'); void c.offsetWidth; c.classList.add('bump'); });
    } catch (err) { form.submit(); }
    btn && btn.classList.remove('loading');
  });

  /* ---------- Product page: variants ---------- */
  const pd = $('#pdForm');
  if (pd) {
    const variants = JSON.parse(pd.dataset.variants || '[]');
    const low = parseInt(pd.dataset.low || '5', 10);
    const groups = {}; $$('[data-opt]', pd).forEach(g => groups[g.dataset.opt] = g);
    const sel = {};
    Object.keys(groups).forEach(k => { const on = $('.opt.on', groups[k]); if (on) sel[k] = on.dataset.value; });
    const vId = $('#variantId'), line = $('#stockLine'), qty = $('#qtyInput'), add = $('#addBtn'), buy = $('#buyBtn');
    const match = (v, s) => Object.keys(groups).every(k => !s[k] || (v[k] || '') === s[k]);

    function refresh() {
      // mark unavailable options
      Object.keys(groups).forEach(k => {
        $$('.opt', groups[k]).forEach(o => {
          const test = Object.assign({}, sel, { [k]: o.dataset.value });
          o.classList.toggle('na', !variants.some(v => match(v, test) && v.stock > 0));
        });
      });
      const complete = Object.keys(groups).every(k => sel[k]);
      const v = complete ? variants.find(x => match(x, sel)) : (variants.length === 1 ? variants[0] : null);
      if (!v) { vId.value = ''; line.className = 'stock-line stock-wait'; line.textContent = 'Select options to see availability'; return; }
      vId.value = v.id; qty.max = Math.max(1, v.stock);
      if (+qty.value > v.stock) qty.value = Math.max(1, v.stock);
      const out = v.stock <= 0;
      add.disabled = buy.disabled = out;
      line.className = 'stock-line stock-' + (out ? 'out' : v.stock <= low ? 'low' : 'in');
      line.textContent = out ? 'Out of stock in this option' : v.stock <= low ? `Hurry! Only ${v.stock} left` : `In stock (${v.stock} available)`;
    }
    Object.keys(groups).forEach(k => $$('.opt', groups[k]).forEach(o => o.addEventListener('click', () => {
      $$('.opt', groups[k]).forEach(x => x.classList.remove('on'));
      o.classList.add('on'); sel[k] = o.dataset.value;
      const c = $(`[data-chosen="${k}"]`); if (c) c.textContent = o.dataset.value;
      refresh();
    })));
    refresh();
  }

  /* gallery + hover zoom */
  const main = $('#pdMain'), mainImg = $('#pdMainImg');
  $$('.pd-thumbs button').forEach(b => b.addEventListener('click', () => {
    $$('.pd-thumbs button').forEach(x => x.classList.remove('on')); b.classList.add('on'); mainImg.src = b.dataset.src;
  }));
  if (main && window.matchMedia('(hover:hover)').matches) {
    main.addEventListener('mousemove', e => {
      const r = main.getBoundingClientRect();
      mainImg.style.transformOrigin = `${(e.clientX - r.left) / r.width * 100}% ${(e.clientY - r.top) / r.height * 100}%`;
      mainImg.style.transform = 'scale(1.8)';
    });
    main.addEventListener('mouseleave', () => { mainImg.style.transform = ''; });
  }

  /* ---------- Shop filters (mobile drawer) ---------- */
  const filters = $('#filters'), fOverlay = $('.filter-overlay');
  $$('[data-open-filters]').forEach(b => b.addEventListener('click', () => { filters.classList.add('open'); fOverlay.classList.add('show'); }));
  $$('[data-close-filters]').forEach(b => b.addEventListener('click', () => { filters.classList.remove('open'); fOverlay.classList.remove('show'); }));

  /* ---------- Checkout ---------- */
  const co = $('#checkoutForm');
  if (co) {
    const cur = co.dataset.currency + ' ';
    const fmt = n => cur + Math.round(n).toLocaleString('en-US');
    const update = () => {
      const zone = ($('input[name=zone]:checked', co) || {}).value || 'inside';
      const ship = parseFloat(zone === 'inside' ? co.dataset.shipIn : co.dataset.shipOut);
      $('#shipAmt').textContent = ship ? fmt(ship) : 'Free';
      $('#totalAmt').textContent = fmt(parseFloat(co.dataset.subtotal) + ship);
      const pay = ($('input[name=payment]:checked', co) || {}).value || 'cod';
      const info = $('#payInfo');
      info.hidden = pay === 'cod';
      if (pay !== 'cod') {
        const num = $('.pay-num', info);
        num.textContent = num.dataset['num' + pay.charAt(0).toUpperCase() + pay.slice(1)] || '';
        $('[data-pay-name]', info).textContent = pay === 'bkash' ? 'bKash' : 'Nagad';
      }
    };
    co.addEventListener('change', update); update();
  }
})();

/* show / hide password */
document.querySelectorAll('.pw-toggle').forEach(b => b.addEventListener('click', () => {
  const i = b.previousElementSibling; i.type = i.type === 'password' ? 'text' : 'password';
}));

/* category row arrows */
(function () {
  const row = document.getElementById('catRow'); if (!row) return;
  const prev = document.querySelector('.cat-arrow.prev'), next = document.querySelector('.cat-arrow.next');
  if (!prev || !next) return;
  const update = () => {
    prev.classList.toggle('show', row.scrollLeft > 5);
    next.classList.toggle('show', row.scrollLeft + row.clientWidth < row.scrollWidth - 5);
  };
  const step = () => Math.max(200, row.clientWidth * 0.7);
  prev.addEventListener('click', () => row.scrollBy({ left: -step(), behavior: 'smooth' }));
  next.addEventListener('click', () => row.scrollBy({ left: step(), behavior: 'smooth' }));
  row.addEventListener('scroll', update, { passive: true });
  window.addEventListener('resize', update);
  update(); setTimeout(update, 600);

  // automatic sliding: one tile at a time, back to the start at the end; pauses on hover / touch
  const ms = parseInt(row.dataset.autoslide || '0', 10);
  if (ms > 0 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    let paused = false, timer;
    const tile = () => { const c = row.querySelector('.cat-card'); return c ? c.getBoundingClientRect().width + parseFloat(getComputedStyle(row).columnGap || 16) : 180; };
    const tick = () => {
      if (paused || row.scrollWidth <= row.clientWidth + 5) return;
      if (row.scrollLeft + row.clientWidth >= row.scrollWidth - 5) row.scrollTo({ left: 0, behavior: 'smooth' });
      else row.scrollBy({ left: tile(), behavior: 'smooth' });
    };
    const start = () => { clearInterval(timer); timer = setInterval(tick, ms); };
    const scroller = row.parentElement;
    scroller.addEventListener('mouseenter', () => paused = true);
    scroller.addEventListener('mouseleave', () => paused = false);
    row.addEventListener('touchstart', () => { paused = true; }, { passive: true });
    row.addEventListener('touchend', () => { setTimeout(() => paused = false, 4000); }, { passive: true });
    [prev, next].forEach(b => b.addEventListener('click', start));
    start();
  }
})();


/* wishlist hearts */
document.addEventListener('click', async e => {
  const b = e.target.closest('[data-wish]'); if (!b) return;
  e.preventDefault(); e.stopPropagation();
  if (b.classList.contains('busy')) return;
  b.classList.add('busy');
  const fd = new FormData(); fd.append('csrf', window.SHOP.csrf); fd.append('product_id', b.dataset.wish);
  try {
    const r = await fetch(window.SHOP.wishUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const d = await r.json();
    document.querySelectorAll(`[data-wish="${b.dataset.wish}"]`).forEach(x => { x.classList.toggle('on', d.on); x.classList.remove('pop'); void x.offsetWidth; if (d.on) x.classList.add('pop'); });
    document.querySelectorAll('[data-wish-count]').forEach(c => { c.textContent = d.count; c.hidden = !d.count; c.classList.remove('bump'); void c.offsetWidth; c.classList.add('bump'); });
    const wrap = document.getElementById('toasts');
    if (wrap) { const t = document.createElement('div'); t.className = 'toast toast-success'; t.textContent = d.message; wrap.appendChild(t);
      setTimeout(() => { t.classList.add('hide'); setTimeout(() => t.remove(), 450); }, 2600); }
    if (!d.on && document.querySelector('[data-wish-page]')) { const card = b.closest('.p-card'); card && (card.style.transition = '.3s', card.style.opacity = '0', setTimeout(() => card.remove(), 300)); }
  } catch (err) {}
  b.classList.remove('busy');
});

/* liquid navbar hover (desktop) */
(function () {
  const nav = document.getElementById('mainNav'); if (!nav) return;
  const lq = nav.querySelector('.nav-liquid'); if (!lq) return;
  const items = [...nav.querySelectorAll(':scope > a:not(.nav-mobile-only), :scope > .has-drop > a')];
  const desktop = () => window.matchMedia('(min-width: 921px)').matches;
  let cur = null, lastL = null, hideT;
  const place = (el, instant) => {
    if (!el || !desktop()) return;
    const n = nav.getBoundingClientRect(), r = el.getBoundingClientRect();
    const l = r.left - n.left, w = r.width, top = r.top - n.top, h = r.height;
    const dist = lastL === null ? 0 : Math.abs(l - lastL);
    const right = lastL !== null && l > lastL;
    // the leading edge moves fast, the trailing edge follows slower -> liquid stretch
    const fast = Math.min(.34, .2 + dist / 2600), slow = fast + Math.min(.42, .16 + dist / 900);
    const rr = n.width - (l + w);
    // moving right: the right edge leads and the left edge follows (and vice-versa) -> water-like stretch
    lq.style.setProperty('--lq-dl', (instant ? 0 : (right ? slow : fast)) + 's');
    lq.style.setProperty('--lq-dr', (instant ? 0 : (right ? fast : slow)) + 's');
    lq.style.setProperty('--lq-l', l + 'px'); lq.style.setProperty('--lq-r', rr + 'px');
    lq.style.setProperty('--lq-top', top + 'px'); lq.style.setProperty('--lq-h', h + 'px');
    // a little droplet that trails behind and merges back in
    const ds = Math.min(h * .55, 22);
    lq.style.setProperty('--lq-dsz', (dist > 20 ? ds : 0) + 'px');
    lq.style.setProperty('--lq-dx', (l + w / 2 - ds / 2) + 'px');
    clearTimeout(lq._dropT); lq._dropT = setTimeout(() => lq.style.setProperty('--lq-dsz', '0px'), 650); // drop merges back
    if (dist > 20) { lq.classList.remove('wobble'); void lq.offsetWidth; lq.classList.add('wobble'); setTimeout(() => lq.classList.remove('wobble'), 260); }
    lq.classList.add('show');
    items.forEach(i => i.classList.toggle('lq-on', i === el));
    lastL = l; cur = el;
  };
  const home = () => items.find(i => i.classList.contains('active'));
  items.forEach(el => {
    el.addEventListener('mouseenter', () => { clearTimeout(hideT); place(el); });
    el.addEventListener('click', e => {
      if (!desktop()) return;
      const n = nav.getBoundingClientRect(), s = Math.max(el.offsetWidth, 60) * 1.6;
      const rp = document.createElement('span'); rp.className = 'lq-ripple';
      rp.style.cssText = `left:${e.clientX - n.left}px;top:${e.clientY - n.top}px;width:${s}px;height:${s}px`;
      nav.appendChild(rp); setTimeout(() => rp.remove(), 750);
    });
  });
  nav.addEventListener('mouseleave', () => {
    hideT = setTimeout(() => {
      const a = home();
      if (a) place(a); else { lq.classList.remove('show'); items.forEach(i => i.classList.remove('lq-on')); }
    }, 120);
  });
  const init = () => { lastL = null; const a = home(); if (a) place(a, true); else lq.classList.remove('show'); };
  window.addEventListener('resize', () => { if (desktop()) init(); });
  if (document.readyState === 'complete') init(); else window.addEventListener('load', init);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(init);
})();
