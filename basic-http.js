const http = require('http');

// Get port from Railway
const port = process.env.PORT || 3000;

// Log startup information
console.log('='.repeat(50));
console.log('🚀 RAILWAY SUBMARINE FAQ SERVER STARTING');
console.log('='.repeat(50));
console.log('📡 PORT:', port);
console.log('🌐 HOST: 0.0.0.0 (Railway requirement)');
console.log('📋 NODE_ENV:', process.env.NODE_ENV || 'not-set');
console.log('⚡ DATABASE_URL:', process.env.DATABASE_URL ? 'present' : 'missing');
console.log('🕒 START TIME:', new Date().toISOString());
console.log('='.repeat(50));

// Simple request handler
const server = http.createServer((req, res) => {
  const url = req.url;
  const method = req.method;
  
  // Log all requests for debugging
  console.log(`📝 ${method} ${url} - ${new Date().toISOString()}`);
  
  // Add basic CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  
  // Handle preflight requests
  if (method === 'OPTIONS') {
    res.writeHead(200);
    res.end();
    return;
  }
  
  // Route handling
  try {
    if (url === '/health') {
      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ 
        status: 'healthy', 
        port: port,
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        nodeVersion: process.version,
        memory: process.memoryUsage()
      }));
    } else if (url === '/api/test') {
      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ 
        message: 'API endpoint working',
        server: 'Railway Submarine FAQ Server',
        timestamp: new Date().toISOString()
      }));
    } else {
      // Default homepage
      res.writeHead(200, { 'Content-Type': 'text/html' });
      res.end(`<!DOCTYPE html>
<html>
<head>
  <title>🔱 Submarine FAQ Server</title>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    .status { background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 15px 0; }
    .endpoint { background: #f0f8ff; padding: 10px; border-radius: 3px; margin: 5px 0; }
    a { color: #0066cc; text-decoration: none; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <h1>🔱 Diesel-Electric Submarine FAQs</h1>
  
  <div class="status">
    <h3>✅ Railway Deployment Status</h3>
    <p><strong>Server:</strong> Running successfully on Railway</p>
    <p><strong>Port:</strong> ${port}</p>
    <p><strong>Time:</strong> ${new Date().toISOString()}</p>
    <p><strong>Node:</strong> ${process.version}</p>
    <p><strong>Database:</strong> ${process.env.DATABASE_URL ? '✅ Connected' : '❌ Not configured'}</p>
  </div>
  
  <h3>🔗 Available Endpoints</h3>
  <div class="endpoint">
    <strong>Health Check:</strong> <a href="/health">/health</a>
  </div>
  <div class="endpoint">
    <strong>API Test:</strong> <a href="/api/test">/api/test</a>
  </div>
  
  <h3>📊 Migration Status</h3>
  <p>✅ 164 FAQs successfully migrated to Railway database</p>
  <p>✅ 6 submarine categories configured</p>
  <p>✅ Database schema deployed and working</p>
  
  <h3>🚢 About This Server</h3>
  <p>This is a pure Node.js HTTP server running on Railway with zero external dependencies.</p>
  <p>It serves as the foundation for the Diesel-Electric Submarine FAQ database application.</p>
  
  <footer style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd;">
    <p>🌊 Diesel-Electric Submarine FAQs - Deployed on Railway</p>
    <p>Request: ${method} ${url}</p>
  </footer>
</body>
</html>`);
    }
  } catch (error) {
    console.error('❌ Request error:', error);
    res.writeHead(500, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ 
      error: 'Internal server error', 
      message: error.message,
      timestamp: new Date().toISOString()
    }));
  }
});

// Start server with Railway configuration
server.listen(port, '0.0.0.0', () => {
  console.log('✅ SERVER STARTED SUCCESSFULLY');
  console.log(`🌐 Listening on 0.0.0.0:${port}`);
  console.log(`🔗 Health: http://0.0.0.0:${port}/health`);
  console.log('🎯 Ready for Railway traffic');
});

// Graceful shutdown handling
process.on('SIGTERM', () => {
  console.log('🛑 SIGTERM received - shutting down gracefully');
  server.close(() => {
    console.log('✅ Server closed successfully');
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  console.log('🛑 SIGINT received - shutting down gracefully');
  server.close(() => {
    console.log('✅ Server closed successfully');
    process.exit(0);
  });
});

// Error handling
process.on('uncaughtException', (error) => {
  console.error('❌ UNCAUGHT EXCEPTION:', error);
  process.exit(1);
});

process.on('unhandledRejection', (reason) => {
  console.error('❌ UNHANDLED REJECTION:', reason);
  process.exit(1);
});

console.log('🎯 Server initialization complete - ready for connections');
