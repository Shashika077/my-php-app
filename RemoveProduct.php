<?php
// Include database connection
include 'db.php';
header("Access-Control-Allow-Origin: *"); // Adjust this to your frontend domain in production
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Allow POST requests
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Function to handle the deletion
function deleteProduct($product_id, $conn) {
    // Start a transaction
    $conn->begin_transaction();

    // Delete from product table
    if ($stmt_product = $conn->prepare("DELETE FROM product WHERE product_id = ?")) {
        $stmt_product->bind_param("s", $product_id);

        // Execute the statement
        if ($stmt_product->execute()) {
            $conn->commit(); // Commit transaction if delete is successful
            echo json_encode(["message" => "Product deleted successfully"]);
        } else {
            $conn->rollback(); // Rollback if delete fails
            echo json_encode(["error" => "Error deleting product: " . $stmt_product->error]);
        }

        // Close the product statement
        $stmt_product->close();
    } else {
        echo json_encode(["error" => "Error preparing product delete statement: " . $conn->error]);
    }

    // Close the connection
    $conn->close();
}

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['product_id'])) {
        $product_id = $_GET['product_id'];
        deleteProduct($product_id, $conn);
    } else {
        echo json_encode(["error" => "Product ID not provided."]);
    }

// Handle POST request with JSON data
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true); // Decode JSON to associative array

    if (isset($data['product_id'])) {
        $product_id = $data['product_id'];
        deleteProduct($product_id, $conn);
    } else {
        echo json_encode(["error" => "Product ID not provided."]);
    }

// Method not allowed
} else {
    echo json_encode(["error" => "Invalid request method. Only GET and POST are allowed."]);
}
?>
