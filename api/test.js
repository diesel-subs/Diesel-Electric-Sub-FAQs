export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  return res.json({
    status: 'API Working!',
    message: 'Node.js API is functional',
    timestamp: new Date().toISOString()
  });
}