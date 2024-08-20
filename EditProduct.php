<?php
// Include database connection
include 'db.php';
header("Access-Control-Allow-Origin: *"); // Adjust this to your frontend domain in production
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Allow POST requests
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true); // Decode JSON to associative array

// Check if the product_id is provided
if (isset($data['product_id'])) {
    $product_id = $data['product_id'];

    // Initialize variables for SQL query
    $setClause = [];
    $params = [];
    $types = "";

    // Build the dynamic SQL query
    if (isset($data['name'])) {
        $setClause[] = "name = ?";
        $params[] = $data['name'];
        $types .= "s";
    }
    if (isset($data['category'])) {
        $setClause[] = "category = ?";
        $params[] = $data['category'];
        $types .= "s";
    }
    if (isset($data['bprice'])) {
        $setClause[] = "bprice = ?";
        $params[] = $data['bprice'];
        $types .= "d"; // 'd' for double/decimal
    }
    if (isset($data['sprice'])) {
        $setClause[] = "sprice = ?";
        $params[] = $data['sprice'];
        $types .= "d"; // 'd' for double/decimal
    }
    if (isset($data['quantity'])) {
        $setClause[] = "quantity = ?";
        $params[] = $data['quantity'];
        $types .= "i"; // 'i' for integer
    }

    if (count($setClause) > 0) {
        // Join all the clauses and prepare the SQL statement
        $setClauseStr = implode(", ", $setClause);
        $sql = "UPDATE product SET $setClauseStr WHERE product_id = ?";

        // Prepare and execute the update query
        if ($stmt = $conn->prepare($sql)) {
            // Bind the parameters, including product_id at the end
            $params[] = $product_id;
            $types .= "s"; // 's' for string (product_id)

            // Bind the parameters dynamically
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Product updated successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error updating product: " . $stmt->error]);
            }

            // Close the statement
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No fields to update."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Product ID not provided."]);
}

// Close the connection
$conn->close();
?>
