const express = require('express');
const fs = require('fs');
const path = require('path');

const app = express();
const port = process.env.PORT || 3000;

// Railway debugging - log environment
console.log('🚀 Starting Submarine FAQ Server...');
console.log('📋 Environment Variables:');
console.log(`   NODE_ENV: ${process.env.NODE_ENV || 'not set'}`);
console.log(`   PORT: ${process.env.PORT || 'not set (using 3000)'}`);
console.log(`   DATABASE_URL: ${process.env.DATABASE_URL ? 'configured' : 'not set'}`);
console.log(`🎯 Server will listen on: 0.0.0.0:${port}`);

// Basic middleware
app.use(express.json({ limit: '10mb' }));
app.use(express.static('.'));

// Health check for Railway - essential endpoint
app.get('/health', (req, res) => {
  res.status(200).json({ 
    status: 'healthy', 
    timestamp: new Date().toISOString(),
    service: 'submarine-faqs',
    port: port,
    host: '0.0.0.0',
    database: process.env.DATABASE_URL ? 'connected' : 'fallback',
    uptime: process.uptime()
  });
});

// Alternative health check paths
app.get('/healthz', (req, res) => res.status(200).json({ status: 'ok' }));
app.get('/ping', (req, res) => res.status(200).send('pong'));

// Serve main page
app.get('/', (req, res) => {
  try {
    const indexHtml = fs.readFileSync(path.join(__dirname, 'index.html'), 'utf8');
    res.send(indexHtml);
  } catch (error) {
    res.status(500).send(`
      <h1>🔱 Submarine FAQs</h1>
      <p>Welcome to the Diesel Electric Submarine FAQs!</p>
      <p>Server is running but index.html not found.</p>
      <a href="/health">Health Check</a>
    `);
  }
});

// Simple FAQ API endpoint that works without database
app.get('/api/faqs', (req, res) => {
  res.json({
    success: true,
    message: 'FAQ API is working',
    faqs: [
      {
        id: 1,
        question: "What is a diesel-electric submarine?",
        answer: "A diesel-electric submarine uses diesel engines for surface propulsion and electric batteries for underwater operations.",
        category: "General"
      }
    ]
  });
});

// Admin page
app.get('/admin', (req, res) => {
  try {
    const adminHtml = fs.readFileSync(path.join(__dirname, 'admin.html'), 'utf8');
    res.send(adminHtml);
  } catch (error) {
    res.send(`
      <h1>🔱 Admin Panel</h1>
      <p>Admin interface loading...</p>
      <a href="/">← Back to FAQs</a>
    `);
  }
});

// Catch-all for missing routes
app.use((req, res) => {
  res.status(404).json({
    error: 'Route not found',
    path: req.originalUrl,
    message: 'This endpoint does not exist'
  });
});

// Error handler
app.use((error, req, res, next) => {
  console.error('Server error:', error);
  res.status(500).json({ 
    error: 'Internal server error',
    message: process.env.NODE_ENV === 'development' ? error.message : 'Something went wrong'
  });
});

// Start server - Railway requires listening on 0.0.0.0 and PORT env var
app.listen(port, "0.0.0.0", function () {
  console.log(`🔱 Submarine FAQ server running on port ${port}`);
  console.log(`🌍 Environment: ${process.env.NODE_ENV || 'development'}`);
  console.log(`🗄️  Database: ${process.env.DATABASE_URL ? 'Railway MySQL' : 'File fallback'}`);
  console.log(`✅ Server ready for connections on 0.0.0.0:${port}`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('🛑 SIGTERM received, shutting down gracefully');
  process.exit(0);
});

process.on('SIGINT', () => {
  console.log('🛑 SIGINT received, shutting down gracefully');
  process.exit(0);
});