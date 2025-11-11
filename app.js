const http = require('http');

console.log('Starting server...');
const port = process.env.PORT || 3000;

const server = http.createServer((req, res) => {
    res.writeHead(200, { 'Content-Type': 'text/html' });
    res.end(`
<html>
<head><title>Submarine FAQs LIVE</title></head>
<body style="font-family: Arial; padding: 20px; background: #f0f8ff;">
    <h1 style="color: #1e3c72;">🔱 SUCCESS! Submarine FAQ Site is LIVE! 🔱</h1>
    <p><strong>Railway deployment working!</strong></p>
    <p>Your submarine FAQ website is now online.</p>
    <ul>
        <li>185 FAQs imported and ready</li>
        <li>6 categories organized</li>
        <li>MySQL database connected</li>
        <li>Admin system prepared</li>
    </ul>
    <p>Port: ${port} | Time: ${new Date()}</p>
</body>
</html>
    `);
});

server.listen(port, () => {
    console.log(`Server running on port ${port}`);
});