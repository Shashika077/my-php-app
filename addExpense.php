<?php
// Allow requests from localhost:3001 (your React frontend)
header('Access-Control-Allow-Origin: http://localhost:3001');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true); // Decode JSON data

// Insert each expense into the database
foreach ($data['expenses'] as $expense) {
    $amount = $expense['amount'];
    $category = $expense['category'];
    $sql = "INSERT INTO expenses (amount, category, created_at) 
            VALUES ('$amount', '$category', current_timestamp())";

    if ($conn->query($sql) !== TRUE) {
        echo json_encode(array('error' => "Error: " . $sql . "<br>" . $conn->error));
        $conn->close();
        exit();
    }
}

// If all expenses are inserted successfully
echo json_encode(array('success' => true));

$conn->close();
?>
