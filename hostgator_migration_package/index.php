<?php
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="hero-section">
        <h1>Diesel-Electric Submarine FAQs</h1>
        <p class="lead">A comprehensive collection of frequently asked questions about diesel-electric submarines, with special focus on World War II US submarines.</p>
        
        <!-- Feedback Call-to-Action -->
        <div class="alert alert-info mt-3" role="alert">
            <i class="fas fa-lightbulb"></i> 
            <strong>Help us improve!</strong> Can't find what you're looking for? Have corrections or suggestions? 
            <a href="feedback.php" class="alert-link">Share your feedback</a> to help us make this resource better.
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h2>Browse by Category</h2>
            <div class="category-grid">
                <?php
                $categories = [
                    'US WW2 Subs in General' => [
                        'description' => 'General information about US submarines in World War II',
                        'icon' => 'fas fa-ship'
                    ],
                    'Hull and Compartments' => [
                        'description' => 'Structure, design, and compartment layout of submarines',
                        'icon' => 'fas fa-cogs'
                    ],
                    'Operating US Subs in WW2' => [
                        'description' => 'Operational procedures and tactics used during WWII',
                        'icon' => 'fas fa-compass'
                    ],
                    'Life Aboard WW2 US Subs' => [
                        'description' => 'Daily life, conditions, and experiences of submarine crews',
                        'icon' => 'fas fa-users'
                    ],
                    'Crews Aboard WW2 US Subs' => [
                        'description' => 'Crew composition, roles, and personnel information',
                        'icon' => 'fas fa-user-friends'
                    ],
                    'Battles Small and Large' => [
                        'description' => 'Combat engagements and battle histories',
                        'icon' => 'fas fa-crosshairs'
                    ]
                ];

                foreach ($categories as $name => $info): 
                    $slug = str_replace([' ', '/'], ['-', '-'], strtolower($name));
                ?>
                <div class="category-card">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="<?php echo $info['icon']; ?>"></i>
                                <?php echo htmlspecialchars($name); ?>
                            </h5>
                            <p class="card-text"><?php echo htmlspecialchars($info['description']); ?></p>
                            <a href="category.php?cat=<?php echo urlencode($name); ?>" class="btn btn-primary">
                                Explore FAQs
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Community Feedback Section -->
            <div class="mt-4 p-3 bg-light rounded">
                <h4><i class="fas fa-comments"></i> Community Powered Knowledge</h4>
                <p class="mb-2">This FAQ collection is improved by visitors like you! Found an error? Missing information? Have a question that's not covered?</p>
                <div class="btn-group" role="group">
                    <a href="feedback.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Submit Question
                    </a>
                    <a href="feedback.php?type=correction" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Report Issue
                    </a>
                    <a href="feedback.php?type=suggestion" class="btn btn-info btn-sm">
                        <i class="fas fa-star"></i> Suggest Improvement
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-6">
            <h3>Recent Questions</h3>
            <div id="recent-questions">
                <!-- Recent questions will be loaded here via AJAX -->
            </div>
        </div>
        <div class="col-md-6">
            <h3>Quick Search</h3>
            <form id="search-form">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search FAQs..." name="q" id="search-input">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            Search
                        </button>
                    </div>
                </div>
            </form>
            <div id="search-results" class="mt-3"></div>
            
            <!-- Feedback Widget -->
            <div class="card mt-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-heart"></i> Your Input Matters</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Help us make this the most comprehensive submarine FAQ resource!</p>
                    <div class="d-grid gap-2">
                        <a href="feedback.php" class="btn btn-success btn-sm">
                            <i class="fas fa-pencil-alt"></i> Share Feedback
                        </a>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> Takes less than 2 minutes
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load recent questions on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRecentQuestions();
    setupSearch();
});

function loadRecentQuestions() {
    fetch('api/recent-questions.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recent-questions');
            if (data.success && data.questions) {
                let html = '<ul class="list-group">';
                data.questions.forEach(question => {
                    html += `<li class="list-group-item">
                        <a href="faq.php?id=${question.id}">${question.title}</a>
                        <small class="text-muted d-block">Category: ${question.category}</small>
                    </li>`;
                });
                html += '</ul>';
                container.innerHTML = html;
            }
        })
        .catch(error => console.error('Error loading recent questions:', error));
}

function setupSearch() {
    document.getElementById('search-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const query = document.getElementById('search-input').value;
        if (query.trim()) {
            searchFAQs(query);
        }
    });
}

function searchFAQs(query) {
    fetch(`api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('search-results');
            if (data.success && data.results) {
                let html = '<h5>Search Results:</h5><ul class="list-group">';
                data.results.forEach(result => {
                    html += `<li class="list-group-item">
                        <a href="faq.php?id=${result.id}">${result.title}</a>
                        <small class="text-muted d-block">${result.excerpt}</small>
                    </li>`;
                });
                html += '</ul>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p>No results found.</p>';
            }
        })
        .catch(error => console.error('Error searching:', error));
}

// Interactive feedback prompts
let interactionCount = 0;
let feedbackShown = false;

function trackInteraction() {
    interactionCount++;
    
    // After user has interacted 3 times, show a subtle feedback prompt
    if (interactionCount >= 3 && !feedbackShown && !localStorage.getItem('feedback_prompted')) {
        setTimeout(showFeedbackPrompt, 2000);
        feedbackShown = true;
    }
}

function showFeedbackPrompt() {
    const prompt = document.createElement('div');
    prompt.className = 'alert alert-success alert-dismissible fade show position-fixed';
    prompt.style.cssText = 'top: 20px; right: 20px; max-width: 350px; z-index: 1050;';
    prompt.innerHTML = `
        <i class="fas fa-thumbs-up"></i>
        <strong>Enjoying the site?</strong> Help us improve with your feedback!
        <a href="feedback.php" class="alert-link ms-2">Quick feedback</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(prompt);
    localStorage.setItem('feedback_prompted', 'true');
    
    // Auto-hide after 8 seconds
    setTimeout(() => {
        if (prompt.parentNode) {
            prompt.remove();
        }
    }, 8000);
}

// Track various interactions
document.addEventListener('DOMContentLoaded', function() {
    // Track category clicks
    document.querySelectorAll('.category-card a').forEach(link => {
        link.addEventListener('click', trackInteraction);
    });
    
    // Track search usage
    document.getElementById('search-form').addEventListener('submit', trackInteraction);
    
    // Track recent questions clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('#recent-questions a')) {
            trackInteraction();
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>