    </main>

    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p>A comprehensive resource for diesel-electric submarine information, with detailed focus on World War II US submarines.</p>
                </div>
                <div class="col-md-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light">Home</a></li>
                        <li><a href="search.php" class="text-light">Search</a></li>
                        <li><a href="about.php" class="text-light">About</a></li>
                        <li><a href="contact.php" class="text-light">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Categories</h6>
                    <ul class="list-unstyled">
                        <li><a href="category.php?cat=US WW2 Subs in General" class="text-light">US WW2 Subs</a></li>
                        <li><a href="category.php?cat=Hull and Compartments" class="text-light">Hull & Compartments</a></li>
                        <li><a href="category.php?cat=Operating US Subs in WW2" class="text-light">Operations</a></li>
                        <li><a href="category.php?cat=Life Aboard WW2 US Subs" class="text-light">Life Aboard</a></li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4 mb-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">Last updated<a class="text-muted" href="admin/login.php" style="text-decoration:none; cursor: default;">:</a> <?php echo date('F j, Y'); ?></small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <!-- Exit Intent Feedback System -->
    <script>
    let exitIntentShown = false;
    
    function showExitIntentFeedback() {
        if (exitIntentShown || localStorage.getItem('exit_feedback_shown')) return;
        
        exitIntentShown = true;
        localStorage.setItem('exit_feedback_shown', 'true');
        
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'exitFeedbackModal';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            Wait! Before you go...
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Did you find what you were looking for?</strong></p>
                        <p>Your feedback helps us improve this submarine knowledge base for everyone!</p>
                        <div class="d-grid gap-2">
                            <button onclick="quickFeedback('helpful')" class="btn btn-success">
                                <i class="fas fa-thumbs-up"></i> Yes, very helpful!
                            </button>
                            <button onclick="quickFeedback('partial')" class="btn btn-warning">
                                <i class="fas fa-meh"></i> Partially, but could be better
                            </button>
                            <button onclick="quickFeedback('not_helpful')" class="btn btn-danger">
                                <i class="fas fa-thumbs-down"></i> No, I need more help
                            </button>
                        </div>
                        <div class="mt-3 text-center">
                            <a href="feedback.php" class="btn btn-link">Share detailed feedback</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
    
    function quickFeedback(type) {
        // Send quick feedback
        fetch('api/quick-feedback.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                type: 'exit_intent',
                value: type,
                page: window.location.pathname
            })
        });
        
        // Show thank you and close
        document.querySelector('#exitFeedbackModal .modal-body').innerHTML = `
            <div class="text-center">
                <i class="fas fa-heart text-danger fa-3x mb-3"></i>
                <h4>Thank you!</h4>
                <p>Your feedback helps us improve. Safe sailing! ⚓</p>
            </div>
        `;
        
        setTimeout(() => {
            bootstrap.Modal.getInstance(document.getElementById('exitFeedbackModal')).hide();
        }, 2000);
    }
    
    // Detect exit intent (mouse leaving window area)
    document.addEventListener('mouseleave', function(e) {
        if (e.clientY <= 0) {
            showExitIntentFeedback();
        }
    });
    
    // Also trigger on beforeunload for mobile
    window.addEventListener('beforeunload', function() {
        if (Math.random() < 0.3) { // Show to 30% of users
            showExitIntentFeedback();
        }
    });
    </script>
</body>
</html>
