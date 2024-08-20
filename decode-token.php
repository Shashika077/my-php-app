<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Fetch the token from POST body
$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'] ?? null;

if (!$token) {
    echo json_encode(['status' => 'error', 'message' => 'Token not provided.']);
    exit;
}

$key = $_ENV['JWT_SECRET_KEY'] ?? '';

// Debug output to verify key
file_put_contents('php://stderr', "JWT_SECRET_KEY: $key\n");

// Decode the JWT token
try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));

    // Extract user details
    $user = (array)$decoded; // Convert the decoded object to an array

    // Check if 'name' is part of the decoded token
    if (isset($user['name2'])) {
        $response = [
            'status' => 'success',
            'user' => [
                'email' => $user['email'],
                'name2' => $user['name2']
            ]
        ];
    } else {
        $response = [
            'status' => 'success',
            'user' => [
                'email' => $user['email']
                // Name not found
            ]
        ];
    }

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'Invalid token: ' . $e->getMessage()
    ];
}

echo json_encode($response);
?>
