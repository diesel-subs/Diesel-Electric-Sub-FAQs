/* submit.js — with “Current answer” placeholder
   - Prefills category/question/name/email
   - Shows existing answer as a placeholder in the note field
   - Sends JSON (including existing_answer) to dieselsubs.com/api/submit.php
*/

// --- MkDocs Material + plain page bootstrap ---
(function () {
  const INIT_FLAG = 'data-ds-submit-initialized';

  function initSubmitForm() {
    const form = document.getElementById('ds-form');
    if (!form) {
      // console.warn('[submit] form #ds-form not found; skipping init for this page');
      return;
    }
    if (form.hasAttribute(INIT_FLAG)) {
      return; // already initialized on this page render
    }
    form.setAttribute(INIT_FLAG, '1');
    initSubmitLogic();  // main logic defined below
  }

  // First load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSubmitForm);
  } else {
    initSubmitForm();
  }

  // Re-run on MkDocs Material instant navigation
  if (window.document$ && typeof window.document$.subscribe === 'function') {
    window.document$.subscribe(() => {
      setTimeout(initSubmitForm, 0);
    });
  }
})();

// --- Main logic function -----------------------------------------------------
function initSubmitLogic() {
  const ENDPOINT = 'https://dieselsubs.com/api/submit.php';
  const FORM_KEY = 'ds_user_info';
  const PREFILL_KEY = 'ds_prefill';
  const SEND_TIMEOUT_MS = 15000;

  // Elements
  const form = document.getElementById('ds-form');
  const statusEl = document.getElementById('ds-status');
  const nameEl = document.getElementById('ds-name');
  const emailEl = document.getElementById('ds-email');
  const comments = document.getElementById('ds-comments');
  const hp = document.getElementById('ds-website'); // honeypot
  const catTextEl = document.getElementById('ds-category-text');
  const qTextEl = document.getElementById('ds-question-text');
  let catInput = document.getElementById('ds-category');
  let qInput = document.getElementById('ds-question');

  if (!form) {
    console.warn('[submit] form #ds-form not found; aborting init');
    return;
  }

  const setStatus = (msg) => { if (statusEl) statusEl.textContent = msg; };
  const relUrl = () => location.pathname + location.search + location.hash;

  // --- Show thank you modal and redirect back to FAQ ---
  function showThankYouModal() {
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 10000;
    `;

    // Create modal dialog
    const modal = document.createElement('div');
    modal.style.cssText = `
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      text-align: center;
      max-width: 400px;
      margin: 1rem;
    `;

    // Create message
    const message = document.createElement('p');
    message.textContent = 'Thank you — your feedback was sent.';
    message.style.cssText = `
      margin: 0 0 1.5rem 0;
      font-size: 1.1rem;
      color: #333;
    `;

    // Create OK button
    const okButton = document.createElement('button');
    okButton.textContent = 'OK';
    okButton.style.cssText = `
      background: #1976d2;
      color: white;
      border: none;
      padding: 0.75rem 2rem;
      border-radius: 4px;
      font-size: 1rem;
      cursor: pointer;
    `;

    // Handle modal dismissal
    function dismissModal() {
      document.body.removeChild(overlay);

      console.log('[submit] Redirecting back to FAQ');

      // Strategy 1: Use document.referrer if it's a FAQ page (not feedback)
      if (document.referrer &&
        document.referrer.includes(window.location.origin) &&
        !document.referrer.includes('category=') &&
        !document.referrer.includes('question=') &&
        !document.referrer.includes('feedback')) {
        console.log('[submit] Returning to referrer FAQ:', document.referrer);
        window.location.href = document.referrer;
        return;
      }

      // Strategy 2: Try to construct FAQ URL from form data
      const category = catInput?.value || '';
      const question = qInput?.value || '';

      if (category && question) {
        // Build the FAQ page URL
        const origin = location.origin;
        const port = location.port ? ':' + location.port : '';

        let basePath;
        if (origin.includes('localhost') || origin.includes('127.0.0.1')) {
          basePath = `${location.protocol}//${location.hostname}${port}`;
        } else {
          basePath = origin;
          if (location.pathname.includes('/diesel-subs/')) {
            basePath += '/diesel-subs';
          }
        }

        // Construct the FAQ URL
        const faqUrl = `${basePath}/categories/${encodeURIComponent(category)}/${encodeURIComponent(question)}/`;
        console.log('[submit] Constructed FAQ URL:', faqUrl);
        window.location.href = faqUrl;
        return;
      }

      // Strategy 3: Fallback to categories if we can't determine the specific FAQ
      const origin = location.origin;
      const port = location.port ? ':' + location.port : '';

      let categoriesUrl;
      if (origin.includes('localhost') || origin.includes('127.0.0.1')) {
        categoriesUrl = `${location.protocol}//${location.hostname}${port}/categories/`;
      } else {
        let basePath = '/';
        if (location.pathname.includes('/diesel-subs/')) {
          basePath = '/diesel-subs/';
        }
        categoriesUrl = origin + basePath + 'categories/';
      }

      console.log('[submit] Fallback to categories:', categoriesUrl);
      window.location.href = categoriesUrl;
    }

    // Event listeners
    okButton.addEventListener('click', dismissModal);
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) dismissModal();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') dismissModal();
    });

    // Assemble and show modal
    modal.appendChild(message);
    modal.appendChild(okButton);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    // Focus the OK button
    okButton.focus();
  }

  // --- Ensure hidden inputs exist (so values are actually submitted) ---
  function ensureHiddenInputs() {
    if (!catInput) {
      catInput = document.createElement('input');
      catInput.type = 'hidden';
      catInput.id = 'ds-category';
      catInput.name = 'category';
      form.appendChild(catInput);
    } else {
      catInput.disabled = false;
    }
    if (!qInput) {
      qInput = document.createElement('input');
      qInput.type = 'hidden';
      qInput.id = 'ds-question';
      qInput.name = 'question';
      form.appendChild(qInput);
    } else {
      qInput.disabled = false;
    }
  }
  ensureHiddenInputs();

  // --- Prefill utilities ---
  function urlParams() {
    const p = new URLSearchParams(location.search);
    return {
      category: p.get('category') || '',
      question: p.get('question') || ''
    };
  }

  function loadPrefill() {
    try {
      const raw = sessionStorage.getItem(PREFILL_KEY);
      if (raw) return JSON.parse(raw);
    } catch { }
    const u = urlParams();
    if (u.category || u.question)
      return { ...u, src: relUrl(), existing_answer: '' };
    return null;
  }

  function prefillCategoryQuestion(pref) {
    const fromPref = {
      category: (pref?.category || '').trim(),
      question: (pref?.question || '').trim()
    };
    const cat = (catInput?.value || '').trim()
      || (catTextEl?.textContent || '').trim()
      || fromPref.category;
    const q = (qInput?.value || '').trim()
      || (qTextEl?.textContent || '').trim()
      || fromPref.question;

    if (catTextEl) catTextEl.textContent = cat;
    if (qTextEl) qTextEl.textContent = q;
    if (catInput) catInput.value = cat;
    if (qInput) qInput.value = q;
  }

  function prefillNameEmailFromLocal() {
    try {
      const saved = JSON.parse(localStorage.getItem(FORM_KEY) || '{}');
      if (saved.name && nameEl) nameEl.value = saved.name;
      if (saved.email && emailEl) emailEl.value = saved.email;
    } catch { }
  }

  function saveNameEmail() {
    const info = {
      name: (nameEl?.value || '').trim(),
      email: (emailEl?.value || '').trim()
    };
    try { localStorage.setItem(FORM_KEY, JSON.stringify(info)); } catch { }
  }

  // --- Extract existing answer ---
  function getExistingAnswer(pref) {
    let existing = (pref?.existing_answer || '').toString().trim();
    if (!existing) {
      try {
        const label = Array.from(document.querySelectorAll('.tabbed-set label'))
          .find(l => (l.textContent || '').trim().toLowerCase() === 'detailed answer');
        const panel = label?.nextElementSibling;
        if (panel && panel.classList.contains('tabbed-content'))
          existing = (panel.innerText || '').trim();
      } catch { }
    }
    if (existing.length > 8000) existing = existing.slice(0, 8000) + '…';
    return existing;
  }

  // --- Robust getters ---
  function getCategory(pref) {
    const fromText = (catTextEl?.textContent || '').trim();
    const fromInput = (catInput?.value || '').trim();
    const fromPref = (pref?.category || '').trim();
    const fromURL = urlParams().category.trim();
    return fromInput || fromText || fromPref || fromURL || '';
  }
  function getQuestion(pref) {
    const fromText = (qTextEl?.textContent || '').trim();
    const fromInput = (qInput?.value || '').trim();
    const fromPref = (pref?.question || '').trim();
    const fromURL = urlParams().question.trim();
    return fromInput || fromText || fromPref || fromURL || '';
  }

  function buildPayload(pref) {
    catInput.value = getCategory(pref);
    qInput.value = getQuestion(pref);

    return {
      category: catInput.value,
      question: qInput.value,
      comments: (comments?.value || '').trim(),
      submitter_name: (nameEl?.value || '').trim(),
      submitter_email: (emailEl?.value || '').trim(),
      page_url: pref?.src || relUrl(),
      existing_answer: getExistingAnswer(pref),
      tts_ms: performance.now() | 0
    };
  }

  async function postJSON(url, data, timeoutMs) {
    const controller = new AbortController();
    const to = setTimeout(() => controller.abort(), timeoutMs);
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'fetch' },
        body: JSON.stringify(data),
        signal: controller.signal
      });
      const ct = res.headers.get('content-type') || '';
      const text = await res.text();
      console.log('[submit] status', res.status, 'ct', ct, 'body:', text);
      let json = null;
      if (ct.includes('application/json')) { try { json = JSON.parse(text); } catch { } }
      return { ok: res.ok, status: res.status, json, text };
    } finally { clearTimeout(to); }
  }

  // --- Initialize ---
  const prefill = loadPrefill();
  prefillCategoryQuestion(prefill);
  prefillNameEmailFromLocal();

  const existing = getExistingAnswer(prefill);
  if (comments && existing) comments.placeholder = `Enter your feedback in this area.\n\nClick the button below to start with the existing answer.\n`;

  nameEl?.addEventListener('change', saveNameEmail);
  emailEl?.addEventListener('change', saveNameEmail);

  // --- Submit handler ---
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (hp && hp.value.trim() !== '') {
      setStatus('Thank you!');
      return;
    }

    catInput.value = getCategory(prefill);
    qInput.value = getQuestion(prefill);
    const category = catInput.value.trim();
    const question = qInput.value.trim();
    const note = (comments?.value || '').trim();

    if (!category || !question || !note) {
      setStatus('Please fill Category, Question, and Your note.');
      return;
    }

    setStatus('Sending…');
    const payload = buildPayload(prefill);
    saveNameEmail();

    try {
      const resp = await postJSON(ENDPOINT, payload, SEND_TIMEOUT_MS);
      if (!resp.ok) {
        setStatus(`Send failed (HTTP ${resp.status}).`);
        return;
      }
      if (resp.json?.ok) {
        // Show modal dialog with thank you message
        showThankYouModal();
        comments.value = '';
      } else {
        setStatus(`Send failed: ${resp.json?.error || 'Unknown error'}`);
      }
    } catch (err) {
      console.error('[submit] exception:', err);
      setStatus('Sorry: Load failed (network/timeout/CORS).');
    }
  });

  // --- Prefill on focus --- DISABLED
  // Auto-population of textarea on focus has been disabled per user request

  // --- "Click to copy current answer" button ---
  const copyBtn = document.getElementById('ds-copy-current');
  if (copyBtn && comments) {
    copyBtn.addEventListener('click', () => {
      // Get the existing answer from the same function used for form submission
      const prefill = loadPrefill();
      const existingAnswer = getExistingAnswer(prefill);
      
      if (!existingAnswer) {
        console.log('[submit] No existing answer found to copy');
        return;
      }
      
      console.log('[submit] Copying existing answer to textarea');
      comments.value = existingAnswer.trim();
      comments.focus();
    });
  }
}
