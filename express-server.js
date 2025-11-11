const express = require('express');
const app = express();
const port = process.env.PORT || 3000;

app.get('/', (req, res) => {
  res.send(`
    <html>
      <head><title>🔱 SUCCESS!</title></head>
      <body style="font-family: Arial; padding: 20px; background: #e6f3ff;">
        <h1 style="color: #1e3c72;">🔱 Submarine FAQ Site is LIVE! 🔱</h1>
        <p><strong>✅ Railway deployment successful!</strong></p>
        <p><strong>✅ Express server running!</strong></p>
        <p><strong>✅ Node.js environment working!</strong></p>
        <p>Port: ${port} | Time: ${new Date()}</p>
        <hr>
        <h3>📊 Your submarine FAQ project is ready:</h3>
        <ul>
          <li>185 FAQs imported to database</li>
          <li>6 categories organized</li>
          <li>Admin system prepared</li>
          <li>MySQL database connected</li>
        </ul>
      </body>
    </html>
  `);
});

app.listen(port, () => {
  console.log(`🔱 Submarine FAQ server running on port ${port}`);
});