<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../includes/header.php';

// Simple authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get statistics
$totalFaqs = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs WHERE is_published = 1");
    $publishedFaqs = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs WHERE is_published = 0");
    $draftFaqs = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM faqs");
    $totalFaqs = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $totalCategories = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM feedback WHERE status = 'pending'");
    $pendingFeedback = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT SUM(view_count) as total FROM faqs");
    $totalViews = $stmt->fetch()['total'] ?? 0;
    
    // Recent FAQs
    $stmt = $pdo->query("SELECT id, title, created_at, view_count FROM faqs ORDER BY created_at DESC LIMIT 5");
    $recentFaqs = $stmt->fetchAll();
    
    // Popular FAQs
    $stmt = $pdo->query("SELECT id, title, view_count FROM faqs ORDER BY view_count DESC LIMIT 5");
    $popularFaqs = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        <div>
            <a href="../index.php" class="btn btn-outline-primary me-2" target="_blank">
                <i class="fas fa-external-link-alt"></i> View Site
            </a>
            <a href="logout.php" class="btn btn-outline-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Error: <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="quick-actions">
                                    <a href="manage-faqs.php" class="btn btn-success btn-lg quick-action-btn">
                                        <i class="fas fa-list"></i>
                                        <span>FAQs (<?php echo number_format($totalFaqs); ?>)</span>
                                    </a>
                                    <a href="manage-categories.php" class="btn btn-info btn-lg quick-action-btn">
                                        <i class="fas fa-folder-open"></i>
                                        <span>Categories (<?php echo number_format($totalCategories); ?>)</span>
                                    </a>
                            <a href="feedback-review.php" class="btn btn-warning btn-lg quick-action-btn">
                                <i class="fas fa-comments"></i>
                                <span>Feedback</span>
                            </a>
                            <a href="manage-contributions.php" class="btn btn-outline-primary btn-lg quick-action-btn">
                                <i class="fas fa-hands-helping"></i>
                                <span>Contributors</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="row">
        <!-- Recent FAQs -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-clock"></i> Recent FAQs</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentFaqs)): ?>
                        <p class="text-muted">No FAQs found.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentFaqs as $faq): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">
                                            <a href="../faq.php?id=<?php echo $faq['id']; ?>" target="_blank" class="text-decoration-none">
                                                <?php echo htmlspecialchars($faq['title']); ?>
                                            </a>
                                        </div>
                                        <small class="text-muted">
                                            Created: <?php echo date('M j, Y', strtotime($faq['created_at'])); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><?php echo $faq['view_count']; ?> views</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Popular FAQs -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-fire"></i> Most Popular FAQs</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($popularFaqs)): ?>
                        <p class="text-muted">No FAQs found.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($popularFaqs as $faq): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">
                                            <a href="../faq.php?id=<?php echo $faq['id']; ?>" target="_blank" class="text-decoration-none">
                                                <?php echo htmlspecialchars($faq['title']); ?>
                                            </a>
                                        </div>
                                    </div>
                                    <span class="badge bg-success rounded-pill"><?php echo $faq['view_count']; ?> views</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}
.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    min-width: 220px;
    min-height: 140px;
    padding: 20px 20px;
    text-align: center;
    white-space: normal;
}
</style>

<?php require_once '../includes/footer.php'; ?>
