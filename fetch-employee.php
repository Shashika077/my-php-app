<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

require_once 'vendor/autoload.php'; // Include Composer's autoload file for JWT decoding

use \Firebase\JWT\JWT;

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the JSON data from the request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true); // Decode JSON data to associative array

    // Check if JSON data contains a token
    if (!isset($data['token'])) {
        echo json_encode(['success' => false, 'message' => 'Token is required in JSON body']);
        exit;
    }

    $token = $data['token'];

    try {
        // Decode the token using JWT_SECRET_KEY from environment variables
        $secretKey = getenv('JWT_SECRET_KEY'); // Retrieve secret key from environment variables
        if (!$secretKey) {
            throw new Exception('JWT secret key not found in environment variables');
        }

        $decoded = JWT::decode($token, $secretKey, array('HS256'));

        // Return the decoded payload as JSON response
        echo json_encode(['success' => true, 'data' => (array)$decoded]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Token decoding failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Only POST method is allowed']);
}
?>
