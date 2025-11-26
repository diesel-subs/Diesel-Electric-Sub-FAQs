<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../includes/header.php';

// Locale-aware date formatter (falls back to US-style if intl not available)
if (!function_exists('formatLocalDate')) {
    function formatLocalDate($datetime) {
        // Fixed US-style numeric date
        return date('m/d/Y', strtotime($datetime));
    }
}

// AJAX reorder handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['faq_order'], $data['category_id']) && is_array($data['faq_order'])) {
        $catId = (int)$data['category_id'];
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE faqs SET display_order = ? WHERE id = ? AND category_id = ?");
            $pos = 10;
            foreach ($data['faq_order'] as $faqId) {
                $stmt->execute([$pos, (int)$faqId, $catId]);
                $pos += 10;
            }
            $pdo->commit();
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}

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

// Get categories for filter (needed to set default)
$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC");
$categories = $categoriesStmt->fetchAll();

// Default to first category if none selected
if ($category === '' && !empty($categories)) {
    $category = (string)$categories[0]['id'];
}

// Build query
$whereConditions = [];
$params = [];

if ($search) {
    $whereConditions[] = "(title LIKE ? OR question LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%"; 
    $params[] = "%$search%";
}

if ($category !== '') {
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
                    <select class="form-select" id="category" name="category" required>
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
                    <table class="table table-striped align-middle" id="faq-table">
                        <thead>
                            <tr>
                                <th style="width:45px;"></th>
                                <th>Question</th>
                                <th>Status</th>
                                <th class="text-center">Views</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="faq-table-rows">
                            <?php foreach ($faqs as $faq): ?>
                                <tr class="faq-row-table" draggable="true" data-id="<?php echo $faq['id']; ?>">
                                    <td class="text-center">
                                        <span class="drag-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($faq['title']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($faq['is_published']): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo $faq['view_count']; ?></td>
                                    <td>
                                        <span class="created-date" data-date="<?php echo htmlspecialchars($faq['created_at']); ?>">
                                            <?php echo htmlspecialchars(formatLocalDate($faq['created_at'])); ?>
                                        </span>
                                    </td>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Table drag-drop reorder
    const tableBody = document.getElementById('faq-table-rows');
    const filterCategorySelect = document.getElementById('category');
    if (tableBody && filterCategorySelect) {
        let draggedRow = null;
        const tableRows = Array.from(tableBody.querySelectorAll('.faq-row-table'));

        tableRows.forEach(row => {
            row.addEventListener('dragstart', tableDragStart);
            row.addEventListener('dragover', tableDragOver);
            row.addEventListener('drop', tableDrop);
            row.addEventListener('dragend', tableDragEnd);
        });

        function tableDragStart(e) {
            draggedRow = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        }

        function tableDragOver(e) {
            e.preventDefault();
            const target = e.currentTarget;
            if (target === draggedRow) return;
            const rect = target.getBoundingClientRect();
            const offset = rect.y + rect.height / 2;
            if (e.clientY - offset > 0) {
                target.after(draggedRow);
            } else {
                target.before(draggedRow);
            }
        }

        function tableDrop(e) {
            e.preventDefault();
            saveTableOrder();
        }

        function tableDragEnd() {
            this.classList.remove('dragging');
            saveTableOrder();
        }

        async function saveTableOrder() {
            const ids = Array.from(tableBody.querySelectorAll('.faq-row-table')).map(r => r.dataset.id);
            const catId = filterCategorySelect.value;
            try {
                await fetch('manage-faqs.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ faq_order: ids, category_id: catId })
                });
            } catch (err) {
                console.error('Failed to save FAQ order', err);
            }
        }
    }
});
</script>

<style>
.drag-handle {
    cursor: grab;
}
.drag-handle:active {
    cursor: grabbing;
}
.faq-row-table.dragging {
    opacity: 0.6;
    background: #f8f9fa;
}
</style>

<?php require_once '../includes/footer.php'; ?>
