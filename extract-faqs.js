const fs = require('fs');
const path = require('path');

// Map filenames to category info
const categoryMap = {
  '05-Hull-and-Compartments.md': {
    id: 1,
    name: 'Hull and Compartments',
    description: 'Learn about submarine construction, hull design, and compartment layouts.'
  },
  '08-US-WW2-Subs-in-General.md': {
    id: 2,
    name: 'US WW2 Subs in General',
    description: 'General information about American submarines during World War II.'
  },
  '10-Operating-US-WW2-Subs.md': {
    id: 3,
    name: 'Operating US Subs in WW2',
    description: 'Operational procedures, tactics, and submarine warfare techniques.'
  },
  '12-Crews-Aboard-US-WW2-Subs.md': {
    id: 4,
    name: 'Who Were the Crews Aboard WW2 US Subs',
    description: 'Information about submarine crews, their roles, and backgrounds.'
  },
  '15-Life-Aboard-US-WW2-Subs.md': {
    id: 5,
    name: 'Life Aboard WW2 US Subs',
    description: 'Daily life, living conditions, and crew experiences aboard submarines.'
  },
  '20-Attacks-and-Battles-Small-and-Large.md': {
    id: 6,
    name: 'Attacks and Battles, Small and Large',
    description: 'Combat operations, battles, and military engagements.'
  }
};

function extractFAQsFromMarkdown(content, categoryInfo) {
  const faqs = [];
  let faqId = 1;

  // Split content by ** patterns to find questions
  const sections = content.split(/\*\*(.*?)\*\*/g);

  for (let i = 1; i < sections.length; i += 2) {
    const question = sections[i].trim();
    const answer = sections[i + 1] ? sections[i + 1].trim() : '';

    if (question && answer && question.includes('?')) {
      // Clean up the answer - remove markdown formatting and extra whitespace
      const cleanAnswer = answer
        .replace(/\n\n+/g, '\n\n')
        .replace(/>\s*\*\*NOTE:\*\*/g, 'NOTE:')
        .replace(/>\s*/g, '')
        .trim();

      if (cleanAnswer.length > 10) { // Only include substantial answers
        faqs.push({
          id: faqId++,
          question: question.trim(),
          answer: cleanAnswer,
          category_id: categoryInfo.id,
          category_name: categoryInfo.name
        });
      }
    }
  }

  return faqs;
}

// Process all markdown files
const allFAQs = [];
const categories = [];
let totalFAQId = 1;

Object.entries(categoryMap).forEach(([filename, categoryInfo]) => {
  const filePath = path.join(__dirname, filename);

  if (fs.existsSync(filePath)) {
    console.log(`Processing ${filename}...`);
    const content = fs.readFileSync(filePath, 'utf8');
    const faqs = extractFAQsFromMarkdown(content, categoryInfo);

    // Update FAQ IDs to be globally unique
    faqs.forEach(faq => {
      faq.id = totalFAQId++;
    });

    allFAQs.push(...faqs);
    categories.push(categoryInfo);

    console.log(`  Extracted ${faqs.length} FAQs`);
  } else {
    console.log(`File not found: ${filename}`);
  }
});

// Generate the JavaScript data structure
const jsData = `// Real submarine FAQ data extracted from markdown files
// Generated on ${new Date().toISOString()}
// Total FAQs: ${allFAQs.length}

const submarineData = {
  categories: ${JSON.stringify(categories, null, 2)},
  faqs: ${JSON.stringify(allFAQs, null, 2)}
};

export default async function handler(req, res) {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  const { action, category_id, q } = req.query;

  try {
    switch (action) {
      case 'categories':
        return res.json(submarineData.categories);
        
      case 'faqs':
        if (category_id) {
          const categoryFaqs = submarineData.faqs.filter(faq => 
            faq.category_id === parseInt(category_id)
          );
          return res.json(categoryFaqs);
        } else {
          return res.json(submarineData.faqs);
        }
        
      case 'search':
        if (q) {
          const searchResults = submarineData.faqs.filter(faq =>
            faq.question.toLowerCase().includes(q.toLowerCase()) ||
            faq.answer.toLowerCase().includes(q.toLowerCase())
          );
          return res.json(searchResults);
        } else {
          return res.json([]);
        }
        
      case 'stats':
        return res.json({
          total_faqs: submarineData.faqs.length,
          total_categories: submarineData.categories.length,
          status: 'online'
        });
        
      case 'init':
        return res.json({ 
          message: 'Real submarine FAQ API ready!', 
          categories: submarineData.categories.length,
          faqs: submarineData.faqs.length,
          source: 'Extracted from markdown files'
        });
        
      default:
        return res.status(400).json({ error: 'Invalid action' });
    }
    
  } catch (error) {
    console.error('API error:', error);
    return res.status(500).json({ error: 'API error: ' + error.message });
  }
}`;

// Write the new API file
fs.writeFileSync(path.join(__dirname, 'api', 'real-submarine-faqs.js'), jsData);

console.log(`\\n✅ SUCCESS!`);
console.log(`📊 Total Categories: ${categories.length}`);
console.log(`📋 Total FAQs: ${allFAQs.length}`);
console.log(`📁 Generated: api/real-submarine-faqs.js`);
console.log(`\\nFAQs by category:`);

categories.forEach(cat => {
  const count = allFAQs.filter(faq => faq.category_id === cat.id).length;
  console.log(`  ${cat.name}: ${count} FAQs`);
});