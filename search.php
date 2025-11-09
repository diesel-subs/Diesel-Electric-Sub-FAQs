<?php
$page_title = 'Advanced Search';
$page_description = 'Search through our comprehensive database of submarine FAQs with advanced filters and options.';
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h1 class="card-title mb-0">
                        <i class="fas fa-search"></i>
                        Advanced Search
                    </h1>
                </div>
                <div class="card-body">
                    <form id="advancedSearchForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="searchQuery" class="form-label">Search Terms</label>
                                <input type="text" class="form-control" id="searchQuery" name="query" 
                                       placeholder="Enter keywords, phrases, or questions...">
                                <div class="form-text">Search in titles, questions, and answers</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="searchCategory" class="form-label">Category</label>
                                <select class="form-select" id="searchCategory" name="category">
                                    <option value="">All Categories</option>
                                    <?php
                                    try {
                                        $stmt = $pdo->query("
                                            SELECT id, name, COUNT(f.id) as faq_count 
                                            FROM categories c 
                                            LEFT JOIN faqs f ON c.id = f.category_id AND f.status = 'published'
                                            GROUP BY c.id, c.name 
                                            ORDER BY c.name
                                        ");
                                        while ($category = $stmt->fetch()) {
                                            echo "<option value='" . htmlspecialchars($category['name']) . "'>";
                                            echo htmlspecialchars($category['name']) . " ({$category['faq_count']} FAQs)";
                                            echo "</option>";
                                        }
                                    } catch (Exception $e) {
                                        echo "<option value=''>Error loading categories</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="searchField" class="form-label">Search In</label>
                                <select class="form-select" id="searchField" name="field">
                                    <option value="all">All Fields</option>
                                    <option value="title">Titles Only</option>
                                    <option value="question">Questions Only</option>
                                    <option value="answer">Answers Only</option>
                                    <option value="tags">Tags Only</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="sortBy" class="form-label">Sort By</label>
                                <select class="form-select" id="sortBy" name="sort">
                                    <option value="relevance">Relevance</option>
                                    <option value="title">Title (A-Z)</option>
                                    <option value="views">Most Viewed</option>
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="resultsPerPage" class="form-label">Results Per Page</label>
                                <select class="form-select" id="resultsPerPage" name="limit">
                                    <option value="10">10 results</option>
                                    <option value="25" selected>25 results</option>
                                    <option value="50">50 results</option>
                                    <option value="100">100 results</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="featuredOnly" name="featured">
                                    <label class="form-check-label" for="featuredOnly">Featured FAQs Only</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="exactMatch" name="exact">
                                    <label class="form-check-label" for="exactMatch">Exact Phrase Match</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <button type="reset" class="btn btn-outline-secondary" id="clearForm">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Search Results -->
            <div id="searchResults" class="mt-4" style="display: none;">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Search Results</h5>
                        <span id="resultCount" class="badge bg-primary"></span>
                    </div>
                    <div class="card-body" id="resultsContainer">
                        <!-- Results will be loaded here -->
                    </div>
                </div>
            </div>
            
            <!-- Popular Searches -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-fire"></i>
                        Popular Search Topics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Common Questions:</h6>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <button class="btn btn-sm btn-outline-primary search-suggestion" data-query="torpedo">Torpedoes</button>
                                <button class="btn btn-sm btn-outline-primary search-suggestion" data-query="crew">Crew Size</button>
                                <button class="btn btn-sm btn-outline-primary search-suggestion" data-query="depth">Diving Depth</button>
                                <button class="btn btn-sm btn-outline-primary search-suggestion" data-query="food">Food & Meals</button>
                                <button class="btn btn-sm btn-outline-primary search-suggestion" data-query="engine">Engines</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Technical Topics:</h6>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <button class="btn btn-sm btn-outline-info search-suggestion" data-query="periscope">Periscope</button>
                                <button class="btn btn-sm btn-outline-info search-suggestion" data-query="ballast">Ballast Tanks</button>
                                <button class="btn btn-sm btn-outline-info search-suggestion" data-query="sonar">Sonar</button>
                                <button class="btn btn-sm btn-outline-info search-suggestion" data-query="battery">Batteries</button>
                                <button class="btn btn-sm btn-outline-info search-suggestion" data-query="compartment">Compartments</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Advanced search functionality
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('advancedSearchForm');
    const resultsDiv = document.getElementById('searchResults');
    const resultsContainer = document.getElementById('resultsContainer');
    const resultCount = document.getElementById('resultCount');
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });
    
    // Handle clear button
    document.getElementById('clearForm').addEventListener('click', function() {
        resultsDiv.style.display = 'none';
        form.reset();
    });
    
    // Handle search suggestions
    document.querySelectorAll('.search-suggestion').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('searchQuery').value = this.dataset.query;
            performSearch();
        });
    });
    
    function performSearch() {
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        for (let [key, value] of formData.entries()) {
            if (value.trim()) {
                params.append(key, value.trim());
            }
        }
        
        // Show loading
        resultsContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
        resultsDiv.style.display = 'block';
        
        // Perform search
        fetch('/api/search.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                displayResults(data);
            })
            .catch(error => {
                resultsContainer.innerHTML = '<div class="alert alert-danger">Error performing search. Please try again.</div>';
            });
    }
    
    function displayResults(data) {
        if (data.success) {
            const results = data.results;
            resultCount.textContent = `${results.length} results`;
            
            if (results.length === 0) {
                resultsContainer.innerHTML = '<div class="text-center text-muted">No results found. Try different search terms or browse categories.</div>';
                return;
            }
            
            let html = '';
            results.forEach(result => {
                html += `
                    <div class="border-bottom pb-3 mb-3">
                        <h6><a href="faq.php?slug=${result.slug}" class="text-decoration-none">${result.title}</a></h6>
                        <p class="text-muted small mb-2">${result.question}</p>
                        <p class="mb-2">${result.short_answer || result.answer.substring(0, 200) + '...'}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-secondary">${result.category_name}</span>
                            <small class="text-muted">${result.views} views</small>
                        </div>
                    </div>
                `;
            });
            
            resultsContainer.innerHTML = html;
        } else {
            resultsContainer.innerHTML = '<div class="alert alert-warning">Search error: ' + data.message + '</div>';
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>