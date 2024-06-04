<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['userid']) || !isset($_SESSION['designation']) || $_SESSION['designation'] !== 'ADMIN') {
    header('Location: login.php'); // Redirect to login page if not logged in as admin
    exit();
}

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

// Handle event addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $ename = $_POST['ename'];
    $entry_fees = $_POST['entry_fees'];
    $etype = $_POST['etype'];
    $elocation = $_POST['elocation'];
    $edate = $_POST['edate'];
    $etime = $_POST['etime'];
    $slots = $_POST['slots'];
    $total_members = $_POST['total_members'];

    $query = "INSERT INTO Events (Eid, Ename, Entry_fees, EType, ELocation, EDate, ETime, SLOTS, TOTAL_MEMBERS) VALUES (EVENTS_SEQ.NEXTVAL, :ename, :entry_fees, :etype, :elocation, :edate, :etime, :slots, :total_members)";
    $stmt = oci_parse($connection, $query);

    oci_bind_by_name($stmt, ':ename', $ename);
    oci_bind_by_name($stmt, ':entry_fees', $entry_fees);
    oci_bind_by_name($stmt, ':etype', $etype);
    oci_bind_by_name($stmt, ':elocation', $elocation);
    oci_bind_by_name($stmt, ':edate', $edate);
    oci_bind_by_name($stmt, ':etime', $etime);
    oci_bind_by_name($stmt, ':slots', $slots);
    oci_bind_by_name($stmt, ':total_members', $total_members);

    oci_execute($stmt);
    oci_free_statement($stmt);
}

// Handle event deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    $eid = $_POST['eid'];

    $query = "DELETE FROM Events WHERE Eid = :eid";
    $stmt = oci_parse($connection, $query);
    oci_bind_by_name($stmt, ':eid', $eid);

    oci_execute($stmt);
    oci_free_statement($stmt);
}

// Retrieve all events
$query = "SELECT * FROM Events";
$stmt = oci_parse($connection, $query);
oci_execute($stmt);
$events = [];
while ($row = oci_fetch_assoc($stmt)) {
    $events[] = $row;
}
oci_free_statement($stmt);

oci_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        .header {
            background: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }
        .header h1 {
            margin: 0;
        }
        .form-container, .table-container {
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-container h2, .table-container h2 {
            margin-bottom: 20px;
        }
        .form-container form, .table-container table {
            width: 100%;
        }
        .form-container input[type="text"], .form-container input[type="number"], .form-container input[type="date"], .form-container input[type="time"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
        }
        .form-container button, .table-container button {
            background: #333;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
        }
        .form-container button:hover, .table-container button:hover {
            background: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background: #333;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Panel</h1>
    </div>
    <div class="container">
        <div class="form-container">
            <h2>Admin Event Updation form</h2>
            <form method="post" action="">
                <input type="text" name="ename" placeholder="Event Name" required>
                <input type="number" name="entry_fees" placeholder="Entry Fees" required>
                <input type="text" name="etype" placeholder="Event Type" required>
                <input type="text" name="elocation" placeholder="Event Location" required>
                <input type="date" name="edate" placeholder="Event Date" required>
                <input type="time" name="etime" placeholder="Event Time" required>
                <input type="number" name="slots" placeholder="Slots" required>
                <input type="number" name="total_members" placeholder="Total Members" required>
            </form>
        </div>

        <div class="table-container">
            <h2>View Events</h2>
            <table>
                <tr>
                    <th>Event ID</th>
                    <th>Name</th>
                    <th>Entry Fees</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Slots</th>
                    <th>Total Members</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($events as $event) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($event['EID']); ?></td>
                    <td><?php echo htmlspecialchars($event['ENAME']); ?></td>
                    <td><?php echo htmlspecialchars($event['ENTRY_FEES']); ?></td>
                    <td><?php echo htmlspecialchars($event['ETYPE']); ?></td>
                    <td><?php echo htmlspecialchars($event['ELOCATION']); ?></td>
                    <td><?php echo htmlspecialchars($event['EDATE']); ?></td>
                    <td><?php echo htmlspecialchars($event['ETIME']); ?></td>
                    <td><?php echo htmlspecialchars($event['SLOTS']); ?></td>
                    <td><?php echo htmlspecialchars($event['TOTAL_MEMBERS']); ?></td>
                    <td>
                        <form method="post" action="" style="display:inline;">
                            <input type="hidden" name="eid" value="<?php echo htmlspecialchars($event['EID']); ?>">
                            <button type="submit" name="delete_event">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>
</html>
