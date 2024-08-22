
$host = 'we-server.mysql.database.azure.com';
$port = 3306;
$username = 'creuugqssa';
$password = 'ZfiK0QRaD6$b7eii';
$database = 'web';

// Path to your SSL certificate
$ssl_ca = '/home/site/wwwroot/certs/ca-cert.pem'; // Adjust path as needed

// Create a new MySQLi connection
$mysqli = new mysqli($host, $username, $password, $database, $port);

// Check connection
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// Set SSL options
$mysqli->ssl_set(null, null, $ssl_ca, null, null);

// Verify if SSL is enabled
$result = $mysqli->query("SHOW VARIABLES LIKE 'have_ssl'");
$row = $result->fetch_assoc();
if ($row['Value'] != 'YES') {
    die("SSL is not enabled on the MySQL connection.");
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
