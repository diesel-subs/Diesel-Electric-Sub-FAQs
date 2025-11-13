#!/usr/bin/env node

// Test the basic-http.js server
const http = require('http');

console.log('🧪 Testing basic HTTP server...');

// Start the server in background
const { spawn } = require('child_process');
const server = spawn('node', ['basic-http.js'], {
  env: { ...process.env, PORT: '3333' },
  stdio: 'pipe'
});

let serverOutput = '';
server.stdout.on('data', (data) => {
  serverOutput += data.toString();
  console.log('📝 Server:', data.toString().trim());
});

server.stderr.on('data', (data) => {
  console.error('❌ Server error:', data.toString().trim());
});

// Wait for server to start, then test
setTimeout(() => {
  console.log('\n🔍 Testing server response...');
  
  const testReq = http.request({
    hostname: 'localhost',
    port: 3333,
    path: '/health',
    method: 'GET'
  }, (res) => {
    let data = '';
    res.on('data', chunk => data += chunk);
    res.on('end', () => {
      console.log('✅ Server response:', data);
      console.log('✅ Status code:', res.statusCode);
      server.kill();
      process.exit(0);
    });
  });

  testReq.on('error', (err) => {
    console.error('❌ Request failed:', err.message);
    server.kill();
    process.exit(1);
  });

  testReq.end();
}, 2000);

// Cleanup after 10 seconds
setTimeout(() => {
  console.log('⏰ Test timeout - killing server');
  server.kill();
  process.exit(1);
}, 10000);