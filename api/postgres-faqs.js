import { sql } from '@vercel/postgres';

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
        const categories = await sql`SELECT * FROM categories ORDER BY name`;
        return res.json(categories.rows);

      case 'faqs':
        if (category_id) {
          const faqs = await sql`
            SELECT f.*, c.name as category_name 
            FROM faqs f 
            JOIN categories c ON f.category_id = c.id 
            WHERE f.category_id = ${category_id}
            ORDER BY f.question
          `;
          return res.json(faqs.rows);
        } else {
          const allFaqs = await sql`
            SELECT f.*, c.name as category_name 
            FROM faqs f 
            JOIN categories c ON f.category_id = c.id 
            ORDER BY c.name, f.question
          `;
          return res.json(allFaqs.rows);
        }

      case 'search':
        if (q) {
          const searchResults = await sql`
            SELECT f.*, c.name as category_name 
            FROM faqs f 
            JOIN categories c ON f.category_id = c.id 
            WHERE f.question ILIKE ${'%' + q + '%'} OR f.answer ILIKE ${'%' + q + '%'}
            ORDER BY f.question
          `;
          return res.json(searchResults.rows);
        } else {
          return res.json([]);
        }

      case 'stats':
        const faqCount = await sql`SELECT COUNT(*) as count FROM faqs`;
        const categoryCount = await sql`SELECT COUNT(*) as count FROM categories`;

        return res.json({
          total_faqs: parseInt(faqCount.rows[0].count),
          total_categories: parseInt(categoryCount.rows[0].count),
          status: 'online'
        });

      case 'setup':
        // Setup database tables
        await setupDatabase();
        return res.json({ message: 'Database setup complete' });

      default:
        return res.status(400).json({ error: 'Invalid action' });
    }

  } catch (error) {
    console.error('Database error:', error);
    return res.status(500).json({ error: 'Database error: ' + error.message });
  }
}

async function setupDatabase() {
  // Create categories table
  await sql`
    CREATE TABLE IF NOT EXISTS categories (
      id SERIAL PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      description TEXT
    )
  `;

  // Create faqs table
  await sql`
    CREATE TABLE IF NOT EXISTS faqs (
      id SERIAL PRIMARY KEY,
      question TEXT NOT NULL,
      answer TEXT NOT NULL,
      category_id INTEGER REFERENCES categories(id),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
  `;

  // Insert sample data if tables are empty
  const categoryCount = await sql`SELECT COUNT(*) FROM categories`;
  if (parseInt(categoryCount.rows[0].count) === 0) {
    // Insert categories
    await sql`
      INSERT INTO categories (name, description) VALUES 
      ('Hull and Compartments', 'Learn about submarine construction, hull design, and compartment layouts.'),
      ('US WW2 Subs in General', 'General information about American submarines during World War II.'),
      ('Life Aboard WW2 US Subs', 'Daily life, living conditions, and crew experiences aboard submarines.'),
      ('Operating US Subs in WW2', 'Operational procedures, tactics, and submarine warfare techniques.'),
      ('Attacks and Battles, Small and Large', 'Combat operations, battles, and military engagements.'),
      ('Who Were the Crews Aboard WW2 US Subs', 'Information about submarine crews, their roles, and backgrounds.')
    `;

    // Insert sample FAQs
    await sql`
      INSERT INTO faqs (question, answer, category_id) VALUES 
      ('Is a submarine a boat or a ship?', 'Submarines are traditionally called "boats" in naval terminology, despite their size. This tradition dates back to early submarines which were small enough to be considered boats.', 2),
      ('How deep could WW2 submarines dive?', 'Most WW2 US submarines had a test depth of around 300 feet, with a crush depth estimated at about 500-600 feet. However, some submarines exceeded these limits in emergency situations.', 4),
      ('What was daily life like aboard a WW2 submarine?', 'Life aboard WW2 submarines was cramped and challenging. Crews worked in shifts, shared bunks (hot bunking), and dealt with limited fresh water, cramped quarters, and the constant smell of diesel fuel and battery acid.', 3),
      ('How were submarine crews selected?', 'Submarine crews were volunteers who underwent rigorous training and psychological evaluation. They needed to work well in confined spaces and handle the stress of underwater operations.', 6),
      ('What was the most famous submarine attack of WW2?', 'One of the most famous attacks was the sinking of the Japanese aircraft carrier Shinano by USS Archerfish in November 1944, making it the largest warship ever sunk by a submarine.', 5),
      ('How thick was a submarine hull?', 'WW2 submarine pressure hulls were typically 7/8 to 1 inch thick, made of high-tensile steel. The hull had to withstand enormous water pressure at diving depths.', 1)
    `;
  }
}