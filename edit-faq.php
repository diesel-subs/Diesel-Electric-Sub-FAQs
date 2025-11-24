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
    <style>
        .editor-container {
            height: calc(100vh - 200px);
            min-height: 600px;
        }
        
        .editor-pane, .preview-pane {
            height: 100%;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
        
        .editor-textarea {
            height: calc(100% - 50px);
            resize: none;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .preview-content {
            height: calc(100% - 50px);
            overflow-y: auto;
            padding: 15px;
            background: white;
        }
        
        .toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 8px 12px;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        
        .toolbar button {
            margin-right: 5px;
            padding: 4px 8px;
            border: none;
            background: white;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .toolbar button:hover {
            background: #e9ecef;
        }
        
        .form-floating textarea {
            height: 120px;
        }
        
        .word-count {
            font-size: 0.875rem;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
        }
        
        .preview-pane h1, .preview-pane h2, .preview-pane h3 {
            color: #1e40af;
        }
        
        .preview-pane table {
            margin: 1rem 0;
        }
        
        .preview-pane blockquote {
            border-left: 4px solid #1e40af;
            margin: 1rem 0;
            padding-left: 1rem;
            font-style: italic;
        }
        
        .preview-pane code {
            background: #f8f9fa;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 0.9em;
        }
        
        .preview-pane pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
            overflow-x: auto;
        }
        
        .status-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        
        .quick-insert {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 0.375rem;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .quick-insert h6 {
            color: #1565c0;
            margin-bottom: 8px;
        }
        
        .quick-insert button {
            font-size: 0.8rem;
            padding: 2px 6px;
            margin: 2px;
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
                        <button type="button" class="btn btn-outline-secondary me-2" onclick="previewMode()">
                            <i class="fas fa-eye"></i> Preview Only
                        </button>
                        <button type="button" class="btn btn-outline-primary me-2" onclick="splitMode()">
                            <i class="fas fa-columns"></i> Split View
                        </button>
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

            <div class="row editor-container">
                <div class="col-lg-6" id="editorColumn">
                    <div class="editor-pane">
                        <div class="toolbar">
                            <i class="fas fa-code"></i> Markdown Editor
                            <div class="float-end">
                                <small class="text-muted">Live Preview →</small>
                            </div>
                        </div>
                        <textarea class="form-control editor-textarea" id="main_answer" name="main_answer" 
                                  placeholder="Enter your detailed answer using Markdown formatting..."><?php echo $faq ? htmlspecialchars($faq['answer']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="col-lg-6" id="previewColumn">
                    <div class="preview-pane">
                        <div class="toolbar">
                            <i class="fas fa-eye"></i> Live Preview
                            <div class="float-end">
                                <small class="text-muted">Updates as you type</small>
                            </div>
                        </div>
                        <div class="preview-content" id="previewContent">
                            <p class="text-muted"><i class="fas fa-info-circle"></i> Start typing in the editor to see your preview...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4 mb-5">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <div class="word-count">
                            Markdown Content - Words: <span id="mainAnswerCount">0</span> | Characters: <span id="mainAnswerChars">0</span>
                        </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    
    <script>
        // Initialize editor
        const editor = document.getElementById('main_answer');
        const preview = document.getElementById('previewContent');
        
        let debounceTimer;
        
        // Update preview with debouncing
        function updatePreview() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const content = editor.value;
                if (content.trim() === '') {
                    preview.innerHTML = '<p class="text-muted"><i class="fas fa-info-circle"></i> Start typing in the editor to see your preview...</p>';
                    return;
                }
                
                // Send to server for rendering
                fetch('render-markdown.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'content=' + encodeURIComponent(content)
                })
                .then(response => response.text())
                .then(html => {
                    preview.innerHTML = html;
                    // Re-highlight code blocks
                    Prism.highlightAllUnder(preview);
                })
                .catch(error => {
                    preview.innerHTML = '<div class="alert alert-danger">Preview error: ' + error.message + '</div>';
                });
                
                updateWordCount();
            }, 300);
        }
        
        // Update word counts
        function updateWordCount() {
            const mainText = editor.value;
            
            document.getElementById('mainAnswerCount').textContent = mainText.trim() ? mainText.trim().split(/\s+/).length : 0;
            document.getElementById('mainAnswerChars').textContent = mainText.length;
        }
        
        // Insert Markdown syntax
        function insertMarkdown(syntax) {
            const start = editor.selectionStart;
            const end = editor.selectionEnd;
            const selectedText = editor.value.substring(start, end);
            
            let insertText = syntax;
            let cursorPos = start;
            
            // Handle different syntax types
            if (syntax.includes('**Bold Text**')) {
                insertText = selectedText ? `**${selectedText}**` : '**Bold Text**';
                cursorPos = selectedText ? end + 4 : start + 2;
            } else if (syntax.includes('*Italic Text*')) {
                insertText = selectedText ? `*${selectedText}*` : '*Italic Text*';
                cursorPos = selectedText ? end + 2 : start + 1;
            } else if (syntax.startsWith('#')) {
                insertText = selectedText ? `${syntax.replace('Header', selectedText)}` : syntax;
                cursorPos = start + insertText.length;
            } else {
                insertText = selectedText ? syntax.replace(/(Text|item|Quote|code)/, selectedText) : syntax;
                cursorPos = start + insertText.length;
            }
            
            editor.value = editor.value.substring(0, start) + insertText + editor.value.substring(end);
            editor.focus();
            editor.setSelectionRange(cursorPos, cursorPos);
            updatePreview();
        }
        
        // Insert table template
        function insertTable() {
            const tableTemplate = `| Column 1 | Column 2 | Column 3 |
|----------|----------|----------|
| Row 1 | Data | Data |
| Row 2 | Data | Data |`;
            
            const start = editor.selectionStart;
            editor.value = editor.value.substring(0, start) + tableTemplate + editor.value.substring(editor.selectionEnd);
            editor.focus();
            editor.setSelectionRange(start + tableTemplate.length, start + tableTemplate.length);
            updatePreview();
        }
        
        // View mode functions
        function previewMode() {
            document.getElementById('editorColumn').style.display = 'none';
            document.getElementById('previewColumn').className = 'col-12';
        }
        
        function splitMode() {
            document.getElementById('editorColumn').style.display = 'block';
            document.getElementById('editorColumn').className = 'col-lg-6';
            document.getElementById('previewColumn').className = 'col-lg-6';
        }
        
        // Save draft
        function saveDraft() {
            showStatus('Saving draft...', 'info');
            
            const formData = new FormData(document.getElementById('faqForm'));
            formData.append('save_draft', '1');
            
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
        
        // Event listeners
        editor.addEventListener('input', updatePreview);
        
        // Keyboard shortcuts
        editor.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'b':
                        e.preventDefault();
                        insertMarkdown('**Bold Text**');
                        break;
                    case 'i':
                        e.preventDefault();
                        insertMarkdown('*Italic Text*');
                        break;
                    case 's':
                        e.preventDefault();
                        saveDraft();
                        break;
                }
            }
        });
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            updatePreview();
            updateWordCount();
        });
    </script>
</body>
</html>
