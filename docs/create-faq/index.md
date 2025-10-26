# Create New FAQ

<form id="faq-form">
  <div style="margin-bottom: 1rem;">
    <label for="category" style="display: block; margin-bottom: 0.5rem;">Category:</label>
    <select id="category" required style="width: 100%; padding: 0.5rem; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 14px; font-family: inherit;">
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
    <input type="text" id="question" required style="width: 100%; padding: 0.5rem; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 14px; font-family: inherit;" 
           placeholder="Enter the FAQ question...">
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="short_answer" style="display: block; margin-bottom: 0.5rem;">Short Answer:</label>
    <textarea id="short_answer" required style="width: 100%; padding: 0.5rem; height: 80px; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 14px; font-family: inherit;" 
              placeholder="Brief answer for the Quick Answer tab"></textarea>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="detailed_answer" style="display: block; margin-bottom: 0.5rem;">Detailed Answer:</label>
    <textarea id="detailed_answer" required style="width: 100%; padding: 0.5rem; height: 120px; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 14px; font-family: inherit;" 
              placeholder="Complete detailed explanation"></textarea>
  </div>

  <div style="margin-bottom: 1rem;">
    <label for="author" style="display: block; margin-bottom: 0.5rem;">Author (optional):</label>
    <input type="text" id="author" style="width: 100%; padding: 0.5rem; border: 2px solid #2196F3; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 14px; font-family: inherit;" 
           placeholder="Your name">
  </div>

  <!-- Honeypot -->
  <input type="text" id="website" style="position: absolute; left: -9999px;" tabindex="-1">

  <button type="submit" style="background: #2196F3; color: white; border: none; padding: 0.75rem 1.5rem; 
                               border-radius: 4px; cursor: pointer; font-size: 1rem;">
    Create FAQ
  </button>
</form>

<div id="status" style="margin-top: 1rem; padding: 1rem; border-radius: 4px; display: none;"></div>

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

// Save GitHub token to localStorage
function saveToken() {
  const tokenInput = document.getElementById('token-input');
  const token = tokenInput.value.trim();
  
  if (!token) {
    alert('Please enter a valid GitHub token');
    return;
  }
  
  if (!token.startsWith('ghp_') && !token.startsWith('github_pat_')) {
    alert('Invalid token format. GitHub tokens should start with "ghp_" or "github_pat_"');
    return;
  }
  
  localStorage.setItem('github_token', token);
  alert('Token saved! You can now create FAQs.');
  
  // Hide the token input and retry the form submission
  const status = document.getElementById('status');
  status.style.display = 'none';
}

