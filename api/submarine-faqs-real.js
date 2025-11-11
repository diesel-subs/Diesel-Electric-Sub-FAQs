// Import real submarine FAQ data from markdown files
import { submarineData } from '../submarine-faq-data.js';

// Add descriptions to categories
function getCategoryDescription(name) {
  const descriptions = {
    'Hull and Compartments': 'Learn about submarine construction, hull design, and compartment layouts.',
    'US WW2 Subs in General': 'General information about American submarines during World War II.',
    'Life Aboard WW2 US Subs': 'Daily life, living conditions, and crew experiences aboard submarines.',
    'Operating US Subs in WW2': 'Operational procedures, tactics, and submarine warfare techniques.',
    'Attacks and Battles, Small and Large': 'Combat operations, battles, and military engagements.',
    'Who Were the Crews Aboard WW2 US Subs': 'Information about submarine crews, their roles, and backgrounds.'
  };
  return descriptions[name] || 'Detailed information about submarine operations and technology.';
}

// Enhance categories with descriptions
const enhancedCategories = submarineData.categories.map(category => ({
  ...category,
  description: getCategoryDescription(category.name)
}));

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
        return res.json(enhancedCategories);

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
        // Data is already loaded from markdown files
        return res.json({
          message: 'Real submarine FAQ data loaded!',
          categories: submarineData.categories.length,
          faqs: submarineData.faqs.length,
          breakdown: enhancedCategories.map(cat => ({
            name: cat.name,
            count: submarineData.faqs.filter(f => f.category_id === cat.id).length
          }))
        });

      default:
        return res.status(400).json({ error: 'Invalid action' });
    }

  } catch (error) {
    console.error('API error:', error);
    return res.status(500).json({ error: 'API error: ' + error.message });
  }
}