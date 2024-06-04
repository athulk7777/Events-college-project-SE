<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['userid']) || !isset($_SESSION['designation'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$userid = $_SESSION['userid'];

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

// Get EID from Co_ord table based on UserId
$query = "SELECT Eid FROM Co_ord WHERE UserId = :userid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':userid', $userid);
oci_execute($stmt);
$eid = null;
if ($row = oci_fetch_assoc($stmt)) {
    $eid = $row['EID'];
}
oci_free_statement($stmt);

if (!$eid) {
    die("Event ID not found for the given user.");
}

// Get event type and total members from Events table based on EID
$query = "SELECT Etype, Total_Members FROM Events WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);
$etype = null;
$totalMembers = 1;
if ($row = oci_fetch_assoc($stmt)) {
    $etype = $row['ETYPE'];
    $totalMembers = $row['TOTAL_MEMBERS'];
}
oci_free_statement($stmt);

if (!$etype) {
    die("Event type not found for the given event.");
}

// Determine the team value based on the event type
$team = ($etype === 'SOLO') ? 'NO' : 'YES';

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
        for ($i = 1; $i < $totalMembers; $i++) {
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
}

include 'header.php';

oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>On-Spot Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            overflow: auto;
            background-color: #000; /* Ensure background color for visibility */
        }
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .container {
            text-align: center;
            padding: 50px;
            position: relative;
            z-index: 1;
            color: #fff;
        }
        .form-container {
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            text-align: left;
            max-width: 500px;
            margin: auto;
            overflow-y: auto; /* Add scrollbar for vertical overflow */
            max-height: 400px; /* Set maximum height for the container */
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            border-radius: 5px;
        }
        .form-container button {
            background-color: white;
            border: none;
            color: #000;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.5s, color 0.5s;
        }
        .form-container button:hover {
            background-color: #000;
            color: #fff;
        }
        h1 {
            color: #fff;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h1>On-Spot Registration</h1>
        <?php if (isset($message)) { echo "<p>$message</p>"; } ?>
        <div class="form-container">
            <form method="POST" action="">
                <label for="rname">Name:</label>
                <input type="text" id="rname" name="rname" required>

                <label for="phoneno">Phone Number:</label>
                <input type="text" id="phoneno" name="phoneno" required>

                <label for="college">College:</label>
                <input type="text" id="college" name="college" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <?php if ($team === 'YES'): ?>
                    <p>Team Members:</p>
                    <?php for ($i = 1; $i < $totalMembers; $i++): ?>
                        <p style="margin-top: 20px;">Team Member <?php echo $i; ?>:</p>
                        <label for="team_rname_<?php echo $i; ?>">Name:</label>
                        <input type="text" id="team_rname_<?php echo $i; ?>" name="team_rname_<?php echo $i; ?>">

                        <label for="team_college_<?php echo $i; ?>">College:</label>
                        <input type="text" id="team_college_<?php echo $i; ?>" name="team_college_<?php echo $i; ?>">

                        <label for="team_email_<?php echo $i; ?>">Email:</label>
                        <input type="email" id="team_email_<?php echo $i; ?>" name="team_email_<?php echo $i; ?>">
                    <?php endfor; ?>
                <?php endif; ?>

                <button type="submit">Register</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        particlesJS('particles-js', {
            "particles": {
                "number": {
                    "value": 80,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },
                "color": {
                    "value": "#ffffff"
                },
                "shape": {
                    "type": "circle",
                    "stroke": {
                        "width": 0,
                        "color": "#000000"
                    },
                    "polygon": {
                        "nb_sides": 5
                    }
                },
                "opacity": {
                    "value": 0.5,
                    "random": false
                },
                "size": {
                    "value": 3,
                    "random": true
                },
                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#ffffff",
                    "opacity": 0.4,
                    "width": 1
                },
                "move": {
                    "enable": true,
                    "speed": 6,
                    "direction": "none",
                    "random": false,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "repulse"
                    },
                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },
                    "resize": true
                },
                "modes": {
                    "grab": {
                        "distance": 400,
                        "line_linked": {
                            "opacity": 1
                        }
                    },
                    "bubble": {
                        "distance": 400,
                        "size": 40,
                        "duration": 2,
                        "opacity": 8,
                        "speed": 3
                    },
                    "repulse": {
                        "distance": 200,
                        "duration": 0.4
                    },
                    "push": {
                        "particles_nb": 4
                    },
                    "remove": {
                        "particles_nb": 2
                    }
                }
            },
            "retina_detect": true
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
