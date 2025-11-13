import { CorrespondenceDatabase, FAQDatabase } from '../lib/database.js';

// Simple token validation for admin functions
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
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (req.method === 'OPTIONS') {
        res.status(200).end();
        return;
    }

    try {
        if (req.method === 'POST') {
            // Submit new feedback/correspondence
            const { 
                name, 
                email, 
                feedback_type, 
                faq_id, 
                subject, 
                message, 
                rating 
            } = req.body;
            
            if (!message || !subject) {
                res.status(400).json({ 
                    success: false, 
                    message: 'Subject and message are required' 
                });
                return;
            }

            // Validate FAQ ID if provided
            if (faq_id) {
                const faq = await FAQDatabase.getFAQById(faq_id);
                if (!faq) {
                    res.status(400).json({ 
                        success: false, 
                        message: 'Invalid FAQ ID' 
                    });
                    return;
                }
            }

            const feedbackId = await CorrespondenceDatabase.createFeedback({
                name: name || null,
                email: email || null,
                feedback_type: feedback_type || 'general',
                faq_id: faq_id ? parseInt(faq_id) : null,
                subject,
                message,
                rating: rating ? parseInt(rating) : null
            });

            res.status(201).json({ 
                success: true, 
                message: 'Feedback submitted successfully',
                feedback_id: feedbackId
            });
        }
        else if (req.method === 'GET') {
            // Get feedback (admin only)
            if (!validateToken(req.headers.authorization)) {
                res.status(401).json({ success: false, message: 'Unauthorized' });
                return;
            }

            const { status, id } = req.query;

            if (id) {
                // Get specific feedback
                const feedback = await CorrespondenceDatabase.getFeedbackById(parseInt(id));
                if (!feedback) {
                    res.status(404).json({ success: false, message: 'Feedback not found' });
                    return;
                }
                res.status(200).json({ success: true, feedback });
            } else {
                // Get all feedback, optionally filtered by status
                const feedback = await CorrespondenceDatabase.getAllFeedback(status);
                res.status(200).json({ success: true, feedback });
            }
        }
        else if (req.method === 'PUT') {
            // Update feedback status (admin only)
            if (!validateToken(req.headers.authorization)) {
                res.status(401).json({ success: false, message: 'Unauthorized' });
                return;
            }

            const { id, status, admin_notes } = req.body;
            
            if (!id || !status) {
                res.status(400).json({ 
                    success: false, 
                    message: 'ID and status are required' 
                });
                return;
            }

            const validStatuses = ['pending', 'approved', 'rejected', 'implemented'];
            if (!validStatuses.includes(status)) {
                res.status(400).json({ 
                    success: false, 
                    message: 'Invalid status. Must be one of: ' + validStatuses.join(', ')
                });
                return;
            }

            await CorrespondenceDatabase.updateFeedbackStatus(
                parseInt(id), 
                status, 
                admin_notes || null
            );

            res.status(200).json({ 
                success: true, 
                message: 'Feedback status updated successfully' 
            });
        }
        else {
            res.status(405).json({ success: false, message: 'Method not allowed' });
        }
    } catch (error) {
        console.error('Correspondence API error:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Server error occurred',
            error: process.env.NODE_ENV === 'development' ? error.message : 'Internal server error'
        });
    }
}