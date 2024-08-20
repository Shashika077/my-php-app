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

// Fetch all pending requests
$stmt = $conn->prepare("SELECT * FROM requests");
if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['status' => 'success', 'requests' => $requests]);

$stmt->close();
$conn->close();
?>
