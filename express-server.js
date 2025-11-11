const express = require('express');
const app = express();
const port = process.env.PORT || 3000;

app.get('/', (req, res) => {
  res.send(`
    const express = require('express');
const fs = require('fs');
const path = require('path');
const app = express();
const port = process.env.PORT || 3000;

// Serve static files
app.use(express.static('.'));

app.get('/', (req, res) => {
  // Read and serve the actual index.html file
  fs.readFile(path.join(__dirname, 'index.html'), 'utf8', (err, data) => {
    if (err) {
      res.status(500).send('Error loading page');
      return;
    }
    res.send(data);
  });
});
  `);
});

app.listen(port, () => {
  console.log(`🔱 Submarine FAQ server running on port ${port}`);
});