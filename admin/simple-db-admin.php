<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'execute_query':
                    $query = trim($_POST['query']);
                    if (!empty($query)) {
                        $stmt = $pdo->prepare($query);
                        $stmt->execute();
                        
                        if (stripos($query, 'SELECT') === 0) {
                            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $message = "Query executed successfully. " . count($results) . " rows returned.";
                        } else {
                            $rowCount = $stmt->rowCount();
                            $message = "Query executed successfully. $rowCount rows affected.";
                        }
                        $messageType = 'success';
                    }
                    break;
                    
                case 'clear_table':
                    $table = $_POST['table'];
                    $validTables = ['faqs', 'categories', 'feedback', 'related_faqs'];
                    if (in_array($table, $validTables)) {
                        $stmt = $pdo->prepare("DELETE FROM $table");
                        $stmt->execute();
                        $message = "Table '$table' cleared successfully.";
                        $messageType = 'success';
                    }
                    break;
                    
                case 'drop_table':
                    $table = $_POST['table'];
                    $validTables = ['faqs', 'categories', 'feedback', 'related_faqs'];
                    if (in_array($table, $validTables)) {
                        $stmt = $pdo->prepare("DROP TABLE IF EXISTS $table");
                        $stmt->execute();
                        $message = "Table '$table' dropped successfully.";
                        $messageType = 'success';
                    }
                    break;
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Get table information
$tables = [];
try {
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tableName = $row[0];
        
        // Get row count
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `$tableName`");
        $rowCount = $countStmt->fetchColumn();
        
        // Get table structure
        $structStmt = $pdo->query("DESCRIBE `$tableName`");
        $columns = $structStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $tables[$tableName] = [
            'row_count' => $rowCount,
            'columns' => $columns
        ];
    }
} catch (Exception $e) {
    $message = "Error fetching table information: " . $e->getMessage();
    $messageType = 'danger';
}

