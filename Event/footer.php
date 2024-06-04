<?php

$config = include('config.php');

$host = $config['host'];
$port = $config['port'];
$service_name = $config['service_name'];
$oracleUsername = $config['username'];
$oraclePassword = $config['password'];

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
        <p>&copy; <?php echo date('Y'); ?> PUDUCHERRY TECHNOLOGICAL UNIVERSITY. All rights reserved.</p>
        <p>Contact Us: info@ptuniv.edu.in | 0413-2655281-288</p>
        <p>Address: East coast Road, Pillaichavady, Puducherry, 605 014</p>
    </div>
</body>
</html>
