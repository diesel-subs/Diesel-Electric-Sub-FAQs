<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../includes/header.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$feedback_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = null;
$error = null;

// Handle status update to completed/implemented
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $feedback_id > 0) {
    $newStatus = 'implemented';
    try {
        $stmt = $pdo->prepare("UPDATE feedback SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $feedback_id]);
        $message = "Feedback #{$feedback_id} marked as completed.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch feedback details
$stmt = $pdo->prepare("
    SELECT f.*, faq.title AS faq_title, c.name AS category_name
    FROM feedback f
    LEFT JOIN faqs faq ON f.faq_id = faq.id
    LEFT JOIN categories c ON (
        (f.category_id IS NOT NULL AND f.category_id = c.id) OR
        (faq.category_id = c.id)
    )
    WHERE f.id = ?
    LIMIT 1
");
$stmt->execute([$feedback_id]);
$fb = $stmt->fetch();

if (!$fb) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Feedback not found.</div></div>";
    require_once '../includes/footer.php';
    exit;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="fas fa-comment-dots"></i> Feedback #<?php echo (int)$fb['id']; ?></h1>
        <div>
            <a href="feedback-review.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Feedback</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger">Error: <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <p class="mb-1"><strong>Status:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($fb['status'])); ?></span></p>
            <p class="mb-1"><strong>Created:</strong> <?php echo htmlspecialchars(date('m/d/Y H:i', strtotime($fb['created_at']))); ?></p>
            <p class="mb-1"><strong>Type:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_',' ', $fb['feedback_type']))); ?></p>
            <?php if (!empty($fb['category_name'])): ?>
                <p class="mb-1"><strong>Category:</strong> <?php echo htmlspecialchars($fb['category_name']); ?></p>
            <?php endif; ?>
            <?php if (!empty($fb['faq_title'])): ?>
                <p class="mb-1"><strong>FAQ:</strong> <?php echo htmlspecialchars($fb['faq_title']); ?></p>
            <?php endif; ?>
            <p class="mb-1"><strong>From:</strong> <?php echo htmlspecialchars($fb['name'] ?: 'Anonymous'); ?>
                <?php if (!empty($fb['email'])): ?>
                    <span class="text-muted">(<?php echo htmlspecialchars($fb['email']); ?>)</span>
                <?php endif; ?>
            </p>
            <hr>
            <p class="mb-0"><strong>Message:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($fb['message'])); ?></p>
        </div>
    </div>

    <form method="POST" class="d-inline">
        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Mark Completed</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