// Get sample data for each table
$sampleData = [];
foreach (array_keys($tables) as $tableName) {
    try {
        $stmt = $pdo->query("SELECT * FROM `$tableName` LIMIT 5");
        $sampleData[$tableName] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $sampleData[$tableName] = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Database Admin - Submarine FAQs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }
        .query-textarea {
            font-family: monospace;
            font-size: 14px;
        }
        .danger-zone {
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        .collapse {
            margin-bottom: 10px;
        }
        .table-details {
            border-left: 3px solid #007bff;
            padding-left: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-ship"></i> Submarine FAQs Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="manage-faqs.php">Manage FAQs</a>
                <a class="nav-link active" href="simple-db-admin.php">Database</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1><i class="bi bi-database"></i> Simple Database Administration</h1>
                <p class="text-muted">Manage your database tables and execute SQL queries</p>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tables Section -->
        <?php foreach ($tables as $tableName => $info): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <!-- Table Overview Card -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="bi bi-table"></i> <?php echo htmlspecialchars($tableName); ?>
                                    </h5>
                                    <small class="text-muted">
                                        Rows: <?php echo $info['row_count']; ?> | Columns: <?php echo count($info['columns']); ?>
                                    </small>
                                </div>
                                <div>
                                    <button class="btn btn-outline-primary btn-sm toggle-details" 
                                            data-table="<?php echo htmlspecialchars($tableName); ?>">
                                        <i class="bi bi-chevron-down"></i> Show Details
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Table Details (Initially Hidden) -->
                        <div class="card-body table-details d-none" id="details-<?php echo $tableName; ?>">
                            <!-- Column Structure -->
                            <h6><i class="bi bi-columns"></i> Column Structure:</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Column</th>
                                            <th>Type</th>
                                            <th>Null</th>
                                            <th>Key</th>
                                            <th>Default</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($info['columns'] as $column): ?>
                                            <tr>
                                                <td><code><?php echo htmlspecialchars($column['Field']); ?></code></td>
                                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($column['Type']); ?></span></td>
                                                <td><?php echo $column['Null'] === 'YES' ? '<span class="text-success">YES</span>' : '<span class="text-danger">NO</span>'; ?></td>
                                                    <td><?php echo !empty($column['Key']) ? '<span class="badge bg-warning text-dark">' . htmlspecialchars($column['Key']) . '</span>' : '-'; ?></td>
                                                    <td><?php echo $column['Default'] !== null && $column['Default'] !== '' ? htmlspecialchars($column['Default']) : '<em class="text-muted">NULL</em>'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Sample Data -->
                            <?php if (!empty($sampleData[$tableName])): ?>
                                <h6><i class="bi bi-clipboard-data"></i> Sample Data (First 5 rows):</h6>
                                <div class="table-container">
                                    <table class="table table-sm table-striped table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <?php foreach (array_keys($sampleData[$tableName][0]) as $column): ?>
                                                    <th><?php echo htmlspecialchars($column); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sampleData[$tableName] as $row): ?>
                                                <tr>
                                                    <?php foreach ($row as $value): ?>
                                                        <td title="<?php echo htmlspecialchars($value ?? ''); ?>">
                                                            <?php 
                                                            $displayValue = $value ?? '';
                                                            echo htmlspecialchars(strlen($displayValue) > 50 ? substr($displayValue, 0, 50) . '...' : $displayValue); 
                                                            ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> No data in this table.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- SQL Query Executor -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-code-slash"></i> SQL Query Executor</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="execute_query">
                            <div class="mb-3">
                                <label for="query" class="form-label">SQL Query:</label>
                                <textarea name="query" id="query" class="form-control query-textarea" rows="4" 
                                          placeholder="Enter your SQL query here...
Examples:
SELECT * FROM faqs LIMIT 10;
UPDATE faqs SET is_published = 1 WHERE id = 1;
SHOW TABLES;
DESCRIBE faqs;"><?php echo isset($_POST['query']) ? htmlspecialchars($_POST['query']) : ''; ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-play-fill"></i> Execute Query
                            </button>
                        </form>

                        <!-- Query Results -->
                        <?php if (isset($results) && !empty($results)): ?>
                            <hr>
                            <h6>Query Results:</h6>
                            <div class="table-container">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <?php foreach (array_keys($results[0]) as $column): ?>
                                                <th><?php echo htmlspecialchars($column); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $row): ?>
                                            <tr>
                                                <?php foreach ($row as $value): ?>
                                                    <td><?php echo htmlspecialchars($value); ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="row">
            <div class="col-12">
                <div class="danger-zone">
                    <h5 class="text-danger"><i class="bi bi-exclamation-triangle"></i> Danger Zone</h5>
                    <p class="text-muted">These actions cannot be undone. Use with caution!</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Clear Table Data</h6>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to clear all data from this table? This cannot be undone!')">
                                <input type="hidden" name="action" value="clear_table">
                                <div class="input-group mb-2">
                                    <select name="table" class="form-select" required>
                                        <option value="">Select table...</option>
                                        <?php foreach (array_keys($tables) as $tableName): ?>
                                            <option value="<?php echo htmlspecialchars($tableName); ?>"><?php echo htmlspecialchars($tableName); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-outline-danger">Clear Data</button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Drop Table</h6>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to drop this table? This will delete the table structure and all data permanently!')">
                                <input type="hidden" name="action" value="drop_table">
                                <div class="input-group mb-2">
                                    <select name="table" class="form-select" required>
                                        <option value="">Select table...</option>
                                        <?php foreach (array_keys($tables) as $tableName): ?>
                                            <option value="<?php echo htmlspecialchars($tableName); ?>"><?php echo htmlspecialchars($tableName); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-danger">Drop Table</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simple toggle functionality for table details
            const toggleButtons = document.querySelectorAll('.toggle-details');
            
            toggleButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const tableName = this.getAttribute('data-table');
                    const detailsDiv = document.getElementById('details-' + tableName);
                    const icon = this.querySelector('i');
                    
                    if (detailsDiv.classList.contains('d-none')) {
                        // Show details
                        detailsDiv.classList.remove('d-none');
                        icon.className = 'bi bi-chevron-up';
                        this.innerHTML = '<i class="bi bi-chevron-up"></i> Hide Details';
                        this.classList.remove('btn-outline-primary');
                        this.classList.add('btn-primary');
                    } else {
                        // Hide details
                        detailsDiv.classList.add('d-none');
                        icon.className = 'bi bi-chevron-down';
                        this.innerHTML = '<i class="bi bi-chevron-down"></i> Show Details';
                        this.classList.remove('btn-primary');
                        this.classList.add('btn-outline-primary');
                    }
                });
            });
        });
    </script>
</body>
</html>