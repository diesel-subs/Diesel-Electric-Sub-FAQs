<?php
require_once 'config/database.php';
require_once 'includes/markdown-helper.php';

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
    ORDER BY display_order ASC, featured DESC, title ASC
");
$stmt->execute([$category['id']]);
$faqs = $stmt->fetchAll();

// Preload contributions grouped by FAQ
$contribMap = [];
$contribStmt = $pdo->prepare("
    SELECT faq_id, contributor_name, contributed_at, notes
    FROM faq_contributions
    WHERE faq_id IN (SELECT id FROM faqs WHERE category_id = ?)
    ORDER BY contributed_at DESC, id DESC
");
$contribStmt->execute([$category['id']]);
while ($row = $contribStmt->fetch()) {
    $contribMap[$row['faq_id']][] = $row;
}

$page_title = $category['name'];
$page_description = '';
// Clear description to avoid showing legacy "Questions about ..." copy
$category['description'] = '';
require_once 'includes/header.php';

function category_icon_fallback($name, $icon) {
    $icon = trim((string)$icon);
    if (!empty($icon) && stripos($icon, 'question-circle') === false) {
        return $icon;
    }
    $map = [
        'us ww2 subs in general' => 'fas fa-ship',
        'hull and compartments' => 'fas fa-cogs',
        'operating us subs in ww2' => 'fas fa-compass',
        'life aboard ww2 us subs' => 'fas fa-users',
        'who were the crews aboard ww2 us subs' => 'fas fa-user-friends',
        'crews aboard ww2 us subs' => 'fas fa-user-friends',
        'attacks and battles, small and large' => 'fas fa-crosshairs',
    ];
    $key = strtolower(trim($name));
    return $map[$key] ?? 'fas fa-ship';
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <!-- Breadcrumb and Search -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    </ol>
                </nav>
                <div class="category-search flex-grow-1" style="max-width: 640px;">
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" placeholder="Enter text to search category..." id="category-search">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
            </div>

            <!-- Category name below breadcrumb -->
            <h1 class="mb-4"><?php echo htmlspecialchars($category['name']); ?></h1>

            <?php if (empty($faqs)): ?>
                <div class="alert alert-info">
                    <h4>No FAQs Available</h4>
                    <p>There are currently no FAQs available in this category. Check back later for updates!</p>
                    <a href="index.php" class="btn btn-primary">Browse Other Categories</a>
                </div>
            <?php else: ?>
                <!-- Spacer after header/search -->
                <div class="mb-4"></div>

                <!-- Feedback Incentive Section -->
                <div class="feedback-incentive mb-4">
                    <div class="alert alert-info border-info">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-trophy fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading mb-1">Help Us Improve This Category!</h5>
                                <p class="mb-2">Your feedback really helps.</p>
                            </div>
                            <div class="ms-2">
                                <a href="feedback.php?category_id=<?php echo (int)$category['id']; ?>&category=<?php echo urlencode($category['name']); ?>" 
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-star"></i> Share Feedback
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQs List -->
                <ol class="faqs-list list-unstyled">
                    <?php foreach ($faqs as $idx => $faq): ?>
                        <?php $num = $idx + 1; ?>
                        <li class="faq-row" data-faq-id="<?php echo $faq['id']; ?>">
                            <div class="faq-row-header" data-bs-toggle="collapse" data-bs-target="#faq-collapse-<?php echo $faq['id']; ?>" aria-expanded="false">
                                <span class="faq-number"><?php echo $num; ?></span>
                                <span class="faq-question"><?php echo htmlspecialchars($faq['title']); ?></span>
                                <span class="faq-arrow"><i class="fas fa-chevron-down"></i></span>
                            </div>
                            <div id="faq-collapse-<?php echo $faq['id']; ?>" class="collapse">
                                    <div class="faq-body">
                                        <div class="faq-content">
                                            <?php echo render_content($faq['answer']); ?>
                                        </div>
                                    <?php if (!empty($faq['author']) || !empty($faq['date_submitted'])): ?>
                                        <div class="text-muted mt-3">
                                            <strong>Created by:</strong>
                                            <?php if (!empty($faq['author'])): ?>
                                                <span class="ms-1"><i class="fas fa-user"></i> <?php echo htmlspecialchars($faq['author']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($faq['date_submitted'])): ?>
                                                <span class="ms-3"><i class="fas fa-calendar-alt"></i> <?php echo date('M j, Y', strtotime($faq['date_submitted'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($contribMap[$faq['id']])): ?>
                                        <?php $label = count($contribMap[$faq['id']]) > 1 ? 'Contributions by:' : 'Contribution by:'; ?>
                                        <div class="mt-2 text-muted">
                                            <?php foreach ($contribMap[$faq['id']] as $idx => $c): ?>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="contrib-label"><?php echo $idx === 0 ? $label : ''; ?></span>
                                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($c['contributor_name']); ?></span>
                                                    <?php if (!empty($c['contributed_at'])): ?>
                                                        <span><i class="fas fa-calendar-alt"></i> <?php echo date('M j, Y', strtotime($c['contributed_at'])); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
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
                                    <?php 
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
                        </li>
                    <?php endforeach; ?>
                </ol>

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

<style>
.faqs-list {
    counter-reset: faq-counter;
}
.faq-row {
    border-bottom: 1px solid #e5e5e5;
}
.faq-row-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    cursor: pointer;
    user-select: none;
}
.faq-number {
    font-weight: 700;
    color: #444;
    min-width: 22px;
}
.faq-question {
    font-weight: 600;
    flex: 1;
    color: #111;
}
.faq-arrow {
    color: #666;
    transition: transform 0.2s ease;
}
.faq-row-header[aria-expanded="true"] .faq-arrow i {
    transform: rotate(180deg);
}
.faq-body {
    padding: 0 0 10px 34px;
}
.faq-row:last-child {
    border-bottom: none;
}
.contrib-label {
    display: inline-block;
    width: 210px;
    font-weight: 600;
}
</style>

<?php require_once 'includes/footer.php'; ?>
