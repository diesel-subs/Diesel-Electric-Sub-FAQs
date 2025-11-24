<?php
// save-faq.php - Handle FAQ creation and updates
require_once 'config/database.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $faq_id = isset($_POST['faq_id']) ? (int)$_POST['faq_id'] : 0;
    $title = trim($_POST['title'] ?? '');
    $question = trim($_POST['question'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $main_answer = trim($_POST['main_answer'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 1);
    $author = trim($_POST['author'] ?? '');
    $date_submitted = trim($_POST['date_submitted'] ?? '');
    $is_draft = isset($_POST['save_draft']);
    
    // Validation
    if (empty($title)) {
        throw new Exception('Title is required');
    }
    if (empty($question)) {
        throw new Exception('Question is required');
    }
    if (empty($category_id)) {
        throw new Exception('Category is required');
    }
    if (empty($main_answer)) {
        throw new Exception('Main answer is required');
    }
    
    // Prepare data
    $data = [
        'title' => $title,
        'slug' => generate_slug($title),
        'question' => $question,
        'category_id' => $category_id,
        'answer' => $main_answer,
        'author' => $author ?: null,
        'date_submitted' => $date_submitted ?: null,
        'display_order' => $display_order
    ];
    
    if ($faq_id > 0) {
        // Update existing FAQ
        $sql = "UPDATE faqs SET 
                title = :title,
                slug = :slug,
                question = :question, 
                category_id = :category_id, 
                answer = :answer, 
                author = :author,
                date_submitted = :date_submitted,
                display_order = :display_order
                WHERE id = :id";
        
        $data['id'] = $faq_id;
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute($data)) {
            // If it's not a draft save via AJAX, redirect to the FAQ page
            if (!$is_draft) {
                header("Location: faq.php?id={$faq_id}&updated=1");
                exit;
            }
            
            echo json_encode([
                'success' => true, 
                'message' => $is_draft ? 'Draft saved successfully' : 'FAQ updated successfully',
                'faq_id' => $faq_id
            ]);
        } else {
            throw new Exception('Failed to update FAQ');
        }
        
    } else {
        // Create new FAQ
        $sql = "INSERT INTO faqs (title, slug, question, category_id, answer, author, date_submitted, display_order) 
                VALUES (:title, :slug, :question, :category_id, :answer, :author, :date_submitted, :display_order)";
        
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute($data)) {
            $new_id = $pdo->lastInsertId();
            
            // If it's not a draft save via AJAX, redirect to the new FAQ page
            if (!$is_draft) {
                header("Location: faq.php?id={$new_id}&created=1");
                exit;
            }
            
            echo json_encode([
                'success' => true, 
                'message' => $is_draft ? 'Draft saved successfully' : 'FAQ created successfully',
                'faq_id' => $new_id
            ]);
        } else {
            throw new Exception('Failed to create FAQ');
        }
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function generate_slug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return $text ?: uniqid('faq-');
}
?>
