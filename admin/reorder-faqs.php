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

$success = null;
$error = null;
$category_id = (int)($_GET['category_id'] ?? $_POST['category_id'] ?? 0);

// Load categories
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $category_id > 0 && isset($_POST['sort_order']) && is_array($_POST['sort_order'])) {
    // AJAX autosave handled below; keep POST block for non-js fallback
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE faqs SET display_order = ? WHERE id = ? AND category_id = ?");
        $position = 10;
        foreach ($_POST['sort_order'] as $faqId => $orderVal) {
            $stmt->execute([$position, (int)$faqId, $category_id]);
            $position += 10;
        }
        $pdo->commit();
        $success = "FAQ order updated.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to update order: " . $e->getMessage();
    }
}

$faqs = [];
if ($category_id > 0) {
    $faqStmt = $pdo->prepare("SELECT id, title, display_order FROM faqs WHERE category_id = ? ORDER BY display_order ASC, title ASC");
    $faqStmt->execute([$category_id]);
    $faqs = $faqStmt->fetchAll();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-sort-numeric-down"></i> Reorder FAQs</h1>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="GET" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_id === (int)$cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <?php if ($category_id > 0): ?>
                    <a href="edit-faq.php?category_id=<?php echo $category_id; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-plus"></i> Add FAQ to this category
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if ($category_id > 0 && empty($faqs)): ?>
        <p class="text-muted">No FAQs found in this category.</p>
    <?php endif; ?>

    <?php if ($category_id > 0 && !empty($faqs)): ?>
    <form method="POST" id="reorderForm">
        <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
        <div class="list-group" id="faq-list">
            <?php foreach ($faqs as $faq): ?>
                <div class="list-group-item d-flex align-items-center draggable-row" draggable="true" data-id="<?php echo $faq['id']; ?>">
                    <span class="drag-handle me-3"><i class="fas fa-grip-vertical"></i></span>
                    <span class="faq-title flex-grow-1"><?php echo htmlspecialchars($faq['title']); ?></span>
                    <input type="hidden" name="sort_order[<?php echo $faq['id']; ?>]" value="<?php echo (int)$faq['display_order']; ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </form>
    <?php endif; ?>
</div>

<style>
.draggable-row {
    cursor: grab;
}
.draggable-row.dragging {
    opacity: 0.6;
    background: #f8f9fa;
}
.drag-handle {
    cursor: grab;
    color: #6c757d;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('faq-list');
    if (!list) return;
    const rows = Array.from(list.querySelectorAll('.draggable-row'));

    rows.forEach(row => {
        row.addEventListener('dragstart', handleDragStart);
        row.addEventListener('dragover', handleDragOver);
        row.addEventListener('drop', handleDrop);
        row.addEventListener('dragend', handleDragEnd);
    });

    let dragged = null;

    function handleDragStart(e) {
        dragged = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    }

    function handleDragOver(e) {
        e.preventDefault();
        const target = e.currentTarget;
        if (target === dragged) return;
        const rect = target.getBoundingClientRect();
        const offset = rect.y + rect.height / 2;
        if (e.clientY - offset > 0) {
            target.after(dragged);
        } else {
            target.before(dragged);
        }
    }

    function handleDrop(e) {
        e.preventDefault();
        updateOrderValues();
    }

    function handleDragEnd() {
        this.classList.remove('dragging');
        updateOrderValues();
        autoSave();
    }

    function updateOrderValues() {
        const rows = Array.from(list.querySelectorAll('.draggable-row'));
        rows.forEach((row, idx) => {
            const input = row.querySelector('input[type=\"hidden\"]');
            input.value = (idx + 1) * 10;
        });
    }
    function autoSave() {
        const form = document.getElementById('reorderForm');
        const formData = new FormData(form);
        fetch('reorder-faqs.php', { method: 'POST', body: formData }).catch(err => console.error('Autosave failed', err));
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
