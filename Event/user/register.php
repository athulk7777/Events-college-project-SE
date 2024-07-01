<?php
session_start();

// Configuration and Database Connection
$config = include('../config.php');

$host = $config['host'];
$port = $config['port'];
$service_name = $config['service_name'];
$oracleUsername = $config['username'];
$oraclePassword = $config['password'];

$connStr = "(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = $port))(CONNECT_DATA = (SERVICE_NAME = $service_name)))";
$connection = oci_connect($oracleUsername, $oraclePassword, $connStr);

if (!$connection) {
    $error = oci_error();
    die("Connection failed: " . $error['message']);
}

// Get Eid from URL
if (!isset($_GET['Eid'])) {
    die("Event ID not provided.");
}
$eid = $_GET['Eid'];

// Fetch event type and total members
$query = "SELECT Etype, Total_Members FROM Events WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);

$event = oci_fetch_assoc($stmt);
$eventType = $event['ETYPE'];
$totalMembers = $event['TOTAL_MEMBERS'];

oci_free_statement($stmt);

// Fetch user details from the session email
$email = $_SESSION['email'];
// $email = "abhinavs1954@gmail.com"; // For testing purposes
$query = "SELECT UNAME, PHONENO, COLLEGE, EMAIL FROM USER_DETAILS WHERE EMAIL = :email";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':email', $email);
oci_execute($stmt);

$userDetails = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

// Function to generate a unique RegId
function generateUniqueRegId($connection) {
    $query = "SELECT REGID_SQ.NEXTVAL AS REGID FROM DUAL";
    $stmt = oci_parse($connection, $query);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);
    $regId = $row['REGID'];
    oci_free_statement($stmt);
    return $regId;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $regId = generateUniqueRegId($connection);
    $rName = $_POST['rname'];
    $phoneNo = $_POST['phoneno'];
    $college = $_POST['college'];
    $email = $_POST['email'];
    $team = ($eventType === 'SOLO') ? 'NO' : 'YES';

    $query = "INSERT INTO Registration (RegId, Eid, RName, PhoneNo, College, Email, TEAM)
              VALUES (:regid, :eid, :rname, :phoneno, :college, :email, :team)";
    $stmt = oci_parse($connection, $query);
    oci_bind_by_name($stmt, ':regid', $regId);
    oci_bind_by_name($stmt, ':eid', $eid);
    oci_bind_by_name($stmt, ':rname', $rName);
    oci_bind_by_name($stmt, ':phoneno', $phoneNo);
    oci_bind_by_name($stmt, ':college', $college);
    oci_bind_by_name($stmt, ':email', $email);
    oci_bind_by_name($stmt, ':team', $team);

    if (oci_execute($stmt)) {
        $message = "Registration successful!";
    } else {
        $error = oci_error($stmt);
        $message = "Error: " . $error['message'];
    }
    oci_free_statement($stmt);

    // Handle team members if event is a team event
    if ($team === 'YES') {
        $teamQuery = "INSERT INTO Teams (RegId, RName, College, Email) VALUES (:regid, :rname, :college, :email)";
        $teamStmt = oci_parse($connection, $teamQuery);
        for ($i = 1; $i <= $totalMembers; $i++) {
            $teamRName = $_POST["team_rname_$i"];
            $teamCollege = $_POST["team_college_$i"];
            $teamEmail = $_POST["team_email_$i"];
            if (!empty($teamRName) && !empty($teamCollege) && !empty($teamEmail)) {
                oci_bind_by_name($teamStmt, ':regid', $regId);
                oci_bind_by_name($teamStmt, ':rname', $teamRName);
                oci_bind_by_name($teamStmt, ':college', $teamCollege);
                oci_bind_by_name($teamStmt, ':email', $teamEmail);
                oci_execute($teamStmt);
            }
        }
        oci_free_statement($teamStmt);
    }

    // Redirect to registration completion page after successful registration
    if (!isset($error)) {
        echo "<script>
                var regId = '$regId';
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('regId').value = regId;
                    document.getElementById('paymentModal').style.display = 'block';
                });
              </script>";
    }
}

oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ff0303 0%, #2923d9 100%);
            overflow-x: hidden;
            animation: backgroundAnimation 15s infinite alternate;
        }

        @keyframes backgroundAnimation {
            0%, 100% {
                background: linear-gradient(135deg, #ff0303 0%, #2923d9 100%);
            }
            50% {
                background: linear-gradient(135deg, #2923d9 0%, #ff0303 100%);
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
            flex-direction: column;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 18px;
            margin-bottom: 5px;
            display: block;
            color: #ff9a9e;
            animation: fadeIn 1.5s ease-in-out;
        }

        .form-group input,
        .form-group select {
            width: calc(100% - 20px);
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: none;
            outline: none;
            background: rgba(255, 255, 255, 0.9);
            transition: box-shadow 0.3s, background 0.3s;
            animation: fadeIn 1.5s ease-in-out;
        }

        .form-group input:focus,
        .form-group select:focus {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .form-group select {
            appearance: none;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMCIgaGVpZ2h0PSIxMCIgdmlld0JveD0iMCAwIDEwIDEwIj48cGF0aCBkPSJNNSA3LjVsMy41LTMuNWgtM3YtM2gtM3YzaC0zeiIgZmlsbD0iIzAwMCIvPjwvc3ZnPg==');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 10px;
        }

        .btn {
            padding: 15px 30px;
            font-size: 18px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
            color: #fff;
            transition: transform 0.3s, box-shadow 0.3s;
            animation: fadeInUp 1.5s ease-in-out;
        }

        .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .team-member {
            margin-bottom: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
        }

        .team-member h3 {
            font-size: 24px;
            margin-bottom: 10px;
            background: -webkit-linear-gradient(#ff9a9e, #fad0c4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            border: 2px solid;
            border-image: linear-gradient(to right, #ff9a9e, #fad0c4) 1;
            padding: 5px;
            display: inline-block;
            animation: fadeInDown 1s ease-in-out;
        }

        .message {
            font-size: 18px;
            color: #ff9a9e;
            margin-top: 20px;
            text-align: center;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="event-header">
            <h1>Event Registration</h1>
        </div>
        <div class="event-content">
            <div class="card">
                <h2>User Details</h2>
                <form id="registrationForm" action="" method="POST">
                    <div class="form-group">
                        <label for="rname">Name:</label>
                        <input type="text" id="rname" name="rname" value="<?php echo $userDetails['UNAME']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="phoneno">Phone Number:</label>
                        <input type="text" id="phoneno" name="phoneno" value="<?php echo $userDetails['PHONENO']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="college">College:</label>
                        <input type="text" id="college" name="college" value="<?php echo $userDetails['COLLEGE']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="text" id="email" name="email" value="<?php echo $userDetails['EMAIL']; ?>" readonly>
                    </div>
                    <?php if ($eventType === 'TEAM'): ?>
                        <h2>Team Members</h2>
                        <?php for ($i = 1; $i <= $totalMembers; $i++): ?>
                            <div class="team-member">
                                <h3>Team Member <?php echo $i; ?></h3>
                                <div class="form-group">
                                    <label for="team_rname_<?php echo $i; ?>">Name:</label>
                                    <input type="text" id="team_rname_<?php echo $i; ?>" name="team_rname_<?php echo $i; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="team_college_<?php echo $i; ?>">College:</label>
                                    <input type="text" id="team_college_<?php echo $i; ?>" name="team_college_<?php echo $i; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="team_email_<?php echo $i; ?>">Email:</label>
                                    <input type="text" id="team_email_<?php echo $i; ?>" name="team_email_<?php echo $i; ?>">
                                </div>
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                    <button type="button" class="btn" onclick="openPaymentModal()">Register</button>
                </form>
                <?php if (isset($message)): ?>
                    <p class="message"><?php echo $message; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePaymentModal()">&times;</span>
            <h2>Payment</h2>
            <form id="paymentForm" action="reg_complete.php" method="GET">
                <input type="hidden" id="regId" name="regId" value="">
                <button type="button" onclick="processPayment()">Pay Now</button>
            </form>
        </div>
    </div>

    <script>
        function openPaymentModal() {
            var form = document.getElementById("registrationForm");
            if (form.reportValidity()) {
                form.submit();
            }
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function processPayment() {
            var paymentForm = document.getElementById("paymentForm");
            paymentForm.submit();
        }
    </script>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</body>
</html>