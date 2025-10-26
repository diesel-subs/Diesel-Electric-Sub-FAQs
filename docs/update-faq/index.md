# Update Existing FAQ

<div id="search-section" style="margin-bottom: 2rem;">
  <div style="margin-bottom: 1rem;">
    <label for="faq-search" style="display: block; margin-bottom: 0.5rem;">Search for FAQ to Update:</label>
    <input type="text" id="faq-search" style="width: 100%; padding: 0.5rem; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 14px; font-family: inherit;" 
           placeholder="Type question text or filename to search...">
  </div>
  
  <div id="search-results" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; display: none;">
  </div>
  
  <button id="load-faq-btn" style="background: #4CAF50; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; font-size: 14px; display: none;">
    Load Selected FAQ
  </button>
</div>

<form id="update-faq-form" style="display: none;">
  <div style="background: #f0f8ff; border: 1px solid #2196F3; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
    <h3 style="margin-top: 0;">Current FAQ Information</h3>
    <div id="current-info">
      <p><strong>Current File:</strong> <span id="current-filename"></span></p>
      <p><strong>Category:</strong> <span id="current-category"></span></p>
    </div>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="category" style="display: block; margin-bottom: 0.5rem;">Category:</label>
    <select id="category" required style="width: 100%; padding: 0.5rem; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 14px; font-family: inherit;">
      <option value="">Select a category...</option>
      <option value="Battles, Small and Large">Battles, Small and Large</option>
      <option value="Crews Aboard WW2 US Subs">Crews Aboard WW2 US Subs</option>
      <option value="Hull and Compartments">Hull and Compartments</option>
      <option value="Life Aboard WW2 US Subs">Life Aboard WW2 US Subs</option>
      <option value="Operating US WW2 Subs">Operating US WW2 Subs</option>
      <option value="US WW2 Subs in General">US WW2 Subs in General</option>
    </select>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="question" style="display: block; margin-bottom: 0.5rem;">Question:</label>
    <input type="text" id="question" required style="width: 100%; padding: 0.5rem; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 14px; font-family: inherit;"
           placeholder="Enter the FAQ question...">
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="short_answer" style="display: block; margin-bottom: 0.5rem;">Short Answer:</label>
    <textarea id="short_answer" required style="width: 100%; padding: 0.5rem; height: 80px; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 14px; font-family: inherit;"
              placeholder="Brief answer for the Quick Answer tab"></textarea>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="detailed_answer" style="display: block; margin-bottom: 0.5rem;">Detailed Answer:</label>
    <textarea id="detailed_answer" required style="width: 100%; padding: 0.5rem; height: 120px; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 14px; font-family: inherit;"
              placeholder="Complete detailed explanation"></textarea>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="related_topics" style="display: block; margin-bottom: 0.5rem;">Related Topics:</label>
    <textarea id="related_topics" style="width: 100%; padding: 0.5rem; height: 60px; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 14px; font-family: inherit;"
              placeholder="Enter related topics (one per line or comma-separated)"></textarea>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="author" style="display: block; margin-bottom: 0.5rem;">Author (optional):</label>
    <input type="text" id="author" style="width: 100%; padding: 0.5rem; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 14px; font-family: inherit;"
           placeholder="Your name">
  </div>

  <div style="margin-bottom: 1rem;">
    <input type="checkbox" id="create-backup" checked>
    <label for="create-backup" style="margin-left: 0.5rem;">Create backup branch before updating</label>
  </div>

  <!-- Honeypot -->
  <input type="text" id="website" style="position: absolute; left: -9999px;" tabindex="-1">

  <button type="submit" style="background: #FF9800; color: white; border: none; padding: 0.75rem 1.5rem; 
                               border-radius: 4px; cursor: pointer; font-size: 1rem; margin-right: 1rem;">
    Update FAQ
  </button>
  
  <button type="button" id="cancel-btn" style="background: #757575; color: white; border: none; padding: 0.75rem 1.5rem; 
                                                border-radius: 4px; cursor: pointer; font-size: 1rem;">
    Cancel
  </button>
</form>

<div id="status" style="margin-top: 1rem; padding: 1rem; border-radius: 4px; display: none;"></div>

<script>
let currentFaq = null;
let allFaqs = [];

// Load available FAQs on page load
document.addEventListener('DOMContentLoaded', async function() {
  await loadAvailableFaqs();
  setupSearchFunctionality();
  setupFormHandlers();
});

// Load all available FAQs
async function loadAvailableFaqs() {
  try {
    // This would typically come from an API endpoint that lists all FAQs
    // For now, we'll create a simple structure
    allFaqs = await fetchAllFaqs();
  } catch (error) {
    console.error('Error loading FAQs:', error);
    showStatus('Error loading available FAQs', 'error');
  }
}

// Fetch all FAQs (placeholder - would need real API)
async function fetchAllFaqs() {
  // TODO: Implement API endpoint to list all FAQs
  // For now, return empty array - this would be populated by scanning docs/categories/
  return [];
}

// Setup search functionality
function setupSearchFunctionality() {
  const searchInput = document.getElementById('faq-search');
  const searchResults = document.getElementById('search-results');
  const loadBtn = document.getElementById('load-faq-btn');

  searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    
    if (query.length < 2) {
      searchResults.style.display = 'none';
      loadBtn.style.display = 'none';
      return;
    }

    // Filter FAQs based on search query
    const matches = allFaqs.filter(faq => 
      faq.question.toLowerCase().includes(query) ||
      faq.filename.toLowerCase().includes(query) ||
      faq.category.toLowerCase().includes(query)
    );

    displaySearchResults(matches);
  });
}

