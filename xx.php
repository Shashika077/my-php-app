<?php
if (isset($_POST['submititem'])) {
    require_once 'db.php'; // Database connection file

    // Retrieve form data
    $item_name = $_POST['Iname'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $description = isset($_POST['description']) ? $_POST['description'] : NULL;

    // Basic validation
    if (empty($item_name) || empty($quantity) || empty($price) || empty($category)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required.'
        ]);
        exit();
    }

    try {
        // Fetch the category ID based on the category name
        $sql = "SELECT categoryId FROM category WHERE category = ?";
        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            throw new Exception("SQL preparation error");
        }

        mysqli_stmt_bind_param($stmt, "s", $category);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $categoryId = $row['categoryId'];
        } else {
            throw new Exception("Category not found");
        }

        // Handling the image upload (if applicable)
        $target_file = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['image']['name'];
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($image);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if the file is an image
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                throw new Exception("File is not an image");
            }

            // Try to upload the file
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                throw new Exception("Failed to upload image");
            }
        }

        // Prepare and execute the SQL statement
        $sql = "INSERT INTO items (item_name, image_path, quantity, price, categoryId, description) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            throw new Exception("SQL preparation error");
        }

        // Bind parameters and execute statement
        mysqli_stmt_bind_param($stmt, "ssidis", $item_name, $target_file, $quantity, $price, $categoryId, $description);
        mysqli_stmt_execute($stmt);

        // Success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Item added successfully.'
        ]);
        exit();
    } catch (Exception $e) {
        // Log the error message and provide a user-friendly message
        error_log($e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit();
    }
}
?>
