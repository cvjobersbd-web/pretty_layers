/* Lavanya admin scripts */
(function () {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));

  // sidebar on mobile
  const side = $('#aSide'), ov = $('#aOverlay');
  $('#aBurger') && $('#aBurger').addEventListener('click', () => { side.classList.add('open'); ov.classList.add('show'); });
  ov && ov.addEventListener('click', () => { side.classList.remove('open'); ov.classList.remove('show'); });

  // auto-hide alerts
  $$('.a-alert-success').forEach(a => setTimeout(() => { a.style.opacity = '0'; setTimeout(() => a.remove(), 400); }, 4500));

  // image preview on upload
  $$('[data-preview]').forEach(box => {
    const input = $('input[type=file]', box), img = $('img', box);
    input.addEventListener('change', () => {
      const f = input.files[0]; if (!f) return;
      const r = new FileReader(); r.onload = e => { img.src = e.target.result; box.classList.add('has'); }; r.readAsDataURL(f);
    });
  });

  // list chosen banner files
  const hn = $('#heroNew');
  hn && hn.addEventListener('change', () => {
    $('#heroNewList').textContent = hn.files.length ? hn.files.length + ' picture(s) selected — click "Save banner" to upload' : '';
  });

  // ---- variants table ----
  const table = $('#vTable');
  if (table) {
    const body = $('tbody', table), tpl = $('#vRowTpl');
    const total = () => { $('#vTotal').textContent = $$('.v-stock', body).reduce((s, i) => s + (parseInt(i.value, 10) || 0), 0); };
    const addRow = (size = '', color = '', stock = 0) => {
      const tr = tpl.content.firstElementChild.cloneNode(true);
      $('[name="v_size[]"]', tr).value = size; $('[name="v_color[]"]', tr).value = color; $('[name="v_stock[]"]', tr).value = stock;
      body.appendChild(tr); total(); return tr;
    };
    $('#addRow').addEventListener('click', () => { const tr = addRow(); $('input[name="v_size[]"]', tr).focus(); });
    body.addEventListener('click', e => {
      if (!e.target.matches('[data-rm-row]')) return;
      if ($$('tr', body).length === 1) { alert('A product needs at least one variant.'); return; }
      e.target.closest('tr').remove(); total();
    });
    body.addEventListener('input', total); total();
    $('#genBtn').addEventListener('click', () => {
      const split = v => v.split(',').map(s => s.trim()).filter(Boolean);
      const sizes = split($('#genSizes').value), colors = split($('#genColors').value);
      const stock = parseInt($('#genStock').value, 10) || 0;
      if (!sizes.length && !colors.length) { alert('Type some sizes and/or colours separated by commas.'); return; }
      const existing = new Set($$('tr', body).map(tr => ($('[name="v_size[]"]', tr).value + '|' + $('[name="v_color[]"]', tr).value).toLowerCase()));
      // remove the single empty starter row
      $$('tr', body).forEach(tr => { if (!$('[name="v_size[]"]', tr).value && !$('[name="v_color[]"]', tr).value && $('[name="v_id[]"]', tr).value === '0') tr.remove(); });
      (sizes.length ? sizes : ['']).forEach(s => (colors.length ? colors : ['']).forEach(c => {
        if (!existing.has((s + '|' + c).toLowerCase())) addRow(s, c, stock);
      }));
      total();
      $('#genSizes').value = ''; $('#genColors').value = '';
      $('#genSizes').dispatchEvent(new Event('input')); $('#genColors').dispatchEvent(new Event('input'));
    });
    // forgot to press Generate? do it automatically on save
    table.closest('form').addEventListener('submit', () => {
      if ($('#genSizes').value.trim() || $('#genColors').value.trim()) $('#genBtn').click();
    });
  }

  // ---- theme editor ----
  const tf = $('#themeForm');
  if (tf) {
    const prev = $('#themePreview');
    const apply = () => $$('input[type=color]', tf).forEach(i => {
      prev.style.setProperty('--p-' + i.dataset.var, i.value);
      const code = i.parentElement.querySelector('code'); if (code) code.textContent = i.value.toUpperCase();
    });
    $$('input[name=theme_preset]', tf).forEach(r => r.addEventListener('change', () => {
      if (!r.dataset.colors) return;
      const c = JSON.parse(r.dataset.colors);
      ['primary', 'accent', 'dark', 'bg', 'text'].forEach(k => { $(`input[name=color_${k}]`, tf).value = c[k].toLowerCase(); });
      apply();
    }));
    $$('input[type=color]', tf).forEach(i => i.addEventListener('input', () => {
      const custom = $('input[value=custom]', tf); if (custom) custom.checked = true; apply();
    }));
    apply();
  }
})();

