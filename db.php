<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "we-server.mysql.database.azure.com";
$username = "creuugqssa";
$password = "ZfiK0QRaD6$b7eii";
$dbname = "web";

// Path to CA certificate
$ssl_ca_path = '/path/to/DigiCertGlobalRootCA.crt.pem';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, 3306, $ssl_ca_path);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully.<br>";

// Run a test query
$sql = "SHOW TABLES";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Tables in the database:<br>";
    while ($row = $result->fetch_assoc()) {
        echo $row["Tables_in_web"] . "<br>";
    }
} else {
    echo "No tables found in the database.";
}

$conn->close();
?>
