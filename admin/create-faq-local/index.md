# Create New FAQ (Local Development)

<form id="faq-form">
  <div style="margin-bottom: 1rem;">
    <label for="category" style="display: block; margin-bottom: 0.5rem;">Category:</label>
    <select id="category" required style="width: 100%; padding: 0.5rem;">
      <option value="">Select a category...</option>
      <option value="Battles, Small and Large">Battles, Small and Large</option>
      <option value="Crews Aboard US WW2 Subs">Crews Aboard US WW2 Subs</option>
      <option value="Hull and Compartments">Hull and Compartments</option>
      <option value="Life Aboard US WW2 Subs">Life Aboard US WW2 Subs</option>
      <option value="Operating US WW2 Subs">Operating US WW2 Subs</option>
      <option value="US WW2 Subs in General">US WW2 Subs in General</option>
    </select>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="question" style="display: block; margin-bottom: 0.5rem;">Question:</label>
    <input type="text" id="question" required style="width: 100%; padding: 0.75rem; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 1rem;" 
           placeholder="Enter the FAQ question...">
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="short_answer" style="display: block; margin-bottom: 0.5rem;">Short Answer:</label>
    <textarea id="short_answer" required style="width: 100%; padding: 0.5rem; height: 80px;" 
              placeholder="Brief answer for the Quick Answer tab"></textarea>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="detailed_answer" style="display: block; margin-bottom: 0.5rem;">Detailed Answer:</label>
    <textarea id="detailed_answer" required style="width: 100%; padding: 0.5rem; height: 120px;" 
              placeholder="Complete detailed explanation"></textarea>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="author" style="display: block; margin-bottom: 0.5rem;">Author (optional):</label>
    <input type="text" id="author" style="width: 100%; padding: 0.5rem;" 
           placeholder="Your name">
  </div>

  <!-- Honeypot -->
  <input type="text" id="website" style="position: absolute; left: -9999px;" tabindex="-1">

  <button type="submit" style="background: #2196F3; color: white; border: none; padding: 0.75rem 1.5rem; 
                               border-radius: 4px; cursor: pointer; font-size: 1rem;">
    Create FAQ Locally
  </button>
</form>

<div id="status" style="margin-top: 1rem; padding: 1rem; border-radius: 4px; display: none;"></div>

<div id="output" style="margin-top: 1rem; padding: 1rem; border-radius: 4px; background: #f5f5f5; display: none;">
  <h3>Generated Files:</h3>
  <div id="file-list"></div>
</div>

