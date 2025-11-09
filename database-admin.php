<?php
// Include database config first to get SITE_NAME constant
require_once 'config/database.php';

$page_title = 'Database Management';
$page_description = 'Manage your submarine FAQ database';
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="admin-panel">
                <h1><i class="fas fa-database"></i> Database Management</h1>
                <p class="lead">Manage your Submarine FAQ database with these tools.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-tools"></i> phpMyAdmin</h5>
                </div>
                <div class="card-body">
                    <p>Full-featured database administration interface.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Browse and edit data</li>
                        <li><i class="fas fa-check text-success"></i> Run SQL queries</li>
                        <li><i class="fas fa-check text-success"></i> Import/Export data</li>
                        <li><i class="fas fa-check text-success"></i> Manage database structure</li>
                    </ul>
                    <a href="pma.php" class="btn btn-primary" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Open phpMyAdmin
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Database Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Database Name:</strong></td>
                            <td>submarine_faqs</td>
                        </tr>
                        <tr>
                            <td><strong>Host:</strong></td>
                            <td>localhost</td>
                        </tr>
                        <tr>
                            <td><strong>Username:</strong></td>
                            <td>submarine_user</td>
                        </tr>
                        <tr>
                            <td><strong>Tables:</strong></td>
                            <td>categories, faqs, admin_users</td>
                        </tr>
                    </table>
                    
                    <div class="mt-3">
                        <h6>Quick Actions:</h6>
                        <a href="?action=backup" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download"></i> Backup Database
                        </a>
                        <a href="?action=stats" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-chart-bar"></i> View Statistics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['action'])): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>
                        <?php if ($_GET['action'] === 'stats'): ?>
                            <i class="fas fa-chart-bar"></i> Database Statistics
                        <?php elseif ($_GET['action'] === 'backup'): ?>
                            <i class="fas fa-download"></i> Database Backup
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($_GET['action'] === 'stats'): ?>
                        <?php
                        require_once 'config/database.php';
                        try {
                            $stats = [
                                'categories' => $pdo->query("SELECT COUNT(*) as count FROM categories")->fetch()['count'],
                                'faqs' => $pdo->query("SELECT COUNT(*) as count FROM faqs")->fetch()['count'],
                                'published_faqs' => $pdo->query("SELECT COUNT(*) as count FROM faqs WHERE status = 'published'")->fetch()['count'],
                                'total_views' => $pdo->query("SELECT SUM(views) as total FROM faqs")->fetch()['total'] ?? 0,
                                'featured_faqs' => $pdo->query("SELECT COUNT(*) as count FROM faqs WHERE featured = 1")->fetch()['count']
                            ];
                        ?>
                        <div class="row text-center">
                            <div class="col-md-2">
                                <h3 class="text-primary"><?php echo $stats['categories']; ?></h3>
                                <p>Categories</p>
                            </div>
                            <div class="col-md-2">
                                <h3 class="text-success"><?php echo $stats['faqs']; ?></h3>
                                <p>Total FAQs</p>
                            </div>
                            <div class="col-md-2">
                                <h3 class="text-info"><?php echo $stats['published_faqs']; ?></h3>
                                <p>Published</p>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-warning"><?php echo number_format($stats['total_views']); ?></h3>
                                <p>Total Views</p>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-danger"><?php echo $stats['featured_faqs']; ?></h3>
                                <p>Featured FAQs</p>
                            </div>
                        </div>
                        <?php
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">Error loading statistics: ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                        ?>
                    
                    <?php elseif ($_GET['action'] === 'backup'): ?>
                        <div class="alert alert-info">
                            <h6>Manual Backup Instructions:</h6>
                            <p>To create a backup of your database, run this command in your terminal:</p>
                            <code>mysqldump -u submarine_user -p submarine_faqs > backup_$(date +%Y%m%d_%H%M%S).sql</code>
                            <p class="mt-2"><small>You can also use phpMyAdmin's export feature for a GUI-based backup.</small></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="text-center">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>