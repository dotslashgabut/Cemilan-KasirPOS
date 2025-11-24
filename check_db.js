import { Sequelize } from 'sequelize';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Function to parse PHP config file for credentials
function getPhpConfig() {
    try {
        const configPath = path.join(__dirname, 'php_server', 'config.php');
        const content = fs.readFileSync(configPath, 'utf8');

        const hostMatch = content.match(/define\('DB_HOST',\s*'([^']*)'\);/);
        const nameMatch = content.match(/define\('DB_NAME',\s*'([^']*)'\);/);
        const userMatch = content.match(/define\('DB_USER',\s*'([^']*)'\);/);
        const passMatch = content.match(/define\('DB_PASS',\s*'([^']*)'\);/);

        return {
            host: hostMatch ? hostMatch[1] : 'localhost',
            database: nameMatch ? nameMatch[1] : 'cemilan_app_db',
            username: userMatch ? userMatch[1] : 'root',
            password: passMatch ? passMatch[1] : '',
        };
    } catch (error) {
        console.error('Error reading PHP config:', error);
        return null;
    }
}

const config = getPhpConfig();

if (!config) {
    console.error('Could not determine database configuration.');
    process.exit(1);
}

console.log('Using configuration from php_server/config.php:');
console.log(`Host: ${config.host}`);
console.log(`Database: ${config.database}`);
console.log(`User: ${config.username}`);

const sequelize = new Sequelize(
    config.database,
    config.username,
    config.password,
    {
        host: config.host,
        dialect: 'mysql',
        logging: false,
    }
);

async function testConnection() {
    try {
        await sequelize.authenticate();
        console.log('✅ Connection has been established successfully.');
        process.exit(0);
    } catch (error) {
        console.error('❌ Unable to connect to the database:', error);
        process.exit(1);
    }
}

testConnection();
