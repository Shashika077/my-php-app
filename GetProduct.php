
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET'); // Allow GET requests

// Database configuration
$host = 'localhost';
$dbname = 'web';
$username = 'root';
$password = '';

// Create a database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Fetch all products
try {
    $stmt = $pdo->query('SELECT * FROM product');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $products]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $e->getMessage()]);
}
?>
