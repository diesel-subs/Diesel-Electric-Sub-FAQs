<?php
require_once 'config/database.php';

// Get FAQ by ID or slug
$faq_id = $_GET['id'] ?? null;
$faq_slug = $_GET['slug'] ?? null;

if (!$faq_id && !$faq_slug) {
    header('Location: index.php');
    exit;
}

try {
    if ($faq_id) {
        $stmt = $pdo->prepare("
            SELECT f.*, c.name as category_name, c.slug as category_slug
            FROM faqs f 
            JOIN categories c ON f.category_id = c.id 
            WHERE f.id = ? AND f.status = 'published'
        ");
        $stmt->execute([$faq_id]);
    } else {
        $stmt = $pdo->prepare("
            SELECT f.*, c.name as category_name, c.slug as category_slug
            FROM faqs f 
            JOIN categories c ON f.category_id = c.id 
            WHERE f.slug = ? AND f.status = 'published'
        ");
        $stmt->execute([$faq_slug]);
    }
    
    $faq = $stmt->fetch();
    
    if (!$faq) {
        header('HTTP/1.0 404 Not Found');
        include '404.php';
        exit;
    }
    
    // Update view count
    $stmt = $pdo->prepare("UPDATE faqs SET views = views + 1 WHERE id = ?");
    $stmt->execute([$faq['id']]);
    
    // Get related FAQs
    $related_faqs = get_related_faqs($pdo, $faq['id'], 3);
    
} catch (Exception $e) {
    header('Location: index.php');
    exit;
}

$page_title = $faq['title'];
$page_description = $faq['short_answer'] ? substr($faq['short_answer'], 0, 160) : substr($faq['answer'], 0, 160);
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="category.php?cat=<?php echo urlencode($faq['category_name']); ?>"><?php echo htmlspecialchars($faq['category_name']); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($faq['title']); ?></li>
                </ol>
            </nav>

            <!-- FAQ Content -->
            <article class="faq-single">
                <header class="faq-header mb-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h1 class="faq-title"><?php echo htmlspecialchars($faq['title']); ?></h1>
                            <div class="faq-meta text-muted mb-3">
                                <span class="badge bg-primary me-2"><?php echo htmlspecialchars($faq['category_name']); ?></span>
                                <small><i class="fas fa-eye"></i> <?php echo number_format($faq['views']); ?> views</small>
                                <?php if ($faq['featured']): ?>
                                    <span class="badge bg-warning text-dark ms-2">
                                        <i class="fas fa-star"></i> Featured
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="faq-actions">
                            <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard(window.location.href)">
                                <i class="fas fa-share"></i> Share
                            </button>
                        </div>
                    </div>
                </header>

                <div class="faq-question mb-4">
                    <h2 class="h4 text-primary">
                        <i class="fas fa-question-circle"></i>
                        <?php echo htmlspecialchars($faq['question']); ?>
                    </h2>
                </div>

                <div class="faq-answer">
                    <?php if ($faq['short_answer'] && $faq['short_answer'] !== $faq['answer']): ?>
                        <div class="alert alert-info">
                            <h5>Quick Answer:</h5>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($faq['short_answer'])); ?></p>
                        </div>
                        
                        <h5>Detailed Answer:</h5>
                    <?php endif; ?>
                    
                    <div class="answer-content" id="faq-content">
                        <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                    </div>
                </div>

                <!-- Reading Engagement Tracker -->
                <div id="reading-progress" class="mt-4 mb-3" style="display: none;">
                    <div class="alert alert-success alert-dismissible">
                        <h5><i class="fas fa-graduation-cap"></i> Great job reading!</h5>
                        <p class="mb-2">You've read this entire FAQ. Did it answer your question?</p>
                        <div class="btn-group" role="group">
                            <button onclick="markHelpful(true)" class="btn btn-success btn-sm">
                                <i class="fas fa-thumbs-up"></i> Yes, very helpful!
                            </button>
                            <button onclick="markHelpful(false)" class="btn btn-warning btn-sm">
                                <i class="fas fa-question"></i> I need more info
                            </button>
                            <a href="feedback.php?faq_id=<?php echo $faq['id']; ?>&type=suggestion" class="btn btn-info btn-sm">
                                <i class="fas fa-plus"></i> Add details
                            </a>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>

                <?php if (!empty($faq['tags'])): ?>
                    <div class="faq-tags mt-4">
                        <h6>Related Topics:</h6>
                        <?php 
                        $tags = explode(',', $faq['tags']);
                        foreach ($tags as $tag): ?>
                            <span class="badge bg-secondary me-1"><?php echo trim(htmlspecialchars($tag)); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Feedback Widget -->
                <?php 
                $current_faq = $faq;
                include 'includes/feedback-widget.php'; 
                ?>

                <div class="faq-footer mt-4 pt-3 border-top">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                Last updated: <?php echo format_date($faq['updated_at']); ?>
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="feedback.php?faq_id=<?php echo $faq['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Suggest Improvements
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Related FAQs -->
            <?php if (!empty($related_faqs)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-link"></i>
                            Related FAQs
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($related_faqs as $related): ?>
                            <div class="mb-3">
                                <a href="faq.php?id=<?php echo $related['id']; ?>" class="text-decoration-none">
                                    <strong><?php echo htmlspecialchars($related['title']); ?></strong>
                                </a>
                                <br>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($related['category_name']); ?> • 
                                    <span class="badge badge-sm bg-light text-dark"><?php echo ucfirst($related['relationship_type']); ?></span>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Suggest New FAQ -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h6>Have a Question?</h6>
                    <p class="small text-muted">Can't find what you're looking for?</p>
                    <a href="feedback.php?feedback_type=new_faq" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Suggest New FAQ
                    </a>
                </div>
            </div>

            <!-- Popular FAQs in Category -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Popular in <?php echo htmlspecialchars($faq['category_name']); ?></h6>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT id, title, views
                            FROM faqs 
                            WHERE category_id = ? AND id != ? AND status = 'published'
                            ORDER BY views DESC 
                            LIMIT 5
                        ");
                        $stmt->execute([$faq['category_id'], $faq['id']]);
                        $popular = $stmt->fetchAll();
                        
                        if ($popular) {
                            foreach ($popular as $pop) {
                                echo '<div class="mb-2">';
                                echo '<a href="faq.php?id=' . $pop['id'] . '" class="text-decoration-none small">';
                                echo htmlspecialchars($pop['title']);
                                echo '</a>';
                                echo '<br><small class="text-muted">' . number_format($pop['views']) . ' views</small>';
                                echo '</div>';
                            }
                        } else {
                            echo '<small class="text-muted">No other FAQs in this category yet.</small>';
                        }
                    } catch (Exception $e) {
                        echo '<small class="text-muted">Unable to load popular FAQs.</small>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    });
}

// Reading Engagement Tracking
let readingStartTime = Date.now();
let hasScrolledToEnd = false;
let engagementShown = false;

function trackReadingProgress() {
    const content = document.getElementById('faq-content');
    const progressDiv = document.getElementById('reading-progress');
    
    if (!content || !progressDiv || engagementShown) return;
    
    const contentRect = content.getBoundingClientRect();
    const windowHeight = window.innerHeight;
    
    // Check if user has scrolled to bottom 80% of content
    if (contentRect.bottom <= windowHeight * 1.2 && !hasScrolledToEnd) {
        hasScrolledToEnd = true;
        
        // Show engagement after 3 seconds of reaching the end
        setTimeout(() => {
            if (!engagementShown) {
                const timeSpent = (Date.now() - readingStartTime) / 1000;
                
                // Only show if user spent at least 10 seconds reading
                if (timeSpent >= 10) {
                    progressDiv.style.display = 'block';
                    progressDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    engagementShown = true;
                }
            }
        }, 3000);
    }
}

function markHelpful(isHelpful) {
    const faqId = <?php echo $faq['id']; ?>;
    const type = isHelpful ? 'helpful' : 'not_helpful';
    
    fetch('api/quick-feedback.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            type: type,
            faq_id: faqId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('reading-progress').innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-heart text-danger"></i>
                    <strong>Thank you!</strong> Your feedback helps us improve. 
                    ${isHelpful ? '' : ' Consider <a href="feedback.php?faq_id=' + faqId + '&type=suggestion" class="alert-link">sharing more details</a> to help us improve this FAQ.'}
                </div>
            `;
        }
    })
    .catch(error => console.error('Error:', error));
}

// Start tracking when page loads
document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener('scroll', trackReadingProgress);
    window.addEventListener('resize', trackReadingProgress);
    
    // Initial check
    setTimeout(trackReadingProgress, 1000);
});
</script>

<?php require_once 'includes/footer.php'; ?>