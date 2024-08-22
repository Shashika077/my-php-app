<?php
// Handle CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database connection details
$host = 'we-server.mysql.database.azure.com';
$port = 3306;
$username = 'creuugqssa';
$password = 'ZfiK0QRaD6$b7eii';
$database = 'web';

// Path to your SSL certificate
$ssl_ca = '/home/site/wwwroot/certs/ca-cert.pem'; // Ensure this path is correct

// Create a new MySQLi connection with SSL options
$conn = new mysqli();
$conn->ssl_set(null, null, $ssl_ca, null, null); // Set SSL options
$conn->real_connect($host, $username, $password, $database, $port, null, MYSQLI_CLIENT_SSL);

// Check if the connection was successful
if ($conn->connect_errno) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

try {
    // Fetch all employees
    $stmt = $conn->prepare("SELECT * FROM users");
    if ($stmt === false) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $employees = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'employees' => $employees]);

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}

// Close the connection
$conn->close();
?>
