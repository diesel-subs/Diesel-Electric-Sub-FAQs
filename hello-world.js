const http = require('http');

const PORT = process.env.PORT || 3000;

const server = http.createServer((req, res) => {
  res.writeHead(200, { 'Content-Type': 'text/html' });
  res.end(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Hello Railway!</title>
      <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .success { color: #28a745; }
        .info { color: #666; margin: 20px 0; }
      </style>
    </head>
    <body>
      <div class="container">
        <h1 class="success">🎉 Hello Railway!</h1>
        <p class="info">Railway deployment is working perfectly!</p>
        <p><strong>Server Details:</strong></p>
        <ul style="list-style: none; padding: 0;">
          <li>Port: ${PORT}</li>
          <li>Node.js: ${process.version}</li>
          <li>Platform: ${process.platform}</li>
          <li>Timestamp: ${new Date().toISOString()}</li>
        </ul>
        <p class="info">
          <a href="/health">Health Check</a> | 
          <a href="/api/test">API Test</a>
        </p>
      </div>
    </body>
    </html>
  `);
});

// Simple routing for health check and API test
const originalCreateServer = server;
const serverWithRouting = http.createServer((req, res) => {
  const url = req.url;
  
  if (url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({
      status: 'healthy',
      timestamp: new Date().toISOString(),
      port: PORT,
      uptime: process.uptime()
    }));
  } else if (url === '/api/test') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({
      message: 'API is working!',
      method: req.method,
      url: req.url,
      timestamp: new Date().toISOString()
    }));
  } else {
    // Default home page
    originalCreateServer._events.request(req, res);
  }
});

serverWithRouting.listen(PORT, '0.0.0.0', () => {
  console.log(`✅ Hello World server running on http://0.0.0.0:${PORT}`);
  console.log(`🌍 Environment: ${process.env.NODE_ENV || 'development'}`);
  console.log(`⏰ Started at: ${new Date().toISOString()}`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('📴 Received SIGTERM, shutting down gracefully');
  serverWithRouting.close(() => {
    console.log('🔚 Server closed');
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  console.log('📴 Received SIGINT, shutting down gracefully');
  serverWithRouting.close(() => {
    console.log('🔚 Server closed');
    process.exit(0);
  });
});