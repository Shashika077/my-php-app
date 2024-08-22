<?php
// Handle CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database configuration
$host = 'we-server.mysql.database.azure.com';
$port = 3306;
$username = 'creuugqssa';
$password = 'ZfiK0QRaD6$b7eii';
$database = 'web';
$ssl_ca = '/home/site/wwwroot/certs/ca-cert.pem'; // Path to SSL certificate

// Create a new MySQLi connection with SSL options
$conn = new mysqli();
$conn->ssl_set(null, null, $ssl_ca, null, null); // Set SSL options
$conn->real_connect($host, $username, $password, $database, $port, null, MYSQLI_CLIENT_SSL);

// Check if the connection was successful
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to connect to MySQL: ' . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'Request ID is required.']);
    exit;
}

// Fetch the request details
$fetchStmt = $conn->prepare("SELECT * FROM requests WHERE id = ?");
if ($fetchStmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$fetchStmt->bind_param('i', $id);
$fetchStmt->execute();
$result = $fetchStmt->get_result();
$request = $result->fetch_assoc();

if (!$request) {
    echo json_encode(['status' => 'error', 'message' => 'Request not found.']);
    exit;
}

// Move the request to users table
$insertStmt = $conn->prepare("INSERT INTO users (name, email, password, status) VALUES (?, ?, ?, ?)");
if ($insertStmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

// Bind parameters and set the status to 'approved'
$status = 'approved';
$insertStmt->bind_param("ssss", $request['name'], $request['email'], $request['password'], $status);

if ($insertStmt->execute()) {
    // Optionally, delete the approved request if needed
    $deleteStmt = $conn->prepare("DELETE FROM requests WHERE id = ?");
    if ($deleteStmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $deleteStmt->bind_param('i', $id);
    $deleteStmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Request approved and user added.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add user.']);
}

$insertStmt->close();
$fetchStmt->close();
$deleteStmt->close();
$conn->close();
?>
