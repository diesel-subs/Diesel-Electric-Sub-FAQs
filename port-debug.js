const http = require('http');

// Railway port configuration - Railway provides PORT environment variable
const PORT = process.env.PORT || 3000;
const HOST = '0.0.0.0'; // Railway requires binding to all interfaces

console.log('🚀 RAILWAY PORT DEBUG SERVER');
console.log('📡 Railway PORT from env:', process.env.PORT);
console.log('📡 Using PORT:', PORT);
console.log('🌐 Binding to HOST:', HOST);
console.log('📋 All ENV vars:');
Object.keys(process.env).filter(key => key.includes('PORT') || key.includes('HOST')).forEach(key => {
  console.log(`   ${key}:`, process.env[key]);
});

const server = http.createServer((req, res) => {
  console.log(`📝 REQUEST: ${req.method} ${req.url} from ${req.connection.remoteAddress}`);
  
  // Health check endpoint that Railway might be looking for
  if (req.url === '/health' || req.url === '/_health' || req.url === '/healthz') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({
      status: 'healthy',
      port: PORT,
      host: HOST,
      timestamp: new Date().toISOString(),
      uptime: process.uptime()
    }));
    return;
  }
  
  // Default response
  res.writeHead(200, {
    'Content-Type': 'text/plain',
    'Access-Control-Allow-Origin': '*'
  });
  
  const response = `🔱 Railway Port Debug Server

✅ Server Status: Running  
📡 Port: ${PORT}
🌐 Host: ${HOST}  
🕒 Time: ${new Date().toISOString()}
🔗 Request URL: ${req.url}
📍 Remote IP: ${req.connection.remoteAddress}
🏷️  Headers: ${Object.keys(req.headers).length}

📋 Port Environment:
   PORT: ${process.env.PORT || 'not set'}
   NODE_ENV: ${process.env.NODE_ENV || 'not set'}

🎯 If you see this, Railway port binding is working!`;

  res.end(response);
});

// Try multiple binding approaches to troubleshoot port issues
server.listen(PORT, HOST, (err) => {
  if (err) {
    console.error('❌ Server failed to bind to', HOST + ':' + PORT, err);
    
    // Try binding to localhost as fallback
    console.log('🔄 Trying localhost binding...');
    server.listen(PORT, 'localhost', (err2) => {
      if (err2) {
        console.error('❌ Localhost binding also failed:', err2);
        process.exit(1);
      }
      console.log('✅ Server bound to localhost:' + PORT);
    });
  } else {
    console.log('✅ Server successfully bound to', HOST + ':' + PORT);
    console.log('🎯 Railway should be able to reach this server');
  }
});

// Error handling
server.on('error', (err) => {
  console.error('❌ Server error:', err);
  if (err.code === 'EADDRINUSE') {
    console.error('🚫 Port', PORT, 'is already in use');
  } else if (err.code === 'EACCES') {
    console.error('🚫 Permission denied for port', PORT);
  }
  process.exit(1);
});

// Graceful shutdown
const shutdown = () => {
  console.log('🛑 Shutting down server...');
  server.close(() => {
    console.log('✅ Server closed');
    process.exit(0);
  });
};

process.on('SIGTERM', shutdown);
process.on('SIGINT', shutdown);

console.log('🎯 Port debug server initialization complete');