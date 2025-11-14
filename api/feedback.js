const mysql = require('mysql2/promise');

module.exports = async (req, res) => {
    // Set CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    let connection;
    try {
        // Create database connection
        connection = await mysql.createConnection({
            host: process.env.MYSQLHOST,
            port: process.env.MYSQLPORT || 3306,
            user: process.env.MYSQLUSER,
            password: process.env.MYSQLPASSWORD,
            database: process.env.MYSQLDATABASE
        });

        if (req.method === 'POST') {
            // Submit feedback
            const { faq_id, faq_question, feedback_text, user_email } = req.body;

            if (!faq_id || !faq_question || !feedback_text) {
                return res.status(400).json({ 
                    success: false, 
                    message: 'FAQ ID, question, and feedback text are required' 
                });
            }

            // Create feedback table if it doesn't exist
            await connection.execute(`
                CREATE TABLE IF NOT EXISTS feedback (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    faq_id INT NOT NULL,
                    faq_question TEXT NOT NULL,
                    feedback_text TEXT NOT NULL,
                    user_email VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    status ENUM('new', 'reviewed', 'implemented') DEFAULT 'new',
                    INDEX(faq_id),
                    INDEX(created_at),
                    INDEX(status)
                )
            `);

            // Insert feedback
            const [result] = await connection.execute(
                'INSERT INTO feedback (faq_id, faq_question, feedback_text, user_email) VALUES (?, ?, ?, ?)',
                [faq_id, faq_question, feedback_text, user_email || null]
            );

            res.json({ 
                success: true, 
                message: 'Feedback submitted successfully!',
                feedback_id: result.insertId
            });

        } else if (req.method === 'GET') {
            // Get all feedback (for admin)
            const { status } = req.query;

            let query = 'SELECT * FROM feedback';
            let params = [];

            if (status) {
                query += ' WHERE status = ?';
                params.push(status);
            }

            query += ' ORDER BY created_at DESC';

            const [rows] = await connection.execute(query, params);

            res.json({ 
                success: true, 
                feedback: rows 
            });

        } else if (req.method === 'PUT') {
            // Update feedback status (for admin)
            const { id, status } = req.body;

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

            res.json({ 
                success: true, 
                message: 'Feedback status updated successfully' 
            });

        } else {
            res.status(405).json({ success: false, message: 'Method not allowed' });
        }

    } catch (error) {
        console.error('Feedback API error:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Internal server error' 
        });
    } finally {
        // Clean up database connection
        if (connection) {
            try {
                await connection.end();
            } catch (err) {
                console.error('Error closing database connection:', err);
            }
        }
    }
};