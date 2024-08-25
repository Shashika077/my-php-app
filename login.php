<?php
$host = 'we-server.mysql.database.azure.com';
$port = 3306;
$username = 'creuugqssa';
$password = 'ZfiK0QRaD6$b7eii';
$database = 'web';

// Path to your SSL certificate
$ssl_ca = '/home/site/wwwroot/certs/ca-cert.pem'; // Ensure this path is correct

// Create a new MySQLi connection with SSL options
$mysqli = new mysqli();
$mysqli->ssl_set(null, null, $ssl_ca, null, null); // Set SSL options
$mysqli->real_connect($host, $username, $password, $database, $port, null, MYSQLI_CLIENT_SSL);

// Check if the connection was successful
if ($mysqli->connect_errno) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error]);
    exit;
}

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle JSON POST requests
$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$name = $data['name'] ?? ''; 
$name2 = $data['name2'] ?? ''; 

if (empty($email) || empty($password) || empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'Email, password, and user type are required.']);
    $mysqli->close();
    exit;
}

try {
    $stmt = null;
    $user = null;

    if ($name === 'admin') {
        // Check if user is an Admin
        $stmt = $mysqli->prepare("SELECT * FROM admins WHERE email = ?");
    } elseif ($name === 'employer') {
        // Check if user is an Employee
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE email = ?");
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user type.']);
        $mysqli->close();
        exit;
    }

    if ($stmt === false) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify the password
    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful.',
            'user' => [
                'name2' => $user['name2'] // or 'name' if that's the correct field name
            ]
        ]);
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
$mysqli->close();
?>
