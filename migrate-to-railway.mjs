import { FAQDatabase, CategoryDatabase, query } from './lib/database.js';
import fs from 'fs';
import path from 'path';

// Set Railway database connection
process.env.DATABASE_URL = 'mysql://root:gbtsVIhwplSBsgNCGygzkwHjUnjuPaMF@roundhouse.proxy.rlwy.net:56467/railway';
process.env.NODE_ENV = 'production';

console.log('Starting FAQ migration to Railway database...');

// Read existing FAQ data from the corrected-faqs-fallback.js file
const faqFilePath = path.join(process.cwd(), 'api', 'corrected-faqs-fallback.js');
let content = fs.readFileSync(faqFilePath, 'utf8');

// Extract the FAQ data from the JavaScript file
const dataMatch = content.match(/const faqs = (\[[\s\S]*?\]);/);
if (!dataMatch) {
    throw new Error('Could not find FAQ data in fallback file');
}

const faqs = JSON.parse(dataMatch[1]);
console.log(`Found ${faqs.length} FAQs to migrate`);

// First check what categories exist and create mapping
console.log('Checking existing categories...');
const categories = await query('SELECT id, name FROM categories');
console.log('Categories in database:', categories);

// Create category mapping from old IDs to new IDs
const categoryMapping = {
  1: 32,  // Hull and Compartments
  2: 33,  // US WW2 Subs in General  
  3: 36,  // Life Aboard WW2 US Subs
  4: 34,  // Operating US Subs in WW2
  5: 37,  // Attacks and Battles, Small and Large
  6: 35   // Who Were the Crews Aboard WW2 US Subs
};
console.log('Category mapping:', categoryMapping);

// Clear existing FAQs (be careful!)
await query('DELETE FROM faqs WHERE id > 0');
console.log('Cleared existing FAQ data');

// Reset auto increment
await query('ALTER TABLE faqs AUTO_INCREMENT = 1');

let imported = 0;
let skipped = 0;

for (const faq of faqs) {
    try {
        // Clean up undefined values and ensure all fields exist
        const question = faq.question || 'Untitled Question';
        const answer = faq.answer || 'No answer provided';
        const oldCategoryId = faq.category || 1; // Default to category 1 if undefined
        const category = categoryMapping[oldCategoryId] || 32; // Map to Railway database category ID
        
        // Skip if no meaningful content
        if (!faq.question && !faq.answer) {
            skipped++;
            continue;
        }
        
        // Generate a title from the question (truncated to avoid long titles)
        const title = question.length > 60 ? question.substring(0, 60) + '...' : question;
        
        // Generate a slug from the title (URL-friendly)
        const slug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
            .replace(/\s+/g, '-')          // Replace spaces with hyphens
            .replace(/-+/g, '-')           // Replace multiple hyphens with single
            .trim('-');                     // Remove leading/trailing hyphens
        
        // Insert the FAQ
        const result = await query(
            'INSERT INTO faqs (title, slug, question, answer, category_id, status, views) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [title, slug, question, answer, category, 'published', 0]
        );
        
        imported++;
        if (imported % 10 === 0) {
            console.log(`Imported ${imported}/${faqs.length} FAQs...`);
        }
    } catch (error) {
        console.warn(`Skipped FAQ "${(faq.question || 'Unknown').substring(0, 50)}...": ${error.message}`);
        skipped++;
    }
}

console.log(`\nMigration completed! Imported: ${imported}, Skipped: ${skipped}`);
process.exit(0);
