#!/usr/bin/env node

/**
 * PlanetScale Connection Test Script
 * Run this to verify your PlanetScale database connection
 */

import { getConnection, query } from '../lib/database.js';

async function testPlanetScaleConnection() {
    console.log('🚀 Testing PlanetScale connection...\n');

    try {
        // Test 1: Basic connection
        console.log('1. Testing database connection...');
        const connection = await getConnection();
        console.log('✅ Database connection successful');

        // Test 2: Check database info
        console.log('\n2. Checking database information...');
        const dbInfo = await query('SELECT VERSION() as version, DATABASE() as database_name');
        console.log(`✅ MySQL Version: ${dbInfo[0].version}`);
        console.log(`✅ Database Name: ${dbInfo[0].database_name}`);

        // Test 3: Check if tables exist
        console.log('\n3. Checking table structure...');
        const tables = await query(`
            SELECT table_name, table_rows 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
            ORDER BY table_name
        `);
        
        if (tables.length === 0) {
            console.log('⚠️  No tables found - you need to run the schema setup');
            console.log('   Run: mysql -h <host> -u <user> -p -D submarine_faqs < database/schema.sql');
        } else {
            console.log('✅ Found tables:');
            tables.forEach(table => {
                console.log(`   - ${table.table_name} (${table.table_rows || 0} rows)`);
            });
        }

        // Test 4: Check categories
        console.log('\n4. Testing categories...');
        try {
            const categories = await query('SELECT * FROM categories LIMIT 5');
            console.log(`✅ Categories table: ${categories.length} sample records`);
        } catch (e) {
            console.log('⚠️  Categories table not found - run schema setup first');
        }

        // Test 5: Check FAQs
        console.log('\n5. Testing FAQs...');
        try {
            const faqs = await query('SELECT COUNT(*) as count FROM faqs');
            console.log(`✅ FAQs table: ${faqs[0].count} total records`);
        } catch (e) {
            console.log('⚠️  FAQs table not found - run migration after schema setup');
        }

        // Test 6: Test search functionality
        console.log('\n6. Testing search functionality...');
        try {
            const searchResult = await query(`
                SELECT id, question 
                FROM faqs 
                WHERE MATCH(question, answer) AGAINST('submarine' IN BOOLEAN MODE) 
                LIMIT 3
            `);
            console.log(`✅ Full-text search: ${searchResult.length} results for 'submarine'`);
        } catch (e) {
            console.log('⚠️  Full-text search not available yet - needs data migration');
        }

        console.log('\n🎉 PlanetScale connection test completed successfully!');
        console.log('\nNext steps:');
        console.log('1. If no tables found, run: mysql -h <host> -u <user> -p < database/schema.sql');
        console.log('2. If no FAQs found, run migration: POST /api/migrate-faqs');
        console.log('3. Deploy to Vercel and test the admin panel');

    } catch (error) {
        console.error('\n❌ PlanetScale connection test failed:');
        console.error('Error:', error.message);
        console.error('\nTroubleshooting:');
        console.error('1. Check your DATABASE_URL environment variable');
        console.error('2. Verify PlanetScale credentials and database name');
        console.error('3. Ensure SSL is properly configured');
        console.error('4. Check if database exists in PlanetScale dashboard');
        
        if (error.code === 'ENOTFOUND') {
            console.error('\n🔍 DNS resolution failed - check your PlanetScale hostname');
        }
        if (error.code === 'ER_ACCESS_DENIED_ERROR') {
            console.error('\n🔍 Access denied - check username and password');
        }
        if (error.code === 'ER_BAD_DB_ERROR') {
            console.error('\n🔍 Database not found - verify database name in PlanetScale');
        }
    }

    process.exit();
}

// Check if DATABASE_URL is configured
if (!process.env.DATABASE_URL) {
    console.error('❌ DATABASE_URL environment variable is not set');
    console.error('Please set your PlanetScale connection string:');
    console.error('export DATABASE_URL="mysql://user:pass@host/submarine_faqs?ssl={\"rejectUnauthorized\":true}"');
    process.exit(1);
}

testPlanetScaleConnection();