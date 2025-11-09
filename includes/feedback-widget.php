<!-- Quick Feedback Widget for FAQ pages -->
<?php $widget_faq = $current_faq ?? $faq ?? null; ?>
<div class="feedback-widget mt-3 p-2 border rounded bg-light">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <small class="fw-bold">Was this helpful?</small>
            <br><small class="text-muted">Help us improve this FAQ</small>
        </div>
        <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-success" onclick="quickFeedback('helpful', <?php echo $widget_faq['id'] ?? 0; ?>, this)">
                <i class="fas fa-thumbs-up"></i> Yes
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="quickFeedback('not-helpful', <?php echo $widget_faq['id'] ?? 0; ?>, this)">
                <i class="fas fa-thumbs-down"></i> No
            </button>
            <a href="feedback.php?faq_id=<?php echo $widget_faq['id'] ?? ''; ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-comment"></i> Details
            </a>
        </div>
    </div>
    <div id="feedback-message-<?php echo $widget_faq['id'] ?? 'general'; ?>" class="mt-2" style="display: none;"></div>
</div>

<script>
function quickFeedback(type, faqId, buttonElement) {
    const messageDiv = document.getElementById('feedback-message-' + faqId);
    const buttonsContainer = buttonElement.parentElement;
    
    // Disable buttons to prevent double-clicking
    const buttons = buttonsContainer.querySelectorAll('button');
    buttons.forEach(btn => btn.disabled = true);
    
    // Send quick feedback
    fetch('/api/quick-feedback.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            type: type,
            faq_id: faqId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || true) { // Always show thank you message
            const emoji = type === 'helpful' ? '👍' : '👎';
            messageDiv.innerHTML = '<small class="text-success">' + emoji + ' Thank you for your feedback!</small>';
            messageDiv.style.display = 'block';
            
            // Hide after 4 seconds and re-enable buttons
            setTimeout(() => {
                messageDiv.style.display = 'none';
                buttons.forEach(btn => btn.disabled = false);
            }, 4000);
        }
    })
    .catch(() => {
        messageDiv.innerHTML = '<small class="text-info">👍 Thank you for your feedback!</small>';
        messageDiv.style.display = 'block';
        setTimeout(() => {
            messageDiv.style.display = 'none';
            buttons.forEach(btn => btn.disabled = false);
        }, 3000);
    });
}
</script>