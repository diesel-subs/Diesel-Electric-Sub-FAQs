const express = require('express');
const app = express();

// Railway sets PORT automatically
const port = process.env.PORT || 3000;

console.log('🚀 ULTRA MINIMAL SERVER STARTING');
console.log('PORT:', port);

app.get('/', (req, res) => {
  res.send(`
    <h1>🔱 Submarine FAQ Server</h1>
    <p>✅ Railway deployment SUCCESS!</p>
    <p>Port: ${port}</p>
    <p>Time: ${new Date()}</p>
  `);
});

app.listen(port, "0.0.0.0", () => {
  console.log(`✅ Server running on 0.0.0.0:${port}`);
});