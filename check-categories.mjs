import { query } from './lib/database.js';

console.log('Checking Railway database categories...');

try {
  // Check categories table
  const categories = await query('SELECT * FROM categories ORDER BY id');
  console.log('Categories found:', categories);

  // Check if categories table is empty, maybe we need to create some
  if (categories.length === 0) {
    console.log('\nNo categories found. Creating default categories...');
    
    await query(`INSERT INTO categories (name, description) VALUES 
      ('General', 'General submarine questions'),
      ('Operations', 'Submarine operations and procedures'),
      ('Daily Life', 'Life aboard submarines'),
      ('Combat', 'Combat operations and warfare'),
      ('History', 'Historical information and events')`);
      
    const newCategories = await query('SELECT * FROM categories ORDER BY id');
    console.log('Created categories:', newCategories);
  }

  // Check table structure
  const structure = await query('DESCRIBE categories');
  console.log('\nCategories table structure:', structure);

  // Check FAQs table structure  
  const faqStructure = await query('DESCRIBE faqs');
  console.log('\nFAQs table structure:', faqStructure);

} catch (error) {
  console.error('Error:', error);
} finally {
  process.exit(0);
}