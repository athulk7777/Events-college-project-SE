<?php
session_start();

$config = include('../config.php');

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

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    header('Location: user_login.php');
    exit();
}

if (isset($_SESSION['userid'])) {
    echo '<script>console.log("UserID: ' . $_SESSION['userid'] . '")</script>';
}else{
    echo 'no userid';
}

// Fetch event details
if (isset($_GET['Eid'])) {
    $eid = $_GET['Eid'];

    $query = "SELECT Ename, Entry_fees, EType, ELocation, EDate, ETime, SLOTS, TOTAL_MEMBERS FROM Events WHERE Eid = :eid";
    $stmt = oci_parse($connection, $query);

    oci_bind_by_name($stmt, ':eid', $eid);
    oci_execute($stmt);

    $event = oci_fetch_assoc($stmt);

    oci_free_statement($stmt);
} else {
    die("Event ID not provided.");
}

oci_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
    <style>
        /* Your existing styles */
    </style>
</head>
<body>
    <div class="container">
        <div class="event-header">
            <h1><?php echo htmlspecialchars($event['ENAME']); ?></h1>
        </div>
        <div class="event-content">
            <div class="card">
                <h2>Event Details</h2>
                <p><strong>Entry Fees:</strong> <?php echo htmlspecialchars($event['ENTRY_FEES']); ?></p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($event['ETYPE']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($event['ELOCATION']); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($event['EDATE']); ?></p>
                <p><strong>Time:</strong> <?php echo htmlspecialchars($event['ETIME']); ?></p>
                <p><strong>Available Slots:</strong> <?php echo htmlspecialchars($event['SLOTS']); ?></p>
                <p><strong>Total Members:</strong> <?php echo htmlspecialchars($event['TOTAL_MEMBERS']); ?></p>
            </div>
            <div class="card">
                <h2>Prizes</h2>
                <p>1st Place: $1000</p>
                <p>2nd Place: $500</p>
                <p>3rd Place: $250</p>
            </div>
        </div>
        <div class="description card">
            <h2>Description</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque non lacus hendrerit, bibendum libero id, efficitur dolor. Aliquam erat volutpat. Donec mollis tristique mi, ac ullamcorper justo commodo sed. Cras ultricies malesuada odio, a luctus erat pulvinar sit amet.</p>
            <p>Integer et lectus quam. Nullam id turpis tincidunt, fermentum arcu in, gravida massa. Donec nec interdum libero. Vivamus in erat sit amet dolor facilisis vestibulum a ut dui. Maecenas auctor, dui at congue commodo, purus erat viverra sem, in suscipit ligula ligula sit amet eros.</p>
        </div>
        <div class="register-button">
            <button onclick="registerEvent()">Register</button>
        </div>
    </div>

    <script>
        function registerEvent() {
            <?php if (!isset($_SESSION['userid'])): ?>
                window.location.href = 'user_login.php';
            <?php else: ?>
                window.location.href = 'register.php?Eid=<?php echo $eid; ?>';
            <?php endif; ?>
        }
    </script>
</body>
</html>