// Update category index.md file to include the new FAQ
async function updateCategoryIndex(owner, repo, branch, token, category, filename, question) {
  // URL encode the category name for the API path
  const encodedCategory = encodeURIComponent(category);
  const indexPath = `docs/categories/${encodedCategory}/index.md`;
  
  console.log('Updating category index for category:', category);
  console.log('Encoded category:', encodedCategory);
  console.log('Index path:', indexPath);
  
  try {
    // First, get the current index.md content
    const getUrl = `https://api.github.com/repos/${owner}/${repo}/contents/${indexPath}`;
    console.log('Getting index file from:', getUrl);
    
    const getResponse = await fetch(getUrl, {
      headers: {
        'Authorization': `token ${token}`,
        'Accept': 'application/vnd.github.v3+json'
      }
    });
    
    console.log('Get response status:', getResponse.status);
    
    let currentContent = '';
    let sha = null;
    let fileExists = false;
    
    if (getResponse.ok) {
      console.log('Index file exists, updating...');
      const indexData = await getResponse.json();
      currentContent = atob(indexData.content);
      sha = indexData.sha;
      fileExists = true;
      console.log('Existing content length:', currentContent.length);
      console.log('File SHA:', sha);
    } else if (getResponse.status === 404) {
      console.log('Index file does not exist, creating new one...');
      // File doesn't exist, create a new one
      currentContent = `# ${category}\n\n## Questions\n\n`;
      fileExists = false;
      console.log('Will create new file with initial content');
    } else {
      const errorText = await getResponse.text();
      console.error('Unexpected response getting index:', getResponse.status, errorText);
      throw new Error(`Failed to get index file: ${getResponse.status} - ${errorText}`);
    }
    
    // Add the new FAQ entry
    const newEntry = `- [${question}](./${filename})\n`;
    
    // Find the last bullet point and add after it, or add to the end if no bullets exist
    const lines = currentContent.split('\n');
    let lastBulletIndex = -1;
    
    // Find the last line that starts with "- ["
    for (let i = lines.length - 1; i >= 0; i--) {
      if (lines[i].trim().match(/^- \[/)) {
        lastBulletIndex = i;
        break;
      }
    }
    
    let updatedContent;
    if (lastBulletIndex >= 0) {
      // Insert after the last bullet point
      lines.splice(lastBulletIndex + 1, 0, newEntry.trim());
      updatedContent = lines.join('\n');
    } else {
      // No existing bullet points found, add after the description
      // Look for the first blank line after the title and description
      let insertIndex = 2; // Default after title
      for (let i = 2; i < lines.length; i++) {
        if (lines[i].trim() === '') {
          insertIndex = i + 1;
          break;
        }
      }
      lines.splice(insertIndex, 0, '', newEntry.trim());
      updatedContent = lines.join('\n');
    }
    
    console.log('Updated content length:', updatedContent.length);
    console.log('File exists:', fileExists);
    console.log('SHA value:', sha);
    
    // Prepare the update request
    const requestBody = {
      message: `Update ${category} index: add ${question}`,
      content: btoa(unescape(encodeURIComponent(updatedContent))),
      branch: branch,
      committer: {
        name: 'FAQ Creator',
        email: 'faq@dieselsubs.com'
      }
    };
    
    // Include SHA only if file exists (for updates, not creates)
    if (fileExists && sha) {
      requestBody.sha = sha;
      console.log('Including SHA for file update:', sha);
    } else {
      console.log('Creating new file, no SHA needed');
    }
    
    console.log('Request body structure:', {
      message: requestBody.message,
      contentLength: requestBody.content.length,
      branch: requestBody.branch,
      hasSha: !!requestBody.sha,
      sha: requestBody.sha
    });
    
    // Update the index file
    const updateResponse = await fetch(getUrl, {
      method: 'PUT',
      headers: {
        'Authorization': `token ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/vnd.github.v3+json'
      },
      body: JSON.stringify(requestBody)
    });
    
    console.log('Update response status:', updateResponse.status);
    
    if (!updateResponse.ok) {
      const errorData = await updateResponse.text();
      console.error('Update failed:', errorData);
      
      // Handle specific GitHub Pages build conflicts
      if (errorData.includes('higher priority waiting request for pages')) {
        throw new Error('GitHub Pages is building. Category index will update automatically after the build completes.');
      }
      
      throw new Error(`Failed to update index: ${updateResponse.status} - ${errorData}`);
    }
    
    const result = await updateResponse.json();
    console.log('Index update successful:', result);
    return result;
    
  } catch (error) {
    console.error('Error updating category index:', error);
    throw error;
  }
}

document.getElementById('faq-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const status = document.getElementById('status');
  const button = this.querySelector('button[type="submit"]');
  
  // Show loading state
  button.disabled = true;
  button.textContent = 'Creating...';
  status.style.display = 'block';
  status.style.background = '#e3f2fd';
  status.style.color = '#1976d2';
  status.textContent = 'Preparing FAQ...';
  
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
    
    // Generate the markdown content and filename
    const content = generateMarkdownContent(data);
    const filename = generateFilename(data.question) + '.md';
    const filePath = `docs/categories/${data.category}/${filename}`;
    
    console.log('Generated filename:', filename);
    console.log('File path:', filePath);
    
    // GitHub API configuration
    const owner = 'diesel-subs';
    const repo = 'Diesel-Electric-Submarine-FAQs';
    const branch = 'main';
    
    // Check for GitHub token
    const githubToken = localStorage.getItem('github_token');
    
    if (!githubToken) {
      status.style.background = '#fff3cd';
      status.style.color = '#856404';
      status.innerHTML = `
        <strong>GitHub Token Required</strong><br>
        Please set your GitHub Personal Access Token:<br>
        <input type="password" id="token-input" placeholder="ghp_..." style="width: 300px; padding: 0.5rem; margin: 0.5rem 0;">
        <button onclick="saveToken()" style="padding: 0.5rem 1rem; background: #007bff; color: white; border: none; border-radius: 4px;">Save Token</button><br>
        <small>Create a token at: <a href="https://github.com/settings/tokens" target="_blank">GitHub Settings → Developer settings → Personal access tokens</a><br>
        Required permissions: repo (Full control of private repositories)</small>
      `;
      button.disabled = false;
      button.textContent = 'Create FAQ';
      return;
    }
    
    status.textContent = 'Committing to GitHub...';
    console.log('Using GitHub token (first 10 chars):', githubToken.substring(0, 10) + '...');
    
    // Base64 encode the content for GitHub API
    const encodedContent = btoa(unescape(encodeURIComponent(content)));
    console.log('Content encoded, length:', encodedContent.length);
    
    // Check if file already exists first
    const apiUrl = `https://api.github.com/repos/${owner}/${repo}/contents/${filePath}`;
    console.log('Checking if file exists:', apiUrl);
    
    // First, check if the file already exists
    const checkResponse = await fetch(apiUrl, {
      headers: {
        'Authorization': `token ${githubToken}`,
        'Accept': 'application/vnd.github.v3+json'
      }
    });
    
    let existingSha = null;
    if (checkResponse.ok) {
      const existingData = await checkResponse.json();
      existingSha = existingData.sha;
      console.log('File already exists, will update with SHA:', existingSha);
    } else if (checkResponse.status === 404) {
      console.log('File does not exist, will create new file');
    } else {
      const errorText = await checkResponse.text();
      console.error('Error checking file existence:', checkResponse.status, errorText);
      throw new Error(`Failed to check file existence: ${checkResponse.status}`);
    }
    
    // Prepare the request body
    const requestBody = {
      message: `Add FAQ: ${data.question}`,
      content: encodedContent,
      branch: branch,
      committer: {
        name: data.author || 'FAQ Creator',
        email: 'faq@dieselsubs.com'
      }
    };
    
    // Include SHA only if file already exists
    if (existingSha) {
      requestBody.sha = existingSha;
      console.log('Including SHA for file update');
    } else {
      console.log('Creating new file, no SHA needed');
    }
    
    console.log('Request body structure:', {
      message: requestBody.message,
      contentLength: requestBody.content.length,
      branch: requestBody.branch,
      hasSha: !!requestBody.sha
    });
    
    const response = await fetch(apiUrl, {
      method: 'PUT',
      headers: {
        'Authorization': `token ${githubToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/vnd.github.v3+json'
      },
      body: JSON.stringify(requestBody)
    });
    
    console.log('GitHub API response status:', response.status);
    
    if (response.ok) {
      const result = await response.json();
      console.log('Success response:', result);
      
      // Now update the category index.md file
      status.textContent = 'Updating category index...';
      
      try {
        await updateCategoryIndex(owner, repo, branch, githubToken, data.category, filename, data.question);
        console.log('Category index updated successfully');
      } catch (indexError) {
        console.warn('Failed to update category index:', indexError);
        // Don't fail the whole operation if index update fails
      }
      
      // Success
      status.style.background = '#e8f5e8';
      status.style.color = '#2e7d32';
      status.innerHTML = `
        <strong>Success!</strong><br>
        FAQ created and committed to GitHub!<br>
        Category index updated!<br>
        <br>
        <strong>Details:</strong><br>
        File: ${filename}<br>
        Category: ${data.category}<br>
        <br>
        <a href="${result.content.html_url}" target="_blank" style="color: #1976d2;">View file on GitHub</a><br>
        <small>GitHub Pages will rebuild your site automatically (may take a few minutes)</small>
      `;
      
      // Reset form
      this.reset();
      
    } else {
      const errorText = await response.text();
      console.error('GitHub API error response:', errorText);
      
      let errorMessage;
      
      // Handle specific GitHub Pages build conflicts
      if (errorText.includes('higher priority waiting request for pages')) {
        errorMessage = 'GitHub Pages is currently building. Please wait a moment and try again.';
      } else {
        try {
          const errorData = JSON.parse(errorText);
          errorMessage = errorData.message || `GitHub API error: ${response.status}`;
        } catch {
          errorMessage = `GitHub API error: ${response.status} - ${errorText}`;
        }
      }
      
      throw new Error(errorMessage);
    }
    
  } catch (error) {
    console.error('Error creating FAQ:', error);
    
    // Error
    status.style.background = '#ffebee';
    status.style.color = '#c62828';
    status.textContent = 'Error: ' + error.message;
  } finally {
    // Always reset button state
    button.disabled = false;
    button.textContent = 'Create FAQ';
  }
});
</script>