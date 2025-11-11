const fs = require('fs');
const path = require('path');

// Category mapping based on your files
const categories = [
  { id: 1, name: "Hull and Compartments", file: "05-Hull-and-Compartments.md" },
  { id: 2, name: "US WW2 Subs in General", file: "08-US-WW2-Subs-in-General.md" },
  { id: 3, name: "Operating US Subs in WW2", file: "10-Operating-US-WW2-Subs.md" },
  { id: 4, name: "Who Were the Crews Aboard WW2 US Subs", file: "12-Crews-Aboard-US-WW2-Subs.md" },
  { id: 5, name: "Life Aboard WW2 US Subs", file: "15-Life-Aboard-US-WW2-Subs.md" },
  { id: 6, name: "Attacks and Battles, Small and Large", file: "20-Attacks-and-Battles-Small-and-Large.md" }
];

function parseMarkdownFAQsPreserveFormatting(content, categoryId, categoryName) {
  const faqs = [];
  const lines = content.split('\n');
  let faqId = 1;
  let currentQuestion = '';
  let currentAnswer = [];
  let inAnswer = false;
  
  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    
    // Check if this is a question (starts and ends with **)
    if (line.match(/^\*\*.*\*\*$/)) {
      // Save previous FAQ if exists
      if (currentQuestion && currentAnswer.length > 0) {
        // Join answer paragraphs, preserving markdown formatting and paragraph breaks
        const formattedAnswer = currentAnswer
          .join('\n')
          .replace(/\n{3,}/g, '\n\n')  // Replace multiple line breaks with double breaks
          .trim();
          
        faqs.push({
          id: faqId++,
          question: currentQuestion.trim(),
          answer: formattedAnswer,
          category_id: categoryId,
          category_name: categoryName
        });
      }
      
      // Start new question - remove ** markers but preserve internal formatting
      currentQuestion = line.slice(2, -2);
      currentAnswer = [];
      inAnswer = true;
    } else if (inAnswer && !line.startsWith('#')) {
      // This is part of the answer - preserve all formatting and structure
      currentAnswer.push(line);
    }
  }
  
  // Don't forget the last FAQ
  if (currentQuestion && currentAnswer.length > 0) {
    const formattedAnswer = currentAnswer
      .join('\n')
      .replace(/\n{3,}/g, '\n\n')
      .trim();
      
    faqs.push({
      id: faqId++,
      question: currentQuestion.trim(),
      answer: formattedAnswer,
      category_id: categoryId,
      category_name: categoryName
    });
  }
  
  return faqs;
}

function getCategoryDescription(name) {
  const descriptions = {
    'Hull and Compartments': 'Learn about submarine construction, hull design, and compartment layouts.',
    'US WW2 Subs in General': 'General information about American submarines during World War II.',
    'Operating US Subs in WW2': 'Operational procedures, tactics, and submarine warfare techniques.',
    'Who Were the Crews Aboard WW2 US Subs': 'Information about submarine crews, their roles, and backgrounds.',
    'Life Aboard WW2 US Subs': 'Daily life, living conditions, and crew experiences aboard submarines.',
    'Attacks and Battles, Small and Large': 'Combat operations, battles, and military engagements.'
  };
  return descriptions[name] || 'Detailed submarine information and answers.';
}

// Extract all FAQs with preserved formatting
let allFAQs = [];
categories.forEach(category => {
  try {
    const filePath = path.join(__dirname, category.file);
    const content = fs.readFileSync(filePath, 'utf-8');
    const faqs = parseMarkdownFAQsPreserveFormatting(content, category.id, category.name);
    allFAQs = allFAQs.concat(faqs);
    console.log(`${category.name}: ${faqs.length} FAQs (with formatting preserved)`);
  } catch (error) {
    console.error(`Error reading ${category.file}:`, error.message);
  }
});

console.log(`\nTotal FAQs extracted: ${allFAQs.length} with preserved markdown formatting`);

// Generate the complete API file with markdown support
const apiContent = `// Complete submarine FAQ data - ALL ${allFAQs.length} FAQs with preserved markdown formatting
module.exports = async function handler(req, res) {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  const { action, category_id, q } = req.query;

  const categories = ${JSON.stringify(categories.map(cat => ({
    id: cat.id,
    name: cat.name,
    description: getCategoryDescription(cat.name)
  })), null, 2)};

  const faqs = ${JSON.stringify(allFAQs, null, 2)};

  try {
    switch (action) {
      case 'categories':
        return res.json(categories);
        
      case 'faqs':
        if (category_id) {
          const categoryFaqs = faqs.filter(faq => 
            faq.category_id === parseInt(category_id)
          );
          return res.json(categoryFaqs);
        } else {
          return res.json(faqs);
        }
        
      case 'search':
        if (q) {
          const searchResults = faqs.filter(faq =>
            faq.question.toLowerCase().includes(q.toLowerCase()) ||
            faq.answer.toLowerCase().includes(q.toLowerCase())
          );
          return res.json(searchResults);
        } else {
          return res.json([]);
        }
        
      case 'stats':
        return res.json({
          total_faqs: faqs.length,
          total_categories: categories.length,
          status: 'online'
        });
        
      default:
        return res.status(400).json({ error: 'Invalid action' });
    }
    
  } catch (error) {
    console.error('API error:', error);
    return res.status(500).json({ error: 'API error: ' + error.message });
  }
}`;

// Write the improved API file
fs.writeFileSync(path.join(__dirname, 'api/formatted-faqs.js'), apiContent);
console.log(`\n✅ Generated formatted-faqs.js with ${allFAQs.length} FAQs and preserved markdown formatting!`);