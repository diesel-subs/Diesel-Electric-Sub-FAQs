// Main JavaScript functionality for Submarine FAQ site

document.addEventListener('DOMContentLoaded', function () {
  initializeSearchFunctionality();
  initializeThemeToggle();
  initializeLazyLoading();
  initializeTooltips();
});

// Search functionality
function initializeSearchFunctionality() {
  const searchForms = document.querySelectorAll('#search-form');
  searchForms.forEach(form => {
    form.addEventListener('submit', handleSearch);
  });

  // Live search with debouncing
  const searchInputs = document.querySelectorAll('#search-input, #category-search');
  searchInputs.forEach(input => {
    let timeout;
    input.addEventListener('input', function () {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        if (this.id === 'category-search') {
          filterCategoryContent(this.value);
        } else {
          performLiveSearch(this.value);
        }
      }, 300);
    });
  });
}

function handleSearch(e) {
  e.preventDefault();
  const formData = new FormData(e.target);
  const query = formData.get('q');

  if (query && query.trim().length > 0) {
    performSearch(query.trim());
  }
}

function performSearch(query) {
  const resultsContainer = document.getElementById('search-results');
  if (!resultsContainer) return;

  // Show loading indicator
  resultsContainer.innerHTML = '<div class="text-center"><div class="loading"></div> Searching...</div>';

  fetch(`api/search.php?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
      displaySearchResults(data, resultsContainer);
    })
    .catch(error => {
      console.error('Search error:', error);
      resultsContainer.innerHTML = '<div class="alert alert-danger">Search temporarily unavailable. Please try again later.</div>';
    });
}

function performLiveSearch(query) {
  if (query.length < 2) {
    document.getElementById('search-results').innerHTML = '';
    return;
  }
  performSearch(query);
}

function displaySearchResults(data, container) {
  if (data.success && data.results && data.results.length > 0) {
    let html = '<h5>Search Results:</h5><div class="search-results"><ul class="list-group">';
    data.results.forEach(result => {
      html += `
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6><a href="faq.php?id=${result.id}">${result.title}</a></h6>
                            <p class="mb-1 text-muted">${result.excerpt}</p>
                            <small class="text-muted">Category: ${result.category}</small>
                        </div>
                        <small class="text-muted">${result.views} views</small>
                    </div>
                </li>
            `;
    });
    html += '</ul></div>';
    container.innerHTML = html;
  } else {
    container.innerHTML = '<div class="alert alert-info">No results found. Try different keywords.</div>';
  }
}

// Filter content within category pages
function filterCategoryContent(query) {
  const faqItems = document.querySelectorAll('.faq-item');
  let visibleCount = 0;

  faqItems.forEach(item => {
    const title = item.querySelector('.btn-link').textContent.toLowerCase();
    const content = item.querySelector('.faq-content');
    const contentText = content ? content.textContent.toLowerCase() : '';

    const isVisible = title.includes(query.toLowerCase()) ||
      contentText.includes(query.toLowerCase());

    item.style.display = isVisible ? 'block' : 'none';
    if (isVisible) visibleCount++;
  });

  // Update count display
  updateFilterCount(visibleCount, faqItems.length);
}

function updateFilterCount(visible, total) {
  const countElement = document.querySelector('.text-muted');
  if (countElement && countElement.textContent.includes('FAQ(s) found')) {
    countElement.textContent = `${visible} of ${total} FAQ(s) shown`;
  }
}

// Theme toggle functionality
function initializeThemeToggle() {
  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', toggleTheme);

    // Load saved theme
    const savedTheme = localStorage.getItem('submarine-faq-theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
  }
}

function toggleTheme() {
  const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
  const newTheme = currentTheme === 'light' ? 'dark' : 'light';

  document.documentElement.setAttribute('data-theme', newTheme);
  localStorage.setItem('submarine-faq-theme', newTheme);
  updateThemeIcon(newTheme);
}

function updateThemeIcon(theme) {
  const icon = document.querySelector('#theme-toggle i');
  if (icon) {
    icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
  }
}

// Lazy loading for images
function initializeLazyLoading() {
  const images = document.querySelectorAll('img[data-src]');
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.classList.remove('lazy');
        imageObserver.unobserve(img);
      }
    });
  });

  images.forEach(img => imageObserver.observe(img));
}

// Initialize Bootstrap tooltips
function initializeTooltips() {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
}

// FAQ specific functions
function copyFAQLink(faqId) {
  const url = `${window.location.origin}/faq.php?id=${faqId}`;

  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(url).then(() => {
      showCopyFeedback(event.target);
    }).catch(() => {
      fallbackCopyText(url);
    });
  } else {
    fallbackCopyText(url);
  }
}

function fallbackCopyText(text) {
  const textArea = document.createElement('textarea');
  textArea.value = text;
  document.body.appendChild(textArea);
  textArea.focus();
  textArea.select();

  try {
    document.execCommand('copy');
    showCopyFeedback(event.target);
  } catch (err) {
    console.error('Fallback: Could not copy text');
  }

  document.body.removeChild(textArea);
}

function showCopyFeedback(button) {
  const originalText = button.innerHTML;
  const originalClass = button.className;

  button.innerHTML = '<i class="fas fa-check"></i> Copied!';
  button.classList.add('btn-success');
  button.classList.remove('btn-outline-secondary');

  setTimeout(() => {
    button.innerHTML = originalText;
    button.className = originalClass;
  }, 2000);
}

// Track FAQ views
function trackFAQView(faqId) {
  fetch('api/track-view.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({ faq_id: faqId })
  }).catch(error => {
    console.log('View tracking failed:', error);
    // Fail silently, don't disrupt user experience
  });
}

// Smooth scrolling for anchor links
function initializeSmoothScrolling() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });
}

// Utility functions
function debounce(func, wait, immediate) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      timeout = null;
      if (!immediate) func(...args);
    };
    const callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func(...args);
  };
}

function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
  notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
  notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

  document.body.appendChild(notification);

  setTimeout(() => {
    if (notification.parentNode) {
      notification.parentNode.removeChild(notification);
    }
  }, 5000);
}

// Export functions for global use
window.copyFAQLink = copyFAQLink;
window.trackFAQView = trackFAQView;
window.showNotification = showNotification;