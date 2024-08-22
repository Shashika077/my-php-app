<?php
// Handle CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Load environment variables and database connection
require 'vendor/autoload.php'; // Composer autoload
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

include 'db.php'; // Ensure this file contains the mysqli connection setup

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection is not initialized.']);
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

$conn->close();
?>
