const express = require('express');
const fs = require('fs');
const path = require('path');
const app = express();
const port = process.env.PORT || 3000;

// Middleware for parsing JSON requests
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

// Serve static files (CSS, JS, images, etc.)
app.use(express.static('.', {
  maxAge: process.env.NODE_ENV === 'production' ? '1d' : 0
}));

// Health check endpoint for Railway
app.get('/health', (req, res) => {
  res.json({ 
    status: 'healthy', 
    timestamp: new Date().toISOString(),
    service: 'submarine-faqs'
  });
});

// API Routes - dynamically load all API endpoints with safe error handling
const apiDir = path.join(__dirname, 'api');
const loadedRoutes = [];
const failedRoutes = [];

if (fs.existsSync(apiDir)) {
  const files = fs.readdirSync(apiDir).filter(file => file.endsWith('.js'));
  
  files.forEach(file => {
    const routeName = file.replace('.js', '');
    const routePath = `/api/${routeName}`;
    const filePath = path.join(__dirname, 'api', file);
    
    try {
      // Check if file contains ES6 imports (which cause crashes)
      const fileContent = fs.readFileSync(filePath, 'utf8');
      if (fileContent.includes('import ') && fileContent.includes('from ')) {
        console.warn(`⏭️  Skipping ES6 module: ${routePath} (contains import statements)`);
        failedRoutes.push({ route: routePath, reason: 'ES6 imports not supported' });
        return;
      }
      
      const handler = require(filePath);
      
      // Support both default export and module.exports
      const routeHandler = handler.default || handler;
      
      if (typeof routeHandler === 'function') {
        app.all(routePath, routeHandler);
        console.log(`✅ Loaded API route: ${routePath}`);
        loadedRoutes.push(routePath);
      } else {
        console.warn(`⚠️  Invalid handler for ${routePath}: not a function`);
        failedRoutes.push({ route: routePath, reason: 'Handler is not a function' });
      }
    } catch (error) {
      console.warn(`❌ Failed to load API route ${routePath}: ${error.message}`);
      failedRoutes.push({ route: routePath, reason: error.message });
    }
  });
  
  console.log(`\n📊 API Routes Summary:`);
  console.log(`   ✅ Loaded: ${loadedRoutes.length} routes`);
  console.log(`   ❌ Failed: ${failedRoutes.length} routes`);
}

// Serve main pages
app.get('/', (req, res) => {
  fs.readFile(path.join(__dirname, 'index.html'), 'utf8', (err, data) => {
    if (err) {
      console.error('Error loading index.html:', err);
      res.status(500).send('Error loading page');
      return;
    }
    res.send(data);
  });
});

app.get('/admin', (req, res) => {
  fs.readFile(path.join(__dirname, 'admin.html'), 'utf8', (err, data) => {
    if (err) {
      console.error('Error loading admin.html:', err);
      res.status(404).send('Admin page not found');
      return;
    }
    res.send(data);
  });
});

app.get('/welcome', (req, res) => {
  fs.readFile(path.join(__dirname, 'welcome.html'), 'utf8', (err, data) => {
    if (err) {
      console.error('Error loading welcome.html:', err);
      res.status(404).send('Welcome page not found');
      return;
    }
    res.send(data);
  });
});

// 404 handler
app.use((req, res) => {
  res.status(404).send(`
    <h1>🔱 Submarine FAQ - Page Not Found</h1>
    <p>The page you're looking for doesn't exist.</p>
    <a href="/">← Back to FAQs</a>
  `);
});

// Error handler
app.use((error, req, res, next) => {
  console.error('Server error:', error);
  res.status(500).json({ 
    error: 'Internal server error',
    message: process.env.NODE_ENV === 'development' ? error.message : 'Something went wrong'
  });
});

// Start server
app.listen(port, '0.0.0.0', () => {
  console.log(`🔱 Submarine FAQ server running on port ${port}`);
  console.log(`🌍 Server accessible at http://localhost:${port}`);
  console.log(`📊 Environment: ${process.env.NODE_ENV || 'development'}`);
  console.log(`🗄️  Database: ${process.env.DATABASE_URL ? 'Connected' : 'File-based fallback'}`);
});