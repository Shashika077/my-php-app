<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database configuration
$host = 'we-server.mysql.database.azure.com';
$port = 3306;
$username = 'creuugqssa';
$password = 'ZfiK0QRaD6$b7eii';
$dbname = 'web';

// Path to your SSL certificate
$ssl_ca = '/home/site/wwwroot/certs/ca-cert.pem'; // Ensure this path is correct

// Create connection with SSL
$conn = new mysqli();
$conn->ssl_set(null, null, $ssl_ca, null, null);
$conn->real_connect($host, $username, $password, $dbname, $port, null, MYSQLI_CLIENT_SSL);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);
$requestId = isset($data['id']) ? intval($data['id']) : 0;

if ($requestId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request ID']);
    exit();
}

// Prepare and execute the query to delete the request
$stmt = $conn->prepare('DELETE FROM requests WHERE id = ?');
$stmt->bind_param('i', $requestId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Request declined successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to decline request']);
}

// Close connection
$stmt->close();
$conn->close();
?>
