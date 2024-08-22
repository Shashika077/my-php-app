<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database connection settings
$host = 'we-server.mysql.database.azure.com'; // Replace with your host
$dbname = 'web';
$username = 'creuugqssa'; // Replace with your username
$password = 'ZfiK0QRaD6$b7eii'; // Replace with your password
$ssl_ca = '/home/site/wwwroot/certs/ca-cert.pem'; // Path to your SSL certificate

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
    ];

    $conn = new PDO($dsn, $username, $password, $options);

    // Decode JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if billItems and total are provided
    if (isset($data['billItems']) && isset($data['total'])) {
        $billItems = $data['billItems'];
        $total = $data['total'];

        $conn->beginTransaction();

        foreach ($billItems as $item) {
            $productId = $item['id'];
            $quantity = $item['quantity'];
            $sprice = $item['price']; // Selling price
            $bprice = $item['bprice']; // Buying price
            $name = $item['name'];
            $stotal = $sprice * $quantity; // Total selling price for the item
            $btotal = $bprice * $quantity; // Total buying price for the item

            // Insert into sells table
            $insertStmt = $conn->prepare("INSERT INTO sells (product_id, name, sprice, bprice, unit, stotal, btotal, date) 
                                         VALUES (:productId, :name, :sprice, :bprice, :quantity, :stotal, :btotal, CURDATE())");
            $insertStmt->bindParam(':productId', $productId);
            $insertStmt->bindParam(':name', $name);
            $insertStmt->bindParam(':sprice', $sprice);
            $insertStmt->bindParam(':bprice', $bprice);
            $insertStmt->bindParam(':quantity', $quantity);
            $insertStmt->bindParam(':stotal', $stotal);
            $insertStmt->bindParam(':btotal', $btotal);
            $insertStmt->execute();

            // Update product quantity
            $stmt = $conn->prepare("UPDATE product SET quantity = quantity - :quantity WHERE product_id = :productId");
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':productId', $productId);
            $stmt->execute();
        }

        $conn->commit();

        echo json_encode(array("success" => true, "message" => "Bill submitted successfully."));
    } else {
        echo json_encode(array("success" => false, "message" => "Missing billItems or total in request."));
    }
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(array("success" => false, "message" => "Database error: " . $e->getMessage()));
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(array("success" => false, "message" => "Error: " . $e->getMessage()));
}
?>
