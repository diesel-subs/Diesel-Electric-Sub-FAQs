import { FAQDatabase, CategoryDatabase, query } from '../lib/database.js';
import fs from 'fs';
import path from 'path';

// Import existing FAQ data from the file-based system
async function importExistingFAQs() {
    try {
        console.log('Starting FAQ migration...');

        // Read existing FAQ data from the corrected-faqs-fallback.js file
        const faqFilePath = path.join(process.cwd(), 'api', 'corrected-faqs-fallback.js');
        let content = fs.readFileSync(faqFilePath, 'utf8');
        
        // Extract the FAQ data from the JavaScript file
        const dataMatch = content.match(/const faqs = (\[[\s\S]*?\]);/);
        if (!dataMatch) {
            throw new Error('Could not find FAQ data in fallback file');
        }

        const faqs = JSON.parse(dataMatch[1]);
        console.log(`Found ${faqs.length} FAQs to migrate`);

        // Clear existing FAQs (be careful!)
        await query('DELETE FROM faqs WHERE id > 0');
        console.log('Cleared existing FAQ data');

        // Reset auto increment
        await query('ALTER TABLE faqs AUTO_INCREMENT = 1');

        let imported = 0;
        let skipped = 0;

        for (const faq of faqs) {
            try {
                // Validate required fields
                if (!faq.question || !faq.answer || !faq.category_id) {
                    console.warn(`Skipping FAQ ${faq.id}: missing required fields`);
                    skipped++;
                    continue;
                }

                // Verify category exists
                const category = await CategoryDatabase.getCategoryById(faq.category_id);
                if (!category) {
                    console.warn(`Skipping FAQ ${faq.id}: invalid category ${faq.category_id}`);
                    skipped++;
                    continue;
                }

                // Create slug from question
                const slug = faq.question
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .substring(0, 200);

                // Insert FAQ
                const insertSql = `
                    INSERT INTO faqs (
                        category_id, 
                        title, 
                        slug, 
                        question, 
                        answer, 
                        status,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, 'published', NOW())
                `;

                await query(insertSql, [
                    faq.category_id,
                    faq.question.substring(0, 500), // Ensure title fits
                    slug + '-' + Date.now(), // Make slug unique
                    faq.question,
                    faq.answer
                ]);

                imported++;
                
                if (imported % 10 === 0) {
                    console.log(`Imported ${imported} FAQs...`);
                }

            } catch (error) {
                console.error(`Error importing FAQ ${faq.id}:`, error);
                skipped++;
            }
        }

        console.log(`Migration completed:`);
        console.log(`- Imported: ${imported} FAQs`);
        console.log(`- Skipped: ${skipped} FAQs`);

        return { imported, skipped };

    } catch (error) {
        console.error('Migration failed:', error);
        throw error;
    }
}

// API endpoint for running migration
export default async function handler(req, res) {
    // Set CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (req.method === 'OPTIONS') {
        res.status(200).end();
        return;
    }

    if (req.method !== 'POST') {
        res.status(405).json({ message: 'Method not allowed' });
        return;
    }

    // Check for admin authorization
    const authHeader = req.headers.authorization;
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
        res.status(401).json({ success: false, message: 'Unauthorized' });
        return;
    }

    try {
        const token = authHeader.substring(7);
        const decoded = Buffer.from(token, 'base64').toString();
        if (!decoded.startsWith('admin:')) {
            res.status(401).json({ success: false, message: 'Unauthorized' });
            return;
        }
    } catch (error) {
        res.status(401).json({ success: false, message: 'Invalid token' });
        return;
    }

    try {
        const result = await importExistingFAQs();
        res.status(200).json({
            success: true,
            message: 'Migration completed successfully',
            ...result
        });
    } catch (error) {
        console.error('Migration API error:', error);
        res.status(500).json({
            success: false,
            message: 'Migration failed',
            error: error.message
        });
    }
}

// Allow direct execution for testing
if (import.meta.url === `file://${process.argv[1]}`) {
    importExistingFAQs().catch(console.error);
}