<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get search parameters
$query = $_GET['q'] ?? $_GET['query'] ?? '';
$category = $_GET['category'] ?? '';
$field = $_GET['field'] ?? 'all';
$sort = $_GET['sort'] ?? 'relevance';
$limit = min((int)($_GET['limit'] ?? 25), 100);
$featured = isset($_GET['featured']) && $_GET['featured'];
$exact = isset($_GET['exact']) && $_GET['exact'];

// At least one search criteria must be provided
if (empty(trim($query)) && empty(trim($category))) {
    echo json_encode(['success' => false, 'message' => 'Search query or category is required']);
    exit;
}

try {
    // Build dynamic search query
    $selectFields = "f.id, f.title, f.slug, f.views, f.question, f.answer, f.short_answer, f.tags, c.name as category_name";
    $fromClause = "FROM faqs f JOIN categories c ON f.category_id = c.id";
    $whereClause = "WHERE f.status = 'published'";
    $orderClause = "";
    $params = [];
    
    // Add text search conditions if query provided
    if (!empty(trim($query))) {
        $searchTerm = $exact ? $query : '%' . $query . '%';
        $operator = $exact ? '=' : 'LIKE';
        
        if ($field === 'all') {
            $whereClause .= " AND (f.title $operator ? OR f.question $operator ? OR f.answer $operator ? OR f.tags $operator ?)";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        } elseif ($field === 'title') {
            $whereClause .= " AND f.title $operator ?";
            $params[] = $searchTerm;
        } elseif ($field === 'question') {
            $whereClause .= " AND f.question $operator ?";
            $params[] = $searchTerm;
        } elseif ($field === 'answer') {
            $whereClause .= " AND f.answer $operator ?";
            $params[] = $searchTerm;
        } elseif ($field === 'tags') {
            $whereClause .= " AND f.tags $operator ?";
            $params[] = $searchTerm;
        }
    }
    
    // Add category filter
    if (!empty(trim($category))) {
        $whereClause .= " AND c.name = ?";
        $params[] = $category;
    }
    
    // Add featured filter
    if ($featured) {
        $whereClause .= " AND f.featured = 1";
    }
    
    // Add sorting
    switch ($sort) {
        case 'title':
            $orderClause = "ORDER BY f.title ASC";
            break;
        case 'views':
            $orderClause = "ORDER BY f.views DESC";
            break;
        case 'newest':
            $orderClause = "ORDER BY f.created_at DESC";
            break;
        case 'oldest':
            $orderClause = "ORDER BY f.created_at ASC";
            break;
        default: // relevance
            if (!empty(trim($query))) {
                $orderClause = "ORDER BY f.featured DESC, f.views DESC";
            } else {
                $orderClause = "ORDER BY f.title ASC";
            }
            break;
    }
    
    $fullQuery = "SELECT $selectFields $fromClause $whereClause $orderClause LIMIT $limit";
    
    $stmt = $pdo->prepare($fullQuery);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    // Format results
    $formattedResults = array_map(function($row) {
        $excerpt = $row['short_answer'] ?? $row['answer'] ?? '';
        if (strlen($excerpt) > 200) {
            $excerpt = substr($excerpt, 0, 200) . '...';
        }
        
        return [
            'id' => $row['id'],
            'title' => $row['title'],
            'slug' => $row['slug'],
            'question' => $row['question'],
            'answer' => $row['answer'],
            'short_answer' => $row['short_answer'],
            'excerpt' => $excerpt,
            'category_name' => $row['category_name'],
            'views' => $row['views'],
            'tags' => $row['tags']
        ];
    }, $results);

    echo json_encode([
        'success' => true,
        'results' => $formattedResults,
        'total' => count($formattedResults),
        'query' => $query,
        'category' => $category,
        'parameters' => [
            'field' => $field,
            'sort' => $sort,
            'limit' => $limit,
            'featured' => $featured,
            'exact' => $exact
        ]
    ]);

} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Search service temporarily unavailable'
    ]);
}
?>