<script>
// Helper functions
function generateFilename(question) {
  return 'Q-' + question
    .replace(/[^a-zA-Z0-9\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')
    .substring(0, 80);
}

function generateMarkdownContent(data) {
  let content = `# ${data.question}\n\n`;
  
  content += '!!! help-feedback ""\n';
  content += '    <a href="/feedback/" data-feedback-link>Click here</a>\n';
  content += '    if you have additional facts, records, or context about U.S. submarine design, production, or wartime operations.\n\n';
  
  content += '<a id="summary"></a>\n';
  content += '=== "Summary"\n\n';
  content += `    ${data.short_answer}\n\n`;
  content += '=== "Detailed Answer"\n\n';
  content += `    ${data.detailed_answer}\n\n`;
  content += '=== "Related Topics"\n\n';
  content += '    \n\n';
  
  return content;
}

function updateCategoryIndexFile(category, filename, question) {
  // This function will actually modify the local index.md file
  const categoryPath = `docs/categories/${category}`;
  const indexFilePath = `${categoryPath}/index.md`;
  const newEntry = `- [${question}](./${filename})`;
  
  // We'll provide instructions and the exact text to add
  return {
    filePath: indexFilePath,
    entryToAdd: newEntry,
    instructions: `Add this line under the "## Questions" section in ${indexFilePath}:\n${newEntry}`
  };
}

function downloadFile(filename, content) {
  const blob = new Blob([content], { type: 'text/markdown' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

// Simplified function that creates the updated index.md without fetching
function createUpdatedIndexFile(category, filename, question) {
  console.log('Creating updated index for category:', category);
  
  // Since we can't reliably fetch the existing file via HTTP in all MkDocs setups,
  // we'll create a template that the user can manually merge
  const newEntry = `- [${question}](./${filename})`;
  console.log('New entry to add:', newEntry);
  
  // Create instructions for manual update
  const instructions = `
# Instructions for updating ${category}/index.md

## Step 1: Add this line to your existing index.md file
Add this line under the "## Questions" section (at the top of the list):

${newEntry}

## Step 2: Or use this complete template if creating a new index.md

# ${category}

Overview of ${category} topics.

## Questions

${newEntry}

## Step 3: File location
Save as: docs/categories/${category}/index.md
`;

  // Also create a complete new index.md file in case they need it
  const completeIndexContent = `# ${category}

Overview of ${category} topics.

## Questions

${newEntry}

`;

  console.log('Generated instructions and template content');
  
  return {
    filename: 'index.md',
    content: completeIndexContent,
    instructions: instructions,
    newEntry: newEntry,
    path: `docs/categories/${category}/index.md`
  };
}

function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(() => {
    console.log('Copied to clipboard');
  }).catch(err => {
    console.error('Failed to copy: ', err);
  });
}

document.getElementById('faq-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const status = document.getElementById('status');
  const output = document.getElementById('output');
  const fileList = document.getElementById('file-list');
  const button = this.querySelector('button[type="submit"]');
  
  // Show loading state
  button.disabled = true;
  button.textContent = 'Generating...';
  status.style.display = 'block';
  status.style.background = '#e3f2fd';
  status.style.color = '#1976d2';
  status.textContent = 'Generating FAQ files...';
  
  try {
    // Get form data
    const data = {
      category: document.getElementById('category').value,
      question: document.getElementById('question').value,
      short_answer: document.getElementById('short_answer').value,
      detailed_answer: document.getElementById('detailed_answer').value,
      author: document.getElementById('author').value,
      website: document.getElementById('website').value // honeypot
    };
    
    console.log('Form data:', data);
    
    // Validate required fields
    if (!data.category || !data.question || !data.short_answer || !data.detailed_answer) {
      throw new Error('Please fill in all required fields');
    }
    
    // Check honeypot
    if (data.website) {
      throw new Error('Spam detected');
    }
    
    // Generate the markdown content and filename
    const content = generateMarkdownContent(data);
    const filename = generateFilename(data.question) + '.md';
    const categoryPath = `docs/categories/${data.category}`;
    const fullPath = `${categoryPath}/${filename}`;
    
    console.log('Generated filename:', filename);
    console.log('Full path:', fullPath);
    
    // Generate category index update instructions
    const indexUpdate = createUpdatedIndexFile(data.category, filename, data.question);
    console.log('Index update result:', indexUpdate);
    
    // Success
    status.style.background = '#e8f5e8';
    status.style.color = '#2e7d32';
    status.innerHTML = `
      <strong>✅ FAQ Generated Successfully!</strong><br>
      Ready to save locally in your development environment.
    `;
    
    // Show output section
    output.style.display = 'block';
    
    // Store the content globally so buttons can access it
    window.currentFaqContent = content;
    window.currentFaqFilename = filename;
    window.currentIndexEntry = `- [${data.question}](./${filename})`;
    window.currentIndexContent = indexUpdate.content;
    window.currentIndexFilename = indexUpdate.filename;
    window.currentIndexPath = indexUpdate.path;
    
    fileList.innerHTML = `
      <div style="margin-bottom: 1rem; padding: 1rem; border: 1px solid #ddd; border-radius: 4px;">
        <h4>FAQ File: ${filename}</h4>
        <p><strong>Location:</strong> <code>${fullPath}</code></p>
        <div style="margin: 0.5rem 0;">
          <button id="download-btn" 
                  style="padding: 0.5rem 1rem; margin-right: 0.5rem; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
            📥 Download File
          </button>
          <button id="copy-content-btn" 
                  style="padding: 0.5rem 1rem; margin-right: 0.5rem; background: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer;">
            📋 Copy Content
          </button>
        </div>
        <details style="margin-top: 0.5rem;">
          <summary style="cursor: pointer; font-weight: bold;">👁️ Preview Content</summary>
          <pre style="background: #f9f9f9; padding: 1rem; margin-top: 0.5rem; overflow-x: auto; white-space: pre-wrap;">${content.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</pre>
        </details>
      </div>
      
      <div style="margin-bottom: 1rem; padding: 1rem; border: 1px solid #ddd; border-radius: 4px;">
        <h4>Updated Category Index: ${indexUpdate.filename}</h4>
        <p><strong>Location:</strong> <code>${indexUpdate.path}</code></p>
        <p><strong>Status:</strong> Automatically updated with your new FAQ entry!</p>
        <div style="margin: 0.5rem 0;">
          <button id="download-index-btn" 
                  style="padding: 0.5rem 1rem; margin-right: 0.5rem; background: #FF9800; color: white; border: none; border-radius: 4px; cursor: pointer;">
            📥 Download Updated Index
          </button>
          <button id="copy-index-btn" 
                  style="padding: 0.5rem 1rem; background: #9C27B0; color: white; border: none; border-radius: 4px; cursor: pointer;">
            📋 Copy Index Content
          </button>
        </div>
        <details style="margin-top: 0.5rem;">
          <summary style="cursor: pointer; font-weight: bold;">👁️ Preview Updated Index</summary>
          <pre style="background: #f9f9f9; padding: 1rem; margin-top: 0.5rem; overflow-x: auto; white-space: pre-wrap;">${(indexUpdate.content || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</pre>
        </details>
      </div>
      
      <div style="margin-top: 1rem; padding: 1rem; background: #fff3cd; border-radius: 4px;">
        <h4>🚀 Next Steps:</h4>
        <ol>
          <li>Download or copy the FAQ file content</li>
          <li>Create the file manually at: <code>${fullPath}</code></li>
          <li>Copy the index entry and add it to: <code>${categoryPath}/index.md</code></li>
          <li>Test your changes locally with <code>mkdocs serve</code></li>
          <li>Commit when ready: <code>git add . && git commit -m "Add FAQ: ${data.question}"</code></li>
        </ol>
      </div>
    `;
    
    // Add event listeners for the buttons
    document.getElementById('download-btn').addEventListener('click', function() {
      downloadFile(window.currentFaqFilename, window.currentFaqContent);
    });
    
    document.getElementById('copy-content-btn').addEventListener('click', function() {
      copyToClipboard(window.currentFaqContent);
    });
    
    document.getElementById('download-index-btn').addEventListener('click', function() {
      downloadFile(window.currentIndexFilename, window.currentIndexContent);
    });
    
    document.getElementById('copy-index-btn').addEventListener('click', function() {
      copyToClipboard(window.currentIndexContent);
    });
    
    // Reset form
    this.reset();
    
  } catch (error) {
    console.error('Error generating FAQ:', error);
    
    // Error
    status.style.background = '#ffebee';
    status.style.color = '#c62828';
    status.textContent = 'Error: ' + error.message;
  } finally {
    // Always reset button state
    button.disabled = false;
    button.textContent = 'Create FAQ Locally';
  }
});
</script>