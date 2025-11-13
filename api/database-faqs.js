import { FAQDatabase, CategoryDatabase } from '../lib/database.js';

export default async function handler(req, res) {
    // Set CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        res.status(200).end();
        return;
    }

    if (req.method !== 'GET') {
        res.status(405).json({ message: 'Method not allowed' });
        return;
    }

    try {
        const { action, category_id, q } = req.query;

        // Get categories for the response
        const categories = await CategoryDatabase.getAllCategories();

        if (action === 'search' && q) {
            // Search FAQs
            const searchResults = await FAQDatabase.searchFAQs(q);
            const formattedResults = searchResults.map(faq => ({
                id: faq.id,
                question: faq.question,
                answer: faq.answer,
                category_id: faq.category_id,
                category_name: faq.category_name
            }));

            res.status(200).json(formattedResults);
        } else if (category_id) {
            // Get FAQs for specific category
            const categoryFAQs = await FAQDatabase.getFAQsByCategory(parseInt(category_id));
            const formattedFAQs = categoryFAQs.map(faq => ({
                id: faq.id,
                question: faq.question,
                answer: faq.answer,
                category_id: faq.category_id,
                category_name: faq.category_name
            }));

            res.status(200).json(formattedFAQs);
        } else {
            // Return all FAQs organized by category (legacy format for compatibility)
            const allFAQs = await FAQDatabase.getAllFAQs();
            
            // Format for existing frontend compatibility
            const formattedFAQs = allFAQs.map(faq => ({
                id: faq.id,
                question: faq.question,
                answer: faq.answer,
                category_id: faq.category_id,
                category_name: faq.category_name
            }));

            res.status(200).json(formattedFAQs);
        }
    } catch (error) {
        console.error('Database error:', error);
        
        // Fallback to file-based system if database is not available
        try {
            const { default: fallbackHandler } = await import('./corrected-faqs-fallback.js');
            return fallbackHandler(req, res);
        } catch (fallbackError) {
            res.status(500).json({ 
                error: 'Service temporarily unavailable',
                message: 'Both database and fallback systems are unavailable'
            });
        }
    }
}