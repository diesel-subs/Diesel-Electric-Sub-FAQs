module.exports = function handler(req, res) {
    // Set CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        res.status(200).end();
        return;
    }

    if (req.method === 'GET') {
        // Debug endpoint to check password configuration (remove in production)
        return res.json({
            hasEnvPassword: !!process.env.ADMIN_PASSWORD,
            defaultPassword: process.env.ADMIN_PASSWORD ? '[HIDDEN]' : '1945',
            envKeys: Object.keys(process.env).filter(k => k.includes('ADMIN') || k.includes('PASSWORD'))
        });
    }
    
    if (req.method === 'POST') {
        const { password } = req.body;
        
        // Simple password check - in production, use proper authentication
        // Force password to be 1945 regardless of environment variable
        const ADMIN_PASSWORD = '1945';
        
        // Debug logging
        console.log('Auth attempt:', { 
            receivedPassword: password, 
            expectedPassword: ADMIN_PASSWORD,
            envVar: process.env.ADMIN_PASSWORD ? 'SET BUT IGNORED' : 'NOT SET'
        });
        
        if (password === ADMIN_PASSWORD) {
            // Generate simple session token
            const token = Buffer.from(`admin:${Date.now()}`).toString('base64');
            res.status(200).json({ 
                success: true, 
                token: token,
                message: 'Authentication successful' 
            });
        } else {
            res.status(401).json({ 
                success: false, 
                message: 'Invalid password' 
            });
        }
    } else {
        res.status(405).json({ message: 'Method not allowed' });
    }
};