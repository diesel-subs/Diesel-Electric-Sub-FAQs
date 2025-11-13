// Database connection utility for Vercel serverless functions
import { createConnection } from 'mysql2/promise';

// Database configuration optimized for PlanetScale
const dbConfig = {
    charset: 'utf8mb4',
    timezone: '+00:00',
    acquireTimeout: 60000,
    timeout: 60000,
    reconnect: true,
    // PlanetScale optimizations
    multipleStatements: false,
    connectionLimit: 1, // Serverless functions use single connections
    queueLimit: 0,
    // SSL configuration for PlanetScale
    ssl: {
        rejectUnauthorized: true
    }
};

// Configure for PlanetScale using DATABASE_URL
if (process.env.DATABASE_URL) {
    // PlanetScale connection string format:
    // mysql://username:password@host/database?ssl={"rejectUnauthorized":true}
    const url = new URL(process.env.DATABASE_URL);
    
    dbConfig.host = url.hostname;
    dbConfig.port = parseInt(url.port) || 3306;
    dbConfig.user = url.username;
    dbConfig.password = url.password;
    dbConfig.database = url.pathname.substring(1);
    
    // Parse SSL parameters from query string
    const sslParam = url.searchParams.get('ssl');
    if (sslParam) {
        try {
            dbConfig.ssl = JSON.parse(sslParam);
        } catch (e) {
            // Fallback to secure SSL for PlanetScale
            dbConfig.ssl = { rejectUnauthorized: true };
        }
    }
} else {
    // Fallback for local development
    dbConfig.host = process.env.DB_HOST || 'localhost';
    dbConfig.user = process.env.DB_USER || 'root';
    dbConfig.password = process.env.DB_PASSWORD || '';
    dbConfig.database = process.env.DB_NAME || 'submarine_faqs';
    dbConfig.ssl = false; // Disable SSL for local development
}

let connection = null;

export async function getConnection() {
    if (!connection) {
        try {
            connection = await createConnection(dbConfig);
            console.log('Database connection established');
        } catch (error) {
            console.error('Database connection failed:', error);
            throw error;
        }
    }
    return connection;
}

export async function query(sql, params = []) {
    try {
        const conn = await getConnection();
        const [results] = await conn.execute(sql, params);
        return results;
    } catch (error) {
        console.error('Database query error:', error);
        throw error;
    }
}

// FAQ-specific database operations
export class FAQDatabase {
    static async getAllFAQs() {
        const sql = `
            SELECT f.*, c.name as category_name, c.slug as category_slug
            FROM faqs f
            JOIN categories c ON f.category_id = c.id
            WHERE f.status = 'published'
            ORDER BY c.sort_order, f.id
        `;
        return await query(sql);
    }

    static async getFAQById(id) {
        const sql = `
            SELECT f.*, c.name as category_name, c.slug as category_slug
            FROM faqs f
            JOIN categories c ON f.category_id = c.id
            WHERE f.id = ? AND f.status = 'published'
        `;
        const results = await query(sql, [id]);
        return results[0] || null;
    }

    static async getFAQsByCategory(categoryId) {
        const sql = `
            SELECT f.*, c.name as category_name, c.slug as category_slug
            FROM faqs f
            JOIN categories c ON f.category_id = c.id
            WHERE f.category_id = ? AND f.status = 'published'
            ORDER BY f.id
        `;
        return await query(sql, [categoryId]);
    }

    static async searchFAQs(searchTerm) {
        const sql = `
            SELECT f.*, c.name as category_name, c.slug as category_slug,
                   MATCH(f.title, f.question, f.answer, f.tags) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
            FROM faqs f
            JOIN categories c ON f.category_id = c.id
            WHERE f.status = 'published' 
                AND (MATCH(f.title, f.question, f.answer, f.tags) AGAINST(? IN NATURAL LANGUAGE MODE)
                     OR f.question LIKE ? OR f.answer LIKE ?)
            ORDER BY relevance DESC, f.id
            LIMIT 50
        `;
        const searchPattern = `%${searchTerm}%`;
        return await query(sql, [searchTerm, searchTerm, searchPattern, searchPattern]);
    }

    static async createFAQ(data) {
        const sql = `
            INSERT INTO faqs (category_id, title, question, answer, tags, status)
            VALUES (?, ?, ?, ?, ?, 'published')
        `;
        const result = await query(sql, [
            data.category_id,
            data.title || data.question,
            data.question,
            data.answer,
            data.tags || null
        ]);
        return result.insertId;
    }

    static async updateFAQ(id, data) {
        const sql = `
            UPDATE faqs 
            SET category_id = ?, title = ?, question = ?, answer = ?, tags = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        `;
        await query(sql, [
            data.category_id,
            data.title || data.question,
            data.question,
            data.answer,
            data.tags || null,
            id
        ]);
    }

    static async deleteFAQ(id) {
        const sql = `DELETE FROM faqs WHERE id = ?`;
        await query(sql, [id]);
    }

    static async incrementViews(id) {
        const sql = `UPDATE faqs SET views = views + 1 WHERE id = ?`;
        await query(sql, [id]);
    }
}

// Correspondence/Feedback database operations
export class CorrespondenceDatabase {
    static async createFeedback(data) {
        const sql = `
            INSERT INTO feedback (name, email, feedback_type, faq_id, subject, message, rating)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        `;
        const result = await query(sql, [
            data.name || null,
            data.email || null,
            data.feedback_type || 'general',
            data.faq_id || null,
            data.subject,
            data.message,
            data.rating || null
        ]);
        return result.insertId;
    }

    static async getAllFeedback(status = null) {
        let sql = `
            SELECT f.*, faq.question as faq_question
            FROM feedback f
            LEFT JOIN faqs faq ON f.faq_id = faq.id
        `;
        const params = [];
        
        if (status) {
            sql += ` WHERE f.status = ?`;
            params.push(status);
        }
        
        sql += ` ORDER BY f.created_at DESC`;
        return await query(sql, params);
    }

    static async updateFeedbackStatus(id, status, adminNotes = null) {
        const sql = `
            UPDATE feedback 
            SET status = ?, admin_notes = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        `;
        await query(sql, [status, adminNotes, id]);
    }

    static async getFeedbackById(id) {
        const sql = `
            SELECT f.*, faq.question as faq_question, faq.answer as faq_answer
            FROM feedback f
            LEFT JOIN faqs faq ON f.faq_id = faq.id
            WHERE f.id = ?
        `;
        const results = await query(sql, [id]);
        return results[0] || null;
    }
}

// Categories database operations
export class CategoryDatabase {
    static async getAllCategories() {
        const sql = `
            SELECT c.*, COUNT(f.id) as faq_count
            FROM categories c
            LEFT JOIN faqs f ON c.id = f.category_id AND f.status = 'published'
            GROUP BY c.id
            ORDER BY c.sort_order, c.name
        `;
        return await query(sql);
    }

    static async getCategoryById(id) {
        const sql = `SELECT * FROM categories WHERE id = ?`;
        const results = await query(sql, [id]);
        return results[0] || null;
    }
}