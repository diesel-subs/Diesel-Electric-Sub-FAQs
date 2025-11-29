<?php
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/markdown-helper.php';

$faq_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$preset_category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$faq = null;

if ($faq_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$faq_id]);
    $faq = $stmt->fetch();
    
    if (!$faq) {
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $faq ? 'Edit FAQ' : 'Create New FAQ'; ?> - Submarine FAQ Editor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <style>
        .form-floating textarea {
            height: 120px;
        }
        .status-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        #answerEditor {
            height: 500px;
            background: #fff;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1><i class="fas fa-edit text-primary"></i> <?php echo $faq ? 'Edit FAQ: ' . htmlspecialchars($faq['question']) : 'Create New FAQ'; ?></h1>
                    <div>
                        <a href="<?php echo $faq ? 'faq.php?id=' . $faq['id'] : 'index.php'; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form id="faqForm" method="POST" action="save-faq.php">
            <?php if ($faq): ?>
                <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
            <?php endif; ?>
            <input type="hidden" name="answer" id="answer">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo $faq ? htmlspecialchars($faq['title']) : ''; ?>" required>
                        <label for="title"><i class="fas fa-heading"></i> Title</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            $cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
                            foreach ($cats as $cat):
                                $selected = '';
                                if ($faq && $faq['category_id'] == $cat['id']) {
                                    $selected = 'selected';
                                } elseif (!$faq && $preset_category_id == $cat['id']) {
                                    $selected = 'selected';
                                }
                            ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="category_id"><i class="fas fa-folder"></i> Category</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="display_order" name="display_order" 
                               value="<?php echo $faq ? ($faq['display_order'] ?? 1) : '1'; ?>" min="1">
                        <label for="display_order"><i class="fas fa-sort-numeric-up"></i> Display Order</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="question" name="question" 
                               value="<?php echo $faq ? htmlspecialchars($faq['question']) : ''; ?>" required>
                        <label for="question"><i class="fas fa-question-circle"></i> Question</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="author" name="author"
                               value="<?php echo $faq ? htmlspecialchars($faq['author'] ?? '') : ''; ?>">
                        <label for="author"><i class="fas fa-user"></i> Author (optional)</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="date" class="form-control" id="date_submitted" name="date_submitted"
                               value="<?php echo $faq && !empty($faq['date_submitted']) ? date('Y-m-d', strtotime($faq['date_submitted'])) : ''; ?>">
                        <label for="date_submitted"><i class="fas fa-calendar-alt"></i> Date Submitted (optional)</label>
                    </div>
                </div>
            </div>

            <div class="quick-insert">
                <h6><i class="fas fa-magic"></i> Quick Markdown Insert</h6>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('**Bold Text**')">Bold</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('*Italic Text*')">Italic</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('# Header 1')">H1</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('## Header 2')">H2</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('### Header 3')">H3</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('- List item')">List</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('1. Numbered item')">Numbers</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('> Quote')">Quote</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('`code`')">Code</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('[Link Text](URL)')">Link</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertTable()">Table</button>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fas fa-edit"></i> Detailed Answer</label>
                <div id="answerEditor"></div>
            </div>

            <div class="row mt-4 mb-5">
                <div class="col-12">
                    <div class="d-flex justify-content-end">
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="saveDraft()">
                                <i class="fas fa-save"></i> Save Draft
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> <?php echo $faq ? 'Update FAQ' : 'Create FAQ'; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Status Indicator -->
    <div id="statusIndicator" class="status-indicator"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    
    <script>
        let quill;
        
        // Save draft
        function saveDraft() {
            showStatus('Saving draft...', 'info');
            
            const formData = new FormData(document.getElementById('faqForm'));
            formData.append('save_draft', '1');
            formData.set('answer', quill.root.innerHTML);
            
            fetch('save-faq.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showStatus('Draft saved successfully!', 'success');
                } else {
                    showStatus('Error saving draft: ' + data.error, 'danger');
                }
            })
            .catch(error => {
                showStatus('Error saving draft: ' + error.message, 'danger');
            });
        }
        
        // Show status messages
        function showStatus(message, type) {
            const indicator = document.getElementById('statusIndicator');
            indicator.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
            
            if (type === 'success' || type === 'info') {
                setTimeout(() => {
                    const alert = indicator.querySelector('.alert');
                    if (alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 3000);
            }
        }
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            quill = new Quill('#answerEditor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });
            // Set initial content
            const existing = <?php echo json_encode($faq ? $faq['answer'] : ''); ?>;
            quill.root.innerHTML = existing || '';

            // On submit, sync to hidden input
            document.getElementById('faqForm').addEventListener('submit', function(e) {
                document.getElementById('answer').value = quill.root.innerHTML;
            });
        });
    </script>
</body>
</html>
