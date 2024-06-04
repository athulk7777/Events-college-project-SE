<?php
// Database connection details
$host = 'localhost';
$port = '1521';
$service_name = 'flight';
$oracleUsername = 'system';
$oraclePassword = 'abhinav2';

// Connection string
$connStr = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = $port))(CONNECT_DATA = (SERVICE_NAME = $service_name)))";

// Establish connection
$connection = oci_connect($oracleUsername, $oraclePassword, $connStr);

if (!$connection) {
    $error = oci_error();
    die("Connection failed: " . $error['message']);
}

// Fetch sponsors' names ordered by amount in descending order
$query = "SELECT SPONSOR FROM SPONSORS ORDER BY AMOUNT DESC";
$stid = oci_parse($connection, $query);
oci_execute($stid);

$sponsors = [];
while ($row = oci_fetch_assoc($stid)) {
    $sponsors[] = $row['SPONSOR']; // Only store the sponsor name in the array
}

oci_free_statement($stid);
oci_close($connection);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .footer {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .footer .sponsor-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .footer .sponsor-list li {
            margin: 5px 0;
            display: inline;
        }
        .footer .sponsor-list li:not(:last-child):after {
            content: " | "; /* Add a pipe symbol after each sponsor except the last one */
            color: #fff;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="footer">
        <h3>Thank You to Our Sponsors</h3>
        <ul class="sponsor-list">
            <?php foreach ($sponsors as $sponsor): ?>
                <li><?php echo htmlspecialchars($sponsor); ?></li>
            <?php endforeach; ?>
        </ul>
        <p>&copy; <?php echo date('Y'); ?> Your College Name. All rights reserved.</p>
        <p>Contact Us: info@yourcollege.edu | +1 234 567 8900</p>
        <p>Address: 123 College St, City, State, ZIP Code</p>
    </div>
</body>
</html>
