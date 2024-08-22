<?php
// CORS headers
header('Access-Control-Allow-Origin: https://webdev2-def75.web.app');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database connection details
$host = 'we-server.mysql.database.azure.com';
$port = 3306;
$username = 'creuugqssa';
$password = 'ZfiK0QRaD6$b7eii';
$database = 'web';

// Path to your SSL certificate
$ssl_ca = '/home/site/wwwroot/certs/ca-cert.pem'; // Ensure this path is correct

// Create a new MySQLi connection with SSL options
$mysqli = new mysqli();
$mysqli->ssl_set(null, null, $ssl_ca, null, null); // Set SSL options
$mysqli->real_connect($host, $username, $password, $database, $port, null, MYSQLI_CLIENT_SSL);

// Check if the connection was successful
if ($mysqli->connect_errno) {
    die(json_encode(['status' => 'error', 'message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error]));
}

// Handle JSON POST requests
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['expenses']) || !is_array($data['expenses'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing expenses data.']);
    $mysqli->close();
    exit();
}

try {
    // Prepare the statement for inserting expenses
    $stmt = $mysqli->prepare("INSERT INTO expenses (amount, category, created_at) VALUES (?, ?, NOW())");
    
    if ($stmt === false) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }

    // Bind parameters
    $stmt->bind_param('ss', $amount, $category);

    // Insert each expense into the database
    foreach ($data['expenses'] as $expense) {
        $amount = $expense['amount'] ?? null;
        $category = $expense['category'] ?? null;

        // Check if the required data is present
        if ($amount === null || $category === null) {
            echo json_encode(['status' => 'error', 'message' => 'Missing amount or category in expense data.']);
            $mysqli->close();
            exit();
        }

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception('Error executing query: ' . $stmt->error);
        }
    }

    // If all expenses are inserted successfully
    echo json_encode(['status' => 'success', 'message' => 'Expenses inserted successfully.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}

// Close the statement and connection
$stmt->close();
$mysqli->close();
?>
