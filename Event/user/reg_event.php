<?php


require_once('tcpdf/tcpdf.php'); // Include TCPDF library

// Include configuration file
$config = include('../config.php');

// Oracle database connection parameters
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

// Get event ID associated with the registration
$queryEventId = "SELECT EID FROM REGISTRATION WHERE REGID = :regId";
$stmtEventId = oci_parse($connection, $queryEventId);
oci_bind_by_name($stmtEventId, ':regId', $regId);
oci_execute($stmtEventId);
$eventId = oci_fetch_assoc($stmtEventId)['EID'];

// Fetch event details
$eventDetails = getEventDetails($eventId, $connection);

// Fetch registration details including team information
$registrationDetails = getRegistrationDetails($regId, $connection);

// Fetch team details if team exists
$teamDetails = [];
if ($registrationDetails['TEAM'] == 'YES') {
    $teamDetails = getTeamDetails($regId, $connection);
}

// Close Oracle database connection
oci_close($connection);

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
    $content = '
        <style>
            body {
                font-family: \'Arial\', sans-serif;
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

            .event-header {
                text-align: center;
                margin-bottom: 50px;
            }

            .event-header h1 {
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
                justify-content: space-between;
                gap: 20px;
                margin-bottom: 30px;
            }

            .card {
                flex: 1;
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

            .description {
                margin-bottom: 30px;
            }

            .description h2 {
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

            .description p {
                font-size: 18px;
                line-height: 1.6;
                animation: fadeIn 1.5s ease-in-out;
            }

            .register-button {
                display: flex;
                justify-content: center;
                margin-top: 30px;
            }

            button {
                padding: 15px 30px;
                font-size: 20px;
                border: none;
                border-radius: 10px;
                cursor: pointer;
                background: #fff;
                color: #000;
                transition: background 0.3s, color 0.3s, transform 0.3s, box-shadow 0.3s;
            }

            button:hover {
                background: #000;
                color: #fff;
                transform: translateY(-5px);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
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
    ';

    // Add header content
    $content .= '
        <div class="container">
            <div class="event-header">
                <h1>PUDUCHERRY TECHNOLOGICAL UNIVERSITY</h1>
                <p>Puducherry, India</p>
            </div>
            <div class="event-content">
                <div class="card">
                    <h2>Event Details</h2>
                    <p><strong>Event Name:</strong> ' . $eventDetails['ENAME'] . '</p>
                    <p><strong>Event Type:</strong> ' . $eventDetails['ETYPE'] . '</p>
                    <p><strong>Location:</strong> ' . $eventDetails['ELOCATION'] . '</p>
                    <p><strong>Date:</strong> ' . $eventDetails['EDATE'] . '</p>
                    <p><strong>Time:</strong> ' . $eventDetails['ETIME'] . '</p>
                </div>
                <div class="card">
                    <h2>Registration Details</h2>
                    <p><strong>Name:</strong> ' . $registrationDetails['RNAME'] . '</p>
                    <p><strong>Phone Number:</strong> ' . $registrationDetails['PHONENO'] . '</p>
                    <p><strong>College:</strong> ' . $registrationDetails['COLLEGE'] . '</p>
                    <p><strong>Email:</strong> ' . $registrationDetails['EMAIL'] . '</p>
                </div>
            </div>';

    // Add team details if team exists
    if (!empty($teamDetails)) {
        $content .= '
            <div class="description">
                <h2>Team Details</h2>';
        foreach ($teamDetails as $index => $member) {
            $content .= '
                <div class="card">
                    <h2>Team Member ' . ($index + 1) . '</h2>
                    <p><strong>Name:</strong> ' . $member['RNAME'] . '</p>
                    <p><strong>College:</strong> ' . $member['COLLEGE'] . '</p>
                    <p><strong>Email:</strong> ' . $member['EMAIL'] . '</p>
                </div>';
        }
        $content .= '
            </div>';
    }

    // Add download PDF button
    $content .= '
        <div class="register-button">
            <form method="post">
                <button type="submit" name="generate_pdf">Download PDF</button>
            </form>
        </div>
    </div>';

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
    <!-- Integrate PHP-generated styles -->
    <?php echo $content; ?>
</body>
</html>
