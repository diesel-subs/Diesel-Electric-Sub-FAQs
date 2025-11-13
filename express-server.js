import express from 'express';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

// ES module compatibility
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
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

// API Routes - dynamically load all API endpoints
async function loadApiRoutes() {
  const apiDir = path.join(__dirname, 'api');
  if (fs.existsSync(apiDir)) {
    const files = fs.readdirSync(apiDir);
    
    for (const file of files) {
      if (file.endsWith('.js')) {
        const routeName = file.replace('.js', '');
        const routePath = `/api/${routeName}`;
        
        try {
          const handler = await import(path.join(__dirname, 'api', file));
          
          // Support both default export and named exports
          const routeHandler = handler.default || handler;
          
          if (typeof routeHandler === 'function') {
            app.all(routePath, routeHandler);
            console.log(`📡 Loaded API route: ${routePath}`);
          }
        } catch (error) {
          console.warn(`⚠️  Failed to load API route ${routePath}:`, error.message);
        }
      }
    }
  }
}

// Load API routes asynchronously
await loadApiRoutes();

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