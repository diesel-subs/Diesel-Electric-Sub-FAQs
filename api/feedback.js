const mysql = require('mysql2/promise');

module.exports = async (req, res) => {
    // Set CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    let connection = null;
    
    try {
        // Check if database is configured
        const hasDatabase = process.env.DATABASE_URL || 
                          (process.env.MYSQLHOST && process.env.MYSQLUSER && process.env.MYSQLPASSWORD && process.env.MYSQLDATABASE);
        
        if (!hasDatabase) {
            // No database configured - return helpful message
            console.log('No database configured. Available env vars:', Object.keys(process.env).filter(k => k.includes('MYSQL') || k.includes('DATABASE')));
            return res.status(503).json({ 
                success: false, 
                message: 'Database not configured. Please set up MySQL service in Railway.',
                available_env: Object.keys(process.env).filter(k => k.includes('MYSQL') || k.includes('DATABASE'))
            });
        }

        // Create database connection
        let connectionConfig;
        
        if (process.env.DATABASE_URL) {
            // Railway uses DATABASE_URL format
            connectionConfig = process.env.DATABASE_URL;
        } else {
            // Fallback to individual environment variables
            connectionConfig = {
                host: process.env.MYSQLHOST,
                port: process.env.MYSQLPORT || 3306,
                user: process.env.MYSQLUSER,
                password: process.env.MYSQLPASSWORD,
                database: process.env.MYSQLDATABASE
            };
        }
        
        connection = await mysql.createConnection(connectionConfig);

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
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    faq_id INT NOT NULL,
                    faq_question TEXT NOT NULL,
                    feedback_text TEXT NOT NULL,
                    user_email VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    status ENUM('new', 'reviewed', 'implemented') DEFAULT 'new'
                )
            `;
            
            await connection.execute(createTableQuery);

            // Insert feedback
            const [result] = await connection.execute(
                'INSERT INTO feedback (faq_id, faq_question, feedback_text, user_email) VALUES (?, ?, ?, ?)',
                [faq_id, faq_question, feedback_text, user_email || null]
            );

            return res.json({ 
                success: true, 
                message: 'Feedback submitted successfully!',
                feedback_id: result.insertId
            });

        } else if (req.method === 'GET') {
            // Get all feedback (for admin)
            const { status } = req.query || {};

            let query = 'SELECT * FROM feedback';
            let params = [];

            if (status) {
                query += ' WHERE status = ?';
                params.push(status);
            }

            query += ' ORDER BY created_at DESC';

            const [rows] = await connection.execute(query, params);

            return res.json({ 
                success: true, 
                feedback: rows 
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

            await connection.execute(
                'UPDATE feedback SET status = ? WHERE id = ?',
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
        // Always close the connection
        if (connection) {
            try {
                await connection.end();
            } catch (closeError) {
                console.error('Error closing database connection:', closeError);
            }
        }
    }
};