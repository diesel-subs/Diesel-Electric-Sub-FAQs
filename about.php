<?php
$page_title = 'About';
$page_description = 'Learn about this comprehensive collection of diesel-electric submarine FAQs from World War II.';
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h1 class="card-title mb-0">
                        <i class="fas fa-ship"></i>
                        About Diesel-Electric Submarine FAQs
                    </h1>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <i class="fas fa-anchor display-3 text-primary mb-3"></i>
                            <h5>Historical Focus</h5>
                            <p class="text-muted">World War II era US submarines and their operations in the Pacific Theater.</p>
                        </div>
                        <div class="col-md-4 text-center mb-4">
                            <i class="fas fa-users display-3 text-info mb-3"></i>
                            <h5>Crew Life</h5>
                            <p class="text-muted">Daily life, operations, and experiences of submarine crews during wartime.</p>
                        </div>
                        <div class="col-md-4 text-center mb-4">
                            <i class="fas fa-cogs display-3 text-warning mb-3"></i>
                            <h5>Technical Details</h5>
                            <p class="text-muted">Engineering, construction, and operational aspects of diesel-electric submarines.</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h3>About This Collection</h3>
                    <p class="lead">This comprehensive FAQ database contains detailed information about US diesel-electric submarines used during World War II, with particular focus on the experiences and operations in the Pacific Theater.</p>
                    
                    <h4>What You'll Find Here</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check-circle text-success me-2"></i>Submarine construction and design</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Daily life aboard submarines</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Combat operations and tactics</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Crew training and selection</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check-circle text-success me-2"></i>Technical specifications</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Historical battles and engagements</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Ship classes and variants</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Post-war fate of submarines</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Featured Submarine: USS Pampanito (SS-383)</h5>
                        <p class="mb-0">Many examples and details in this collection reference the USS Pampanito, a Balao-class submarine that served in the Pacific during World War II and is now a museum ship in San Francisco.</p>
                    </div>
                    
                    <h4>Categories Covered</h4>
                    <?php
                    try {
                        // Get category statistics
                        $stmt = $pdo->query("
                            SELECT c.name, c.description, COUNT(f.id) as faq_count
                            FROM categories c
                            LEFT JOIN faqs f ON c.id = f.category_id AND f.status = 'published'
                            GROUP BY c.id, c.name, c.description
                            ORDER BY faq_count DESC
                        ");
                        $categories = $stmt->fetchAll();
                        
                        echo "<div class='row'>";
                        foreach ($categories as $category) {
                            echo "<div class='col-md-6 mb-3'>";
                            echo "<div class='card border-0 bg-light'>";
                            echo "<div class='card-body'>";
                            echo "<h6 class='card-title text-primary'>";
                            echo "<i class='fas fa-folder me-2'></i>";
                            echo htmlspecialchars($category['name']);
                            echo "<span class='badge bg-secondary ms-2'>{$category['faq_count']} FAQs</span>";
                            echo "</h6>";
                            echo "<p class='card-text small text-muted'>";
                            echo htmlspecialchars($category['description'] ?? 'Detailed information about ' . $category['name']);
                            echo "</p>";
                            echo "<a href='category.php?cat=" . urlencode($category['name']) . "' class='btn btn-sm btn-outline-primary'>Browse FAQs</a>";
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                        echo "</div>";
                        
                        // Get total statistics
                        $stmt = $pdo->query("SELECT COUNT(*) as total_faqs FROM faqs WHERE status = 'published'");
                        $stats = $stmt->fetch();
                        
                        echo "<div class='alert alert-success mt-4'>";
                        echo "<div class='row text-center'>";
                        echo "<div class='col-md-4'>";
                        echo "<h3 class='text-success'>" . count($categories) . "</h3>";
                        echo "<p class='mb-0'>Categories</p>";
                        echo "</div>";
                        echo "<div class='col-md-4'>";
                        echo "<h3 class='text-success'>" . $stats['total_faqs'] . "</h3>";
                        echo "<p class='mb-0'>Total FAQs</p>";
                        echo "</div>";
                        echo "<div class='col-md-4'>";
                        echo "<h3 class='text-success'>1942-1945</h3>";
                        echo "<p class='mb-0'>War Period Covered</p>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                        
                    } catch (Exception $e) {
                        echo "<div class='alert alert-warning'>Unable to load category statistics.</div>";
                    }
                    ?>
                    
                    <h4>How to Use This Site</h4>
                    <ol>
                        <li><strong>Browse by Category:</strong> Use the Categories dropdown to explore specific topics</li>
                        <li><strong>Search:</strong> Use the Advanced Search to find specific information</li>
                        <li><strong>Related Topics:</strong> Each FAQ includes links to related questions</li>
                        <li><strong>Detailed Answers:</strong> Most FAQs provide both summary and detailed responses</li>
                    </ol>
                    
                    <div class="mt-4 text-center">
                        <a href="index.php" class="btn btn-primary me-3">
                            <i class="fas fa-home"></i> Return to Home
                        </a>
                        <a href="search.php" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i> Search FAQs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>