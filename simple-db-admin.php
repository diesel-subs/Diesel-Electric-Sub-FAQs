<?php
require_once 'config/database.php';

$page_title = 'Database Management';
require_once 'includes/header.php';

$action = $_GET['action'] ?? 'overview';
$table = $_GET['table'] ?? '';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1><i class="fas fa-database text-primary"></i> Database Management</h1>
            <p class="lead">Manage your submarine FAQ database directly through the web interface.</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="?action=overview" class="btn btn-<?php echo $action === 'overview' ? 'primary' : 'outline-primary'; ?>">
                    <i class="fas fa-home"></i> Overview
                </a>
                <a href="?action=browse" class="btn btn-<?php echo $action === 'browse' ? 'primary' : 'outline-primary'; ?>">
                    <i class="fas fa-table"></i> Browse Data
                </a>
                <a href="?action=query" class="btn btn-<?php echo $action === 'query' ? 'primary' : 'outline-primary'; ?>">
                    <i class="fas fa-code"></i> SQL Query
                </a>
                <a href="?action=backup" class="btn btn-<?php echo $action === 'backup' ? 'primary' : 'outline-primary'; ?>">
                    <i class="fas fa-download"></i> Backup
                </a>
            </div>
        </div>
    </div>

    <?php if ($action === 'overview'): ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle"></i> Database Status</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $version = $pdo->query("SELECT VERSION() as version")->fetch()['version'];
                            echo "<p><strong>MySQL Version:</strong> $version</p>";
                            
                            $dbName = $pdo->query("SELECT DATABASE() as db")->fetch()['db'];
                            echo "<p><strong>Current Database:</strong> $dbName</p>";
                            
                            echo "<p><strong>Connection:</strong> <span class='text-success'>✅ Connected</span></p>";
                        } catch (Exception $e) {
                            echo "<p><strong>Connection:</strong> <span class='text-danger'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</span></p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar"></i> Database Statistics</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $faqCount = $pdo->query("SELECT COUNT(*) as count FROM faqs")->fetch()['count'];
                            echo "<p><strong>Total FAQs:</strong> $faqCount</p>";
                            
                            $categoryCount = $pdo->query("SELECT COUNT(*) as count FROM categories")->fetch()['count'];
                            echo "<p><strong>Categories:</strong> $categoryCount</p>";
                            
                            // Get database size
                            $stmt = $pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB' FROM information_schema.tables WHERE table_schema = DATABASE()");
                            $size = $stmt->fetch()['DB Size in MB'];
                            echo "<p><strong>Database Size:</strong> {$size} MB</p>";
                        } catch (Exception $e) {
                            echo "<p class='text-danger'>Error getting statistics: " . htmlspecialchars($e->getMessage()) . "</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-table"></i> Database Tables</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $stmt = $pdo->query("SHOW TABLES");
                            $tables = $stmt->fetchAll();
                            
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-striped'>";
                            echo "<thead><tr><th>Table Name</th><th>Rows</th><th>Actions</th></tr></thead>";
                            echo "<tbody>";
                            
                            foreach ($tables as $tableRow) {
                                $tableName = array_values($tableRow)[0];
                                
                                try {
                                    $rowCount = $pdo->query("SELECT COUNT(*) as count FROM `$tableName`")->fetch()['count'];
                                } catch (Exception $e) {
                                    $rowCount = 'N/A';
                                }
                                
                                echo "<tr>";
                                echo "<td><strong>$tableName</strong></td>";
                                echo "<td>$rowCount</td>";
                                echo "<td>";
                                echo "<a href='?action=browse&table=$tableName' class='btn btn-sm btn-outline-primary me-1'>Browse</a>";
                                echo "<a href='?action=structure&table=$tableName' class='btn btn-sm btn-outline-info'>Structure</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody></table></div>";
                        } catch (Exception $e) {
                            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($action === 'browse' && !empty($table)): ?>
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-table"></i> Browse Table: <?php echo htmlspecialchars($table); ?></h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $limit = 25;
                    $page = (int)($_GET['page'] ?? 1);
                    $offset = ($page - 1) * $limit;
                    
                    $stmt = $pdo->prepare("SELECT * FROM `$table` LIMIT ? OFFSET ?");
                    $stmt->execute([$limit, $offset]);
                    $data = $stmt->fetchAll();
                    
                    if (!empty($data)) {
                        echo "<div class='table-responsive'>";
                        echo "<table class='table table-striped table-sm'>";
                        
                        // Header
                        echo "<thead><tr>";
                        foreach (array_keys($data[0]) as $column) {
                            if (!is_numeric($column)) {
                                echo "<th>" . htmlspecialchars($column) . "</th>";
                            }
                        }
                        echo "</tr></thead>";
                        
                        // Data
                        echo "<tbody>";
                        foreach ($data as $row) {
                            echo "<tr>";
                            foreach ($row as $key => $value) {
                                if (!is_numeric($key)) {
                                    $value = $value ?? '';
                                    $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                                    echo "<td>" . htmlspecialchars($displayValue) . "</td>";
                                }
                            }
                            echo "</tr>";
                        }
                        echo "</tbody></table></div>";
                        
                        // Pagination
                        $totalRows = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                        $totalPages = ceil($totalRows / $limit);
                        
                        if ($totalPages > 1) {
                            echo "<nav><ul class='pagination'>";
                            for ($i = 1; $i <= $totalPages; $i++) {
                                $active = $i === $page ? 'active' : '';
                                echo "<li class='page-item $active'>";
                                echo "<a class='page-link' href='?action=browse&table=$table&page=$i'>$i</a>";
                                echo "</li>";
                            }
                            echo "</ul></nav>";
                        }
                    } else {
                        echo "<div class='alert alert-info'>No data found in table.</div>";
                    }
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
                ?>
            </div>
        </div>

    <?php elseif ($action === 'structure' && !empty($table)): ?>
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-cogs"></i> Table Structure: <?php echo htmlspecialchars($table); ?></h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $stmt = $pdo->query("DESCRIBE `$table`");
                    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($columns) {
                        echo "<div class='table-responsive'>";
                        echo "<table class='table table-striped table-hover'>";
                        echo "<thead class='table-dark'><tr>";
                        echo "<th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
                        echo "</tr></thead><tbody>";
                        
                        foreach ($columns as $column) {
                            echo "<tr>";
                            echo "<td><strong>" . htmlspecialchars($column['Field']) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
                            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
                            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
                            echo "<td>" . htmlspecialchars($column['Default'] ?? '') . "</td>";
                            echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
                            echo "</tr>";
                        }
                        
                        echo "</tbody></table></div>";
                        
                        // Add browse button
                        echo "<div class='mt-3'>";
                        echo "<a href='?action=browse&table=$table' class='btn btn-primary'>Browse Data</a>";
                        echo "<a href='?action=overview' class='btn btn-outline-secondary ms-2'>Back to Overview</a>";
                        echo "</div>";
                    } else {
                        echo "<div class='alert alert-warning'>No structure information found for this table.</div>";
                    }
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>Error loading table structure: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
                ?>
            </div>
        </div>

    <?php elseif ($action === 'query'): ?>
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-code"></i> SQL Query Interface</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="sql_query" class="form-label">SQL Query:</label>
                        <textarea class="form-control" id="sql_query" name="sql_query" rows="5" placeholder="SELECT * FROM faqs LIMIT 10;"><?php echo htmlspecialchars($_POST['sql_query'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Execute Query</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('sql_query').value = 'SELECT * FROM faqs LIMIT 10;'">Sample Query</button>
                </form>
                
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['sql_query'])) {
                    $query = trim($_POST['sql_query']);
                    
                    try {
                        $stmt = $pdo->prepare($query);
                        $stmt->execute();
                        
                        if (stripos($query, 'SELECT') === 0) {
                            $results = $stmt->fetchAll();
                            
                            if (!empty($results)) {
                                echo "<div class='mt-4'>";
                                echo "<h6>Query Results (" . count($results) . " rows):</h6>";
                                echo "<div class='table-responsive'>";
                                echo "<table class='table table-striped table-sm'>";
                                
                                // Header
                                echo "<thead><tr>";
                                foreach (array_keys($results[0]) as $column) {
                                    if (!is_numeric($column)) {
                                        echo "<th>" . htmlspecialchars($column) . "</th>";
                                    }
                                }
                                echo "</tr></thead>";
                                
                                // Data
                                echo "<tbody>";
                                foreach ($results as $row) {
                                    echo "<tr>";
                                    foreach ($row as $key => $value) {
                                        if (!is_numeric($key)) {
                                            echo "<td>" . htmlspecialchars($value) . "</td>";
                                        }
                                    }
                                    echo "</tr>";
                                }
                                echo "</tbody></table></div></div>";
                            } else {
                                echo "<div class='alert alert-info mt-4'>Query executed successfully. No results returned.</div>";
                            }
                        } else {
                            $affected = $stmt->rowCount();
                            echo "<div class='alert alert-success mt-4'>Query executed successfully. $affected rows affected.</div>";
                        }
                    } catch (Exception $e) {
                        echo "<div class='alert alert-danger mt-4'>Query Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                    }
                }
                ?>
            </div>
        </div>

    <?php elseif ($action === 'browse'): ?>
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-table"></i> Browse Database Tables</h5>
            </div>
            <div class="card-body">
                <p>Select a table to browse its contents:</p>
                <?php
                try {
                    $stmt = $pdo->query("SHOW TABLES");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    if ($tables) {
                        echo "<div class='list-group'>";
                        foreach ($tables as $tableName) {
                            $countStmt = $pdo->query("SELECT COUNT(*) FROM `$tableName`");
                            $rowCount = $countStmt->fetchColumn();
                            
                            echo "<a href='?action=browse&table=$tableName' class='list-group-item list-group-item-action d-flex justify-content-between align-items-center'>";
                            echo "<span><i class='fas fa-table me-2'></i>$tableName</span>";
                            echo "<span class='badge bg-primary rounded-pill'>$rowCount rows</span>";
                            echo "</a>";
                        }
                        echo "</div>";
                    } else {
                        echo "<div class='alert alert-warning'>No tables found in the database.</div>";
                    }
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>Error loading tables: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
                ?>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-info">
            <h5>Select an option from the menu above</h5>
            <p>Use the navigation buttons to explore your database:</p>
            <ul>
                <li><strong>Overview:</strong> Database status and table summary</li>
                <li><strong>Browse Data:</strong> View table contents</li>
                <li><strong>SQL Query:</strong> Execute custom SQL commands</li>
                <li><strong>Backup:</strong> Export database data</li>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>