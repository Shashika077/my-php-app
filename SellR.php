<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to execute SQL query and return result as JSON
function executeQuery($conn, $query) {
    $result = $conn->query($query);
    if ($result === false) {
        return null;
    }
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
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
                              VALUES ('$product_id', NOW(), $quantity, $price, $rtotal)";
        
        if ($conn->query($query_insert_sale) === TRUE) {
            // Return success response
            $response = [
                'success' => true,
                'message' => 'Sale added successfully'
            ];
            echo json_encode($response);
        } else {
            // Return error response
            $response = [
                'success' => false,
                'error' => 'Error adding sale: ' . $conn->error
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
$conn->close();
?>
