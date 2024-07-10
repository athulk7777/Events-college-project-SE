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

// Handle search query
$searchResults = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchTerm = $_POST['search'];

    // Search query
    $query = "SELECT Eid, Ename, Entry_fees, EType, ELocation, EDate, ETime, SLOTS, TOTAL_MEMBERS FROM Events WHERE Ename LIKE :searchTerm";
    $stmt = oci_parse($connection, $query);

    $searchTerm = '%' . $searchTerm . '%';
    oci_bind_by_name($stmt, ':searchTerm', $searchTerm);

    oci_execute($stmt);

    while ($row = oci_fetch_assoc($stmt)) {
        // Fetch the photo if it exists
        $eid = $row['EID'];
        $photoQuery = "SELECT photo FROM event_photos WHERE Eid = :eid";
        $photoStmt = oci_parse($connection, $photoQuery);
        oci_bind_by_name($photoStmt, ':eid', $eid);
        oci_execute($photoStmt);
        $photoRow = oci_fetch_assoc($photoStmt);

        if ($photoRow && $photoRow['PHOTO']) {
            $row['PHOTO'] = $photoRow['PHOTO']->load();
        } else {
            $row['PHOTO'] = null;
        }

        $searchResults[] = $row;

        oci_free_statement($photoStmt);
    }

    oci_free_statement($stmt);
} else {
    // Default query to display all events
    $query = "SELECT Eid, Ename, Entry_fees, EType, ELocation, EDate, ETime, SLOTS, TOTAL_MEMBERS FROM Events";
    $stmt = oci_parse($connection, $query);
    oci_execute($stmt);

    while ($row = oci_fetch_assoc($stmt)) {
        // Fetch the photo if it exists
        $eid = $row['EID'];
        $photoQuery = "SELECT photo FROM event_photos WHERE Eid = :eid";
        $photoStmt = oci_parse($connection, $photoQuery);
        oci_bind_by_name($photoStmt, ':eid', $eid);
        oci_execute($photoStmt);
        $photoRow = oci_fetch_assoc($photoStmt);

        if ($photoRow && $photoRow['PHOTO']) {
            $row['PHOTO'] = $photoRow['PHOTO']->load();
        } else {
            $row['PHOTO'] = null;
        }

        $searchResults[] = $row;

        oci_free_statement($photoStmt);
    }

    oci_free_statement($stmt);
}

oci_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Search</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
            overflow-x: hidden;
            animation: backgroundAnimation 15s infinite alternate;
        }

        @keyframes backgroundAnimation {
            0% {
                background: linear-gradient(135deg, #fc1c03 0%, #1916c7 100%);
            }
            50% {
                background: linear-gradient(135deg, #fc1c03 0%, #1916c7 100%);
            }
            100% {
                background: linear-gradient(135deg, #fc1c03 0%, #1916c7 100%);
            }
        }

        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            color: white;
            animation: fadeInUp 1s ease-in-out;
        }

        .search-container {
            background: rgba(255, 255, 255, 0.1);
            color : white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
        }
        .search-container h2 {
            font-size: 28px;
            margin: 0;
            background: -webkit-linear-gradient(#ff9a9e, #fad0c4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            border: 2px solid;
            border-image: linear-gradient(to right, #ff9a9e, #fad0c4) 1;
            padding: 20px;
            display: inline-block;
            animation: fadeInDown 1s ease-in-out;
        }
        .search-container label {
            display: block;
            color: #fff;
            margin-bottom: 5px;
            text-align: left;
        }
        .search-container input, .search-container button {
            width: 100%;
            padding: 10px;
            color: #000;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .search-container button {
            background-color: #000;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-container button:hover {
            color: #000;
            background-color: #fff;
            transition-duration: 0.5s;
        }
        .results-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .event-card {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin: 10px;
            width: 300px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        .event-card h3 {
            font-size: 28px;
            margin: 0;
            margin-bottom: 10px;
            background: -webkit-linear-gradient(#ff9a9e, #fad0c4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            border: 2px solid;
            border-image: linear-gradient(to right, #ff9a9e, #fad0c4) 1;
            padding: 10px;
            display: inline-block;
            animation: fadeInDown 1s ease-in-out;
        }
        .event-card p {
            font-size: 18px;
            line-height: 1.6;
            animation: fadeIn 1.5s ease-in-out;
        }
        .event-photo {
            max-width: 100%;
            max-height: 200px;
            margin-bottom: 10px;
        }
        .event-card form button {
            background-color: #fff;
            color: #000;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .event-card form button:hover {
            background-color: #000;
            color: #fff;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="search-container">
            <h2>Search Events</h2>
            <form method="post" action="">
                <label for="search">Event Name:</label>
                <input type="text" id="search" name="search" required><br>
                <button type="submit">Search</button>
</form>
</div>
<div class="results-container">
<?php if (!empty($searchResults)): ?>
<?php foreach ($searchResults as $event): ?>
<div class="event-card">
<h3><?php echo htmlspecialchars($event['ENAME']); ?></h3>
<?php if ($event['PHOTO']): ?>
<img src="data:image/jpeg;base64,<?php echo base64_encode($event['PHOTO']); ?>" alt="Event Photo" class="event-photo" />
<?php endif; ?>
<p>Entry Fees: <?php echo htmlspecialchars($event['ENTRY_FEES']); ?></p>
<p>Type: <?php echo htmlspecialchars($event['ETYPE']); ?></p>
<p>Location: <?php echo htmlspecialchars($event['ELOCATION']); ?></p>
<p>Date: <?php echo htmlspecialchars($event['EDATE']); ?></p>
<p>Time: <?php echo htmlspecialchars($event['ETIME']); ?></p>
<p>Slots: <?php echo htmlspecialchars($event['SLOTS']); ?></p>
<p>Total Members: <?php echo htmlspecialchars($event['TOTAL_MEMBERS']); ?></p>
<form method="get" action="reg_event.php">
<input type="hidden" name="Eid" value="<?php echo htmlspecialchars($event['EID']); ?>">
<button type="submit">Register</button>
</form>
</div>
<?php endforeach; ?>
<?php else: ?>
<p>No events found.</p>
<?php endif; ?>
</div>
</div>

</body>
</html>
