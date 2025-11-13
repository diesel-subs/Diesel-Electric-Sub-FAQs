require('http').createServer((req, res) => {
  res.writeHead(200, {'Content-Type': 'text/plain'});
  res.end('Railway server working!\nTime: ' + new Date());
}).listen(process.env.PORT || 3000, '0.0.0.0', () => {
  console.log('Server started on port', process.env.PORT || 3000);
});