// Display search results
function displaySearchResults(faqs) {
  const searchResults = document.getElementById('search-results');
  const loadBtn = document.getElementById('load-faq-btn');

  if (faqs.length === 0) {
    searchResults.innerHTML = '<div style="padding: 1rem; color: #666;">No FAQs found matching your search.</div>';
    searchResults.style.display = 'block';
    loadBtn.style.display = 'none';
    return;
  }

  let html = '';
  faqs.forEach((faq, index) => {
    html += `
      <div class="faq-result" data-index="${index}" style="padding: 0.75rem; border-bottom: 1px solid #eee; cursor: pointer;">
        <div style="font-weight: bold; color: #2196F3;">${faq.question}</div>
        <div style="font-size: 0.9em; color: #666;">${faq.category} • ${faq.filename}</div>
      </div>
    `;
  });

  searchResults.innerHTML = html;
  searchResults.style.display = 'block';

  // Add click handlers for results
  searchResults.querySelectorAll('.faq-result').forEach(result => {
    result.addEventListener('click', function() {
      // Remove previous selection
      searchResults.querySelectorAll('.faq-result').forEach(r => r.style.backgroundColor = '');
      
      // Highlight selected
      this.style.backgroundColor = '#e3f2fd';
      
      // Store selected FAQ
      const index = parseInt(this.dataset.index);
      currentFaq = faqs[index];
      
      // Show load button
      loadBtn.style.display = 'inline-block';
    });
  });
}

// Setup form handlers
function setupFormHandlers() {
  const loadBtn = document.getElementById('load-faq-btn');
  const form = document.getElementById('update-faq-form');
  const cancelBtn = document.getElementById('cancel-btn');

  loadBtn.addEventListener('click', loadFaqIntoForm);
  form.addEventListener('submit', handleFormSubmit);
  cancelBtn.addEventListener('click', resetForm);
}

// Load selected FAQ into form
async function loadFaqIntoForm() {
  if (!currentFaq) return;

  try {
    // Load FAQ content (would typically fetch from API)
    const faqContent = await fetchFaqContent(currentFaq);
    
    // Populate form fields
    document.getElementById('current-filename').textContent = currentFaq.filename;
    document.getElementById('current-category').textContent = currentFaq.category;
    document.getElementById('category').value = currentFaq.category;
    document.getElementById('question').value = faqContent.question;
    document.getElementById('short_answer').value = faqContent.short_answer;
    document.getElementById('detailed_answer').value = faqContent.detailed_answer;
    document.getElementById('related_topics').value = faqContent.related_topics.join('\n');
    document.getElementById('author').value = faqContent.author || '';

    // Show form, hide search
    document.getElementById('search-section').style.display = 'none';
    document.getElementById('update-faq-form').style.display = 'block';

  } catch (error) {
    console.error('Error loading FAQ content:', error);
    showStatus('Error loading FAQ content', 'error');
  }
}

// Fetch FAQ content (placeholder)
async function fetchFaqContent(faq) {
  // TODO: Implement API endpoint to get FAQ content
  // For now, return placeholder data
  return {
    question: faq.question,
    short_answer: 'Sample short answer...',
    detailed_answer: 'Sample detailed answer...',
    related_topics: [],
    author: ''
  };
}

// Handle form submission
async function handleFormSubmit(e) {
  e.preventDefault();

  const status = document.getElementById('status');
  const button = this.querySelector('button[type="submit"]');
  
  // Show loading state
  button.disabled = true;
  button.textContent = 'Updating...';
  showStatus('Updating FAQ...', 'info');

  try {
    // Collect form data
    const formData = {
      oldFaq: currentFaq,
      category: document.getElementById('category').value,
      question: document.getElementById('question').value,
      short_answer: document.getElementById('short_answer').value,
      detailed_answer: document.getElementById('detailed_answer').value,
      related_topics: document.getElementById('related_topics').value.split('\n').filter(t => t.trim()),
      author: document.getElementById('author').value,
      create_backup: document.getElementById('create-backup').checked,
      website: document.getElementById('website').value // honeypot
    };

    // Validate required fields
    if (!formData.category || !formData.question || !formData.short_answer || !formData.detailed_answer) {
      throw new Error('Please fill in all required fields');
    }

    // TODO: Submit to update API
    const response = await fetch('/api/update-faq.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData)
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    if (result.success) {
      showStatus(`FAQ updated successfully!<br>
                  Old file: ${result.oldFile}<br>
                  New file: ${result.newFile}<br>
                  References updated: ${result.referencesUpdated}`, 'success');
      
      // Reset form after successful update
      setTimeout(resetForm, 3000);
    } else {
      throw new Error(result.message || 'Failed to update FAQ');
    }

  } catch (error) {
    console.error('Error updating FAQ:', error);
    showStatus(`Error: ${error.message}`, 'error');
  } finally {
    button.disabled = false;
    button.textContent = 'Update FAQ';
  }
}

// Reset form to initial state
function resetForm() {
  document.getElementById('search-section').style.display = 'block';
  document.getElementById('update-faq-form').style.display = 'none';
  document.getElementById('faq-search').value = '';
  document.getElementById('search-results').style.display = 'none';
  document.getElementById('load-faq-btn').style.display = 'none';
  document.getElementById('status').style.display = 'none';
  currentFaq = null;
}

// Show status message
function showStatus(message, type) {
  const status = document.getElementById('status');
  
  const colors = {
    success: { bg: '#e8f5e8', color: '#2e7d32' },
    error: { bg: '#ffebee', color: '#c62828' },
    info: { bg: '#e3f2fd', color: '#1976d2' }
  };

  status.style.background = colors[type].bg;
  status.style.color = colors[type].color;
  status.innerHTML = message;
  status.style.display = 'block';
}
</script>