<?php
    session_start();
// Include TCPDF library
require_once('tcpdf/tcpdf.php');

// Oracle database connection parameters
$config = include('../config.php'); // Assuming config.php contains your database credentials

$host = $config['host'];
$port = $config['port'];
$service_name = $config['service_name'];
$oracleUsername = $config['username'];
$oraclePassword = $config['password'];

// Connection string for Oracle
$connStr = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = $port))(CONNECT_DATA = (SERVICE_NAME = $service_name)))";

// Establish Oracle database connection
$connection = oci_connect($oracleUsername, $oraclePassword, $connStr);

// Check connection
if (!$connection) {
    $error = oci_error();
    die("Connection failed: " . $error['message']);
}

// Function to fetch event details
function getEventDetails($eventId, $connection) {
    $query = "SELECT ENAME, ETYPE, ELOCATION, EDATE, ETIME FROM EVENTS WHERE EID = :eventId";
    $stmt = oci_parse($connection, $query);
    oci_bind_by_name($stmt, ':eventId', $eventId);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);
    return $row;
}

// Function to fetch registration details
function getRegistrationDetails($regId, $connection) {
    $query = "SELECT RNAME, PHONENO, COLLEGE, EMAIL, TEAM FROM REGISTRATION WHERE REGID = :regId";
    $stmt = oci_parse($connection, $query);
    oci_bind_by_name($stmt, ':regId', $regId);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);
    return $row;
}

// Function to fetch team details
function getTeamDetails($regId, $connection) {
    $query = "SELECT RNAME, COLLEGE, EMAIL FROM TEAMS WHERE REGID = :regId";
    $stmt = oci_parse($connection, $query);
    oci_bind_by_name($stmt, ':regId', $regId);
    oci_execute($stmt);
    $teamMembers = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $teamMembers[] = $row;
    }
    return $teamMembers;
}

// Get registration ID from URL parameter
if (!isset($_GET['regId'])) {
    die("Registration ID not provided.");
}
$regId = $_GET['regId'];

// Fetch registration details including team information
$registrationDetails = getRegistrationDetails($regId, $connection);

// Fetch event ID associated with the registration
$queryEventId = "SELECT EID FROM REGISTRATION WHERE REGID = :regId";
$stmtEventId = oci_parse($connection, $queryEventId);
oci_bind_by_name($stmtEventId, ':regId', $regId);
oci_execute($stmtEventId);
$eventId = oci_fetch_assoc($stmtEventId)['EID'];

// Fetch event details
$eventDetails = getEventDetails($eventId, $connection);

// Fetch team details if team exists
$teamDetails = [];
if ($registrationDetails['TEAM'] == 'YES') {
    $teamDetails = getTeamDetails($regId, $connection);
}

// Close Oracle database connection
oci_close($connection);

// Initialize PDF content
$content = '';

