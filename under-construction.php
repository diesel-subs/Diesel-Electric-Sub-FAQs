<?php
// Under Construction - PHP Version
// This is useful if you need server-side logic or database integration

// Configuration
$site_name = "Diesel-Electric Submarine FAQs";
$launch_date = "December 2025";
$progress_percentage = 85;
$contact_email = "admin@submarinefaqs.com";

// Handle newsletter signup (basic example)
$signup_message = "";
if ($_POST['email'] ?? false) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if ($email) {
        // Here you could save to database or send to mailing service
        // For now, just show success message
        $signup_message = "success";
    } else {
        $signup_message = "error";
    }
}

// Submarine facts for rotation
$submarine_facts = [
    "The USS Tang (SS-306) was one of the most successful submarines of WWII!",
    "Diesel-electric submarines could stay submerged for up to 24 hours!",
    "The Gato-class submarines were the workhorses of the Pacific War!",
    "Submarines used periscopes that could extend up to 60 feet!",
    "The USS Wahoo (SS-238) was famous for its aggressive tactics!",
    "A typical WWII submarine crew consisted of about 80 officers and enlisted men!",
    "The USS Silversides (SS-236) sank 23 enemy ships during the war!"
];

$random_fact = $submarine_facts[array_rand($submarine_facts)];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Construction - <?php echo htmlspecialchars($site_name); ?></title>
    <meta name="description" content="The most comprehensive diesel-electric submarine FAQ collection is coming soon. Stay tuned for launch!">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .construction-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .progress-bar-custom {
            height: 10px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            width: <?php echo $progress_percentage; ?>%;
            animation: progressPulse 2s ease-in-out infinite;
        }
        
        @keyframes progressPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .btn-custom {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            height: 2.5rem;
            white-space: nowrap;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
        }
        
        .email-input-custom {
            height: 2.5rem;
            padding: 6px 12px;
        }
        
        .alert-success-custom {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.5);
            color: #28a745;
        }
        
        .alert-danger-custom {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="construction-card p-5 text-center">
                    <!-- Header -->
                    <div class="mb-5">
                        <i class="fas fa-ship fa-4x text-warning mb-3"></i>
                        <h1 class="display-4 fw-bold mb-3">Diving Deep Into Development</h1>
                        <p class="lead mb-0"><?php echo htmlspecialchars($site_name); ?> is surfacing soon!</p>
                    </div>
                    
                    <!-- Progress -->
                    <div class="mb-5">
                        <h4 class="mb-3">
                            <i class="fas fa-cogs"></i> Construction Progress
                        </h4>
                        <div class="progress-bar-custom mb-3">
                            <div class="progress-fill"></div>
                        </div>
                        <p class="mb-0">
                            <strong><?php echo $progress_percentage; ?>% Complete</strong> • 
                            Expected Launch: <span class="text-warning"><?php echo htmlspecialchars($launch_date); ?></span>
                        </p>
                    </div>
                    
                    <!-- Features -->
                    <div class="row mb-5">
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-question-circle fa-2x text-warning mb-2"></i>
                            <h6>183+ Comprehensive FAQs</h6>
                        </div>
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-search fa-2x text-warning mb-2"></i>
                            <h6>Advanced Search System</h6>
                        </div>
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-users fa-2x text-warning mb-2"></i>
                            <h6>Community Driven Content</h6>
                        </div>
                    </div>
                    
                    <!-- Newsletter Signup -->
                    <div class="mb-4">
                        <h4 class="mb-3">
                            <i class="fas fa-bell"></i> Get Launch Notification
                        </h4>
                        
                        <?php if ($signup_message === "success"): ?>
                            <div class="alert alert-success-custom mb-3">
                                <i class="fas fa-check-circle"></i> 
                                <strong>Success!</strong> You'll be notified when we launch. Thank you for your interest!
                            </div>
                        <?php elseif ($signup_message === "error"): ?>
                            <div class="alert alert-danger-custom mb-3">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Error!</strong> Please enter a valid email address.
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="row justify-content-center">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="email" name="email" class="form-control email-input-custom" 
                                           placeholder="Enter your email" required
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                    <button type="submit" class="btn btn-custom">
                                        <i class="fas fa-paper-plane"></i> Notify Me
                                    </button>
                                </div>
                                <small class="text-light mt-2 d-block">
                                    One-time notification only. No spam!
                                </small>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Fun Fact -->
                    <div class="border-top border-secondary pt-4">
                        <small class="text-light">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Submarine Fact:</strong> <?php echo htmlspecialchars($random_fact); ?>
                        </small>
                    </div>
                    
                    <!-- Contact -->
                    <div class="mt-3">
                        <small class="text-light">
                            Questions? <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>" class="text-warning">Contact Us</a>
                        </small>
                    </div>
                </div>
                
                <!-- Admin Access -->
                <?php if (isset($_GET['admin'])): ?>
                    <div class="text-center mt-3">
                        <small>
                            <a href="admin/" class="text-warning">
                                <i class="fas fa-key"></i> Admin Access
                            </a>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-refresh fact every 8 seconds
        const facts = <?php echo json_encode($submarine_facts); ?>;
        let currentFactIndex = facts.indexOf("<?php echo addslashes($random_fact); ?>");
        
        setInterval(() => {
            currentFactIndex = (currentFactIndex + 1) % facts.length;
            document.querySelector('.border-top small').innerHTML = `
                <i class="fas fa-lightbulb me-2"></i>
                <strong>Submarine Fact:</strong> ${facts[currentFactIndex]}
            `;
        }, 8000);
    </script>
</body>
</html>