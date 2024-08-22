<?php
// CORS headers
header('Content-Type: application/json');

header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Allow POST and other methods
header('Access-Control-Allow-Headers: Content-Type'); // Allow Content-Type header


// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Database configuration
$host = 'we-server.mysql.database.azure.com';
$dbname = 'web';
$username = 'creuugqssa';
$password = 'ZfiK0QRaD6$b7eii';
$ssl_ca = '/home/site/wwwroot/certs/ca-cert.pem'; // Path to SSL certificate

// Create a database connection with SSL options
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Handle POST request to add product to bill
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

        // Fetch product details and check availability
        $stmt = $pdo->prepare('SELECT * FROM product WHERE product_id = :product_id FOR UPDATE');
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }

        if ($product['quantity'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Insufficient quantity']);
            exit;
        }

        // Calculate total price
        $totalPrice = $product['sprice'] * $quantity;

        // Update product quantity
        $newQuantity = $product['quantity'] - $quantity;
        $stmt = $pdo->prepare('UPDATE product SET quantity = :new_quantity WHERE product_id = :product_id');
        $stmt->bindParam(':new_quantity', $newQuantity);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();

        $pdo->commit();

        // Return product details and total price
        echo json_encode([
            'success' => true,
            'data' => [
                'name' => $product['name'],
                'price' => $product['sprice'],
                'quantity' => $quantity,
                'totalPrice' => $totalPrice
            ]
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $e->getMessage()]);
    }
}

// Handle GET request to fetch product details
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        exit;
    }

    $productId = $_GET['id'];
    try {
        $stmt = $pdo->prepare('SELECT * FROM product WHERE product_id = :product_id');
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            echo json_encode(['success' => true, 'data' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $e->getMessage()]);
    }
}
?>
