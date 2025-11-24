<!-- Quick Feedback Widget for FAQ pages -->
<?php $widget_faq = $current_faq ?? $faq ?? null; ?>
<div class="feedback-widget mt-3 p-3 border rounded bg-light">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <small class="fw-bold">Have feedback about this FAQ?</small>
            <br><small class="text-muted">Share a quick note so we can improve it.</small>
        </div>
        <a href="feedback.php?faq_id=<?php echo $widget_faq['id'] ?? ''; ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-comment-dots"></i> Send Feedback
        </a>
    </div>
</div>
