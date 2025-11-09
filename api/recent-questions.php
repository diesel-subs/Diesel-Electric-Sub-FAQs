<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $query = "
        SELECT f.id, f.title, f.slug, c.name as category, f.views, f.created_at
        FROM faqs f 
        JOIN categories c ON f.category_id = c.id 
        WHERE f.status = 'published'
        ORDER BY f.created_at DESC, f.views DESC
        LIMIT 5
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $questions = $stmt->fetchAll();

    // Format results
    $formattedQuestions = array_map(function($row) {
        return [
            'id' => $row['id'],
            'title' => $row['title'],
            'slug' => $row['slug'],
            'category' => $row['category'],
            'views' => $row['views'],
            'date' => format_date($row['created_at'])
        ];
    }, $questions);

    echo json_encode([
        'success' => true,
        'questions' => $formattedQuestions
    ]);

} catch (Exception $e) {
    error_log("Recent questions error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Unable to load recent questions'
    ]);
}
?>