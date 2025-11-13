// MINIMAL RAILWAY SERVER - No API routes, no imports, just basic HTTP
const express = require('express');
const app = express();

// Railway provides PORT automatically
const port = process.env.PORT || 3000;

console.log('🚀 MINIMAL SERVER STARTING...');
console.log('📍 PORT from Railway:', port);
console.log('📍 NODE_ENV:', process.env.NODE_ENV || 'not set');

// Simple root endpoint
app.get('/', (req, res) => {
  res.send(`
    <!DOCTYPE html>
    <html>
    <head>
        <title>🔱 Submarine FAQs - Railway Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .status { color: green; font-weight: bold; }
        </style>
    </head>
    <body>
        <h1>🔱 Submarine FAQ Server</h1>
        <p class="status">✅ Railway deployment is WORKING!</p>
        <p>Server running on port: ${port}</p>
        <p>Timestamp: ${new Date().toISOString()}</p>
        <p><a href="/health">Health Check</a></p>
    </body>
    </html>
  `);
});

// Health check for Railway
app.get('/health', (req, res) => {
  res.status(200).json({ 
    status: 'healthy',
    port: port,
    timestamp: new Date().toISOString(),
    message: 'Railway deployment successful!'
  });
});

// Start server on 0.0.0.0 (Railway requirement)
const server = app.listen(port, "0.0.0.0", function() {
  console.log(`✅ RAILWAY SERVER READY on 0.0.0.0:${port}`);
  console.log('🎯 No API routes loaded - pure minimal server');
  console.log('🌐 Railway should connect successfully now!');
});

// Handle Railway shutdown signals
process.on('SIGTERM', () => {
  console.log('🛑 Railway SIGTERM - shutting down gracefully');
  server.close(() => {
    console.log('👋 Server closed');
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  console.log('🛑 SIGINT received');
  process.exit(0);
});