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
        // For now, just simulate database operations until Railway database is properly configured
        console.log('Feedback API called:', req.method, req.body);
        
        // Temporary in-memory storage simulation
        const feedback_store = {
            counter: 1,
            items: []
        };

        if (req.method === 'POST') {
            // Submit feedback
            const { faq_id, faq_question, feedback_text, user_email } = req.body || {};

            if (!faq_id || !faq_question || !feedback_text) {
                return res.status(400).json({ 
                    success: false, 
                    message: 'FAQ ID, question, and feedback text are required' 
                });
            }

            // Simulate storing feedback (temporary)
            const feedback_item = {
                id: feedback_store.counter++,
                faq_id: parseInt(faq_id),
                faq_question,
                feedback_text,
                user_email: user_email || null,
                created_at: new Date().toISOString(),
                status: 'new'
            };
            
            feedback_store.items.push(feedback_item);
            
            console.log('Feedback stored:', feedback_item);

            return res.json({ 
                success: true, 
                message: 'Feedback submitted successfully! (Currently using temporary storage - database integration pending)',
                feedback_id: feedback_item.id,
                note: 'Database will be configured soon for persistent storage'
            });

        } else if (req.method === 'GET') {
            // Get all feedback (for admin) - temporary simulation
            const { status } = req.query || {};

            let filtered_feedback = feedback_store.items;
            if (status) {
                filtered_feedback = feedback_store.items.filter(item => item.status === status);
            }

            // Sort by created_at descending
            filtered_feedback.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            return res.json({ 
                success: true, 
                feedback: filtered_feedback,
                note: 'Using temporary storage - database integration pending'
            });

        } else if (req.method === 'PUT') {
            // Update feedback status (for admin) - temporary simulation
            const { id, status } = req.body || {};

            if (!id || !status || !['new', 'reviewed', 'implemented'].includes(status)) {
                return res.status(400).json({ 
                    success: false, 
                    message: 'Valid feedback ID and status are required' 
                });
            }

            const feedback_item = feedback_store.items.find(item => item.id === parseInt(id));
            if (feedback_item) {
                feedback_item.status = status;
                console.log('Feedback status updated:', feedback_item);
            }

            return res.json({ 
                success: true, 
                message: 'Feedback status updated successfully (temporary storage)',
                note: 'Database integration pending'
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
        // No database cleanup needed for temporary version
        console.log('Feedback API request completed');
    }
};