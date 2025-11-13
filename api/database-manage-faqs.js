import { FAQDatabase, CategoryDatabase } from '../lib/database.js';

// Simple token validation
function validateToken(authHeader) {
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
        return false;
    }
    const token = authHeader.substring(7);
    try {
        const decoded = Buffer.from(token, 'base64').toString();
        return decoded.startsWith('admin:');
    } catch (error) {
        return false;
    }
}

export default async function handler(req, res) {
    // Set CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (req.method === 'OPTIONS') {
        res.status(200).end();
        return;
    }

    // Validate authentication for all non-GET requests
    if (req.method !== 'GET') {
        if (!validateToken(req.headers.authorization)) {
            res.status(401).json({ success: false, message: 'Unauthorized' });
            return;
        }
    }

    try {
        if (req.method === 'GET') {
            // Get all FAQs with category information
            const faqs = await FAQDatabase.getAllFAQs();
            res.status(200).json({ success: true, faqs });
        } 
        else if (req.method === 'POST') {
            // Create new FAQ
            const { category_id, question, answer, tags } = req.body;
            
            if (!category_id || !question || !answer) {
                res.status(400).json({ success: false, message: 'Missing required fields' });
                return;
            }

            // Verify category exists
            const category = await CategoryDatabase.getCategoryById(category_id);
            if (!category) {
                res.status(400).json({ success: false, message: 'Invalid category' });
                return;
            }

            const faqId = await FAQDatabase.createFAQ({
                category_id: parseInt(category_id),
                question,
                answer,
                tags: tags || null
            });

            // Get the created FAQ
            const newFaq = await FAQDatabase.getFAQById(faqId);
            res.status(201).json({ success: true, faq: newFaq });
        }
        else if (req.method === 'PUT') {
            // Update existing FAQ
            const { id, category_id, question, answer, tags } = req.body;
            
            if (!id || !category_id || !question || !answer) {
                res.status(400).json({ success: false, message: 'Missing required fields' });
                return;
            }

            // Verify FAQ exists
            const existingFaq = await FAQDatabase.getFAQById(id);
            if (!existingFaq) {
                res.status(404).json({ success: false, message: 'FAQ not found' });
                return;
            }

            // Verify category exists
            const category = await CategoryDatabase.getCategoryById(category_id);
            if (!category) {
                res.status(400).json({ success: false, message: 'Invalid category' });
                return;
            }

            await FAQDatabase.updateFAQ(id, {
                category_id: parseInt(category_id),
                question,
                answer,
                tags: tags || null
            });

            // Get the updated FAQ
            const updatedFaq = await FAQDatabase.getFAQById(id);
            res.status(200).json({ success: true, faq: updatedFaq });
        }
        else if (req.method === 'DELETE') {
            // Delete FAQ
            const { id } = req.query;
            
            if (!id) {
                res.status(400).json({ success: false, message: 'Missing FAQ ID' });
                return;
            }

            // Verify FAQ exists
            const existingFaq = await FAQDatabase.getFAQById(id);
            if (!existingFaq) {
                res.status(404).json({ success: false, message: 'FAQ not found' });
                return;
            }

            await FAQDatabase.deleteFAQ(parseInt(id));
            res.status(200).json({ 
                success: true, 
                message: 'FAQ deleted successfully',
                faq: existingFaq 
            });
        }
        else {
            res.status(405).json({ success: false, message: 'Method not allowed' });
        }
    } catch (error) {
        console.error('Database operation error:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Database error occurred',
            error: process.env.NODE_ENV === 'development' ? error.message : 'Internal server error'
        });
    }
}