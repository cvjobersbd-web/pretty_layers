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
