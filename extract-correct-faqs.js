const fs = require('fs');
const path = require('path');

// Categories mapping
const categories = [
  { id: 1, name: "Hull and Compartments", file: "05-Hull-and-Compartments.md" },
  { id: 2, name: "US WW2 Subs in General", file: "08-US-WW2-Subs-in-General.md" },
  { id: 3, name: "Operating US Subs in WW2", file: "10-Operating-US-WW2-Subs.md" },
  { id: 4, name: "Who Were the Crews Aboard WW2 US Subs", file: "12-Crews-Aboard-US-WW2-Subs.md" },
  { id: 5, name: "Life Aboard WW2 US Subs", file: "15-Life-Aboard-US-WW2-Subs.md" },
  { id: 6, name: "Attacks and Battles, Small and Large", file: "20-Attacks-and-Battles-Small-and-Large.md" }
];

function extractFAQsFromMarkdown(content, categoryId, categoryName) {
  const faqs = [];
  let currentFAQId = 1;

  // Split content into lines for processing
  const lines = content.split('\n');
  
  for (let i = 0; i < lines.length; i++) {
    const line = lines[i].trim();
    
    // Look for bold text that ends with a question mark
    // Pattern: **text ending with ?**
    const boldQuestionMatch = line.match(/\*\*([^*]+\?)\*\*/g);
    
    if (boldQuestionMatch) {
      for (const match of boldQuestionMatch) {
        // Extract the question text (remove the ** markers)
        const questionText = match.replace(/^\*\*|\*\*$/g, '');
        
        // Make sure it actually ends with a question mark
        if (questionText.endsWith('?')) {
          console.log(`Found question in ${categoryName}: "${questionText}"`);
          
          // Now find the answer - everything until the next bold question or section
          let answer = '';
          let j = i + 1;
          
          while (j < lines.length) {
            const nextLine = lines[j].trim();
            
            // Stop if we find another bold question
            if (nextLine.match(/\*\*([^*]+\?)\*\*/)) {
              break;
            }
            
            // Stop if we find a major heading or section break
            if (nextLine.startsWith('#') || nextLine.startsWith('***') || nextLine.startsWith('---')) {
              break;
            }
            
            // Add line to answer (preserve formatting)
            if (nextLine || answer.trim()) { // Don't start with empty lines, but preserve them within content
              answer += lines[j] + '\n';
            }
            
            j++;
          }
          
          // Clean up the answer
          answer = answer.trim();
          
          if (answer) {
            faqs.push({
              id: currentFAQId++,
              question: questionText,
              answer: answer,
              category_id: categoryId,
              category_name: categoryName
            });
          }
        }
      }
    }
  }
  
  return faqs;
}

// Process all markdown files
let allFAQs = [];
let totalQuestions = 0;

console.log('Extracting FAQs based on correct criteria: **bold text ending with ?**\n');

for (const category of categories) {
  const filePath = path.join(__dirname, category.file);
  
  if (fs.existsSync(filePath)) {
    console.log(`\nProcessing ${category.name} (${category.file})...`);
    
    const content = fs.readFileSync(filePath, 'utf-8');
    const categoryFAQs = extractFAQsFromMarkdown(content, category.id, category.name);
    
    console.log(`Found ${categoryFAQs.length} questions in ${category.name}`);
    
    allFAQs = allFAQs.concat(categoryFAQs);
    totalQuestions += categoryFAQs.length;
    
    // Show first few questions as samples
    if (categoryFAQs.length > 0) {
      console.log('Sample questions:');
      categoryFAQs.slice(0, 3).forEach((faq, index) => {
        console.log(`  ${index + 1}. "${faq.question}"`);
      });
    }
  } else {
    console.log(`Warning: File not found: ${category.file}`);
  }
}

console.log(`\n\nSUMMARY:`);
console.log(`Total questions found: ${totalQuestions}`);
console.log(`Total categories: ${categories.length}`);

// Save the results
fs.writeFileSync('corrected-faqs.json', JSON.stringify(allFAQs, null, 2));
console.log('\nResults saved to corrected-faqs.json');

// Show breakdown by category
console.log('\nBreakdown by category:');
categories.forEach(category => {
  const count = allFAQs.filter(faq => faq.category_id === category.id).length;
  console.log(`  ${category.name}: ${count} questions`);
});