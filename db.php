<?php
$host = 'we-server.mysql.database.azure.com';
$port = 3306;
$username = 'creuugqssa';
$password = 'ZfiK0QRaD6$b7eii';
$database = 'web';

// Path to your SSL certificate
$ssl_ca = '/home/site/wwwroot/certs/ca-cert.pem'; // Adjust path as needed

// Create a new MySQLi connection with SSL options
$mysqli = new mysqli();
$mysqli->ssl_set(null, null, $ssl_ca, null, null); // Set SSL options
$mysqli->real_connect($host, $username, $password, $database, $port, null, MYSQLI_CLIENT_SSL);

// Check if the connection was successful
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// Verify if SSL is enabled
$result = $mysqli->query("SHOW VARIABLES LIKE 'have_ssl'");
if ($result) {
    $row = $result->fetch_assoc();
    if ($row['Value'] != 'YES') {
        die("SSL is not enabled on the MySQL connection.");
    }
} else {
    die("Failed to check SSL status: " . $mysqli->error);
}

// Perform a test query
$result = $mysqli->query("SELECT NOW() AS now");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Current time: " . $row['now'];
} else {
    die("Query failed: " . $mysqli->error);
}

// Close the connection
$mysqli->close();
?>
