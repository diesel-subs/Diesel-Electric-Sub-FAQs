<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$success = null;
$error = null;

// Handle AJAX drag-save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Category reordering
    if (isset($data['order']) && is_array($data['order'])) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
            foreach ($data['order'] as $index => $id) {
                $stmt->execute([($index + 1) * 10, (int)$id]);
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

// Handle edit/delete (non-AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    if ($_POST['action'] === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($name === '') {
            $error = "Name is required.";
        } else {
            $nextOrder = (int)$pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 10 AS next_order FROM categories")->fetchColumn();
            $slug = get_category_slug($name);
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, icon, sort_order) VALUES (?, ?, ?, '', ?)");
            $stmt->execute([$name, $slug, $description, $nextOrder]);
            $success = "Category added.";
        }
    }

    if ($_POST['action'] === 'update_category' && isset($_POST['category_id'])) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, icon = '' WHERE id = ?");
        $stmt->execute([
            trim($_POST['name'] ?? ''),
            trim($_POST['description'] ?? ''),
            (int)$_POST['category_id']
        ]);
        $success = "Category updated.";
    }

    if ($_POST['action'] === 'delete_category' && isset($_POST['category_id'])) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([(int)$_POST['category_id']]);
        $success = "Category deleted.";
    }
}

// Load categories
$categoriesStmt = $pdo->query("SELECT id, name, description, icon, sort_order FROM categories ORDER BY sort_order ASC, name ASC");
$categories = $categoriesStmt->fetchAll();

?>

<?php require_once '../includes/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-folder-open"></i> Categories</h1>
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
        <div class="card-body border-bottom">
            <form method="POST" class="row g-3 align-items-end">
                <input type="hidden" name="action" value="add_category">
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" name="name" placeholder="New category name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Description (optional)</label>
                    <input type="text" class="form-control" name="description" placeholder="Short description">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="table-responsive">
                    <table class="table align-middle" id="category-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Actions</th>
                                <th style="width: 25px;">Drag</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody id="category-rows">
                            <?php foreach ($categories as $cat): ?>
                                <tr data-id="<?php echo $cat['id']; ?>">
                                    <td class="text-start">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary edit-btn" data-target="edit-cat-<?php echo $cat['id']; ?>" title="Edit" aria-label="Edit category">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger delete-btn" data-id="<?php echo $cat['id']; ?>" title="Delete" aria-label="Delete category">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="drag-handle" title="Drag to reorder">
                                            <i class="fas fa-grip-vertical"></i>
                                        </span>
                                    </td>
                                    <td class="fw-bold">
                                        <a href="../category.php?cat=<?php echo urlencode($cat['name']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </a>
                                    </td>
                                </tr>
                                <tr class="edit-row d-none" id="edit-cat-<?php echo $cat['id']; ?>">
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="form-label mb-1">Name</label>
                                                <input type="text" class="form-control form-control-sm edit-name" value="<?php echo htmlspecialchars($cat['name']); ?>">
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label mb-1">Description</label>
                                                <input type="text" class="form-control form-control-sm edit-description" value="<?php echo htmlspecialchars($cat['description'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-primary save-edit-btn" data-id="<?php echo $cat['id']; ?>">
                                                <i class="fas fa-save"></i> Save
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary cancel-edit-btn" data-target="edit-cat-<?php echo $cat['id']; ?>">
                                                Cancel
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
        if (row.classList.contains('edit-row')) return;
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
        saveOrder();
    }

    function handleDragEnd() {
        this.classList.remove('dragging');
        updateOrderValues();
        saveOrder();
    }

    function updateOrderValues() {
        const newRows = Array.from(tbody.querySelectorAll('tr'));
        newRows.forEach((row, index) => {
            const orderValue = (index + 1) * 10; // leave gaps for future inserts
            const input = row.querySelector('input[type="hidden"]');
            input.value = orderValue;
        });
    }

    async function saveOrder() {
        const ids = Array.from(tbody.querySelectorAll('tr')).map(row => row.dataset.id);
        try {
            await fetch('manage-categories.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ order: ids })
            });
        } catch (err) {
            console.error('Failed to save order', err);
        }
    }

    // Edit handlers
    document.addEventListener('click', (e) => {
        if (e.target.closest('.edit-btn')) {
            const targetId = e.target.closest('.edit-btn').dataset.target;
            const editRow = document.getElementById(targetId);
            if (editRow) {
                editRow.classList.toggle('d-none');
            }
        }

        if (e.target.closest('.cancel-edit-btn')) {
            const targetId = e.target.closest('.cancel-edit-btn').dataset.target;
            const editRow = document.getElementById(targetId);
            if (editRow) {
                editRow.classList.add('d-none');
            }
        }

        if (e.target.closest('.save-edit-btn')) {
            const id = e.target.closest('.save-edit-btn').dataset.id;
            const editRow = document.getElementById('edit-cat-' + id);
            if (!editRow) return;
            const name = editRow.querySelector('.edit-name').value;
            const description = editRow.querySelector('.edit-description').value;
            saveCategory(id, { name, description });
        }

        if (e.target.closest('.delete-btn')) {
            const id = e.target.closest('.delete-btn').dataset.id;
            if (confirm('Delete this category? This will also remove related FAQs.')) {
                saveCategory(id, null, true);
            }
        }
    });

    async function saveCategory(id, data, isDelete = false) {
        const form = new FormData();
        form.append('action', isDelete ? 'delete_category' : 'update_category');
        form.append('category_id', id);
        if (!isDelete && data) {
            form.append('name', data.name);
            form.append('description', data.description);
        }
        try {
            await fetch('manage-categories.php', { method: 'POST', body: form });
            window.location.reload();
        } catch (err) {
            console.error('Failed to save category', err);
        }
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
.btn-group-sm > .btn {
    padding: 0.2rem 0.35rem;
}
</style>

<?php require_once '../includes/footer.php'; ?>
