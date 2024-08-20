<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
            $price = $item['price'];
            $name = $item['name'];
            $stotal = $price * $quantity;
            $btotal = $stotal; // Assuming buying price is the same as selling price for this example

            // Insert into sells table
            $insertStmt = $conn->prepare("INSERT INTO sells (product_id, name, sprice, bprice, unit, stotal, btotal, date) 
                                         VALUES (:productId, :name, :sprice, :bprice, :quantity, :stotal, :btotal, CURDATE())");
            $insertStmt->bindParam(':productId', $productId);
            $insertStmt->bindParam(':name', $name);
            $insertStmt->bindParam(':sprice', $price);
            $insertStmt->bindParam(':bprice', $price); // Assuming buying price equals selling price
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
