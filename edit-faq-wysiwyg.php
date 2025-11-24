<?php
require_once 'config/database.php';
require_once 'includes/header.php';

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
    <title><?php echo $faq ? 'Edit FAQ' : 'Create New FAQ'; ?> - WYSIWYG Editor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Quill.js WYSIWYG Editor - No API Key Required -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script></script>
    
    <style>
        .form-floating textarea.wysiwyg {
            height: 120px;
        }
        
        .editor-container {
            min-height: 500px;
        }
        
        .word-count {
            font-size: 0.875rem;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
        }
        
        .status-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        
        .quick-format {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 0.375rem;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .quick-format h6 {
            color: #1565c0;
            margin-bottom: 8px;
        }
        
        .editor-help {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1><i class="fas fa-edit text-primary"></i> <?php echo $faq ? 'Edit FAQ: ' . htmlspecialchars($faq['title'] ?? $faq['question']) : 'Create New FAQ'; ?></h1>
                    <div>
                        <a href="edit-faq.php<?php echo $faq ? '?id=' . $faq['id'] : ($preset_category_id ? '?category_id=' . $preset_category_id : ''); ?>" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-code"></i> Markdown Editor
                        </a>
                        <a href="<?php echo $faq ? 'faq.php?id=' . $faq['id'] : 'index.php'; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="editor-help">
            <h6><i class="fas fa-magic text-primary"></i> WYSIWYG Editor - True Visual Editing</h6>
            <p class="mb-1">This is a <strong>What You See Is What You Get</strong> editor. Format your text exactly as you want it to appear:</p>
            <ul class="mb-0">
                <li>Use the toolbar buttons for <strong>bold</strong>, <em>italic</em>, headers, lists, and more</li>
                <li>Insert tables, links, and images directly</li>
                <li>No Markdown knowledge required - just click and type!</li>
            </ul>
        </div>

        <form id="faqForm" method="POST" action="save-faq-wysiwyg.php">
            <?php if ($faq): ?>
                <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
            <?php endif; ?>
            
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

            <div class="quick-format">
                <h6><i class="fas fa-palette"></i> Quick Insert Templates</h6>
                <div class="btn-group-sm mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="insertTemplate('basic_info')">
                        <i class="fas fa-info"></i> Basic Info
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="insertTemplate('tech_specs')">
                        <i class="fas fa-cogs"></i> Tech Specs
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="insertTemplate('comparison')">
                        <i class="fas fa-table"></i> Comparison Table
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="insertTemplate('timeline')">
                        <i class="fas fa-clock"></i> Timeline
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Text Formatting:</strong><br>
                        <small>Bold, Italic, Underline, Colors</small>
                    </div>
                    <div class="col-md-4">
                        <strong>Structure:</strong><br>
                        <small>Headers, Lists, Tables, Links</small>
                    </div>
                    <div class="col-md-4">
                        <strong>Media:</strong><br>
                        <small>Images, Code blocks, Quotes</small>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <label for="main_answer" class="form-label">
                        <i class="fas fa-file-alt"></i> Detailed Answer - 
                        <strong>WYSIWYG Editor</strong>
                        <small class="text-muted">(Format text visually - no coding required)</small>
                    </label>
                    <div class="editor-container">
                        <!-- Quill Editor Container -->
                        <div id="quill-editor" style="height: 400px;"></div>
                        <!-- Hidden textarea for form submission -->
                        <textarea id="main_answer" name="main_answer" style="display: none;"><?php echo $faq ? htmlspecialchars($faq['answer']) : ''; ?></textarea>
                    </div>
                    <div class="word-count mt-2">
                        Content Length: <span id="mainAnswerCount">0</span> words
                    </div>
                </div>
            </div>

            <div class="row mt-4 mb-5">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="button" class="btn btn-outline-info me-2" onclick="previewContent()">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="saveDraft()">
                                <i class="fas fa-save"></i> Save Draft
                            </button>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> <?php echo $faq ? 'Update FAQ' : 'Create FAQ'; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">FAQ Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Indicator -->
    <div id="statusIndicator" class="status-indicator"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize Quill.js WYSIWYG Editor
        const toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
            ['blockquote', 'code-block'],
            ['link', 'image', 'video', 'formula'],

            [{ 'header': 1 }, { 'header': 2 }],               // custom button values
            [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
            [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
            [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
            [{ 'direction': 'rtl' }],                         // text direction

            [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

            [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
            [{ 'font': [] }],
            [{ 'align': [] }],

            ['clean']                                         // remove formatting button
        ];

        const quill = new Quill('#quill-editor', {
            modules: {
                toolbar: toolbarOptions,
                history: {
                    delay: 2000,
                    maxStack: 500,
                    userOnly: true
                }
            },
            placeholder: 'Enter your detailed FAQ answer here. Use the toolbar above to format your text...',
            theme: 'snow'
        });

        // Load existing content if editing
        const existingContent = document.getElementById('main_answer').value;
        if (existingContent) {
            quill.root.innerHTML = existingContent;
        }

        // Update hidden textarea when content changes
        quill.on('text-change', function() {
            document.getElementById('main_answer').value = quill.root.innerHTML;
            updateWordCount();
        });

        // Template insertion
        function insertTemplate(templateType) {
            let template = '';
            
            switch(templateType) {
                case 'basic_info':
                    template = `<h3>Basic Information</h3>
                    <p><strong>Type:</strong> [Submarine Class]</p>
                    <p><strong>Service Period:</strong> [Years]</p>
                    <p><strong>Navy:</strong> [Country/Navy]</p>
                    <p><strong>Primary Role:</strong> [Attack/Fleet/Coastal]</p>`;
                    break;
                    
                case 'tech_specs':
                    template = `<h3>Technical Specifications</h3>
                    <ul>
                        <li><strong>Length:</strong> [feet/meters]</li>
                        <li><strong>Beam:</strong> [feet/meters]</li>
                        <li><strong>Draft:</strong> [feet/meters]</li>
                        <li><strong>Displacement:</strong> [tons surfaced/submerged]</li>
                        <li><strong>Crew:</strong> [number of personnel]</li>
                        <li><strong>Armament:</strong> [torpedoes, guns, etc.]</li>
                    </ul>`;
                    break;
                    
                case 'comparison':
                    template = `<table>
                        <thead>
                            <tr>
                                <th>Specification</th>
                                <th>Class A</th>
                                <th>Class B</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Length</td>
                                <td>[feet]</td>
                                <td>[feet]</td>
                                <td>Overall length</td>
                            </tr>
                            <tr>
                                <td>Speed</td>
                                <td>[knots]</td>
                                <td>[knots]</td>
                                <td>Maximum submerged</td>
                            </tr>
                        </tbody>
                    </table>`;
                    break;
                    
                case 'timeline':
                    template = `<h3>Timeline</h3>
                    <ul>
                        <li><strong>[Date]:</strong> [Event description]</li>
                        <li><strong>[Date]:</strong> [Event description]</li>
                        <li><strong>[Date]:</strong> [Event description]</li>
                    </ul>`;
                    break;
            }
            
            if (template) {
                const range = quill.getSelection();
                if (range) {
                    quill.clipboard.dangerouslyPasteHTML(range.index, template);
                } else {
                    quill.clipboard.dangerouslyPasteHTML(template);
                }
            }
        }

        // Update word counts
        function updateWordCount() {
            const mainContent = quill.getText();
            
            document.getElementById('mainAnswerCount').textContent = mainContent.trim() ? mainContent.trim().split(/\s+/).length : 0;
        }
        
        // Preview content
        function previewContent() {
            const title = document.getElementById('title').value;
            const question = document.getElementById('question').value;
            const mainContent = quill.root.innerHTML;
            
            const previewHtml = `
                <div class="faq-preview">
                    <h2>${title || 'Untitled FAQ'}</h2>
                    <div class="alert alert-primary">
                        <h5><i class="fas fa-question-circle"></i> ${question || 'No question entered'}</h5>
                    </div>
                    <div class="answer-content">
                        ${mainContent || '<p class="text-muted">No detailed answer entered</p>'}
                    </div>
                </div>
            `;
            
            document.getElementById('previewContent').innerHTML = previewHtml;
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        }
        
        // Save draft
        function saveDraft() {
            showStatus('Saving draft...', 'info');
            
            // Update hidden textarea with current Quill content
            document.getElementById('main_answer').value = quill.root.innerHTML;
            
            const formData = new FormData(document.getElementById('faqForm'));
            formData.append('save_draft', '1');
            
            fetch('save-faq-wysiwyg.php', {
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
        
        // Form submission handler
        document.getElementById('faqForm').addEventListener('submit', function(e) {
            // Update the textarea with Quill content before submission
            document.getElementById('main_answer').value = quill.root.innerHTML;
        });
        
        // Event listeners
        // No short answer; just track main answer length
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 's':
                        e.preventDefault();
                        saveDraft();
                        break;
                    case 'p':
                        e.preventDefault();
                        previewContent();
                        break;
                }
            }
        });
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            updateWordCount();
        });
    </script>
</body>
</html>
