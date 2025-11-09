<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Database Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Simple Database Manager</h1>
        
        <?php
        require_once 'config/database.php';
        
        try {
            // Test connection
            $stmt = $pdo->query("SELECT 'Database connection successful!' as message");
            $result = $stmt->fetch();
            echo "<div class='alert alert-success'>" . $result['message'] . "</div>";
            
            // Show databases
            echo "<h3>Available Databases:</h3>";
            $stmt = $pdo->query("SHOW DATABASES");
            echo "<ul class='list-group mb-4'>";
            while ($row = $stmt->fetch()) {
                echo "<li class='list-group-item'>" . $row['Database'] . "</li>";
            }
            echo "</ul>";
            
            // Show tables in submarine_faqs database
            echo "<h3>Tables in submarine_faqs:</h3>";
            $pdo->exec("USE submarine_faqs");
            $stmt = $pdo->query("SHOW TABLES");
            echo "<ul class='list-group mb-4'>";
            while ($row = $stmt->fetch()) {
                $tableName = $row['Tables_in_submarine_faqs'];
                echo "<li class='list-group-item'>";
                echo "<strong>$tableName</strong> ";
                echo "<a href='?table=$tableName' class='btn btn-sm btn-primary'>View Data</a>";
                echo "</li>";
            }
            echo "</ul>";
            
            // Show table data if requested
            if (isset($_GET['table'])) {
                $tableName = $_GET['table'];
                echo "<h3>Data from table: $tableName</h3>";
                
                $stmt = $pdo->query("SELECT * FROM `$tableName` LIMIT 10");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($rows) {
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-striped'>";
                    echo "<thead><tr>";
                    foreach (array_keys($rows[0]) as $column) {
                        echo "<th>$column</th>";
                    }
                    echo "</tr></thead><tbody>";
                    
                    foreach ($rows as $row) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</tbody></table></div>";
                } else {
                    echo "<p>No data found in table.</p>";
                }
            }
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Database Error: " . $e->getMessage() . "</div>";
        }
        ?>
        
        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary">Back to Main Site</a>
        </div>
    </div>
</body>
</html>