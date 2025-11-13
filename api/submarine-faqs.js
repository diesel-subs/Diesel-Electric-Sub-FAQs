// Import real submarine FAQ data from markdown files
const { submarineData } = require('../submarine-faq-data.js');

// Add descriptions to categories
const categoriesWithDescriptions = submarineData.categories.map(category => ({
  ...category,
  description: getCategoryDescription(category.name)
}));

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

// Use the real data
const fullSubmarineData = {
  categories: categoriesWithDescriptions,
  faqs: submarineData.faqs.slice(0, 5) // Remove this line - this was for sample data
    {
  id: 1,
    question: "Is a submarine a boat or a ship?",
      answer: "Submarines are traditionally called 'boats' in naval terminology, despite their size. This tradition dates back to early submarines which were small enough to be considered boats.",
        category_id: 2,
          category_name: "US WW2 Subs in General"
},
{
  id: 2,
    question: "How deep could WW2 submarines dive?",
      answer: "Most WW2 US submarines had a test depth of around 300 feet, with a crush depth estimated at about 500-600 feet. However, some submarines exceeded these limits in emergency situations.",
        category_id: 4,
          category_name: "Operating US Subs in WW2"
},
{
  id: 3,
    question: "What was daily life like aboard a WW2 submarine?",
      answer: "Life aboard WW2 submarines was cramped and challenging. Crews worked in shifts, shared bunks (hot bunking), and dealt with limited fresh water, cramped quarters, and the constant smell of diesel fuel and battery acid.",
        category_id: 3,
          category_name: "Life Aboard WW2 US Subs"
},
{
  id: 4,
    question: "How were submarine crews selected?",
      answer: "Submarine crews were volunteers who underwent rigorous training and psychological evaluation. They needed to work well in confined spaces and handle the stress of underwater operations.",
        category_id: 6,
          category_name: "Who Were the Crews Aboard WW2 US Subs"
},
{
  id: 5,
    question: "What was the most famous submarine attack of WW2?",
      answer: "One of the most famous attacks was the sinking of the Japanese aircraft carrier Shinano by USS Archerfish in November 1944, making it the largest warship ever sunk by a submarine.",
        category_id: 5,
          category_name: "Attacks and Battles, Small and Large"
},
{
  id: 6,
    question: "How thick was a submarine hull?",
      answer: "WW2 submarine pressure hulls were typically 7/8 to 1 inch thick, made of high-tensile steel. The hull had to withstand enormous water pressure at diving depths.",
        category_id: 1,
          category_name: "Hull and Compartments"
},
{
  id: 7,
    question: "How long were typical WW2 submarine patrols?",
      answer: "WW2 submarine patrols typically lasted 45-75 days, depending on fuel capacity, food supplies, and mission objectives. Crews would spend weeks at sea without seeing sunlight.",
        category_id: 4,
          category_name: "Operating US Subs in WW2"
},
{
  id: 8,
    question: "What did submarine crews eat during long patrols?",
      answer: "Submarine crews started with fresh food that was quickly consumed, then relied on canned goods, dried foods, and preserved items. Coffee and chocolate were precious morale boosters.",
        category_id: 3,
          category_name: "Life Aboard WW2 US Subs"
},
{
  id: 9,
    question: "How did submarines navigate underwater?",
      answer: "WW2 submarines used dead reckoning, compass bearings, and celestial navigation when surfaced. Underwater navigation relied on gyrocompasses, speed logs, and careful plotting.",
        category_id: 4,
          category_name: "Operating US Subs in WW2"
},
{
  id: 10,
    question: "What was the crew size of a typical WW2 US submarine?",
      answer: "Most WW2 US submarines carried crews of 60-80 men, including officers and enlisted personnel. Space was so limited that crew members often shared bunks in rotating shifts.",
        category_id: 6,
          category_name: "Who Were the Crews Aboard WW2 US Subs"
}
  ]
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
        // Data is already loaded in-memory - no setup needed
        return res.json({
          message: 'Submarine FAQ API ready!',
          categories: submarineData.categories.length,
          faqs: submarineData.faqs.length
        });

      default:
        return res.status(400).json({ error: 'Invalid action' });
    }

  } catch (error) {
    console.error('API error:', error);
    return res.status(500).json({ error: 'API error: ' + error.message });
  }
}