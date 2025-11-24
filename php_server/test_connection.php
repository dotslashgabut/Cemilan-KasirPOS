<?php
/**
 * Test Database Connection
 * URL: /php_server/test_connection.php
 * 
 * Usage: 
 * - Browser: http://localhost/cemilan-kasirpos/php_server/test_connection.php
 * - CLI: php test_connection.php
 */

require_once 'config.php';

header('Content-Type: application/json');

try {
    // Test basic connection
    $stmt = $pdo->query("SELECT DATABASE() as current_db, VERSION() as mysql_version");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get table list
    $tablesStmt = $pdo->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get table row counts
    $tableCounts = [];
    foreach ($tables as $table) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $count = $countStmt->fetchColumn();
        $tableCounts[$table] = $count;
    }
    
    // Success response
    echo json_encode([
        'status' => 'success',
        'message' => '✅ Database connection successful!',
        'database' => $result['current_db'],
        'mysql_version' => $result['mysql_version'],
        'tables' => $tables,
        'table_counts' => $tableCounts,
        'total_tables' => count($tables),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '❌ Database connection failed!',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>
