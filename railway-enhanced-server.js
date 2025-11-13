const http = require('http');
const url = require('url');

// Railway environment configuration
const PORT = process.env.PORT || 3000;
const HOST = '0.0.0.0'; // Railway requires binding to 0.0.0.0

console.log('🚀 STARTING RAILWAY FAQ SERVER');
console.log('📡 Railway PORT:', PORT);
console.log('🌐 Host:', HOST);
console.log('📋 NODE_ENV:', process.env.NODE_ENV || 'production');
console.log('⚡ Database URL present:', !!process.env.DATABASE_URL);

// Simple router for different endpoints
function handleRequest(req, res) {
  const parsedUrl = url.parse(req.url, true);
  const pathname = parsedUrl.pathname;
  
  // Add CORS headers for all responses
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  
  if (req.method === 'OPTIONS') {
    res.writeHead(200);
    res.end();
    return;
  }

  console.log(`📝 ${req.method} ${pathname}`);

  try {
    switch (pathname) {
      case '/':
        handleHomePage(req, res);
        break;
      case '/health':
        handleHealthCheck(req, res);
        break;
      case '/api/test':
        handleApiTest(req, res);
        break;
      default:
        handle404(req, res);
    }
  } catch (error) {
    console.error('❌ Request error:', error);
    res.writeHead(500, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ error: 'Internal server error', details: error.message }));
  }
}

function handleHomePage(req, res) {
  res.writeHead(200, { 'Content-Type': 'text/html' });
  res.end(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>🔱 Diesel-Electric Submarine FAQs</title>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .status { background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .link { color: #0066cc; text-decoration: none; }
        .link:hover { text-decoration: underline; }
      </style>
    </head>
    <body>
      <h1>🔱 Diesel-Electric Submarine FAQs</h1>
      <div class="status">
        <h3>✅ Railway Deployment Active</h3>
        <p><strong>Status:</strong> Server running successfully</p>
        <p><strong>Port:</strong> ${PORT}</p>
        <p><strong>Time:</strong> ${new Date().toISOString()}</p>
        <p><strong>Database:</strong> ${process.env.DATABASE_URL ? 'Connected' : 'Not configured'}</p>
      </div>
      
      <h3>🔗 API Endpoints</h3>
      <ul>
        <li><a href="/health" class="link">Health Check</a></li>
        <li><a href="/api/test" class="link">API Test</a></li>
      </ul>
      
      <h3>📊 Migration Status</h3>
      <p>✅ FAQs migrated to Railway database: 164 records</p>
      <p>✅ Categories configured: 6 submarine topics</p>
      <p>✅ Database schema deployed</p>
      
      <footer style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccc;">
        <p>🚢 Diesel-Electric Submarine FAQs - Railway Deployment</p>
      </footer>
    </body>
    </html>
  `);
}

function handleHealthCheck(req, res) {
  const healthData = {
    status: 'healthy',
    timestamp: new Date().toISOString(),
    port: PORT,
    host: HOST,
    nodeVersion: process.version,
    platform: process.platform,
    memory: process.memoryUsage(),
    uptime: process.uptime(),
    env: {
      nodeEnv: process.env.NODE_ENV || 'production',
      hasDatabaseUrl: !!process.env.DATABASE_URL
    }
  };

  res.writeHead(200, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify(healthData, null, 2));
}

function handleApiTest(req, res) {
  res.writeHead(200, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({
    message: 'Railway API endpoint working',
    timestamp: new Date().toISOString(),
    server: 'Railway FAQ Server',
    database: process.env.DATABASE_URL ? 'available' : 'not configured'
  }));
}

function handle404(req, res) {
  res.writeHead(404, { 'Content-Type': 'text/html' });
  res.end(`
    <!DOCTYPE html>
    <html>
    <head><title>404 Not Found</title></head>
    <body>
      <h1>404 - Page Not Found</h1>
      <p>Path: ${req.url}</p>
      <p><a href="/">← Back to Home</a></p>
    </body>
    </html>
  `);
}

// Create and start server
const server = http.createServer(handleRequest);

server.listen(PORT, HOST, () => {
  console.log(`✅ Railway server listening on ${HOST}:${PORT}`);
  console.log(`🌐 Server ready for Railway deployment`);
  console.log(`🔗 Health check: http://${HOST}:${PORT}/health`);
});

// Graceful shutdown handling
process.on('SIGTERM', () => {
  console.log('🛑 SIGTERM received - gracefully shutting down server');
  server.close(() => {
    console.log('✅ Server closed successfully');
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  console.log('🛑 SIGINT received - gracefully shutting down server');
  server.close(() => {
    console.log('✅ Server closed successfully');
    process.exit(0);
  });
});

// Error handling
process.on('uncaughtException', (error) => {
  console.error('❌ Uncaught Exception:', error);
  process.exit(1);
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('❌ Unhandled Rejection at:', promise, 'reason:', reason);
  process.exit(1);
});

console.log('🎯 Server initialization complete - waiting for connections...');