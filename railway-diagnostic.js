const http = require('http');
const fs = require('fs');

// Railway Deployment Diagnostic Tool - Issue #4
// Checking region/deployment settings and Railway-specific configuration

const PORT = process.env.PORT || 3000;
const RAILWAY_ENVIRONMENT = process.env.RAILWAY_ENVIRONMENT;
const RAILWAY_ENVIRONMENT_NAME = process.env.RAILWAY_ENVIRONMENT_NAME;
const RAILWAY_SERVICE_NAME = process.env.RAILWAY_SERVICE_NAME;
const RAILWAY_PROJECT_NAME = process.env.RAILWAY_PROJECT_NAME;
const RAILWAY_PROJECT_ID = process.env.RAILWAY_PROJECT_ID;
const RAILWAY_SERVICE_ID = process.env.RAILWAY_SERVICE_ID;
const RAILWAY_DEPLOYMENT_ID = process.env.RAILWAY_DEPLOYMENT_ID;
const RAILWAY_REPLICA_ID = process.env.RAILWAY_REPLICA_ID;
const RAILWAY_REGION = process.env.RAILWAY_REGION;
const RAILWAY_PUBLIC_DOMAIN = process.env.RAILWAY_PUBLIC_DOMAIN;
const RAILWAY_PRIVATE_DOMAIN = process.env.RAILWAY_PRIVATE_DOMAIN;

function logDiagnostic(message) {
  const timestamp = new Date().toISOString();
  const logEntry = `[${timestamp}] ${message}\n`;
  console.log(message);
  
  try {
    fs.appendFileSync('/tmp/railway-diagnostic.log', logEntry);
  } catch (err) {
    console.log(`Log write failed: ${err.message}`);
  }
}

function getRailwayDiagnostics() {
  return {
    timestamp: new Date().toISOString(),
    deployment: {
      environment: RAILWAY_ENVIRONMENT || 'unknown',
      environmentName: RAILWAY_ENVIRONMENT_NAME || 'unknown',
      serviceName: RAILWAY_SERVICE_NAME || 'unknown',
      projectName: RAILWAY_PROJECT_NAME || 'unknown',
      projectId: RAILWAY_PROJECT_ID || 'unknown',
      serviceId: RAILWAY_SERVICE_ID || 'unknown',
      deploymentId: RAILWAY_DEPLOYMENT_ID || 'unknown',
      replicaId: RAILWAY_REPLICA_ID || 'unknown'
    },
    network: {
      region: RAILWAY_REGION || 'unknown',
      publicDomain: RAILWAY_PUBLIC_DOMAIN || 'unknown',
      privateDomain: RAILWAY_PRIVATE_DOMAIN || 'unknown',
      port: PORT,
      bindHost: '0.0.0.0'
    },
    system: {
      nodeVersion: process.version,
      platform: process.platform,
      arch: process.arch,
      memoryUsage: process.memoryUsage(),
      uptime: process.uptime(),
      cwd: process.cwd(),
      pid: process.pid
    },
    env: {
      NODE_ENV: process.env.NODE_ENV || 'unknown',
      PATH: process.env.PATH ? 'set' : 'not set',
      HOME: process.env.HOME || 'unknown',
      USER: process.env.USER || 'unknown'
    }
  };
}

