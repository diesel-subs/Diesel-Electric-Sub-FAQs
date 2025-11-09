<?php
require_once 'config/database.php';

$category_name = $_GET['cat'] ?? '';
if (empty($category_name)) {
    header('Location: index.php');
    exit;
}

// Get category information
$stmt = $pdo->prepare("SELECT * FROM categories WHERE name = ?");
$stmt->execute([$category_name]);
$category = $stmt->fetch();

if (!$category) {
    header('HTTP/1.0 404 Not Found');
    $page_title = 'Category Not Found';
    require_once 'includes/header.php';
    echo '<div class="container"><h1>Category Not Found</h1><p>The requested category does not exist.</p></div>';
    require_once 'includes/footer.php';
    exit;
}

// Get FAQs for this category
$stmt = $pdo->prepare("
    SELECT * FROM faqs 
    WHERE category_id = ? AND status = 'published' 
    ORDER BY featured DESC, title ASC
");
$stmt->execute([$category['id']]);
$faqs = $stmt->fetchAll();

$page_title = $category['name'];
$page_description = $category['description'];
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($category['name']); ?></li>
                </ol>
            </nav>

            <!-- Category Header -->
            <div class="category-header mb-4">
                <h1>
                    <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
                    <?php echo htmlspecialchars($category['name']); ?>
                </h1>
                <p class="lead"><?php echo htmlspecialchars($category['description']); ?></p>
            </div>

            <?php if (empty($faqs)): ?>
                <div class="alert alert-info">
                    <h4>No FAQs Available</h4>
                    <p>There are currently no FAQs available in this category. Check back later for updates!</p>
                    <a href="index.php" class="btn btn-primary">Browse Other Categories</a>
                </div>
            <?php else: ?>
                <!-- FAQ Count and Search -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="text-muted"><?php echo count($faqs); ?> FAQ(s) found</span>
                    <div class="category-search">
                        <input type="text" class="form-control" placeholder="Search within category..." id="category-search">
                    </div>
                </div>

                <!-- Feedback Incentive Section -->
                <div class="feedback-incentive mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="alert alert-info border-info">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-trophy fa-2x text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="alert-heading mb-1">Help Us Improve This Category!</h5>
                                        <p class="mb-2">Your feedback makes our submarine knowledge base more accurate and helpful for everyone.</p>
                                        <div class="feedback-stats">
                                            <small class="text-muted">
                                                📊 This category has <strong><?php echo count($faqs); ?> FAQs</strong> • 
                                                Join <strong id="feedback-count">our community</strong> of submarine enthusiasts!
                                            </small>
                                        </div>
                                    </div>
                                    <div class="ms-2">
                                        <a href="feedback.php?category=<?php echo urlencode($category['name']); ?>" 
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-star"></i> Share Feedback
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-body text-center py-2">
                                    <h6 class="card-title mb-1">
                                        <i class="fas fa-lightbulb text-warning"></i> Quick Actions
                                    </h6>
                                    <div class="btn-group-vertical btn-group-sm w-100">
                                        <a href="feedback.php?type=new_faq&category=<?php echo urlencode($category['name']); ?>" 
                                           class="btn btn-outline-success btn-sm">
                                            + Suggest New FAQ
                                        </a>
                                        <a href="feedback.php?type=correction&category=<?php echo urlencode($category['name']); ?>" 
                                           class="btn btn-outline-warning btn-sm">
                                            🔧 Report Issue
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQs List -->
                <div class="faqs-container">
                    <?php foreach ($faqs as $faq): ?>
                        <div class="faq-item mb-4" data-faq-id="<?php echo $faq['id']; ?>">
                            <div class="card">
                                <div class="card-header" id="faq-heading-<?php echo $faq['id']; ?>">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#faq-collapse-<?php echo $faq['id']; ?>"
                                                aria-expanded="false">
                                            <?php if ($faq['featured']): ?>
                                                <i class="fas fa-star text-warning"></i>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($faq['title']); ?>
                                            <small class="text-muted">(<?php echo $faq['views']; ?> views)</small>
                                        </button>
                                    </h5>
                                </div>
                                <div id="faq-collapse-<?php echo $faq['id']; ?>" 
                                     class="collapse" 
                                     data-bs-parent=".faqs-container">
                                    <div class="card-body">
                                        <div class="faq-content">
                                            <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                                        </div>
                                        <?php if (!empty($faq['tags'])): ?>
                                            <div class="faq-tags mt-3">
                                                <small class="text-muted">Tags: </small>
                                                <?php 
                                                $tags = explode(',', $faq['tags']);
                                                foreach ($tags as $tag): ?>
                                                    <span class="badge bg-secondary me-1"><?php echo trim(htmlspecialchars($tag)); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <!-- Feedback Widget -->
                                        <?php 
                                        // Set the current FAQ for the feedback widget
                                        $current_faq = $faq;
                                        include 'includes/feedback-widget.php'; 
                                        ?>
                                        
                                        <div class="faq-actions mt-3">
                                            <a href="faq.php?id=<?php echo $faq['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-link"></i> Direct Link
                                            </a>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="copyLink(<?php echo $faq['id']; ?>)">
                                                <i class="fas fa-copy"></i> Copy Link
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Back to Categories -->
                <div class="text-center mt-5">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to All Categories
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Category search functionality
document.getElementById('category-search').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const title = item.querySelector('.btn-link').textContent.toLowerCase();
        const content = item.querySelector('.faq-content').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || content.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Copy link functionality
function copyLink(faqId) {
    const url = window.location.origin + '/faq.php?id=' + faqId;
    navigator.clipboard.writeText(url).then(() => {
        // Show success feedback
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
        }, 2000);
    });
}

// Track FAQ views when expanded
document.addEventListener('DOMContentLoaded', function() {
    const faqColllapses = document.querySelectorAll('[id^="faq-collapse-"]');
    faqColllapses.forEach(collapse => {
        collapse.addEventListener('shown.bs.collapse', function() {
            const faqId = this.id.replace('faq-collapse-', '');
            // Track view (you can send AJAX request to track views)
            fetch('api/track-view.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({faq_id: faqId})
            });
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>