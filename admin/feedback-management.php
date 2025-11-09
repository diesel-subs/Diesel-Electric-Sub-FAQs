<?php
$page_title = 'Feedback Management';
$page_description = 'Review and manage visitor feedback and suggestions';
require_once '../config/database.php';

// Simple admin check (you can enhance this later)
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    // For now, just show a login form
    if ($_POST['admin_password'] ?? '' === 'submarine_admin_2024') {
        $_SESSION['admin_logged_in'] = true;
    } else {
        ?>
        <!DOCTYPE html>
        <html><head><title>Admin Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head><body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5>Admin Login</h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <input type="password" class="form-control" name="admin_password" placeholder="Admin Password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </body></html>
        <?php
        exit;
    }
}

require_once '../includes/header.php';

// Handle feedback actions
if ($_POST['action'] ?? '') {
    $feedback_id = (int)($_POST['feedback_id'] ?? 0);
    $action = $_POST['action'];
    
    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE feedback SET status = 'approved' WHERE id = ?");
            $stmt->execute([$feedback_id]);
            echo "<div class='alert alert-success'>Feedback approved!</div>";
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE feedback SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$feedback_id]);
            echo "<div class='alert alert-info'>Feedback rejected!</div>";
        } elseif ($action === 'implement') {
            $stmt = $pdo->prepare("UPDATE feedback SET status = 'implemented' WHERE id = ?");
            $stmt->execute([$feedback_id]);
            echo "<div class='alert alert-success'>Feedback marked as implemented!</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-comments"></i> Feedback Management</h1>
        <a href="../index.php" class="btn btn-outline-primary">Back to Site</a>
    </div>
    
    <?php
    try {
        // Get feedback statistics
        $stmt = $pdo->query("
            SELECT 
                status,
                COUNT(*) as count,
                feedback_type,
                AVG(rating) as avg_rating
            FROM feedback 
            GROUP BY status, feedback_type
            ORDER BY status, feedback_type
        ");
        $stats = $stmt->fetchAll();
        
        // Get recent feedback
        $stmt = $pdo->query("
            SELECT f.*, faq.title as faq_title, c.name as category_name
            FROM feedback f
            LEFT JOIN faqs faq ON f.faq_id = faq.id
            LEFT JOIN categories c ON faq.category_id = c.id
            ORDER BY f.created_at DESC
            LIMIT 50
        ");
        $feedback_list = $stmt->fetchAll();
        
        // Display statistics
        echo "<div class='row mb-4'>";
        $status_counts = [];
        $type_counts = [];
        
        foreach ($stats as $stat) {
            $status_counts[$stat['status']] = ($status_counts[$stat['status']] ?? 0) + $stat['count'];
            $type_counts[$stat['feedback_type']] = ($type_counts[$stat['feedback_type']] ?? 0) + $stat['count'];
        }
        
        echo "<div class='col-md-3'><div class='card'><div class='card-body text-center'>";
        echo "<h4 class='text-warning'>" . ($status_counts['pending'] ?? 0) . "</h4>";
        echo "<p class='mb-0'>Pending</p></div></div></div>";
        
        echo "<div class='col-md-3'><div class='card'><div class='card-body text-center'>";
        echo "<h4 class='text-success'>" . ($status_counts['approved'] ?? 0) . "</h4>";
        echo "<p class='mb-0'>Approved</p></div></div></div>";
        
        echo "<div class='col-md-3'><div class='card'><div class='card-body text-center'>";
        echo "<h4 class='text-info'>" . ($status_counts['implemented'] ?? 0) . "</h4>";
        echo "<p class='mb-0'>Implemented</p></div></div></div>";
        
        echo "<div class='col-md-3'><div class='card'><div class='card-body text-center'>";
        echo "<h4 class='text-primary'>" . array_sum($status_counts) . "</h4>";
        echo "<p class='mb-0'>Total</p></div></div></div>";
        
        echo "</div>";
        
        // Display feedback list
        echo "<div class='card'>";
        echo "<div class='card-header'><h5>Recent Feedback</h5></div>";
        echo "<div class='card-body'>";
        
        if (empty($feedback_list)) {
            echo "<p class='text-muted text-center'>No feedback yet.</p>";
        } else {
            foreach ($feedback_list as $fb) {
                $badge_color = match($fb['status']) {
                    'pending' => 'bg-warning',
                    'approved' => 'bg-success',
                    'rejected' => 'bg-danger',
                    'implemented' => 'bg-info',
                    default => 'bg-secondary'
                };
                
                echo "<div class='border rounded p-3 mb-3'>";
                echo "<div class='d-flex justify-content-between align-items-start'>";
                echo "<div>";
                echo "<h6>" . htmlspecialchars($fb['subject'] ?: 'No subject') . "</h6>";
                echo "<small class='text-muted'>";
                echo "From: " . htmlspecialchars($fb['name'] ?: 'Anonymous') . " | ";
                echo "Type: " . ucfirst($fb['feedback_type']) . " | ";
                echo "Date: " . format_date($fb['created_at']);
                if ($fb['faq_title']) {
                    echo " | FAQ: " . htmlspecialchars($fb['faq_title']);
                }
                echo "</small>";
                if ($fb['rating']) {
                    echo "<div class='mt-1'>";
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $fb['rating'] ? '⭐' : '☆';
                    }
                    echo "</div>";
                }
                echo "</div>";
                echo "<span class='badge $badge_color'>" . ucfirst($fb['status']) . "</span>";
                echo "</div>";
                echo "<p class='mt-2 mb-2'>" . nl2br(htmlspecialchars($fb['message'])) . "</p>";
                
                if ($fb['status'] === 'pending') {
                    echo "<div class='d-flex gap-2'>";
                    echo "<form method='POST' class='d-inline'>";
                    echo "<input type='hidden' name='feedback_id' value='{$fb['id']}'>";
                    echo "<input type='hidden' name='action' value='approve'>";
                    echo "<button type='submit' class='btn btn-sm btn-success'>Approve</button>";
                    echo "</form>";
                    
                    echo "<form method='POST' class='d-inline'>";
                    echo "<input type='hidden' name='feedback_id' value='{$fb['id']}'>";
                    echo "<input type='hidden' name='action' value='reject'>";
                    echo "<button type='submit' class='btn btn-sm btn-outline-danger'>Reject</button>";
                    echo "</form>";
                    
                    echo "<form method='POST' class='d-inline'>";
                    echo "<input type='hidden' name='feedback_id' value='{$fb['id']}'>";
                    echo "<input type='hidden' name='action' value='implement'>";
                    echo "<button type='submit' class='btn btn-sm btn-info'>Mark Implemented</button>";
                    echo "</form>";
                    echo "</div>";
                }
                
                echo "</div>";
            }
        }
        
        echo "</div>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error loading feedback: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    ?>
    
</div>

<?php require_once '../includes/footer.php'; ?>