<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000'); // Adjust to your frontend origin
header('Access-Control-Allow-Methods: POST, OPTIONS'); // Allow POST and OPTIONS methods
header('Access-Control-Allow-Headers: Content-Type'); // Allow Content-Type header
header('Access-Control-Max-Age: 3600'); // Cache preflight requests for 1 hour

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}

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

// Handle POST request to restore product quantity
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Parse JSON request body
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['product_id']) || !isset($data['quantity'])) {
        echo json_encode(['success' => false, 'message' => 'Product ID and Quantity are required']);
        exit;
    }

    $productId = $data['product_id'];
    $quantity = $data['quantity'];

    try {
        $pdo->beginTransaction();

        // Fetch current product quantity
        $stmt = $pdo->prepare('SELECT * FROM product WHERE product_id = :product_id FOR UPDATE');
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }

        // Calculate new quantity after restoring
        $newQuantity = $product['quantity'] + $quantity;

        // Update product quantity
        $stmt = $pdo->prepare('UPDATE product SET quantity = :new_quantity WHERE product_id = :product_id');
        $stmt->bindParam(':new_quantity', $newQuantity);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();

        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Product quantity restored successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $e->getMessage()]);
    }
}
?>
