<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Attempt a simple query
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        echo json_encode([
            "status" => "success",
            "message" => "Database connection established successfully.",
            "database" => DB_NAME,
            "host" => DB_HOST
        ]);
    } else {
        throw new Exception("Query failed.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
}
?>
