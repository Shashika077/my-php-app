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
$password2 = $data['password2'] ?? '';
$name = $data['name'] ?? ''; 
$name2 = $data['name2'] ?? ''; 
$secretKey = $data['secretKey'] ?? ''; // Secret key for Admins

// Validate input fields
if (empty($email) || empty($password) || empty($password2) || empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'Email, password, password confirmation, and user type are required.']);
    $mysqli->close();
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    $mysqli->close();
    exit;
}

// Check if passwords match and validate password length
if ($password !== $password2) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
    $mysqli->close();
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long.']);
    $mysqli->close();
    exit;
}

// Check if the email already exists
$emailCheckQuery = ($name === 'admin') ? 
    "SELECT COUNT(*) FROM admins WHERE email = ?" : 
    "SELECT COUNT(*) FROM requests WHERE email = ?";

$emailCheckStmt = $mysqli->prepare($emailCheckQuery);
if ($emailCheckStmt === false) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $mysqli->error]);
    $mysqli->close();
    exit;
}

$emailCheckStmt->bind_param('s', $email);
$emailCheckStmt->execute();
$emailCheckStmt->bind_result($emailCount);
$emailCheckStmt->fetch();
$emailCheckStmt->close();

if ($emailCount > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already registered.']);
    $mysqli->close();
    exit;
}

try {
    $stmt = null;
    $message = '';

    if ($name === 'admin') {
        // Admin registration
        if ($secretKey !== '1234') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid secret key. Admin account creation failed.']);
            $mysqli->close();
            exit;
        }
        $stmt = $mysqli->prepare("INSERT INTO admins (email, password, name, secret_key) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $mysqli->error);
        }
        $stmt->bind_param("ssss", $email, password_hash($password, PASSWORD_BCRYPT), $name, $secretKey);
        $message = 'Registration successful.';
    } elseif ($name === 'employer') {
        // User (Employee) registration
        $stmt = $mysqli->prepare("INSERT INTO requests (email, password, name) VALUES (?, ?, ?)");
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $mysqli->error);
        }
        $stmt->bind_param("sss", $email, password_hash($password, PASSWORD_BCRYPT), $name2);
        $message = 'Registration successful. Please wait for your approval.';
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user type.']);
        $mysqli->close();
        exit;
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => $message]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $stmt->error]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}

// Close the connection
$mysqli->close();
?>
