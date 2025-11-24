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

// Fetch FAQs for dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$faqs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $faq_id = (int)($_POST['faq_id'] ?? 0);
    $contributor_name = trim($_POST['contributor_name'] ?? '');
    $contributed_at = trim($_POST['contributed_at'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($faq_id <= 0 || $contributor_name === '') {
        $error = "FAQ and contributor name are required.";
    } else {
        if ($contributed_at !== '') {
            $dt = date_create($contributed_at);
            $contributed_at = $dt ? $dt->format('Y-m-d') : null;
        } else {
            $contributed_at = null;
        }

        $stmt = $pdo->prepare("INSERT INTO faq_contributions (faq_id, contributor_name, contributed_at, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$faq_id, $contributor_name, $contributed_at, $notes !== '' ? $notes : null]);
        $success = "Contribution recorded.";
    }
}

// Recent contributions
$recent = $pdo->query("
    SELECT fc.id, fc.contributor_name, fc.contributed_at, fc.notes, f.title, fc.faq_id
    FROM faq_contributions fc
    JOIN faqs f ON fc.faq_id = f.id
    ORDER BY fc.contributed_at DESC, fc.id DESC
    LIMIT 50
")->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-hands-helping"></i> Manage Contributions</h1>
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

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-plus"></i> Add Contribution</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3" id="contribForm">
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">FAQ</label>
                    <select name="faq_id" id="faq_id" class="form-select" required>
                        <option value="">Select FAQ</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contributor Name</label>
                    <input type="text" name="contributor_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date Submitted (optional)</label>
                    <input type="date" name="contributed_at" class="form-control">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="What was contributed?"></textarea>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Contribution
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list"></i> Recent Contributions</h5>
        </div>
        <div class="card-body">
            <?php if (empty($recent)): ?>
                <p class="text-muted">No contributions recorded yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>FAQ</th>
                                <th>Contributor</th>
                                <th>Date</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $row): ?>
                                <tr>
                                    <td><a href="../faq.php?id=<?php echo $row['faq_id']; ?>" target="_blank"><?php echo htmlspecialchars($row['title']); ?></a></td>
                                    <td><?php echo htmlspecialchars($row['contributor_name']); ?></td>
                                    <td><?php echo $row['contributed_at'] ? date('M j, Y', strtotime($row['contributed_at'])) : '—'; ?></td>
                                    <td><?php echo $row['notes'] ? nl2br(htmlspecialchars($row['notes'])) : '<span class="text-muted">—</span>'; ?></td>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const categorySelect = document.getElementById('category_id');
    const faqSelect = document.getElementById('faq_id');

    async function loadFaqs(categoryId) {
        faqSelect.innerHTML = '<option value="">Select FAQ</option>';
        if (!categoryId) return;
        const categoryName = categorySelect.options[categorySelect.selectedIndex].text || '';
        try {
            const res = await fetch(`../api/search.php?category=${encodeURIComponent(categoryName)}&limit=500&sort=title`);
            const data = await res.json();
            if (data.success && Array.isArray(data.results)) {
                const options = data.results
                    .sort((a, b) => a.title.localeCompare(b.title))
                    .map(f => `<option value="${f.id}">${f.title}</option>`)
                    .join('');
                faqSelect.innerHTML = '<option value="">Select FAQ</option>' + options;
            }
        } catch (e) {
            console.error('Failed to load FAQs', e);
        }
    }

    categorySelect.addEventListener('change', () => loadFaqs(categorySelect.value));
});
</script>
