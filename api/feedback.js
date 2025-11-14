const { Pool } = require('pg');

module.exports = async (req, res) => {
    // Set CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    let client = null;
    
    try {
        // Check if database is configured
        console.log('Environment check - DATABASE_URL exists:', !!process.env.DATABASE_URL);
        console.log('All env vars:', Object.keys(process.env).filter(k => k.includes('DATABASE') || k.includes('POSTGRES') || k.includes('PG')));
        
        if (!process.env.DATABASE_URL) {
            return res.status(503).json({ 
                success: false, 
                message: 'Database not configured. DATABASE_URL environment variable is required.',
                env_info: Object.keys(process.env).filter(k => k.includes('DATABASE') || k.includes('POSTGRES') || k.includes('PG'))
            });
        }

        // Create database connection pool
        const pool = new Pool({
            connectionString: process.env.DATABASE_URL,
            ssl: process.env.NODE_ENV === 'production' ? { rejectUnauthorized: false } : false
        });
        
        client = await pool.connect();

        if (req.method === 'POST') {
            // Submit feedback
            const { faq_id, faq_question, feedback_text, user_email } = req.body || {};

            if (!faq_id || !faq_question || !feedback_text) {
                return res.status(400).json({ 
                    success: false, 
                    message: 'FAQ ID, question, and feedback text are required' 
                });
            }

            // Create feedback table if it doesn't exist
            const createTableQuery = `
                CREATE TABLE IF NOT EXISTS feedback (
                    id SERIAL PRIMARY KEY,
                    faq_id INTEGER NOT NULL,
                    faq_question TEXT NOT NULL,
                    feedback_text TEXT NOT NULL,
                    user_email VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new', 'reviewed', 'implemented'))
                )
            `;
            
            await client.query(createTableQuery);

            // Insert feedback
            const result = await client.query(
                'INSERT INTO feedback (faq_id, faq_question, feedback_text, user_email) VALUES ($1, $2, $3, $4) RETURNING id',
                [faq_id, faq_question, feedback_text, user_email || null]
            );

            return res.json({ 
                success: true, 
                message: 'Feedback submitted successfully!',
                feedback_id: result.rows[0].id
            });

        } else if (req.method === 'GET') {
            // Get all feedback (for admin)
            const { status } = req.query || {};

            let query = 'SELECT * FROM feedback';
            let params = [];

            if (status) {
                query += ' WHERE status = $1';
                params.push(status);
            }

            query += ' ORDER BY created_at DESC';

            const result = await client.query(query, params);

            return res.json({ 
                success: true, 
                feedback: result.rows 
            });

        } else if (req.method === 'PUT') {
            // Update feedback status (for admin)
            const { id, status } = req.body || {};

            if (!id || !status || !['new', 'reviewed', 'implemented'].includes(status)) {
                return res.status(400).json({ 
                    success: false, 
                    message: 'Valid feedback ID and status are required' 
                });
            }

            await client.query(
                'UPDATE feedback SET status = $1 WHERE id = $2',
                [status, id]
            );

            return res.json({ 
                success: true, 
                message: 'Feedback status updated successfully' 
            });

        } else {
            return res.status(405).json({ 
                success: false, 
                message: 'Method not allowed' 
            });
        }

    } catch (error) {
        console.error('Feedback API error:', error);
        return res.status(500).json({ 
            success: false, 
            message: 'Internal server error: ' + error.message
        });
    } finally {
        // Always release the client back to the pool
        if (client) {
            try {
                client.release();
            } catch (closeError) {
                console.error('Error releasing database client:', closeError);
            }
        }
    }
};