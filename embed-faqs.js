const fs = require('fs');

// Read the corrected FAQs JSON file
const faqs = JSON.parse(fs.readFileSync('corrected-faqs.json', 'utf-8'));

// Generate the embedded API file
const apiContent = `// Corrected submarine FAQ data - 166 FAQs extracted based on **bold questions ending with ?**
module.exports = async function handler(req, res) {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  const { action, category_id, q } = req.query;

  const categories = [
    {
      "id": 1,
      "name": "Hull and Compartments",
      "description": "Learn about submarine construction, hull design, and compartment layouts."
    },
    {
      "id": 2,
      "name": "US WW2 Subs in General",
      "description": "General information about American submarines during World War II."
    },
    {
      "id": 3,
      "name": "Operating US Subs in WW2",
      "description": "Operational procedures, tactics, and submarine warfare techniques."
    },
    {
      "id": 4,
      "name": "Who Were the Crews Aboard WW2 US Subs",
      "description": "Information about submarine crews, their roles, and backgrounds."
    },
    {
      "id": 5,
      "name": "Life Aboard WW2 US Subs",
      "description": "Daily life, living conditions, and crew experiences aboard submarines."
    },
    {
      "id": 6,
      "name": "Attacks and Battles, Small and Large",
      "description": "Combat operations, battles, and military engagements."
    }
  ];

  const faqs = ${JSON.stringify(faqs, null, 2)};

  if (action === 'stats') {
    return res.json({
      total_faqs: faqs.length,
      total_categories: categories.length,
      breakdown: categories.map(cat => ({
        category: cat.name,
        count: faqs.filter(faq => faq.category_id === cat.id).length
      }))
    });
  }

  if (action === 'categories') {
    return res.json(categories);
  }

  if (action === 'faqs') {
    const categoryFAQs = faqs.filter(faq => faq.category_id == category_id);
    return res.json(categoryFAQs);
  }

  if (action === 'search') {
    if (!q) {
      return res.json([]);
    }
    
    const query = q.toLowerCase();
    const searchResults = faqs.filter(faq => 
      faq.question.toLowerCase().includes(query) || 
      faq.answer.toLowerCase().includes(query)
    );
    
    return res.json(searchResults);
  }

  // Default response
  return res.json({
    message: "Submarine FAQ API - Corrected Data",
    total_faqs: faqs.length,
    categories: categories.length,
    actions: ['stats', 'categories', 'faqs', 'search']
  });
};`;

// Write the new API file
fs.writeFileSync('api/corrected-faqs.js', apiContent);
console.log('Generated embedded API file: api/corrected-faqs.js');
console.log(`Total FAQs embedded: ${faqs.length}`);