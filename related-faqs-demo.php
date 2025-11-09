<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Related FAQs Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Related FAQs Functionality Demo</h1>
        
        <?php
        require_once 'config/database.php';
        
        try {
            // Show all FAQs with their related FAQs
            echo "<h3>FAQs and Their Related Questions:</h3>";
            
            $stmt = $pdo->query("
                SELECT id, title, slug, category_id 
                FROM faqs 
                WHERE status = 'published' 
                ORDER BY title
            ");
            $faqs = $stmt->fetchAll();
            
            foreach ($faqs as $faq) {
                echo "<div class='card mb-3'>";
                echo "<div class='card-header'>";
                echo "<h5>FAQ #{$faq['id']}: " . htmlspecialchars($faq['title']) . "</h5>";
                echo "</div>";
                echo "<div class='card-body'>";
                
                // Get related FAQs
                $related = get_related_faqs($pdo, $faq['id']);
                
                if ($related) {
                    echo "<h6>Related Questions:</h6>";
                    echo "<ul class='list-group list-group-flush'>";
                    foreach ($related as $rel) {
                        $badge_class = match($rel['relationship_type']) {
                            'similar' => 'bg-primary',
                            'prerequisite' => 'bg-warning text-dark',
                            'followup' => 'bg-success',
                            'category_related' => 'bg-info text-dark',
                            default => 'bg-secondary'
                        };
                        
                        echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                        echo htmlspecialchars($rel['title']);
                        echo "<span class='badge $badge_class'>" . ucfirst($rel['relationship_type']) . "</span>";
                        echo "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p class='text-muted'>No related questions found.</p>";
                }
                
                echo "</div>";
                echo "</div>";
            }
            
            // Show the related_faqs table data
            echo "<hr><h3>Related FAQs Table Data:</h3>";
            $stmt = $pdo->query("
                SELECT rf.*, 
                       f1.title as faq_title, 
                       f2.title as related_faq_title
                FROM related_faqs rf
                JOIN faqs f1 ON rf.faq_id = f1.id
                JOIN faqs f2 ON rf.related_faq_id = f2.id
                ORDER BY rf.id
            ");
            $relationships = $stmt->fetchAll();
            
            if ($relationships) {
                echo "<div class='table-responsive'>";
                echo "<table class='table table-striped'>";
                echo "<thead><tr>";
                echo "<th>ID</th><th>FAQ</th><th>Related FAQ</th><th>Relationship</th><th>Created</th>";
                echo "</tr></thead><tbody>";
                
                foreach ($relationships as $rel) {
                    echo "<tr>";
                    echo "<td>" . $rel['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($rel['faq_title']) . "</td>";
                    echo "<td>" . htmlspecialchars($rel['related_faq_title']) . "</td>";
                    echo "<td><span class='badge bg-secondary'>" . ucfirst($rel['relationship_type']) . "</span></td>";
                    echo "<td>" . format_date($rel['created_at']) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table></div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
        ?>
        
        <div class="mt-4">
            <a href="simple-db.php" class="btn btn-secondary">Database Manager</a>
            <a href="index.php" class="btn btn-primary">Main Site</a>
        </div>
    </div>
</body>
</html>