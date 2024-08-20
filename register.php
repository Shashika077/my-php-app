<?php
require 'vendor/autoload.php'; // Composer autoload
use \Firebase\JWT\JWT;
use Dotenv\Dotenv;

// CORS headers
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Include database connection
include 'db.php'; 

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection is not initialized.']);
    exit;
}

// Handle JSON POST requests
$data = json_decode(file_get_contents('php://input'), true);

$name = $data['name'] ?? '';
$name2 = $data['name2'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$password2 = $data['password2'] ?? '';
$secretKey = $data['secretKey'] ?? ''; // Secret key for Admins

if (empty($name) || empty($email) || empty($password) || empty($password2)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    exit;
}
if ($password !== $password2) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
    exit;
}
if (strlen($password) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long.']);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    if ($name === 'admin') {
        // Admin registration
        if ($secretKey !== '1234') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid secret key. Admin account creation failed.']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO admins (email, password, name, secret_key) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("ssss", $email, $hashedPassword, $name, $secretKey);
    } else {
        // User (Employee) registration
        $stmt = $conn->prepare("INSERT INTO requests (email, password, name) VALUES (?, ?, ?)");
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("sss", $email, $hashedPassword, $name2);
    }

    if ($stmt->execute()) {
        $key = $_ENV['JWT_SECRET_KEY'];
        $payload = [
            'email' => $email,
            'exp' => time() + 3600 // Token expires in 1 hour
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        echo json_encode(['status' => 'success', 'message' => 'Registration successful : Please wait until you receive approval.', 'token' => $token]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed.']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