// Check if form is submitted to generate PDF
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('Registration Details');
    $pdf->SetSubject('Event Registration');

    // Add a page
    $pdf->AddPage();

    // Set some content to display
    $content .= '
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f0f0f0;
            }
            .container {
                width: 100%;
                padding: 20px;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
            }
            .header h1 {
                color: #007bff;
                margin: 0;
            }
            .event-details, .registration-details {
                background-color: #fff;
                border: 1px solid #ccc;
                padding: 15px;
                margin-bottom: 20px;
            }
            .event-details h2, .registration-details h2 {
                color: #007bff;
                margin-top: 0;
            }
            .team-details {
                margin-top: 20px;
            }
            .team-member {
                margin-bottom: 15px;
            }
            .team-member h3 {
                color: #007bff;
                margin-top: 0;
            }
        </style>
        <div class="container">
            <div class="header">
                <h1>PUDUCHERRY TECHNOLOGICAL UNIVERSITY</h1>
                <p>Puducherry, India</p>
            </div>
            <div class="event-details">
                <h2>Event Details</h2>
                <p><strong>Event Name:</strong> ' . $eventDetails['ENAME'] . '</p>
                <p><strong>Event Type:</strong> ' . $eventDetails['ETYPE'] . '</p>
                <p><strong>Location:</strong> ' . $eventDetails['ELOCATION'] . '</p>
                <p><strong>Date:</strong> ' . $eventDetails['EDATE'] . '</p>
                <p><strong>Time:</strong> ' . $eventDetails['ETIME'] . '</p>
            </div>
            <div class="registration-details">
                <h2>Registration Details</h2>
                <p><strong>Name:</strong> ' . $registrationDetails['RNAME'] . '</p>
                <p><strong>Phone Number:</strong> ' . $registrationDetails['PHONENO'] . '</p>
                <p><strong>College:</strong> ' . $registrationDetails['COLLEGE'] . '</p>
                <p><strong>Email:</strong> ' . $registrationDetails['EMAIL'] . '</p>
            </div>
    ';

    // Add team details if team exists
    if (!empty($teamDetails)) {
        $content .= '
            <div class="team-details">
                <h2>Team Details</h2>';
        foreach ($teamDetails as $index => $member) {
            $content .= '
                <div class="team-member">
                    <h3>Team Member ' . ($index + 1) . '</h3>
                    <p><strong>Name:</strong> ' . $member['RNAME'] . '</p>
                    <p><strong>College:</strong> ' . $member['COLLEGE'] . '</p>
                    <p><strong>Email:</strong> ' . $member['EMAIL'] . '</p>
                </div>';
        }
        $content .= '</div>';
    }

    // Close container
    $content .= '</div>';

    // Print content on the PDF
    $pdf->writeHTML($content, true, false, true, false, '');

    // Close and output PDF document
    $pdf->Output('registration_details.pdf', 'D'); // D for download
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Details</title>
</head>
<body>
    <form method="post">
        <div class="container">
            <div class="header">
                <h1>PUDUCHERRY TECHNOLOGICAL UNIVERSITY</h1>
                <p>Puducherry, India</p>
            </div>
            <div class="event-details">
                <h2>Event Details</h2>
                <p><strong>Event Name:</strong> <?php echo $eventDetails['ENAME']; ?></p>
                <p><strong>Event Type:</strong> <?php echo $eventDetails['ETYPE']; ?></p>
                <p><strong>Location:</strong> <?php echo $eventDetails['ELOCATION']; ?></p>
                <p><strong>Date:</strong> <?php echo $eventDetails['EDATE']; ?></p>
                <p><strong>Time:</strong> <?php echo $eventDetails['ETIME']; ?></p>
            </div>
            <div class="registration-details">
                <h2>Registration Details</h2>
                <p><strong>Name:</strong> <?php echo $registrationDetails['RNAME']; ?></p>
                <p><strong>Phone Number:</strong> <?php echo $registrationDetails['PHONENO']; ?></p>
                <p><strong>College:</strong> <?php echo $registrationDetails['COLLEGE']; ?></p>
                <p><strong>Email:</strong> <?php echo $registrationDetails['EMAIL']; ?></p>
            </div>
            <?php if (!empty($teamDetails)): ?>
                <div class="team-details">
                    <h2>Team Details</h2>
                    <?php foreach ($teamDetails as $index => $member): ?>
                        <div class="team-member">
                            <h3>Team Member <?php echo ($index + 1); ?></h3>
                            <p><strong>Name:</strong> <?php echo $member['RNAME']; ?></p>
                            <p><strong>College:</strong> <?php echo $member['COLLEGE']; ?></p>
                            <p><strong>Email:</strong> <?php echo $member['EMAIL']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="btn-container">
                <button type="submit" name="generate_pdf">Download PDF</button>
                <a href="index.php" class="exit-btn">Exit</a>
            </div>
        </div>
    </form>
</body>
</html>

