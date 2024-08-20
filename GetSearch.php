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

// Fetch products based on search term
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

try {
    if (!empty($searchTerm)) {
        // Use prepared statement to prevent SQL injection
        $stmt = $pdo->prepare('SELECT product_id, name, category, quantity, bprice, sprice FROM product WHERE product_id LIKE :search OR name LIKE :search OR category LIKE :search');
        $stmt->execute(['search' => "%$searchTerm%"]);
    } else {
        // If no search term is provided, return an empty array
        $products = [];
        echo json_encode(['success' => true, 'data' => $products]);
        exit;
    }

    // Fetch all matching products
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $products]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $e->getMessage()]);
}
?>
