<?php
require_once 'config/database.php';
require_once 'includes/header.php';

// Get feedback statistics
try {
    // Total feedback count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM feedback WHERE status = 'approved'");
    $total_feedback = $stmt->fetch()['total'];
    
    // Recent feedback
    $stmt = $pdo->query("
        SELECT f.*, fq.title as faq_title, c.name as category_name 
        FROM feedback f 
        LEFT JOIN faqs fq ON f.faq_id = fq.id 
        LEFT JOIN categories c ON fq.category_id = c.id 
        WHERE f.status = 'approved' 
        ORDER BY f.created_at DESC 
        LIMIT 10
    ");
    $recent_feedback = $stmt->fetchAll();
    
    // Quick feedback stats (if table exists)
    $quick_stats = [];
    try {
        $stmt = $pdo->query("
            SELECT value, COUNT(*) as count 
            FROM quick_feedback 
            WHERE type = 'exit_intent' 
            GROUP BY value
        ");
        $quick_stats = $stmt->fetchAll();
    } catch (Exception $e) {
        // Table might not exist yet
    }
    
    // Top categories by feedback
    $stmt = $pdo->query("
        SELECT c.name, COUNT(f.id) as feedback_count
        FROM categories c
        LEFT JOIN faqs fq ON c.id = fq.category_id
        LEFT JOIN feedback f ON fq.id = f.faq_id
        WHERE f.status = 'approved'
        GROUP BY c.id, c.name
        ORDER BY feedback_count DESC
        LIMIT 6
    ");
    $category_stats = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

$page_title = "Community Feedback Dashboard";
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-chart-line"></i> Community Feedback Dashboard</h1>
                    <p class="text-muted">See how our submarine knowledge base grows with community input!</p>
                </div>
                <div>
                    <a href="feedback.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Your Feedback
                    </a>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-comments fa-2x mb-2"></i>
                            <h3><?php echo number_format($total_feedback); ?></h3>
                            <small>Total Feedback Submissions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-thumbs-up fa-2x mb-2"></i>
                            <h3><?php 
                                $helpful_count = 0;
                                foreach ($quick_stats as $stat) {
                                    if ($stat['value'] == 'helpful') $helpful_count = $stat['count'];
                                }
                                echo number_format($helpful_count);
                            ?></h3>
                            <small>Helpful Ratings</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h3><?php echo count($category_stats); ?></h3>
                            <small>Active Categories</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <h3><?php echo date('M d'); ?></h3>
                            <small>Last Updated</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Recent Community Feedback -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-history"></i> Recent Community Contributions</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_feedback)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5>Be the First to Contribute!</h5>
                                    <p class="text-muted">No feedback submissions yet. Help us improve by sharing your thoughts!</p>
                                    <a href="feedback.php" class="btn btn-primary">Share Feedback</a>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recent_feedback as $item): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-comment-dots text-primary"></i>
                                                    <?php 
                                                    if ($item['faq_title']) {
                                                        echo 'Feedback on: ' . htmlspecialchars($item['faq_title']);
                                                    } else {
                                                        echo 'General ' . ucfirst($item['feedback_type']) . ' Feedback';
                                                    }
                                                    ?>
                                                </h6>
                                                <small><?php echo date('M j, Y', strtotime($item['created_at'])); ?></small>
                                            </div>
                                            <?php if ($item['category_name']): ?>
                                                <p class="mb-1"><small class="text-muted">Category: <?php echo htmlspecialchars($item['category_name']); ?></small></p>
                                            <?php endif; ?>
                                            <small>
                                                <span class="badge bg-<?php 
                                                    echo match($item['feedback_type']) {
                                                        'correction' => 'warning',
                                                        'suggestion' => 'info',
                                                        'praise' => 'success',
                                                        default => 'secondary'
                                                    };
                                                ?>"><?php echo ucfirst($item['feedback_type']); ?></span>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Category Leaderboard -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-trophy"></i> Most Active Categories</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($category_stats)): ?>
                                <p class="text-muted">No feedback data available yet.</p>
                            <?php else: ?>
                                <?php foreach ($category_stats as $index => $cat): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <?php if ($index === 0): ?>
                                                <i class="fas fa-crown text-warning"></i>
                                            <?php elseif ($index === 1): ?>
                                                <i class="fas fa-medal text-secondary"></i>
                                            <?php elseif ($index === 2): ?>
                                                <i class="fas fa-award text-warning"></i>
                                            <?php else: ?>
                                                <i class="fas fa-circle text-muted"></i>
                                            <?php endif; ?>
                                            <small><?php echo htmlspecialchars($cat['name']); ?></small>
                                        </div>
                                        <span class="badge bg-primary"><?php echo $cat['feedback_count']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <hr>
                            <div class="text-center">
                                <h6>Join Our Community!</h6>
                                <p class="small text-muted">Help improve our submarine knowledge base</p>
                                <a href="feedback.php" class="btn btn-success btn-sm w-100">
                                    <i class="fas fa-heart"></i> Contribute Now
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Feedback Stats -->
                    <?php if (!empty($quick_stats)): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6><i class="fas fa-pulse"></i> Quick Feedback</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($quick_stats as $stat): ?>
                                <div class="d-flex justify-content-between mb-1">
                                    <small>
                                        <?php 
                                        echo match($stat['value']) {
                                            'helpful' => '👍 Helpful',
                                            'partial' => '😐 Partial',
                                            'not_helpful' => '👎 Needs Work',
                                            default => ucfirst($stat['value'])
                                        };
                                        ?>
                                    </small>
                                    <span class="badge bg-info"><?php echo $stat['count']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Call to Action -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-success">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5><i class="fas fa-rocket"></i> Want to Make a Difference?</h5>
                                <p class="mb-0">Every piece of feedback helps us create the most comprehensive submarine resource on the web. Your expertise matters!</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="feedback.php" class="btn btn-success">Share Your Knowledge</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.badge {
    font-size: 0.75em;
}
</style>

<?php require_once 'includes/footer.php'; ?>