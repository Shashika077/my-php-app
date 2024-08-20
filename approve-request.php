<?php
// Handle CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Load environment variables and database connection
require 'vendor/autoload.php'; // Composer autoload
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

include 'db.php'; // Ensure this file contains the mysqli connection setup

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
