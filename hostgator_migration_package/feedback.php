<?php
$page_title = 'Feedback & Suggestions';
$page_description = 'Help us improve our submarine FAQ collection by sharing your feedback, corrections, and suggestions.';
require_once 'config/database.php';
require_once 'includes/header.php';

// Handle feedback submission
$submitted = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $feedback_type = $_POST['feedback_type'] ?? '';
        $faq_id = !empty($_POST['faq_id']) ? (int)$_POST['faq_id'] : null;
        $subject = sanitize_input($_POST['subject'] ?? '');
        $message = sanitize_input($_POST['message'] ?? '');
        $rating = !empty($_POST['rating']) ? (int)$_POST['rating'] : null;
        
        if (empty($message)) {
            throw new Exception('Message is required');
        }
        
        // Insert feedback into database
        $stmt = $pdo->prepare("
            INSERT INTO feedback (name, email, feedback_type, faq_id, subject, message, rating, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->execute([$name, $email, $feedback_type, $faq_id, $subject, $message, $rating]);
        $submitted = true;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <?php if ($submitted): ?>
                <div class="alert alert-success">
                    <h4><i class="fas fa-check-circle"></i> Thank You!</h4>
                    <p>Your feedback has been submitted successfully. We appreciate your contribution to improving our submarine FAQ collection!</p>
                    <a href="index.php" class="btn btn-primary">Return to Home</a>
                </div>
            <?php else: ?>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h1 class="card-title mb-0">
                        <i class="fas fa-comments"></i>
                        Share Your Feedback
                    </h1>
                </div>
                <div class="card-body">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <p class="lead">Help us improve our submarine FAQ collection! Your feedback is valuable whether you're a history buff, veteran, bubblehead, researcher, or just curious about WWII submarines.</p>
                    
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <i class="fas fa-edit display-4 text-info mb-2"></i>
                            <h6>Corrections</h6>
                            <p class="small text-muted">Found an error? Let us know!</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-plus-circle display-4 text-success mb-2"></i>
                            <h6>Suggestions</h6>
                            <p class="small text-muted">Ideas for new content or features</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-star display-4 text-warning mb-2"></i>
                            <h6>Reviews</h6>
                            <p class="small text-muted">Rate your experience</p>
                        </div>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       placeholder="Optional - helps us give credit">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Optional - for follow-up questions">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="feedback_type" class="form-label">Feedback Type *</label>
                                <select class="form-select" id="feedback_type" name="feedback_type" required>
                                    <option value="">Select type...</option>
                                    <option value="correction" <?php echo (isset($_GET['type']) && $_GET['type'] === 'correction') ? 'selected' : ''; ?>>Factual Correction</option>
                                    <option value="suggestion" <?php echo (isset($_GET['type']) && $_GET['type'] === 'suggestion') ? 'selected' : ''; ?>>Content Suggestion</option>
                                    <option value="new_faq" <?php echo (isset($_GET['type']) && $_GET['type'] === 'new_faq') ? 'selected' : ''; ?>>Request New FAQ</option>
                                    <option value="technical">Technical Issue</option>
                                    <option value="general">General Feedback</option>
                                    <option value="praise">Compliment/Praise</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="rating" class="form-label">Overall Rating</label>
                                <select class="form-select" id="rating" name="rating">
                                    <option value="">Rate your experience...</option>
                                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                    <option value="4">⭐⭐⭐⭐ Very Good</option>
                                    <option value="3">⭐⭐⭐ Good</option>
                                    <option value="2">⭐⭐ Fair</option>
                                    <option value="1">⭐ Needs Improvement</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="related_faq" class="form-label">Related FAQ (Optional)</label>
                            <select class="form-select" id="related_faq" name="faq_id">
                                <option value="">Select if feedback relates to a specific FAQ...</option>
                                <?php
                                try {
                                    $stmt = $pdo->query("
                                        SELECT f.id, f.title, c.name as category_name 
                                        FROM faqs f 
                                        JOIN categories c ON f.category_id = c.id 
                                        WHERE f.status = 'published' 
                                        ORDER BY c.name, f.title
                                    ");
                                    $current_category = '';
                                    while ($faq = $stmt->fetch()) {
                                        if ($faq['category_name'] !== $current_category) {
                                            if ($current_category !== '') echo '</optgroup>';
                                            echo '<optgroup label="' . htmlspecialchars($faq['category_name']) . '">';
                                            $current_category = $faq['category_name'];
                                        }
                                        echo '<option value="' . $faq['id'] . '">' . htmlspecialchars($faq['title']) . '</option>';
                                    }
                                    if ($current_category !== '') echo '</optgroup>';
                                } catch (Exception $e) {
                                    echo '<option value="">Error loading FAQs</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   placeholder="Brief summary of your feedback">
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" 
                                      placeholder="Please share your feedback, suggestions, corrections, or questions..." required></textarea>
                            <div class="form-text">Be as detailed as possible. For corrections, please include specific information or sources.</div>
                        </div>
                        
                        <div class="alert alert-info">
                            <small>
                                <strong>Types of feedback we especially appreciate:</strong><br>
                                • Factual corrections with sources<br>
                                • Personal experiences from veterans or family members<br>
                                • Additional historical details or context<br>
                                • Suggestions for new FAQ topics<br>
                                • Technical issues with the website<br>
                                • Ideas for improving user experience
                            </small>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Home
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php endif; ?>
            
            <!-- Feedback Statistics -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i>
                        Community Contributions
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        // Get feedback statistics (you'll need to create the feedback table first)
                        $stmt = $pdo->query("
                            SELECT 
                                COUNT(*) as total_feedback,
                                AVG(rating) as avg_rating,
                                COUNT(CASE WHEN feedback_type = 'correction' THEN 1 END) as corrections,
                                COUNT(CASE WHEN feedback_type = 'suggestion' THEN 1 END) as suggestions
                            FROM feedback 
                            WHERE status IN ('approved', 'pending')
                        ");
                        $stats = $stmt->fetch();
                        
                        if ($stats && $stats['total_feedback'] > 0) {
                            echo "<div class='row text-center'>";
                            echo "<div class='col-md-3'>";
                            echo "<h4 class='text-primary'>" . $stats['total_feedback'] . "</h4>";
                            echo "<p class='mb-0 small'>Total Feedback</p>";
                            echo "</div>";
                            echo "<div class='col-md-3'>";
                            echo "<h4 class='text-success'>" . round($stats['avg_rating'] ?? 0, 1) . "/5</h4>";
                            echo "<p class='mb-0 small'>Average Rating</p>";
                            echo "</div>";
                            echo "<div class='col-md-3'>";
                            echo "<h4 class='text-info'>" . $stats['corrections'] . "</h4>";
                            echo "<p class='mb-0 small'>Corrections</p>";
                            echo "</div>";
                            echo "<div class='col-md-3'>";
                            echo "<h4 class='text-warning'>" . $stats['suggestions'] . "</h4>";
                            echo "<p class='mb-0 small'>Suggestions</p>";
                            echo "</div>";
                            echo "</div>";
                        } else {
                            echo "<p class='text-center text-muted'>Be the first to leave feedback!</p>";
                        }
                    } catch (Exception $e) {
                        echo "<p class='text-center text-muted'>Help us build a community of submarine history enthusiasts!</p>";
                    }
                    ?>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>