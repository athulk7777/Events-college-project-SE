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

$userEmail = $_SESSION['email']; // Assuming email is stored in the session

// Fetch registered events
$query = "SELECT Registration.RegId, Events.Ename, Events.Entry_fees, Events.EType, Events.ELocation, Events.EDate, Events.ETime, 
                 Registration.RName, Registration.PhoneNo, Registration.College, Registration.Email, Registration.TEAM
          FROM Registration 
          JOIN Events ON Registration.Eid = Events.Eid 
          WHERE Registration.Email = :email";

$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':email', $userEmail);
oci_execute($stmt);

$events = [];
while ($row = oci_fetch_assoc($stmt)) {
    if ($row['TEAM'] === 'YES') {
        $teamQuery = "SELECT RName, College, Email FROM Teams WHERE RegId = :regid";
        $teamStmt = oci_parse($connection, $teamQuery);
        oci_bind_by_name($teamStmt, ':regid', $row['REGID']);
        oci_execute($teamStmt);

        $teamMembers = [];
        while ($teamRow = oci_fetch_assoc($teamStmt)) {
            $teamMembers[] = $teamRow;
        }
        $row['TEAM_MEMBERS'] = $teamMembers;
        oci_free_statement($teamStmt);
    }
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
    <title>My Registered Events</title>
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
            color: #fff;
            animation: fadeInUp 1s ease-in-out;
        }

        .header {
            text-align: center;
            margin-bottom: 50px;
        }

        .header h1 {
            font-size: 48px;
            margin: 0;
            background: -webkit-linear-gradient(#ff9a9e, #fad0c4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            border: 2px solid;
            border-image: linear-gradient(to right, #ff9a9e, #fad0c4) 1;
            padding: 10px;
            display: inline-block;
            animation: fadeInDown 1s ease-in-out;
        }

        .event-content {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            flex: 1 1 calc(33.333% - 20px);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .card h2 {
            font-size: 28px;
            margin-bottom: 20px;
            background: -webkit-linear-gradient(#ff9a9e, #fad0c4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            border: 2px solid;
            border-image: linear-gradient(to right, #ff9a9e, #fad0c4) 1;
            padding: 5px;
            display: inline-block;
            animation: fadeInDown 1s ease-in-out;
        }

        .card p {
            font-size: 18px;
            line-height: 1.6;
            animation: fadeIn 1.5s ease-in-out;
        }

        .team-members {
            margin-top: 20px;
        }

        .team-members h3 {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .team-members ul {
            list-style: none;
            padding: 0;
        }

        .team-members li {
            font-size: 18px;
            line-height: 1.6;
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
        <div class="header">
            <h1>My Registered Events</h1>
        </div>
        <div class="event-content">
            <?php if (empty($events)): ?>
                <p>No registered events found.</p>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="card">
                        <h2><?php echo htmlspecialchars($event['ENAME']); ?></h2>
                        <p><strong>Entry Fees:</strong> <?php echo htmlspecialchars($event['ENTRY_FEES']); ?></p>
                        <p><strong>Type:</strong> <?php echo htmlspecialchars($event['ETYPE']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($event['ELOCATION']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($event['EDATE']); ?></p>
                        <p><strong>Time:</strong> <?php echo htmlspecialchars($event['ETIME']); ?></p>
                        <p><strong>Registrant Name:</strong> <?php echo htmlspecialchars($event['RNAME']); ?></p>
                        <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($event['PHONENO']); ?></p>
                        <p><strong>College:</strong> <?php echo htmlspecialchars($event['COLLEGE']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($event['EMAIL']); ?></p>
                        <p><strong>Team Registration:</strong> <?php echo htmlspecialchars($event['TEAM']); ?></p>
                        <?php if ($event['TEAM'] === 'YES' && !empty($event['TEAM_MEMBERS'])): ?>
                            <div class="team-members">
                                <h3>Team Members:</h3>
                                <ul>
                                    <?php foreach ($event['TEAM_MEMBERS'] as $member): ?>
                                        <li><strong>Name:</strong> <?php echo htmlspecialchars($member['RNAME']); ?>, <strong>College:</strong> <?php echo htmlspecialchars($member['COLLEGE']); ?>, <strong>Email:</strong> <?php echo htmlspecialchars($member['EMAIL']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
