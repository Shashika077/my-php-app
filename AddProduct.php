<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: POST'); // Allow POST requests
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Allow specific headers

// Database configuration
$host = 'we-server.mysql.database.azure.com';
$dbname = 'web';
$username = 'creuugqssa';
$password = 'ZfiK0QRaD6$b7eii';
$ssl_ca = '/home/site/wwwroot/certs/ca-cert.pem'; // Path to SSL certificate

// Create a database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if image file is uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Image upload failed.']);
        exit;
    }

    // Check if required fields are set
    $requiredFields = ['product_id', 'name', 'bprice', 'sprice', 'category', 'quantity'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            exit;
        }
    }

    $productId = $_POST['product_id'];
    $name = $_POST['name'];
    $bprice = $_POST['bprice'];
    $sprice = $_POST['sprice'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];

    // Handle image upload
    $image = $_FILES['image'];
    $uploadDir = 'uploads/';
    $uploadFile = $uploadDir . basename($image['name']);

    // Check if upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it does not exist
    }

    // Attempt to move the uploaded file
    if (!move_uploaded_file($image['tmp_name'], $uploadFile)) {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
        exit;
    }

    // Calculate btotal
    $btotal = $bprice * $quantity;

    // Prepare and execute the SQL query to insert product
    try {
        $stmt = $pdo->prepare('INSERT INTO product (product_id, name, bprice, sprice, category, quantity, btotal, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$productId, $name, $bprice, $sprice, $category, $quantity, $btotal, $uploadFile]);

        echo json_encode(['success' => true, 'message' => 'Product added successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
