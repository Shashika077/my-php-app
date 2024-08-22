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
$secretKey = $data['secretKey'] ?? '';

if (empty($email) || empty($password) || empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'Email, password, and user type are required.']);
    exit;
}

try {
    if ($name === 'admin') {
        // Admin registration
        if ($secretKey !== '1234') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid secret key.']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO admins (email, password, name, secret_key) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("ssss", $email, password_hash($password, PASSWORD_BCRYPT), $name, $secretKey);
    } else {
        // User (Employee) registration
        $stmt = $conn->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("sss", $email, password_hash($password, PASSWORD_BCRYPT), $name);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Registration successful.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Insert failed: ' . $stmt->error]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
