<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Comprehensive FAQs about diesel-electric submarines, focusing on WWII US submarines'; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-ship"></i>
                <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="category.php?cat=US WW2 Subs in General">US WW2 Subs in General</a></li>
                            <li><a class="dropdown-item" href="category.php?cat=Hull and Compartments">Hull and Compartments</a></li>
                            <li><a class="dropdown-item" href="category.php?cat=Operating US Subs in WW2">Operating US Subs in WW2</a></li>
                            <li><a class="dropdown-item" href="category.php?cat=Life Aboard WW2 US Subs">Life Aboard WW2 US Subs</a></li>
                            <li><a class="dropdown-item" href="category.php?cat=Crews Aboard WW2 US Subs">Crews Aboard WW2 US Subs</a></li>
                            <li><a class="dropdown-item" href="category.php?cat=Battles Small and Large">Battles Small and Large</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search.php">Advanced Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="feedbackDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-comments"></i> Community
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="feedback.php">
                                <i class="fas fa-pencil-alt"></i> Share Feedback
                            </a></li>
                            <li><a class="dropdown-item" href="feedback-dashboard.php">
                                <i class="fas fa-chart-line"></i> Community Dashboard
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="feedback.php?type=new_faq">
                                <i class="fas fa-plus"></i> Suggest New FAQ
                            </a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/dashboard.php">Admin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pma.php" target="_blank" title="Database Management">
                                <i class="fas fa-database"></i> phpMyAdmin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/login.php">Admin Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pma.php" target="_blank" title="Database Management">
                                <i class="fas fa-database"></i> DB
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-content">