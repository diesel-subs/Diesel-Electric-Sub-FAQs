// feedback-link.js — runs on FAQ pages
(() => {
  // ---------- Helpers ----------
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  function getQuestion() {
    const h1 = $('article h1, .md-content__inner h1, .md-typeset h1');
    return h1 ? h1.textContent.trim() : '';
  }

  function getCategoryFromPath() {
    // Expect paths like: /categories/<Category Name>/<slug>[/...]
    const parts = location.pathname.split('/').filter(Boolean);
    const i = parts.indexOf('categories');
    if (i >= 0 && parts[i + 1]) {
      return decodeURIComponent(parts[i + 1]).replace(/\s+/g, ' ').trim();
    }
    return '';
  }

  function getDetailedAnswer() {
    // Strategy A: pymdownx.tabbed — find a tab group and label "Detailed Answer"
    // Works with Material's tabbed structure (inputs/labels/panels).
    try {
      const sets = $$('.tabbed-set, .md-typeset .tabbed-set');
      for (const set of sets) {
        const labels = $$('label', set);
        // Find the label that reads "Detailed Answer"
        const label = labels.find(l => /Detailed Answer/i.test(l.textContent || ''));
        if (label) {
          // Try common panel containers: role="tabpanel" or .tabbed-content children
          // Prefer the first non-empty panel following the label
          const panels = $$('[role="tabpanel"], .tabbed-content > div, .tabbed-pane', set);
          const panel = panels.find(p => (p.textContent || '').trim().length > 0);
          if (panel) return (panel.innerText || panel.textContent || '').trim();
        }
      }
    } catch { }

    // Strategy B: fallback by heading scan (H2/H3 “Detailed Answer”), collect until next H2/H3
    try {
      const heads = $$('article h2, article h3, .md-typeset h2, .md-typeset h3');
      const idx = heads.findIndex(h => /Detailed Answer/i.test(h.textContent || ''));
      if (idx >= 0) {
        let txt = '';
        for (let n = heads[idx].nextElementSibling; n; n = n.nextElementSibling) {
          if (/^H[23]$/.test(n.tagName)) break;
          txt += ' ' + ((n.innerText || n.textContent || '').trim());
        }
        if (txt.trim()) return txt.trim();
      }
    } catch { }

    // Strategy C: last resort — whole article (minus H1)
    try {
      const article = $('article, .md-content__inner, .md-typeset');
      if (article) {
        const clone = article.cloneNode(true);
        const h1 = $('h1', clone);
        if (h1) h1.remove();
        const txt = (clone.innerText || clone.textContent || '').trim();
        if (txt) return txt;
      }
    } catch { }

    return '';
  }

  function buildPrefill() {
    const category = (
      $('[data-ds-category]')?.textContent ||
      $('#ds-category-text')?.textContent ||
      getCategoryFromPath()
    ).trim();

    const question = getQuestion();
    let existing = getDetailedAnswer();
    console.log('[feedback-link] prefill', { category, question, existing });

    // Sanitize & limit stored text
    if (existing.length > 8000) existing = existing.slice(0, 8000) + '…';

    // Use relative path for portability (avoids localhost origins in prod)
    const src = location.pathname + location.search + location.hash;

    return { category, question, existing_answer: existing, src };
  }

  // ---------- Click handler for any feedback link ----------
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[data-feedback-link], [data-feedback-link]');
    if (!a) return;

    const payload = buildPrefill();
    try {
      sessionStorage.setItem('ds_prefill', JSON.stringify(payload));
    } catch { }

    // Append short, visible params to the target URL (optional, for UX)
    const url = new URL(a.getAttribute('href') || '#', location.origin);
    const params = new URLSearchParams(url.search);
    if (payload.category) params.set('category', payload.category);
    if (payload.question) params.set('question', payload.question.slice(0, 252));
    params.set('prefill', '1');
    url.search = params.toString();

    e.preventDefault();
    location.assign(url.toString());
  });
})();
