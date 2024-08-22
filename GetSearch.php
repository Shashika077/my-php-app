<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET'); // Allow GET requests

// Database configuration
$host = 'we-server.mysql.database.azure.com'; // Replace with your host
$dbname = 'web';
$username = 'creuugqssa'; // Replace with your username
$password = 'ZfiK0QRaD6$b7eii'; // Replace with your password
$ssl_ca = '/home/site/wwwroot/certs/ca-cert.pem'; // Path to your SSL certificate

// Create a database connection
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
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
