const fs = require('fs');
const path = require('path');

// Category mapping
const categoryMap = {
  '05-Hull-and-Compartments.md': { id: 1, name: 'Hull and Compartments' },
  '08-US-WW2-Subs-in-General.md': { id: 2, name: 'US WW2 Subs in General' },
  '15-Life-Aboard-US-WW2-Subs.md': { id: 3, name: 'Life Aboard WW2 US Subs' },
  '10-Operating-US-WW2-Subs.md': { id: 4, name: 'Operating US Subs in WW2' },
  '20-Attacks-and-Battles-Small-and-Large.md': { id: 5, name: 'Attacks and Battles, Small and Large' },
  '12-Crews-Aboard-US-WW2-Subs.md': { id: 6, name: 'Who Were the Crews Aboard WW2 US Subs' }
};

function parseMarkdownFAQs(content, category) {
  const faqs = [];
  const lines = content.split('\n');
  let currentQuestion = null;
  let currentAnswer = [];
  let questionId = 1;

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i].trim();

    // Check if this line is a question (starts and ends with **)
    if (line.startsWith('**') && line.endsWith('**') && line.length > 4) {
      // Save previous FAQ if exists
      if (currentQuestion && currentAnswer.length > 0) {
        faqs.push({
          id: questionId++,
          question: currentQuestion,
          answer: currentAnswer.join('\n').trim(),
          category_id: category.id,
          category_name: category.name
        });
      }

      // Start new question
      currentQuestion = line.replace(/^\*\*/, '').replace(/\*\*$/, '').trim();
      currentAnswer = [];
    } else if (currentQuestion && line) {
      // Add to current answer if we have a question
      currentAnswer.push(line);
    } else if (!line && currentQuestion && currentAnswer.length > 0) {
      // Empty line might end the answer, but continue collecting
      continue;
    }
  }

  // Don't forget the last FAQ
  if (currentQuestion && currentAnswer.length > 0) {
    faqs.push({
      id: questionId++,
      question: currentQuestion,
      answer: currentAnswer.join('\n').trim(),
      category_id: category.id,
      category_name: category.name
    });
  }

  return faqs;
}

function generateFAQData() {
  const allFAQs = [];
  let globalId = 1;

  // Process each markdown file
  Object.keys(categoryMap).forEach(filename => {
    const filePath = path.join(__dirname, filename);

    if (fs.existsSync(filePath)) {
      console.log(`Processing ${filename}...`);
      const content = fs.readFileSync(filePath, 'utf8');
      const category = categoryMap[filename];
      const faqs = parseMarkdownFAQs(content, category);

      // Assign global IDs
      faqs.forEach(faq => {
        faq.id = globalId++;
        allFAQs.push(faq);
      });

      console.log(`Found ${faqs.length} FAQs in ${category.name}`);
    } else {
      console.log(`File not found: ${filename}`);
    }
  });

  return {
    categories: Object.values(categoryMap),
    faqs: allFAQs
  };
}

// Generate the data
const data = generateFAQData();
console.log(`\nTotal: ${data.faqs.length} FAQs across ${data.categories.length} categories`);

// Write to JavaScript file that can be imported
const jsContent = `// Auto-generated submarine FAQ data from markdown files
// Generated on ${new Date().toISOString()}

export const submarineData = ${JSON.stringify(data, null, 2)};
`;

fs.writeFileSync('submarine-faq-data.js', jsContent);
console.log('\nGenerated submarine-faq-data.js with all FAQ data!');

// Show sample of data
console.log('\nSample FAQs:');
data.faqs.slice(0, 3).forEach(faq => {
  console.log(`- [${faq.category_name}] ${faq.question.substring(0, 60)}...`);
});