<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../includes/header.php';

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && isset($_POST['faq_id'])) {
        $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
        $stmt->execute([$_POST['faq_id']]);
        $success = "FAQ deleted successfully!";
    }
    
    if ($_POST['action'] === 'toggle_publish' && isset($_POST['faq_id'])) {
        $stmt = $pdo->prepare("UPDATE faqs SET is_published = NOT is_published WHERE id = ?");
        $stmt->execute([$_POST['faq_id']]);
        $success = "FAQ status updated!";
    }
}

// Get search parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$whereConditions = [];
$params = [];

if ($search) {
    $whereConditions[] = "(title LIKE ? OR question LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%"; 
    $params[] = "%$search%";
}

if ($category) {
    $whereConditions[] = "category_id = ?";
    $params[] = $category;
}

if ($status === 'published') {
    $whereConditions[] = "is_published = 1";
} elseif ($status === 'draft') {
    $whereConditions[] = "is_published = 0";
}

$whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get FAQs
$sql = "
    SELECT f.*, c.name as category_name 
    FROM faqs f 
    LEFT JOIN categories c ON f.category_id = c.id 
    $whereClause 
    ORDER BY f.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$faqs = $stmt->fetchAll();

// Get categories for filter
$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-list"></i> Manage FAQs</h1>
        <div>
            <a href="../edit-faq-wysiwyg.php" class="btn btn-success me-2">
                <i class="fas fa-plus"></i> New FAQ
            </a>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" placeholder="Search FAQs...">
                </div>
                
                <div class="col-md-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- FAQs Table -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-table"></i> FAQs (<?php echo count($faqs); ?> found)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($faqs)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No FAQs found</h5>
                    <p class="text-muted">Try adjusting your search criteria or create a new FAQ.</p>
                    <a href="../edit-faq-wysiwyg.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create First FAQ
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($faqs as $faq): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($faq['title']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($faq['question'], 0, 100)); ?>...</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($faq['category_name']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($faq['is_published']): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $faq['view_count']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($faq['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="../faq.php?id=<?php echo $faq['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" target="_blank" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="../edit-faq-wysiwyg.php?id=<?php echo $faq['id']; ?>" 
                                               class="btn btn-sm btn-outline-success" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_publish">
                                                <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-info" 
                                                        title="<?php echo $faq['is_published'] ? 'Unpublish' : 'Publish'; ?>">
                                                    <i class="fas fa-<?php echo $faq['is_published'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this FAQ?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>