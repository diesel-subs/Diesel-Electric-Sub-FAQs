<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration for Railway
$host = 'viaduct.proxy.rlwy.net';
$port = 26748;
$dbname = 'submarine_faqs';
$username = 'submarine_user';
$password = 'submarine2024!';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_GET['action'] ?? 'categories';
    
    switch ($action) {
        case 'categories':
            // Get all categories
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($categories);
            break;
            
        case 'faqs':
            $category_id = $_GET['category_id'] ?? null;
            
            if ($category_id) {
                // Get FAQs for specific category
                $stmt = $pdo->prepare("
                    SELECT f.*, c.name as category_name 
                    FROM faqs f 
                    JOIN categories c ON f.category_id = c.id 
                    WHERE f.category_id = ? 
                    ORDER BY f.question
                ");
                $stmt->execute([$category_id]);
            } else {
                // Get all FAQs
                $stmt = $pdo->query("
                    SELECT f.*, c.name as category_name 
                    FROM faqs f 
                    JOIN categories c ON f.category_id = c.id 
                    ORDER BY c.name, f.question
                ");
            }
            
            $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($faqs);
            break;
            
        case 'search':
            $query = $_GET['q'] ?? '';
            if ($query) {
                $stmt = $pdo->prepare("
                    SELECT f.*, c.name as category_name 
                    FROM faqs f 
                    JOIN categories c ON f.category_id = c.id 
                    WHERE f.question LIKE ? OR f.answer LIKE ?
                    ORDER BY f.question
                ");
                $stmt->execute(["%$query%", "%$query%"]);
                $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($faqs);
            } else {
                echo json_encode([]);
            }
            break;
            
        case 'stats':
            // Get site statistics
            $faqCount = $pdo->query("SELECT COUNT(*) FROM faqs")->fetchColumn();
            $categoryCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
            
            echo json_encode([
                'total_faqs' => $faqCount,
                'total_categories' => $categoryCount,
                'status' => 'online'
            ]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
}
?>