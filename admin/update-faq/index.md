# Update FAQ

<div id="search-section" style="margin-bottom: 2rem;">
  <div style="margin-bottom: 1rem;">
    <label for="faq-search" style="display: block; margin-bottom: 0.5rem;">Search for FAQ to Update:</label>
    <input type="text" id="faq-search" style="width: 100%; padding: 0.5rem; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 16px; font-family: inherit;"
           placeholder="Type question text or filename to search...">
  </div>
  
  <div id="search-results" style="max-height: 300px; overflow-y: auto; border: 1px solid #2196F3; border-radius: 4px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: none;">
  </div>
</div>

<form id="update-faq-form" style="display: none;">
  <div style="background: #e3f2fd; border: 2px solid #2196F3; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; box-shadow: 0 2px 4px rgba(33, 150, 243, 0.2);">
    <h3 style="margin-top: 0; color: #1976d2;">Current FAQ Information</h3>
    <div id="current-info">
      <p><strong>Current File:</strong> <span id="current-filename" style="font-family: monospace; background: #fff; padding: 2px 4px; border-radius: 3px;"></span></p>
      <p><strong>Category:</strong> <span id="current-category" style="color: #1976d2; font-weight: 500;"></span></p>
    </div>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="category" style="display: block; margin-bottom: 0.5rem;">Category:</label>
    <select id="category" required style="width: 100%; padding: 0.5rem; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 16px; font-family: inherit;">
      <option value="">Select a category...</option>
      <option value="Battles, Small and Large">Battles, Small and Large</option>
      <option value="Crews Aboard WW2 US Subs">Crews Aboard WW2 US Subs</option>
      <option value="Hull and Compartments">Hull and Compartments</option>
      <option value="Life Aboard WW2 US Subs">Life Aboard WW2 US Subs</option>
      <option value="Operating US Subs in WW2">Operating US Subs in WW2</option>
      <option value="US WW2 Subs in General">US WW2 Subs in General</option>
    </select>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="question" style="display: block; margin-bottom: 0.5rem;">Question:</label>
    <input type="text" id="question" required style="width: 100%; padding: 0.5rem; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 16px; font-family: inherit;"
           placeholder="Enter the FAQ question...">
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="short_answer" style="display: block; margin-bottom: 0.5rem;">Short Answer:</label>
    <textarea id="short_answer" required style="width: 100%; padding: 0.5rem; height: 80px; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 16px; font-family: inherit;"
              placeholder="Enter a brief answer..."></textarea>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="detailed_answer" style="display: block; margin-bottom: 0.5rem;">Detailed Answer:</label>
    <textarea id="detailed_answer" required style="width: 100%; padding: 0.5rem; height: 120px; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 16px; font-family: inherit;"
              placeholder="Enter a detailed answer..."></textarea>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="related_topics" style="display: block; margin-bottom: 0.5rem;">Related Topics (one per line):</label>
    <textarea id="related_topics" style="width: 100%; padding: 0.5rem; height: 60px; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 16px; font-family: inherit;"
              placeholder="Enter related topics (one per line)..."></textarea>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="author" style="display: block; margin-bottom: 0.5rem;">Author (optional):</label>
    <input type="text" id="author" style="width: 100%; padding: 0.5rem; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 16px; font-family: inherit;"
           placeholder="Enter author name (optional)...">
  </div>

  <button type="submit" style="background: #2196F3; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    Update FAQ
  </button>
  
  <button type="button" onclick="resetForm()" style="background: #666; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-left: 1rem;">
    Cancel
  </button>
</form>

<div id="status" style="margin-top: 1rem; padding: 1rem; border-radius: 4px; display: none;"></div>

<script>
window.currentFaq = null;
let allFaqs = [];

document.addEventListener('DOMContentLoaded', async function() {
  console.log('Page loaded, starting FAQ system...');
  await loadFAQs();
  setupSearch();
  setupForm();
  console.log('FAQ system initialized');
});

async function loadFAQs() {
  try {
    console.log('Loading FAQs from API...');
    const response = await fetch('http://localhost:8080/list-faqs.php');
    const result = await response.json();

    if (result.success && result.faqs) {
      allFaqs = result.faqs;
      console.log('Successfully loaded', allFaqs.length, 'FAQs');
    } else {
      throw new Error('API returned unsuccessful response');
    }
  } catch (error) {
    console.error('Error loading FAQs:', error);
    showStatus('Error loading FAQs: ' + error.message, 'error');
  }
}

function setupSearch() {
  const searchInput = document.getElementById('faq-search');
  const searchResults = document.getElementById('search-results');
  
  searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();

    if (query.length < 2) {
      searchResults.style.display = 'none';
      return;
    }

    const matches = allFaqs.filter(faq =>
      faq.question.toLowerCase().includes(query) ||
      faq.filename.toLowerCase().includes(query) ||
      faq.category.toLowerCase().includes(query) ||
      faq.short_answer.toLowerCase().includes(query)
    );

    console.log('Search for "' + query + '" found', matches.length, 'matches');

    if (matches.length === 0) {
      searchResults.innerHTML = '<div style="padding: 1rem;">No matches found</div>';
    } else {
      let html = '';
      matches.slice(0, 10).forEach((faq, index) => {
        html += '<div style="padding: 0.2rem; cursor: pointer; border-bottom: 1px solid #eee; color: #1976d2; font-size: 0.675rem;" onclick="loadFAQ(\'' + faq.question.replace(/'/g, "\\'") + '\')">';
        html += faq.question;
        html += '</div>';
      });
      searchResults.innerHTML = html;
    }

    searchResults.style.display = 'block';
  });
}

function loadFAQ(question) {
  console.log('Loading FAQ:', question);
  
  const faq = allFaqs.find(f => f.question === question);
  if (!faq) {
    console.error('FAQ not found');
    return;
  }
  
  window.currentFaq = faq;
  
  // Populate form
  document.getElementById('current-filename').textContent = faq.filename;
  document.getElementById('current-category').textContent = faq.category;
  document.getElementById('category').value = faq.category;
  document.getElementById('question').value = faq.question;
  document.getElementById('short_answer').value = faq.short_answer;
  document.getElementById('detailed_answer').value = faq.detailed_answer;
  document.getElementById('related_topics').value = (faq.related_topics || []).join('\n');
  document.getElementById('author').value = faq.author || '';
  
  // Show form, hide search
  document.getElementById('search-section').style.display = 'none';
  document.getElementById('update-faq-form').style.display = 'block';
  
  console.log('FAQ loaded successfully, category set to:', faq.category);
}

function setupForm() {
  const form = document.getElementById('update-faq-form');
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    console.log('Form submitted');
    alert('Form submission functionality not implemented in this demo');
  });
}

function resetForm() {
  document.getElementById('search-section').style.display = 'block';
  document.getElementById('update-faq-form').style.display = 'none';
  document.getElementById('faq-search').value = '';
  document.getElementById('search-results').style.display = 'none';
  window.currentFaq = null;
}

function showStatus(message, type) {
  const status = document.getElementById('status');
  const colors = {
    success: { bg: '#d4edda', color: '#155724' },
    error: { bg: '#f8d7da', color: '#721c24' },
    info: { bg: '#d1ecf1', color: '#0c5460' }
  };
  
  status.style.background = colors[type].bg;
  status.style.color = colors[type].color;
  status.innerHTML = message;
  status.style.display = 'block';
}
</script>

```
