<?php
// Smart Feedback Prompts - Include this on pages where you want intelligent feedback prompts

function generateSmartFeedbackPrompt($page_type, $content_id = null) {
    global $pdo;
    
    $prompts = [];
    
    switch ($page_type) {
        case 'faq':
            if ($content_id) {
                try {
                    // Check if this FAQ has low engagement
                    $stmt = $pdo->prepare("
                        SELECT f.*, 
                               (SELECT COUNT(*) FROM feedback WHERE faq_id = f.id) as feedback_count,
                               CASE 
                                   WHEN f.views < 10 THEN 'low'
                                   WHEN f.views < 50 THEN 'medium'
                                   ELSE 'high'
                               END as engagement_level
                        FROM faqs f WHERE f.id = ?
                    ");
                    $stmt->execute([$content_id]);
                    $faq = $stmt->fetch();
                    
                    if ($faq) {
                        if ($faq['engagement_level'] === 'low') {
                            $prompts[] = [
                                'type' => 'low_engagement',
                                'message' => "This FAQ is new to our collection. Is it helpful? Your feedback helps others find quality content!",
                                'action' => "Rate this FAQ",
                                'urgency' => 'medium'
                            ];
                        }
                        
                        if ($faq['feedback_count'] == 0) {
                            $prompts[] = [
                                'type' => 'no_feedback',
                                'message' => "Be the first to help improve this FAQ! Share what's missing or what works well.",
                                'action' => "Be the first to comment",
                                'urgency' => 'high'
                            ];
                        }
                    }
                } catch (Exception $e) {
                    // Fallback prompt
                    $prompts[] = [
                        'type' => 'general',
                        'message' => "Help us improve! Your submarine expertise makes our FAQ collection better.",
                        'action' => "Share feedback",
                        'urgency' => 'low'
                    ];
                }
            }
            break;
            
        case 'category':
            $prompts[] = [
                'type' => 'category_expert',
                'message' => "Know something we missed? Help us make this the most comprehensive submarine FAQ category!",
                'action' => "Suggest improvements",
                'urgency' => 'medium'
            ];
            break;
            
        case 'search_no_results':
            $prompts[] = [
                'type' => 'search_miss',
                'message' => "Didn't find what you were looking for? Help us add the content you need!",
                'action' => "Request this topic",
                'urgency' => 'high'
            ];
            break;
            
        default:
            $prompts[] = [
                'type' => 'general',
                'message' => "Your submarine knowledge helps everyone learn. What would you add to our collection?",
                'action' => "Share your expertise",
                'urgency' => 'low'
            ];
    }
    
    return $prompts;
}

function renderSmartPrompt($prompt, $page_id = null) {
    $urgency_colors = [
        'high' => 'danger',
        'medium' => 'warning',
        'low' => 'info'
    ];
    
    $color = $urgency_colors[$prompt['urgency']] ?? 'info';
    
    $feedback_url = 'feedback.php';
    if ($page_id) {
        $feedback_url .= '?faq_id=' . $page_id;
    }
    
    echo '<div class="smart-feedback-prompt mt-3 mb-3">';
    echo '<div class="alert alert-' . $color . ' alert-dismissible fade show" role="alert">';
    echo '<div class="d-flex align-items-center">';
    echo '<div class="me-3">';
    
    switch ($prompt['urgency']) {
        case 'high':
            echo '<i class="fas fa-exclamation-triangle fa-lg"></i>';
            break;
        case 'medium':
            echo '<i class="fas fa-lightbulb fa-lg"></i>';
            break;
        default:
            echo '<i class="fas fa-heart fa-lg"></i>';
    }
    
    echo '</div>';
    echo '<div class="flex-grow-1">';
    echo '<strong>' . htmlspecialchars($prompt['message']) . '</strong>';
    echo '</div>';
    echo '<div class="ms-2">';
    echo '<a href="' . $feedback_url . '" class="btn btn-outline-' . $color . ' btn-sm">';
    echo htmlspecialchars($prompt['action']);
    echo '</a>';
    echo '</div>';
    echo '</div>';
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
    echo '</div>';
}

// Usage: Add this to pages where you want smart prompts
function showSmartFeedbackPrompts($page_type, $content_id = null) {
    // Only show prompts 40% of the time to avoid being intrusive
    if (rand(1, 100) <= 40) {
        $prompts = generateSmartFeedbackPrompt($page_type, $content_id);
        if (!empty($prompts)) {
            // Show one random prompt
            $prompt = $prompts[array_rand($prompts)];
            renderSmartPrompt($prompt, $content_id);
        }
    }
}
?>