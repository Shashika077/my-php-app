<?php
// Enable CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection settings
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

// Function to execute SQL query and return result as JSON
function executeQuery($conn, $query) {
    $result = $conn->query($query);
    if ($result === false) {
        return null;
    }
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

// Calculate Revenue, Purchases, Expenses, and Profit
$query_revenue = "SELECT SUM(stotal) - SUM(btotal) AS total_revenue FROM sells";
$query_purchases = "SELECT SUM(btotal) AS total_purchases FROM product";
$query_expenses = "SELECT SUM(amount) AS total_expenses FROM expenses";
$query_sellr = "SELECT SUM(rtotal) AS total_sellr FROM sell_r";

// Calculate Profit
$total_revenue = executeQuery($conn, $query_revenue);
$total_purchases = executeQuery($conn, $query_purchases);
$total_expenses = executeQuery($conn, $query_expenses);
$total_sellr = executeQuery($conn, $query_sellr);

$revenue = isset($total_revenue[0]['total_revenue']) ? floatval($total_revenue[0]['total_revenue']) : 0;
$purchases = isset($total_purchases[0]['total_purchases']) ? floatval($total_purchases[0]['total_purchases']) : 0;
$expenses = isset($total_expenses[0]['total_expenses']) ? floatval($total_expenses[0]['total_expenses']) : 0;
$sellr = isset($total_sellr[0]['total_sellr']) ? floatval($total_sellr[0]['total_sellr']) : 0;

$profit = $revenue - $expenses;

// Fetch Top Selling Items
$query_top_items = "SELECT product_id, SUM(unit) AS total_quantity 
                   FROM sells 
                   GROUP BY product_id 
                   ORDER BY total_quantity DESC 
                   LIMIT 5";
$top_items = executeQuery($conn, $query_top_items);

// Fetch Expense Categories
$query_expense_categories = "SELECT category, SUM(amount) AS total_amount 
                             FROM expenses 
                             GROUP BY category";
$expense_categories = executeQuery($conn, $query_expense_categories);

// Prepare response data including profit and sell_r total
$response = [
    'revenue' => $revenue,
    'purchases' => $purchases,
    'expenses' => $expenses,
    'profit' => $profit,
    'sellr' => $sellr,
    'top_items' => $top_items,
    'expense_categories' => $expense_categories // Add expense categories to response
];

// Close connection
$conn->close();

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
