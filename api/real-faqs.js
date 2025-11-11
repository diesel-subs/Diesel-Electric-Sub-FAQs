import { promises as fs } from 'fs';
import path from 'path';

// Category mapping based on your files
const categories = [
  { id: 1, name: "Hull and Compartments", file: "05-Hull-and-Compartments.md" },
  { id: 2, name: "US WW2 Subs in General", file: "08-US-WW2-Subs-in-General.md" },
  { id: 3, name: "Operating US Subs in WW2", file: "10-Operating-US-WW2-Subs.md" },
  { id: 4, name: "Who Were the Crews Aboard WW2 US Subs", file: "12-Crews-Aboard-US-WW2-Subs.md" },
  { id: 5, name: "Life Aboard WW2 US Subs", file: "15-Life-Aboard-US-WW2-Subs.md" },
  { id: 6, name: "Attacks and Battles, Small and Large", file: "20-Attacks-and-Battles-Small-and-Large.md" }
];

async function parseMarkdownFAQs(filePath, categoryId, categoryName) {
  try {
    const content = await fs.readFile(filePath, 'utf-8');
    const faqs = [];
    let currentQuestion = '';
    let currentAnswer = '';
    let inQuestion = false;
    let inAnswer = false;
    let faqId = 1;

    const lines = content.split('\n');

    for (const line of lines) {
      if (line.startsWith('**') && line.endsWith('**') && line.length > 4) {
        // Save previous FAQ if exists
        if (currentQuestion && currentAnswer.trim()) {
          faqs.push({
            id: faqId++,
            question: currentQuestion.trim(),
            answer: currentAnswer.trim(),
            category_id: categoryId,
            category_name: categoryName
          });
        }

        // Start new question
        currentQuestion = line.slice(2, -2); // Remove ** markers
        currentAnswer = '';
        inQuestion = true;
        inAnswer = false;
      } else if (inQuestion && line.trim()) {
        // This is part of the answer
        currentAnswer += line + '\n';
        inAnswer = true;
      } else if (inAnswer && line.trim()) {
        // Continue answer
        currentAnswer += line + '\n';
      }
    }

    // Don't forget the last FAQ
    if (currentQuestion && currentAnswer.trim()) {
      faqs.push({
        id: faqId++,
        question: currentQuestion.trim(),
        answer: currentAnswer.trim(),
        category_id: categoryId,
        category_name: categoryName
      });
    }

    return faqs;
  } catch (error) {
    console.error(`Error reading ${filePath}:`, error);
    return [];
  }
}

export default async function handler(req, res) {
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
        return res.json(categories.map(cat => ({
          id: cat.id,
          name: cat.name,
          description: getCategoryDescription(cat.name)
        })));

      case 'faqs':
        if (category_id) {
          const category = categories.find(cat => cat.id === parseInt(category_id));
          if (!category) {
            return res.json([]);
          }

          const filePath = path.join(process.cwd(), category.file);
          const faqs = await parseMarkdownFAQs(filePath, category.id, category.name);
          return res.json(faqs);
        } else {
          // Get all FAQs from all categories
          let allFaqs = [];
          for (const category of categories) {
            const filePath = path.join(process.cwd(), category.file);
            const faqs = await parseMarkdownFAQs(filePath, category.id, category.name);
            allFaqs = allFaqs.concat(faqs);
          }
          return res.json(allFaqs);
        }

      case 'search':
        if (q) {
          let allFaqs = [];
          for (const category of categories) {
            const filePath = path.join(process.cwd(), category.file);
            const faqs = await parseMarkdownFAQs(filePath, category.id, category.name);
            allFaqs = allFaqs.concat(faqs);
          }

          const searchResults = allFaqs.filter(faq =>
            faq.question.toLowerCase().includes(q.toLowerCase()) ||
            faq.answer.toLowerCase().includes(q.toLowerCase())
          );
          return res.json(searchResults);
        } else {
          return res.json([]);
        }

      case 'stats':
        let totalFaqs = 0;
        for (const category of categories) {
          const filePath = path.join(process.cwd(), category.file);
          const faqs = await parseMarkdownFAQs(filePath, category.id, category.name);
          totalFaqs += faqs.length;
        }

        return res.json({
          total_faqs: totalFaqs,
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