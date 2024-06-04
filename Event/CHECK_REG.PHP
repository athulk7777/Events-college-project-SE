<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['userid']) || !isset($_SESSION['designation'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$userid = $_SESSION['userid'];
$designation = $_SESSION['designation'];

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

// Handle delete request
if (isset($_POST['delete'])) {
    $regId = $_POST['regId'];
    $query = "DELETE FROM Registration WHERE RegId = :regId";
    $stmt = oci_parse($connection, $query);
    oci_bind_by_name($stmt, ':regId', $regId);
    oci_execute($stmt);
    oci_free_statement($stmt);
    header("Location: CHECK_REG.php");
    exit();
}

// Retrieve all registrations for the specific event
$query = "SELECT RegId, RName, PhoneNo, College, Email FROM Registration WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);
$registrations = [];
while ($row = oci_fetch_assoc($stmt)) {
    // Get the total number of members for each registration
    $regId = $row['REGID'];
    $memberQuery = "SELECT COUNT(*) AS MEMBER_COUNT FROM Teams WHERE RegId = :regId";
    $memberStmt = oci_parse($connection, $memberQuery);
    oci_bind_by_name($memberStmt, ':regId', $regId);
    oci_execute($memberStmt);
    $memberCountRow = oci_fetch_assoc($memberStmt);
    $memberCount = $memberCountRow['MEMBER_COUNT'] + 1; // Add 1 to include the main registrant
    oci_free_statement($memberStmt);

    $row['TOTAL_MEMBERS'] = $memberCount;
    $registrations[] = $row;
}
oci_free_statement($stmt);

if ($designation === 'CO-ORD') {
    include 'header.php';
}

oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Registrations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            overflow: auto;
        }
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-color: #000;
        }
        .container {
            text-align: center;
            padding: 50px;
            position: relative;
            z-index: 1;
        }
        .details {
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            text-align: left;
            margin-bottom: 0px;
        }
        .details label {
            display: block;
            color: #fff;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        td {
            color: white;
        }
        th {
            background-color: #333;
            color: white;
        }
        h1 {
            padding-top: 30px;
            color: #fff;
        }
        .btn {
            background-color: white;
            border: none;
            color: #000;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #000;
            transition-duration: 0.5s;
            border: none;
            color: #fff;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <h1>Event Registrations</h1>
        <div class="details">
            <h2>Registrations for Event ID: <?php echo htmlspecialchars($eid); ?></h2>
            <table>
                
                <tr>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>College</th>
                    <th>Email</th>
                    <th>Total Members</th>
                    <?php if ($registration['TOTAL_MEMBERS'] > 1) { ?>
                    <th>Action</th>
                    <?php } ?>
                </tr>
                <?php foreach ($registrations as $registration) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($registration['RNAME']); ?></td>
                    <td><?php echo htmlspecialchars($registration['PHONENO']); ?></td>
                    <td><?php echo htmlspecialchars($registration['COLLEGE']); ?></td>
                    <td><?php echo htmlspecialchars($registration['EMAIL']); ?></td>
                    <td><?php echo htmlspecialchars($registration['TOTAL_MEMBERS']); ?></td>
                    
                    <?php if ($registration['TOTAL_MEMBERS'] > 1) { ?>
                    <td style="border-color: #000">
                        <?php if ($designation !== 'VOLUNTEER') { ?>
                            <form method="post" style="margin: 0; display:inline;">
                                <input type="hidden" name="regId" value="<?php echo htmlspecialchars($registration['REGID']); ?>">
                                <button type="submit" name="delete" class="btn">Remove</button>
                            </form>
                        <?php } ?>
                            <form method="get" action="view_memb.php" style="margin: 0; display:inline;">
                                <input type="hidden" name="regId" value="<?php echo htmlspecialchars($registration['REGID']); ?>">
                                <button type="submit" class="btn">View Members</button>
                            </form>
                    </td>
                    
                    <?php } ?>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <script src="js/particles.min.js"></script>
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
