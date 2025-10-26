#

<form id="ds-form" class="ds-form" novalidate>
  <!-- Category + Question table -->
  <table class="ds-meta-table">
    <tr>
      <th>Category:</th>
      <td id="ds-category-text"></td>
    </tr>
    <tr>
      <th>Question:</th>
      <td id="ds-question-text"></td>
    </tr>
  </table>

  <input type="hidden" id="ds-category" name="category">
  <input type="hidden" id="ds-question" name="question" maxlength="252">

 <div class="ds-field">
  <textarea id="ds-comments" name="comments" rows="5" required></textarea>
  <div class="ds-label-with-button">
    <button type="button" id="ds-copy-current" class="ds-mini-btn">
      Fill With Current Answer
    </button>
  </div>
</div>

  <div class="ds-separator" aria-hidden="true"></div>

  <!-- Optional name/email box -->
  <fieldset class="ds-optional">
    <legend>Optional</legend>
    <div class="ds-field ds-inline">
      <label for="ds-name">Your name</label>
      <input id="ds-name" name="submitter_name">
    </div>
    <div class="ds-field ds-inline">
      <label for="ds-email">Email</label>
      <input id="ds-email" name="submitter_email" type="email">
    </div>
  </fieldset>

  <input type="text" name="website" id="ds-website" tabindex="-1" style="position:absolute;left:-9999px">
  <button type="submit">Send</button>
  <p id="ds-status" aria-live="polite"></p>
</form>

<p id="ds-source" style="font-size:.9rem; opacity:.8"></p>

<script>
(() => {
  try {
    const raw = sessionStorage.getItem('ds_prefill');
    if (!raw) return;
    const p = JSON.parse(raw);

    if (p.category) {
      document.getElementById('ds-category-text').textContent = p.category;
      document.getElementById('ds-category').value = p.category;
    }
    if (p.question) {
      document.getElementById('ds-question-text').textContent = p.question;
      document.getElementById('ds-question').value = p.question;
    }

    if (p.src) {
      const el = document.getElementById('ds-source');
      // if (el) el.innerHTML = `From: <a href="${p.src}">${p.src}</a>`;
    }
  } catch {}
})();
</script>

<script>
(() => {
  const el = document.getElementById('ds-source');
  if (!el) return;
  try {
    const raw = sessionStorage.getItem('ds_prefill');
    if (!raw) return;
    const p = JSON.parse(raw);
    if (!p.src) return;

    const u = new URL(p.src, location.href);
    if (u.hostname === '127.0.0.1' || u.hostname === 'localhost') return; // hide in dev

    const label = p.question || decodeURIComponent(u.pathname);
    el.innerHTML = `From: <a href="${p.src}">${label}</a>`;
  } catch {}
})();
</script>

<script>
(() => {
  const nameEl = document.getElementById('ds-name');
  const emailEl = document.getElementById('ds-email');
  const FORM_KEY = 'ds_user_info';

  // --- Prefill name and email from localStorage ---
  try {
    const saved = JSON.parse(localStorage.getItem(FORM_KEY) || '{}');
    if (saved.name && nameEl) nameEl.value = saved.name;
    if (saved.email && emailEl) emailEl.value = saved.email;
  } catch (err) {
    console.warn('Could not parse stored user info', err);
  }

  // --- Save to localStorage whenever user leaves the field ---
  const saveUserInfo = () => {
    const info = {
      name: nameEl?.value?.trim() || '',
      email: emailEl?.value?.trim() || ''
    };
    localStorage.setItem(FORM_KEY, JSON.stringify(info));
  };

  nameEl?.addEventListener('change', saveUserInfo);
  emailEl?.addEventListener('change', saveUserInfo);
})();
</script>
