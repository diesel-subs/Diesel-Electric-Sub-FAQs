const mysql = require('mysql2/promise');

// Database configuration for Railway
const dbConfig = {
  host: 'viaduct.proxy.rlwy.net',
  port: 26748,
  database: 'submarine_faqs',
  user: 'submarine_user',
  password: 'submarine2024!',
  charset: 'utf8mb4'
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
    const connection = await mysql.createConnection(dbConfig);
    
    switch (action) {
      case 'categories':
        const [categories] = await connection.execute('SELECT * FROM categories ORDER BY name');
        await connection.end();
        return res.json(categories);
        
      case 'faqs':
        if (category_id) {
          const [faqs] = await connection.execute(`
            SELECT f.*, c.name as category_name 
            FROM faqs f 
            JOIN categories c ON f.category_id = c.id 
            WHERE f.category_id = ? 
            ORDER BY f.question
          `, [category_id]);
          await connection.end();
          return res.json(faqs);
        } else {
          const [allFaqs] = await connection.execute(`
            SELECT f.*, c.name as category_name 
            FROM faqs f 
            JOIN categories c ON f.category_id = c.id 
            ORDER BY c.name, f.question
          `);
          await connection.end();
          return res.json(allFaqs);
        }
        
      case 'search':
        if (q) {
          const [searchResults] = await connection.execute(`
            SELECT f.*, c.name as category_name 
            FROM faqs f 
            JOIN categories c ON f.category_id = c.id 
            WHERE f.question LIKE ? OR f.answer LIKE ?
            ORDER BY f.question
          `, [`%${q}%`, `%${q}%`]);
          await connection.end();
          return res.json(searchResults);
        } else {
          await connection.end();
          return res.json([]);
        }
        
      case 'stats':
        const [faqCountResult] = await connection.execute('SELECT COUNT(*) as count FROM faqs');
        const [categoryCountResult] = await connection.execute('SELECT COUNT(*) as count FROM categories');
        await connection.end();
        
        return res.json({
          total_faqs: faqCountResult[0].count,
          total_categories: categoryCountResult[0].count,
          status: 'online'
        });
        
      default:
        await connection.end();
        return res.status(400).json({ error: 'Invalid action' });
    }
    
  } catch (error) {
    console.error('Database error:', error);
    return res.status(500).json({ error: 'Database connection failed: ' + error.message });
  }
}