const http = require('http');
const fs = require('fs');

const PORT = process.env.PORT || 3000;

// Create a log file for Railway debugging
const logFile = '/tmp/railway-debug.log';
const log = (message) => {
  const timestamp = new Date().toISOString();
  const logEntry = `${timestamp}: ${message}\n`;
  console.log(message);
  try {
    fs.appendFileSync(logFile, logEntry);
  } catch (e) {
    console.error('Failed to write log:', e.message);
  }
};

log('🚀 RAILWAY SERVICE HEALTH DEBUG SERVER STARTING');
log(`📡 PORT: ${PORT}`);
log(`🌐 NODE_ENV: ${process.env.NODE_ENV || 'not-set'}`);
log(`🔗 DATABASE_URL: ${process.env.DATABASE_URL ? 'present' : 'missing'}`);

// Track all requests and responses
let requestCount = 0;
let startTime = Date.now();

const server = http.createServer((req, res) => {
  requestCount++;
  const reqId = requestCount;
  
  log(`📝 [${reqId}] ${req.method} ${req.url} from ${req.connection.remoteAddress || 'unknown'}`);
  log(`📝 [${reqId}] Headers: ${JSON.stringify(req.headers, null, 2)}`);
  
  // Add response headers that Railway might expect
  res.setHeader('X-Powered-By', 'Railway-Submarine-FAQ');
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Cache-Control', 'no-cache');
  
  try {
    // Handle different health check endpoints
    if (req.url === '/health' || req.url === '/_health' || req.url === '/healthz' || req.url === '/ping') {
      log(`📝 [${reqId}] Health check request`);
      
      const healthResponse = {
        status: 'healthy',
        service: 'submarine-faqs',
        port: PORT,
        uptime: Math.floor((Date.now() - startTime) / 1000),
        requests: requestCount,
        timestamp: new Date().toISOString(),
        pid: process.pid,
        memory: process.memoryUsage(),
        version: process.version
      };
      
      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify(healthResponse, null, 2));
      log(`📝 [${reqId}] Health check response sent`);
      return;
    }
    
    // Root endpoint with comprehensive debug info
    if (req.url === '/' || req.url === '/debug') {
      log(`📝 [${reqId}] Debug page request`);
      
      let debugLogs = '';
      try {
        debugLogs = fs.readFileSync(logFile, 'utf8').split('\n').slice(-20).join('\n');
      } catch (e) {
        debugLogs = 'Could not read debug logs: ' + e.message;
      }
      
      const debugPage = `<!DOCTYPE html>
<html>
<head>
  <title>🔱 Railway Service Health Debug</title>
  <meta charset="UTF-8">
  <style>
    body { font-family: monospace; max-width: 1000px; margin: 0 auto; padding: 20px; }
    .section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { background: #e8f5e8; }
    .error { background: #ffe8e8; }
    pre { background: #fff; padding: 10px; border: 1px solid #ddd; overflow-x: auto; }
  </style>
</head>
<body>
  <h1>🔱 Railway Service Health Debug</h1>
  
  <div class="section success">
    <h3>✅ Service Status</h3>
    <p><strong>Status:</strong> Running successfully on Railway</p>
    <p><strong>Port:</strong> ${PORT}</p>
    <p><strong>Uptime:</strong> ${Math.floor((Date.now() - startTime) / 1000)} seconds</p>
    <p><strong>Requests:</strong> ${requestCount}</p>
    <p><strong>Process ID:</strong> ${process.pid}</p>
    <p><strong>Node Version:</strong> ${process.version}</p>
  </div>
  
  <div class="section">
    <h3>🌐 Environment</h3>
    <p><strong>NODE_ENV:</strong> ${process.env.NODE_ENV || 'not-set'}</p>
    <p><strong>DATABASE_URL:</strong> ${process.env.DATABASE_URL ? 'configured' : 'not-set'}</p>
    <p><strong>Platform:</strong> ${process.platform}</p>
  </div>
  
  <div class="section">
    <h3>📊 Memory Usage</h3>
    <pre>${JSON.stringify(process.memoryUsage(), null, 2)}</pre>
  </div>
  
  <div class="section">
    <h3>📝 Recent Debug Logs</h3>
    <pre>${debugLogs}</pre>
  </div>
  
  <div class="section">
    <h3>🔗 Health Endpoints</h3>
    <p><a href="/health">/health</a> | <a href="/_health">/_health</a> | <a href="/healthz">/healthz</a> | <a href="/ping">/ping</a></p>
  </div>
  
  <p>🎯 If you see this page, Railway deployment is working correctly!</p>
</body>
</html>`;
      
      res.writeHead(200, { 'Content-Type': 'text/html' });
      res.end(debugPage);
      log(`📝 [${reqId}] Debug page response sent`);
      return;
    }
    
    // 404 for other paths
    log(`📝 [${reqId}] 404 for path: ${req.url}`);
    res.writeHead(404, { 'Content-Type': 'text/plain' });
    res.end(`404 Not Found\nPath: ${req.url}\nTime: ${new Date().toISOString()}`);
    
  } catch (error) {
    log(`❌ [${reqId}] Error handling request: ${error.message}`);
    res.writeHead(500, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({
      error: 'Internal server error',
      message: error.message,
      requestId: reqId,
      timestamp: new Date().toISOString()
    }));
  }
});

// Enhanced error handling
server.on('error', (error) => {
  log(`❌ Server error: ${error.message}`);
  if (error.code === 'EADDRINUSE') {
    log(`🚫 Port ${PORT} is already in use`);
  }
});

server.on('clientError', (error, socket) => {
  log(`❌ Client error: ${error.message}`);
  socket.end('HTTP/1.1 400 Bad Request\r\n\r\n');
});

// Start server with comprehensive logging
server.listen(PORT, '0.0.0.0', (err) => {
  if (err) {
    log(`❌ Failed to start server: ${err.message}`);
    process.exit(1);
  }
  
  log(`✅ Server started successfully`);
  log(`🌐 Listening on 0.0.0.0:${PORT}`);
  log(`🎯 Railway should now be able to reach this service`);
  log(`🔗 Health checks available at: /health, /_health, /healthz, /ping`);
  log(`📋 Debug page available at: / and /debug`);
});

// Graceful shutdown with logging
const shutdown = (signal) => {
  log(`🛑 Received ${signal}, shutting down gracefully`);
  server.close(() => {
    log(`✅ Server closed successfully after ${Math.floor((Date.now() - startTime) / 1000)} seconds`);
    process.exit(0);
  });
};

process.on('SIGTERM', () => shutdown('SIGTERM'));
process.on('SIGINT', () => shutdown('SIGINT'));

// Log any uncaught errors
process.on('uncaughtException', (error) => {
  log(`❌ Uncaught exception: ${error.message}`);
  log(`❌ Stack: ${error.stack}`);
  process.exit(1);
});

process.on('unhandledRejection', (reason) => {
  log(`❌ Unhandled rejection: ${reason}`);
  process.exit(1);
});

log('🎯 Health debug server initialization complete - ready for Railway traffic');