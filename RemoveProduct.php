<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Adjust this to your frontend domain in production
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Allow POST and GET requests
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Function to handle the deletion
function deleteProduct($product_id, $conn) {
    try {
        // Start a transaction
        $conn->beginTransaction();

        // Prepare and execute delete statement
        $stmt_product = $conn->prepare("DELETE FROM product WHERE product_id = ?");
        $stmt_product->execute([$product_id]);

        // Commit transaction if delete is successful
        $conn->commit();
        echo json_encode(["message" => "Product deleted successfully"]);
    } catch (PDOException $e) {
        // Rollback transaction if delete fails
        $conn->rollBack();
        echo json_encode(["error" => "Error deleting product: " . $e->getMessage()]);
    }
}

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['product_id'])) {
        $product_id = $_GET['product_id'];
        deleteProduct($product_id, $conn);
    } else {
        echo json_encode(["error" => "Product ID not provided."]);
    }

// Handle POST request with JSON data
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true); // Decode JSON to associative array

    if (isset($data['product_id'])) {
        $product_id = $data['product_id'];
        deleteProduct($product_id, $conn);
    } else {
        echo json_encode(["error" => "Product ID not provided."]);
    }

// Method not allowed
} else {
    echo json_encode(["error" => "Invalid request method. Only GET and POST are allowed."]);
}
?>
