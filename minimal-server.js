const express = require('express');
const app = express();
const port = process.env.PORT || 3000;

console.log('🚀 RAILWAY DEBUG: Starting minimal server...');
console.log('📋 PORT:', process.env.PORT);
console.log('📋 NODE_ENV:', process.env.NODE_ENV);

app.get('/', (req, res) => {
  res.send('🔱 Submarine FAQ Server is ALIVE!');
});

app.get('/health', (req, res) => {
  res.json({ status: 'ok', port: port, timestamp: new Date() });
});

app.listen(port, "0.0.0.0", function() {
  console.log(`✅ Server listening on 0.0.0.0:${port}`);
  console.log('🎯 Railway should be able to connect now!');
});

process.on('SIGTERM', () => {
  console.log('🛑 SIGTERM received');
  process.exit(0);
});