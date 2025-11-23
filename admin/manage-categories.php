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
            <h5 class="mb-0"><i class="fas fa-arrows-alt-v"></i> Reorder Categories</h5>
            <small class="text-muted">Lower numbers appear first. Update the numbers and save.</small>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Sort</th>
                                <th>Name</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td>
                                        <input type="number" 
                                               name="sort_order[<?php echo $cat['id']; ?>]" 
                                               value="<?php echo (int)$cat['sort_order']; ?>" 
                                               class="form-control form-control-sm" 
                                               step="1">
                                    </td>
                                    <td class="fw-bold">
                                        <i class="<?php echo htmlspecialchars($cat['icon'] ?: 'fas fa-folder'); ?>"></i>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </td>
                                    <td class="text-muted">
                                        <?php echo htmlspecialchars($cat['description'] ?? ''); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Tip: use steps of 10 (10, 20, 30…) to leave room for future inserts.</small>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
