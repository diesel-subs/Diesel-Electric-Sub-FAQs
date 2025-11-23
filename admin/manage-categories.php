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

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sort_order'])) {
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
        foreach ($_POST['sort_order'] as $id => $order) {
            $stmt->execute([(int)$order, (int)$id]);
        }
        $pdo->commit();
        $success = "Category order updated.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to update order: " . $e->getMessage();
    }
}

// Load categories
$categoriesStmt = $pdo->query("SELECT id, name, description, icon, sort_order FROM categories ORDER BY sort_order ASC, name ASC");
$categories = $categoriesStmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-folder-open"></i> Manage Categories</h1>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-arrows-alt"></i> Drag to Reorder Categories</h5>
            <small class="text-muted">Drag rows with the handle to set order. Lower numbers appear first.</small>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="table-responsive">
                    <table class="table align-middle" id="category-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Drag</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody id="category-rows">
                            <?php foreach ($categories as $cat): ?>
                                <tr data-id="<?php echo $cat['id']; ?>">
                                    <td class="text-center">
                                        <span class="drag-handle" title="Drag to reorder">
                                            <i class="fas fa-grip-vertical"></i>
                                        </span>
                                    </td>
                                    <td class="fw-bold">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Tip: drag to set order; numbers will be saved automatically.</small>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('category-rows');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.forEach(row => {
        row.draggable = true;
        row.addEventListener('dragstart', handleDragStart);
        row.addEventListener('dragover', handleDragOver);
        row.addEventListener('drop', handleDrop);
        row.addEventListener('dragend', handleDragEnd);
    });

    let draggedRow = null;

    function handleDragStart(e) {
        draggedRow = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const target = e.currentTarget;
        if (target === draggedRow) return;

        const bounding = target.getBoundingClientRect();
        const offset = bounding.y + (bounding.height / 2);
        if (e.clientY - offset > 0) {
            target.after(draggedRow);
        } else {
            target.before(draggedRow);
        }
    }

    function handleDrop(e) {
        e.preventDefault();
        updateOrderValues();
    }

    function handleDragEnd() {
        this.classList.remove('dragging');
        updateOrderValues();
    }

    function updateOrderValues() {
        const newRows = Array.from(tbody.querySelectorAll('tr'));
        newRows.forEach((row, index) => {
            const orderValue = (index + 1) * 10; // leave gaps for future inserts
            const input = row.querySelector('input[type="hidden"]');
            input.value = orderValue;
        });
    }
});
</script>

<style>
#category-rows tr.dragging {
    opacity: 0.6;
    background: #f8f9fa;
}
.drag-handle {
    cursor: grab;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem 0.5rem;
}
.drag-handle:active {
    cursor: grabbing;
}
</style>

<?php require_once '../includes/footer.php'; ?>
