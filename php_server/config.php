<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cemilan_app_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// CORS Settings
$allowed_origins = [
    'https://cemilan-app.test',
    'http://cemilan-app.test',
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'http://localhost:3000',
    'http://127.0.0.1:3000',
    'http://localhost',
    'http://127.0.0.1'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (isset($_SERVER['REQUEST_METHOD'])) {
    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        // Default for tools like Postman or direct browser access if not in list (optional, or strict block)
        // header("Access-Control-Allow-Origin: *"); // Keep commented out for security
    }
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
}

// Security Headers
if (isset($_SERVER['REQUEST_METHOD'])) {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    // header("Strict-Transport-Security: max-age=31536000; includeSubDomains"); // Enable if using HTTPS

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Database Connection
try {
    // Check if PDO MySQL driver is available
    if (!extension_loaded('pdo_mysql')) {
        throw new PDOException('PDO MySQL driver is not installed. Please install php-mysql extension.');
    }
    
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->exec("SET NAMES utf8mb4");
} catch(PDOException $exception) {
    $errorMsg = "DB Connection Error: " . $exception->getMessage();
    file_put_contents('php_error.log', date('[Y-m-d H:i:s] ') . $errorMsg . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        "error" => "Database connection failed.",
        "details" => $exception->getMessage()
    ]);
    exit();
}
?>
