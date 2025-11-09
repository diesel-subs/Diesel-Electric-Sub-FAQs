<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Short Answer Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Short Answer Column Demo</h1>
        <p class="lead">The FAQs table now includes a <code>short_answer</code> column limited to 4096 bytes.</p>
        
        <?php
        require_once 'config/database.php';
        
        try {
            // Show all FAQs with both full and short answers
            $stmt = $pdo->query("
                SELECT id, title, question, answer, short_answer,
                       LENGTH(answer) as full_length,
                       LENGTH(short_answer) as short_length
                FROM faqs 
                WHERE status = 'published' 
                ORDER BY id
            ");
            $faqs = $stmt->fetchAll();
            
            foreach ($faqs as $faq) {
                echo "<div class='card mb-4'>";
                echo "<div class='card-header'>";
                echo "<h5>FAQ #{$faq['id']}: " . htmlspecialchars($faq['title']) . "</h5>";
                echo "<div class='d-flex gap-3'>";
                echo "<small class='text-muted'>Full Answer: {$faq['full_length']} bytes</small>";
                echo "<small class='text-muted'>Short Answer: {$faq['short_length']} bytes</small>";
                echo "</div>";
                echo "</div>";
                
                echo "<div class='card-body'>";
                echo "<h6>Question:</h6>";
                echo "<p class='text-muted'>" . htmlspecialchars($faq['question']) . "</p>";
                
                echo "<div class='row'>";
                echo "<div class='col-md-6'>";
                echo "<h6>Full Answer:</h6>";
                echo "<div class='border p-3 bg-light' style='max-height: 200px; overflow-y: auto;'>";
                echo "<small>" . nl2br(htmlspecialchars($faq['answer'])) . "</small>";
                echo "</div>";
                echo "</div>";
                
                echo "<div class='col-md-6'>";
                echo "<h6>Short Answer:</h6>";
                echo "<div class='border p-3 bg-info bg-opacity-10' style='max-height: 200px; overflow-y: auto;'>";
                echo "<small>" . nl2br(htmlspecialchars($faq['short_answer'])) . "</small>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                
                echo "</div>";
                echo "</div>";
            }
            
            // Show table structure
            echo "<hr><h3>Updated Table Structure:</h3>";
            $stmt = $pdo->query("DESCRIBE faqs");
            $columns = $stmt->fetchAll();
            
            echo "<div class='table-responsive'>";
            echo "<table class='table table-striped'>";
            echo "<thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>";
            echo "<tbody>";
            foreach ($columns as $col) {
                $highlight = $col['Field'] === 'short_answer' ? ' class="table-warning"' : '';
                echo "<tr$highlight>";
                echo "<td><strong>" . $col['Field'] . "</strong></td>";
                echo "<td>" . $col['Type'] . "</td>";
                echo "<td>" . $col['Null'] . "</td>";
                echo "<td>" . $col['Key'] . "</td>";
                echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
                echo "<td>" . $col['Extra'] . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table></div>";
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
        ?>
        
        <div class="alert alert-info">
            <h6>New Functions Available:</h6>
            <ul class="mb-0">
                <li><code>generate_short_answer($full_answer, $max_length)</code> - Create a short version of an answer</li>
                <li><code>update_faq_short_answer($pdo, $faq_id, $short_answer)</code> - Update the short answer for an FAQ</li>
            </ul>
        </div>
        
        <div class="mt-4">
            <a href="simple-db.php" class="btn btn-secondary">Database Manager</a>
            <a href="related-faqs-demo.php" class="btn btn-info">Related FAQs Demo</a>
            <a href="index.php" class="btn btn-primary">Main Site</a>
        </div>
    </div>
</body>
</html>