/* product-tab pickers in Home Setting */
document.querySelectorAll('[data-tab-ed]').forEach(ed => {
  const picker = ed.querySelector('.picker'), count = ed.querySelector('[data-count]');
  const sync = () => {
    const manual = ed.querySelector('input[type=radio][value=manual]').checked;
    picker.hidden = !manual; ed.classList.toggle('is-manual', manual);
    count.textContent = ed.querySelectorAll('.pick input:checked').length;
  };
  ed.addEventListener('change', sync);
  ed.querySelector('.pick-search').addEventListener('input', e => {
    const q = e.target.value.trim().toLowerCase();
    ed.querySelectorAll('.pick').forEach(p => { p.style.display = !q || p.dataset.name.includes(q) ? '' : 'none'; });
  });
  sync();
});

/* ---------- small toast ---------- */
function aToast(msg, bad) {
  let t = document.getElementById('aToast');
  if (!t) { t = document.createElement('div'); t.id = 'aToast'; t.className = 'a-toast'; document.body.appendChild(t); }
  t.textContent = msg; t.classList.toggle('bad', !!bad); t.classList.add('show');
  clearTimeout(t._h); t._h = setTimeout(() => t.classList.remove('show'), 2600);
}

/* ---------- tap-to-add chips (sizes / colours) ---------- */
document.querySelectorAll('[data-chips]').forEach(box => {
  const input = document.getElementById(box.dataset.chips);
  const list = () => input.value.split(',').map(s => s.trim()).filter(Boolean);
  const sync = () => { const l = list().map(s => s.toLowerCase()); box.querySelectorAll('button').forEach(b => b.classList.toggle('on', l.includes(b.textContent.trim().toLowerCase()))); };
  box.addEventListener('click', e => {
    const b = e.target.closest('button'); if (!b) return;
    const v = b.textContent.trim(); let l = list();
    l = l.some(x => x.toLowerCase() === v.toLowerCase()) ? l.filter(x => x.toLowerCase() !== v.toLowerCase()) : l.concat(v);
    input.value = l.join(', '); input.dispatchEvent(new Event('input', { bubbles: true })); sync();
  });
  input.addEventListener('input', sync); sync();
});

/* ---------- inventory: quick add panel ---------- */
(() => {
  const card = document.getElementById('qaCard'); if (!card) return;
  const open = document.getElementById('qaOpen'), close = document.getElementById('qaClose');
  open.addEventListener('click', () => { card.hidden = false; card.scrollIntoView({ behavior: 'smooth', block: 'start' }); card.querySelector('[name=name]').focus({ preventScroll: true }); });
  close.addEventListener('click', () => { card.hidden = true; });
  const f = card.querySelector('form'), sum = document.getElementById('qaSum');
  const n = v => v.split(',').map(s => s.trim()).filter(Boolean).length || 1;
  const upd = () => {
    const opts = n(f.sizes.value) * n(f.colors.value), each = parseInt(f.stock_each.value, 10) || 0;
    sum.innerHTML = `This will create <b>${opts}</b> option(s) × <b>${each}</b> pcs = <b>${opts * each}</b> pcs in stock.`;
  };
  f.addEventListener('input', upd); upd();
})();

/* ---------- inventory: one-click stock ---------- */
(() => {
  const list = document.querySelector('.inv-list'); if (!list) return;
  const csrf = list.dataset.csrf;
  const send = async (row, op, value) => {
    row.classList.add('busy');
    const body = new URLSearchParams({ ajax: '1', csrf, op, value, variant_id: row.dataset.id });
    try {
      const r = await fetch('inventory.php', { method: 'POST', body, credentials: 'same-origin' });
      const j = await r.json();
      const input = row.querySelector('input');
      if (j.ok) {
        input.value = j.stock; input.dataset.was = j.stock;
        row.querySelector('.iv-badge').innerHTML = j.badge;
        const tot = row.closest('.inv-card').querySelector('[data-total]'); if (tot) tot.textContent = j.total;
        row.querySelector('.iv-save').hidden = true;
        row.classList.remove('flash'); void row.offsetWidth; row.classList.add('flash');
        aToast('✓ Saved — ' + j.msg);
      } else { input.value = input.dataset.was; aToast(j.msg, true); }
    } catch (e) { aToast('Could not save — check your internet and try again.', true); }
    row.classList.remove('busy');
  };
  list.addEventListener('click', e => {
    const row = e.target.closest('.inv-var'); if (!row || row.classList.contains('busy')) return;
    const d = e.target.closest('[data-d]');
    if (d) { send(row, 'inc', d.dataset.d); return; }
    if (e.target.closest('.iv-save')) send(row, 'set', row.querySelector('input').value);
  });
  list.addEventListener('input', e => {
    const row = e.target.closest('.inv-var'); if (!row) return;
    row.querySelector('.iv-save').hidden = e.target.value === e.target.dataset.was;
  });
  list.addEventListener('keydown', e => {
    if (e.key === 'Enter' && e.target.matches('.inv-var input')) { e.preventDefault(); send(e.target.closest('.inv-var'), 'set', e.target.value); }
  });
})();
