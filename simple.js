console.log('Server starting...');

require('http').createServer(function (req, res) {
  res.writeHead(200, { 'Content-Type': 'text/plain' });
  res.end('SUCCESS! Submarine FAQ server is working on Railway!');
}).listen(process.env.PORT || 3000, function () {
  console.log('Server started on port', process.env.PORT || 3000);
});