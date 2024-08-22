<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Database connection settings
$host = 'we-server.mysql.database.azure.com'; // Replace with your host
$dbname = 'web';
$username = 'creuugqssa'; // Replace with your username
$password = 'ZfiK0QRaD6$b7eii'; // Replace with your password
$ssl_ca = '/home/site/wwwroot/certs/ca-cert.pem'; // Path to your SSL certificate

// Create connection
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
];

try {
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Function to execute SQL query and return result as JSON
function executeQuery($conn, $query, $params = []) {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Handle POST request to add a new sale
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Decode JSON data from the request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Check if all required fields are present
    if (isset($data['product_id'], $data['quantity'], $data['price'])) {
        // Assign values from $data to variables
        $product_id = $data['product_id'];
        $quantity = $data['quantity'];
        $price = $data['price'];

        // Calculate total revenue for the sale
        $rtotal = $quantity * $price;

        // Insert new sale into database
        $query_insert_sale = "INSERT INTO sell_r (product_id, date, quantity, price, rtotal) 
                              VALUES (:product_id, NOW(), :quantity, :price, :rtotal)";

        try {
            $stmt = $conn->prepare($query_insert_sale);
            $stmt->execute([
                ':product_id' => $product_id,
                ':quantity' => $quantity,
                ':price' => $price,
                ':rtotal' => $rtotal
            ]);

            // Return success response
            $response = [
                'success' => true,
                'message' => 'Sale added successfully'
            ];
            echo json_encode($response);
        } catch (PDOException $e) {
            // Return error response
            $response = [
                'success' => false,
                'error' => 'Error adding sale: ' . $e->getMessage()
            ];
            echo json_encode($response);
        }
    } else {
        // Return error response if required fields are missing
        $response = [
            'success' => false,
            'error' => 'Missing required fields: product_id, quantity, price'
        ];
        echo json_encode($response);
    }
}

// Close connection
$conn = null;
?>