function createDiagnosticHTML(diagnostics) {
  return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railway Deployment Diagnostic - Issue #4</title>
    <style>
        body { font-family: monospace; margin: 20px; background: #1a1a1a; color: #00ff00; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #333; background: #222; }
        .good { color: #00ff00; }
        .warning { color: #ffaa00; }
        .error { color: #ff4444; }
        .key { color: #00aaff; }
        .value { color: #ffffff; }
        pre { background: #111; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🚂 Railway Deployment Diagnostic - Issue #4</h1>
    <p class="good">✅ Server is running and responding (this proves the app works)</p>
    
    <div class="section">
        <h2>🌍 Region & Network Configuration</h2>
        <p><span class="key">Railway Region:</span> <span class="value">${diagnostics.network.region}</span></p>
        <p><span class="key">Public Domain:</span> <span class="value">${diagnostics.network.publicDomain}</span></p>
        <p><span class="key">Private Domain:</span> <span class="value">${diagnostics.network.privateDomain}</span></p>
        <p><span class="key">Bind Port:</span> <span class="value">${diagnostics.network.port}</span></p>
        <p><span class="key">Bind Host:</span> <span class="value">${diagnostics.network.bindHost}</span></p>
    </div>

    <div class="section">
        <h2>🚀 Deployment Information</h2>
        <p><span class="key">Environment:</span> <span class="value">${diagnostics.deployment.environment}</span></p>
        <p><span class="key">Environment Name:</span> <span class="value">${diagnostics.deployment.environmentName}</span></p>
        <p><span class="key">Service Name:</span> <span class="value">${diagnostics.deployment.serviceName}</span></p>
        <p><span class="key">Project Name:</span> <span class="value">${diagnostics.deployment.projectName}</span></p>
        <p><span class="key">Project ID:</span> <span class="value">${diagnostics.deployment.projectId}</span></p>
        <p><span class="key">Service ID:</span> <span class="value">${diagnostics.deployment.serviceId}</span></p>
        <p><span class="key">Deployment ID:</span> <span class="value">${diagnostics.deployment.deploymentId}</span></p>
        <p><span class="key">Replica ID:</span> <span class="value">${diagnostics.deployment.replicaId}</span></p>
    </div>

    <div class="section">
        <h2>🖥️ System Information</h2>
        <p><span class="key">Node.js Version:</span> <span class="value">${diagnostics.system.nodeVersion}</span></p>
        <p><span class="key">Platform:</span> <span class="value">${diagnostics.system.platform}</span></p>
        <p><span class="key">Architecture:</span> <span class="value">${diagnostics.system.arch}</span></p>
        <p><span class="key">Memory Usage:</span> <span class="value">${JSON.stringify(diagnostics.system.memoryUsage)}</span></p>
        <p><span class="key">Uptime:</span> <span class="value">${diagnostics.system.uptime} seconds</span></p>
        <p><span class="key">Working Directory:</span> <span class="value">${diagnostics.system.cwd}</span></p>
        <p><span class="key">Process ID:</span> <span class="value">${diagnostics.system.pid}</span></p>
    </div>

    <div class="section">
        <h2>🔧 Environment Variables</h2>
        <p><span class="key">NODE_ENV:</span> <span class="value">${diagnostics.env.NODE_ENV}</span></p>
        <p><span class="key">PATH:</span> <span class="value">${diagnostics.env.PATH}</span></p>
        <p><span class="key">HOME:</span> <span class="value">${diagnostics.env.HOME}</span></p>
        <p><span class="key">USER:</span> <span class="value">${diagnostics.env.USER}</span></p>
    </div>

    <div class="section">
        <h2>🔍 Diagnostic Analysis</h2>
        <ul>
            <li class="${diagnostics.network.region !== 'unknown' ? 'good' : 'warning'}">
                Region Detection: ${diagnostics.network.region !== 'unknown' ? '✅ Region detected' : '⚠️ Region unknown'}
            </li>
            <li class="${diagnostics.deployment.environment !== 'unknown' ? 'good' : 'warning'}">
                Environment: ${diagnostics.deployment.environment !== 'unknown' ? '✅ Environment detected' : '⚠️ Environment unknown'}
            </li>
            <li class="${diagnostics.network.publicDomain !== 'unknown' ? 'good' : 'warning'}">
                Public Domain: ${diagnostics.network.publicDomain !== 'unknown' ? '✅ Domain configured' : '⚠️ Domain unknown'}
            </li>
            <li class="${diagnostics.deployment.projectId !== 'unknown' ? 'good' : 'warning'}">
                Project ID: ${diagnostics.deployment.projectId !== 'unknown' ? '✅ Project ID available' : '⚠️ Project ID missing'}
            </li>
        </ul>
    </div>

    <div class="section">
        <h2>📊 Raw Diagnostic Data</h2>
        <pre>${JSON.stringify(diagnostics, null, 2)}</pre>
    </div>

    <p><em>Generated at ${diagnostics.timestamp}</em></p>
</body>
</html>`;
}

const server = http.createServer((req, res) => {
  const requestId = Math.random().toString(36).substring(2, 15);
  logDiagnostic(`[${requestId}] ${req.method} ${req.url} from ${req.headers['x-forwarded-for'] || req.connection.remoteAddress}`);

  const diagnostics = getRailwayDiagnostics();

  if (req.url === '/') {
    res.writeHead(200, { 'Content-Type': 'text/html' });
    res.end(createDiagnosticHTML(diagnostics));
  } else if (req.url === '/health' || req.url === '/_health' || req.url === '/healthz') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({
      status: 'healthy',
      issue: 4,
      diagnostic: 'Railway region/deployment settings check',
      requestId,
      ...diagnostics
    }));
  } else if (req.url === '/diagnostic' || req.url === '/debug') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify(diagnostics, null, 2));
  } else {
    res.writeHead(404, { 'Content-Type': 'text/plain' });
    res.end('404 Not Found - Try /, /health, or /diagnostic');
  }
});

server.listen(PORT, '0.0.0.0', () => {
  logDiagnostic(`🚂 Railway Diagnostic Server (Issue #4) listening on 0.0.0.0:${PORT}`);
  logDiagnostic(`🌍 Railway Region: ${RAILWAY_REGION || 'unknown'}`);
  logDiagnostic(`🏗️ Railway Environment: ${RAILWAY_ENVIRONMENT || 'unknown'}`);
  logDiagnostic(`📋 Project: ${RAILWAY_PROJECT_NAME || 'unknown'} (${RAILWAY_PROJECT_ID || 'unknown'})`);
  logDiagnostic(`🔧 Service: ${RAILWAY_SERVICE_NAME || 'unknown'} (${RAILWAY_SERVICE_ID || 'unknown'})`);
  logDiagnostic(`🚀 Deployment: ${RAILWAY_DEPLOYMENT_ID || 'unknown'}`);
  logDiagnostic(`🌐 Public Domain: ${RAILWAY_PUBLIC_DOMAIN || 'unknown'}`);
});

// Graceful shutdown handling
process.on('SIGTERM', () => {
  logDiagnostic('📴 Received SIGTERM, shutting down gracefully');
  server.close(() => {
    logDiagnostic('🔚 Server closed');
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  logDiagnostic('📴 Received SIGINT, shutting down gracefully');
  server.close(() => {
    logDiagnostic('🔚 Server closed');
    process.exit(0);
  });
});

// Handle uncaught exceptions
process.on('uncaughtException', (err) => {
  logDiagnostic(`💥 Uncaught Exception: ${err.message}`);
  logDiagnostic(`Stack: ${err.stack}`);
});

process.on('unhandledRejection', (reason, promise) => {
  logDiagnostic(`💥 Unhandled Rejection at: ${promise}, reason: ${reason}`);
});

logDiagnostic('🔄 Railway Diagnostic Tool initialized for Issue #4');