const http = require('http');
const port = process.env.PORT || 3000;

console.log('🚀 RAILWAY MINIMAL SERVER STARTING');
console.log('📡 PORT:', port);
console.log('🕒 TIME:', new Date().toISOString());
console.log('🌐 NODE_ENV:', process.env.NODE_ENV || 'not-set');

const server = http.createServer((req, res) => {
  console.log('📝 REQUEST:', req.method, req.url);
  
  const responseData = `🔱 Railway Submarine FAQ Server
✅ Status: Running
📡 Port: ${port}  
🕒 Time: ${new Date().toISOString()}
🌐 URL: ${req.url}
🔗 Host: ${req.headers.host}
📋 User-Agent: ${req.headers['user-agent']}

🎯 Railway deployment successful!`;

  res.writeHead(200, {
    'Content-Type': 'text/plain; charset=utf-8',
    'Access-Control-Allow-Origin': '*'
  });
  res.end(responseData);
});

server.listen(port, '0.0.0.0', (err) => {
  if (err) {
    console.error('❌ Server failed to start:', err);
    process.exit(1);
  }
  console.log('✅ Server listening on 0.0.0.0:' + port);
  console.log('🎯 Railway server ready for traffic');
});

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('🛑 SIGTERM received');
  server.close(() => process.exit(0));
});

process.on('SIGINT', () => {
  console.log('🛑 SIGINT received');  
  server.close(() => process.exit(0));
});