#!/usr/bin/env node

// Railway Environment Setup Script
// This script helps verify all environment variables are properly configured

console.log('🚂 Railway Environment Setup Check');
console.log('====================================');

// Check required environment variables
const requiredEnvVars = [
  'DATABASE_URL',
  'ADMIN_PASSWORD',
  'NODE_ENV',
  'PORT'
];

const missingVars = [];
const presentVars = [];

requiredEnvVars.forEach(varName => {
  if (process.env[varName]) {
    presentVars.push(varName);
    console.log(`✅ ${varName}: ${varName === 'DATABASE_URL' || varName === 'ADMIN_PASSWORD' ? '[HIDDEN]' : process.env[varName]}`);
  } else {
    missingVars.push(varName);
    console.log(`❌ ${varName}: NOT SET`);
  }
});

console.log('\n📋 Summary:');
console.log(`✅ Present: ${presentVars.length}/${requiredEnvVars.length}`);
console.log(`❌ Missing: ${missingVars.length}/${requiredEnvVars.length}`);

if (missingVars.length > 0) {
  console.log('\n🚨 Missing Environment Variables:');
  missingVars.forEach(varName => {
    console.log(`   - ${varName}`);
  });
  
  console.log('\n📝 Required Actions:');
  console.log('   1. Go to Railway Dashboard → extraordinary-heart → Variables');
  console.log('   2. Add missing variables:');
  missingVars.forEach(varName => {
    if (varName === 'ADMIN_PASSWORD') {
      console.log(`      - ${varName}: [choose secure password]`);
    } else if (varName === 'NODE_ENV') {
      console.log(`      - ${varName}: production`);
    } else {
      console.log(`      - ${varName}: [see Railway documentation]`);
    }
  });
}

if (missingVars.length === 0) {
  console.log('\n🎉 All environment variables are configured!');
  console.log('   Ready to start the FAQ application.');
} else {
  console.log(`\n⚠️  Please add ${missingVars.length} missing environment variable(s) before starting.`);
  process.exit(1);
}

// Test database connection if DATABASE_URL is present
if (process.env.DATABASE_URL) {
  console.log('\n🔍 Testing database connection...');
  try {
    const mysql = require('mysql2/promise');
    (async () => {
      try {
        const connection = await mysql.createConnection(process.env.DATABASE_URL);
        await connection.ping();
        console.log('✅ Database connection successful!');
        await connection.end();
      } catch (error) {
        console.log(`❌ Database connection failed: ${error.message}`);
      }
    })();
  } catch (error) {
    console.log('⚠️  MySQL2 not installed - skipping database test');
  }
}