// Simple test without database for now
const staticData = {
  categories: [
    { id: 1, name: "Hull and Compartments" },
    { id: 2, name: "US WW2 Subs in General" },
    { id: 3, name: "Life Aboard WW2 US Subs" },
    { id: 4, name: "Operating US Subs in WW2" },
    { id: 5, name: "Attacks and Battles, Small and Large" },
    { id: 6, name: "Who Were the Crews Aboard WW2 US Subs" }
  ],
  stats: {
    total_faqs: 185,
    total_categories: 6,
    status: 'online'
  }
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
        return res.json(staticData.categories);

      case 'faqs':
        // Return sample FAQs for testing
        const sampleFAQs = [
          {
            id: 1,
            question: "Is a submarine a boat or a ship?",
            answer: "Submarines are traditionally called 'boats' in naval terminology, despite their size. This tradition dates back to early submarines which were small enough to be considered boats.",
            category_id: category_id || 2,
            category_name: "US WW2 Subs in General"
          },
          {
            id: 2,
            question: "How deep could WW2 submarines dive?",
            answer: "Most WW2 US submarines had a test depth of around 300 feet, with a crush depth estimated at about 500-600 feet. However, some submarines exceeded these limits in emergency situations.",
            category_id: category_id || 4,
            category_name: "Operating US Subs in WW2"
          }
        ];
        return res.json(sampleFAQs);

      case 'search':
        if (q) {
          // Return search results based on query
          const results = [
            {
              id: 3,
              question: `Sample result for "${q}"`,
              answer: `This is a sample FAQ result that matches your search for "${q}". The full database contains 185 detailed FAQs about diesel-electric submarines.`,
              category_name: "Sample Category"
            }
          ];
          return res.json(results);
        } else {
          return res.json([]);
        }

      case 'stats':
        return res.json(staticData.stats);

      default:
        return res.status(400).json({ error: 'Invalid action' });
    }

  } catch (error) {
    console.error('API error:', error);
    return res.status(500).json({ error: 'API error: ' + error.message });
  }
}