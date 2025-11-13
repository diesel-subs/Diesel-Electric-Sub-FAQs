const fs = require('fs');
const path = require('path');

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

// Get FAQ data from the existing corrected-faqs.js file
function getFAQData() {
    try {
        const faqFilePath = path.join(process.cwd(), 'api', 'corrected-faqs.js');
        let content = fs.readFileSync(faqFilePath, 'utf8');
        
        // Extract the FAQ data from the JavaScript file
        const dataMatch = content.match(/const faqs = (\[[\s\S]*?\]);/);
        if (dataMatch) {
            return JSON.parse(dataMatch[1]);
        }
        return [];
    } catch (error) {
        console.error('Error reading FAQ data:', error);
        return [];
    }
}

// Save FAQ data back to the corrected-faqs.js file
function saveFAQData(faqs) {
    try {
        const faqFilePath = path.join(process.cwd(), 'api', 'corrected-faqs.js');
        let content = fs.readFileSync(faqFilePath, 'utf8');
        
        // Find the start and end of the faqs array
        const faqsStart = content.indexOf('const faqs = [');
        const faqsEnd = content.indexOf('];', faqsStart) + 2;
        
        if (faqsStart !== -1 && faqsEnd !== -1) {
            const beforeFaqs = content.substring(0, faqsStart);
            const afterFaqs = content.substring(faqsEnd);
            
            const faqsJson = JSON.stringify(faqs, null, 2);
            const newContent = beforeFaqs + 'const faqs = ' + faqsJson + ';' + afterFaqs;
            
            fs.writeFileSync(faqFilePath, newContent, 'utf8');
            return true;
        }
        return false;
    } catch (error) {
        console.error('Error saving FAQ data:', error);
        return false;
    }
}

module.exports = function handler(req, res) {
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

    const faqs = getFAQData();

    if (req.method === 'GET') {
        res.status(200).json({ success: true, faqs });
    } 
    else if (req.method === 'POST') {
        // Create new FAQ
        const { category_id, question, answer } = req.body;
        
        if (!category_id || !question || !answer) {
            res.status(400).json({ success: false, message: 'Missing required fields' });
            return;
        }

        // Get category name
        const categories = {
            1: "Hull and Compartments",
            2: "US WW2 Subs in General",
            3: "Operating US Subs in WW2",
            4: "Who Were the Crews Aboard WW2 US Subs",
            5: "Life Aboard WW2 US Subs",
            6: "Attacks and Battles, Small and Large"
        };

        // Generate new ID
        const maxId = Math.max(...faqs.map(faq => faq.id || 0), 0);
        const newFaq = {
            id: maxId + 1,
            question,
            answer,
            category_id: parseInt(category_id),
            category_name: categories[category_id]
        };

        faqs.push(newFaq);
        
        if (saveFAQData(faqs)) {
            res.status(201).json({ success: true, faq: newFaq });
        } else {
            res.status(500).json({ success: false, message: 'Failed to save FAQ' });
        }
    }
    else if (req.method === 'PUT') {
        // Update existing FAQ
        const { id, category_id, question, answer } = req.body;
        
        if (!id || !category_id || !question || !answer) {
            res.status(400).json({ success: false, message: 'Missing required fields' });
            return;
        }

        // Get category name
        const categories = {
            1: "Hull and Compartments",
            2: "US WW2 Subs in General",
            3: "Operating US Subs in WW2",
            4: "Who Were the Crews Aboard WW2 US Subs",
            5: "Life Aboard WW2 US Subs",
            6: "Attacks and Battles, Small and Large"
        };

        const faqIndex = faqs.findIndex(faq => faq.id === id);
        if (faqIndex === -1) {
            res.status(404).json({ success: false, message: 'FAQ not found' });
            return;
        }

        faqs[faqIndex] = { 
            id, 
            question, 
            answer, 
            category_id: parseInt(category_id), 
            category_name: categories[category_id] 
        };
        
        if (saveFAQData(faqs)) {
            res.status(200).json({ success: true, faq: faqs[faqIndex] });
        } else {
            res.status(500).json({ success: false, message: 'Failed to update FAQ' });
        }
    }
    else if (req.method === 'DELETE') {
        // Delete FAQ
        const { id } = req.query;
        
        if (!id) {
            res.status(400).json({ success: false, message: 'Missing FAQ ID' });
            return;
        }

        const faqIndex = faqs.findIndex(faq => faq.id === parseInt(id));
        if (faqIndex === -1) {
            res.status(404).json({ success: false, message: 'FAQ not found' });
            return;
        }

        const deletedFaq = faqs.splice(faqIndex, 1)[0];
        
        if (saveFAQData(faqs)) {
            res.status(200).json({ success: true, message: 'FAQ deleted', faq: deletedFaq });
        } else {
            res.status(500).json({ success: false, message: 'Failed to delete FAQ' });
        }
    }
    else {
        res.status(405).json({ success: false, message: 'Method not allowed' });
    }
}