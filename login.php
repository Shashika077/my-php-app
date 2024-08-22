<?php
require 'vendor/autoload.php'; // Composer autoload

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include database connection
include 'db.php'; // Ensure this file properly sets up $conn or $mysqli

// Handle JSON POST requests
$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$name = $data['name'] ?? ''; 
$name2 = $data['name2'] ?? ''; 

if (empty($email) || empty($password) || empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'Email, password, and user type are required.']);
    exit;
}

try {
    $stmt = null;
    $user = null;

    if ($name === 'admin') {
        // Check if user is an Admin
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    } elseif ($name === 'employer') {
        // Check if user is an Employee
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user type.']);
        exit;
    }

    if ($stmt === false) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify the password
    if ($user && password_verify($password, $user['password'])) {
        echo json_encode(['status' => 'success', 'message' => 'Login successful.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}

// Close the statement and connection
if ($stmt) {
    $stmt->close();
}
$conn->close();
?>
