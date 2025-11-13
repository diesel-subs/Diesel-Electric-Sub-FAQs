const http = require('http');

const port = process.env.PORT || 3000;

console.log('🚀 RAILWAY BASIC HTTP SERVER - Zero Dependencies');
console.log('🎯 PORT from Railway:', port);
console.log('📋 NODE_ENV:', process.env.NODE_ENV || 'not set');
console.log('⚡ This should definitely work on Railway!');

const server = http.createServer((req, res) => {
  const url = req.url;
  
  if (url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ 
      status: 'ok', 
      port: port,
      time: new Date().toISOString() 
    }));
  } else {
    res.writeHead(200, { 'Content-Type': 'text/html' });
    res.end(`
      <!DOCTYPE html>
      <html>
      <head><title>🔱 Railway Test</title></head>
      <body>
        <h1>🔱 Railway Deployment SUCCESS!</h1>
        <p>✅ No Express, No npm dependencies</p>
        <p>Port: ${port}</p>
        <p>URL: ${url}</p>
        <p>Time: ${new Date()}</p>
        <p><a href="/health">Health Check</a></p>
      </body>
      </html>
    `);
  }
});

server.listen(port, "0.0.0.0", () => {
  console.log(`✅ HTTP Server listening on 0.0.0.0:${port}`);
  console.log('🎯 No dependencies - should work on Railway!');
});

process.on('SIGTERM', () => {
  console.log('🛑 SIGTERM - closing server');
  server.close(() => process.exit(0));
});