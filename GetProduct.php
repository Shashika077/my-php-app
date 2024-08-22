<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET'); // Allow GET requests

// Database configuration
$host = 'we-server.mysql.database.azure.com';
$dbname = 'web';
$username = 'creuugqssa';
$password = 'ZfiK0QRaD6$b7eii';
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

// Fetch all products
try {
    $stmt = $pdo->query('SELECT * FROM product');
    $products = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $products]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $e->getMessage()]);
}
?>
