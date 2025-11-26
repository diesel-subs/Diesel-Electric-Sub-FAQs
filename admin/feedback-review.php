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

$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];
if ($status_filter !== '') {
    $where[] = 'f.status = ?';
    $params[] = $status_filter;
}
if ($type_filter !== '') {
    $where[] = 'f.feedback_type = ?';
    $params[] = $type_filter;
}
if ($search !== '') {
    $like = '%' . $search . '%';
    $where[] = '(f.message LIKE ? OR f.subject LIKE ? OR f.name LIKE ? OR f.email LIKE ?)';
    array_push($params, $like, $like, $like, $like);
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
    SELECT f.*, faq.title AS faq_title, c.name AS category_name
    FROM feedback f
    LEFT JOIN faqs faq ON f.faq_id = faq.id
    LEFT JOIN categories c ON (
        (f.category_id IS NOT NULL AND f.category_id = c.id) OR
        (faq.category_id = c.id)
    )
    $whereSql
    ORDER BY f.created_at DESC
");
$stmt->execute($params);
$feedback = $stmt->fetchAll();

function truncate_text($text, $len = 140) {
    $text = trim((string)$text);
    if (strlen($text) <= $len) return $text;
    return substr($text, 0, $len - 3) . '...';
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="fas fa-comments"></i> All Feedback</h1>
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Dashboard</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <?php foreach (['pending','approved','rejected','implemented'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $status_filter === $s ? 'selected' : ''; ?>>
                        <?php echo ucfirst($s); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
                <option value="">All</option>
                <?php foreach (['correction','suggestion','new_faq','technical','general','praise'] as $t): ?>
                    <option value="<?php echo $t; ?>" <?php echo $type_filter === $t ? 'selected' : ''; ?>>
                        <?php echo ucfirst(str_replace('_', ' ', $t)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Message, subject, name, email">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <strong><?php echo count($feedback); ?> feedback item(s)</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th>Category</th>
                            <th>FAQ</th>
                            <th>Message</th>
                            <th>From</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($feedback)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No feedback found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($feedback as $fb): ?>
                                <tr>
                                    <td>
                                        <a href="feedback-view.php?id=<?php echo (int)$fb['id']; ?>">
                                            <?php echo (int)$fb['id']; ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars(date('m/d/Y', strtotime($fb['created_at']))); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($fb['status']); ?></span></td>
                                    <td><?php echo htmlspecialchars($fb['category_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($fb['faq_title'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(truncate_text($fb['message'])); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($fb['name'] ?: 'Anonymous'); ?>
                                        <?php if (!empty($fb['email'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($fb['email']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
