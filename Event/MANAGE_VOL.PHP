<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['userid'])) {
    header('Location: CO-ORD_LOGIN.PHP'); // Redirect to login page if not logged in
    exit();
}

$userid = $_SESSION['userid'];

// Database configuration
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
    die("Connection failed: " . htmlspecialchars($error['message']));
}

include 'header.php';

// Fetch Event ID of the logged-in coordinator
$query = "SELECT Eid FROM Co_ord WHERE UserId = :userid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':userid', $userid);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$eid = $row['EID'];
oci_free_statement($stmt);

if (!$eid) {
    die("Event ID not found for the given user.");
}

// Handle delete, approve, and remove actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $regno = $_POST['regno'];
        $query = "DELETE FROM Volunteer WHERE REGNO = :regno AND Eid = :eid";
        $stmt = oci_parse($connection, $query);
        oci_bind_by_name($stmt, ':regno', $regno);
        oci_bind_by_name($stmt, ':eid', $eid);
        oci_execute($stmt);
        oci_free_statement($stmt);
    }

    if (isset($_POST['approve'])) {
        $regno = $_POST['regno'];
        $query = "SELECT * FROM Volunteer WHERE REGNO = :regno AND Eid = :eid";
        $stmt = oci_parse($connection, $query);
        oci_bind_by_name($stmt, ':regno', $regno);
        oci_bind_by_name($stmt, ':eid', $eid);
        oci_execute($stmt);
        $volunteer = oci_fetch_assoc($stmt);
        oci_free_statement($stmt);

        if ($volunteer) {
            // Insert into Co_ord
            $query = "INSERT INTO Co_ord (Cid, Eid, Regno, CName, UserId, Pwd, PHONENO, Designation, prev_pwd)
                      VALUES (CID_SEQ.NEXTVAL, :eid, :regno, :cname, :userid, :pwd, :phoneno, 'VOLUNTEER', DBMS_RANDOM.STRING('X', 10))";
            $stmt = oci_parse($connection, $query);
            oci_bind_by_name($stmt, ':eid', $volunteer['EID']);
            oci_bind_by_name($stmt, ':regno', $volunteer['REGNO']);
            oci_bind_by_name($stmt, ':cname', $volunteer['CNAME']);
            oci_bind_by_name($stmt, ':userid', $volunteer['USERID']);
            oci_bind_by_name($stmt, ':pwd', $volunteer['PWD']);
            oci_bind_by_name($stmt, ':phoneno', $volunteer['PHONENO']);
            oci_execute($stmt);
            oci_free_statement($stmt);

            // Delete from Volunteer
            $query = "DELETE FROM Volunteer WHERE REGNO = :regno AND Eid = :eid";
            $stmt = oci_parse($connection, $query);
            oci_bind_by_name($stmt, ':regno', $regno);
            oci_bind_by_name($stmt, ':eid', $eid);
            oci_execute($stmt);
            oci_free_statement($stmt);
        }
    }

    if (isset($_POST['remove'])) {
        $regno = $_POST['regno'];
        $query = "DELETE FROM Co_ord WHERE REGNO = :regno AND Eid = :eid AND Designation = 'VOLUNTEER'";
        $stmt = oci_parse($connection, $query);
        oci_bind_by_name($stmt, ':regno', $regno);
        oci_bind_by_name($stmt, ':eid', $eid);
        oci_execute($stmt);
        oci_free_statement($stmt);
    }
}

// Fetch volunteers for the joining requests list
$query = "SELECT REGNO, CNAME, USERID, PHONENO FROM Volunteer WHERE Eid = :eid";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);

$volunteers = [];
while ($row = oci_fetch_assoc($stmt)) {
    $volunteers[] = $row;
}
oci_free_statement($stmt);

// Fetch approved volunteers
$query = "SELECT REGNO, CNAME, PHONENO FROM Co_ord WHERE Eid = :eid AND Designation = 'VOLUNTEER'";
$stmt = oci_parse($connection, $query);
oci_bind_by_name($stmt, ':eid', $eid);
oci_execute($stmt);

$approvedVolunteers = [];
while ($row = oci_fetch_assoc($stmt)) {
    $approvedVolunteers[] = $row;
}
oci_free_statement($stmt);

oci_close($connection); // Close the database connection when done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Volunteers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #000;
            color: #fff;
        }
        header, footer {
            background-color: #333;
            color: #fff;
            padding: 10px;
            text-align: center;
        }
        .container {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .joining-requests, .approved-volunteers {
            background-color: #444;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
            padding: 20px;
            margin: 10px 0;
        }
        h2 {
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #555;
        }
        button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            color: #000;
            background-color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }
        button:hover {
            background-color: #000;
            color: #fff;
        }
        button.delete {
            background-color: #fff;
        }
        button.remove {
            background-color: #fff;
        }
        button.remove:hover {
            background-color: #000;
        }
        button.delete:hover {
            background-color: #000;
        }
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <header>
        <h1>Volunteer Management System</h1>
    </header>
    <div class="container">
        <div class="joining-requests">
            <h2>Joining Requests</h2>
            <table>
                <tr>
                    <th>Regno</th>
                    <th>Name</th>
                    <th>UserID</th>
                    <th>Phone No</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($volunteers as $volunteer): ?>
                <tr>
                    <td><?php echo htmlspecialchars($volunteer['REGNO']); ?></td>
                    <td><?php echo htmlspecialchars($volunteer['CNAME']); ?></td>
                    <td><?php echo htmlspecialchars($volunteer['USERID']); ?></td>
                    <td><?php echo htmlspecialchars($volunteer['PHONENO']); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="regno" value="<?php echo htmlspecialchars($volunteer['REGNO']); ?>">
                            <button type="submit" name="approve">Approve</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="regno" value="<?php echo htmlspecialchars($volunteer['REGNO']); ?>">
                            <button type="submit" name="delete" class="delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="approved-volunteers">
            <h2>Approved Volunteers</h2>
            <table>
                <tr>
                    <th>Regno</th>
                    <th>Name</th>
                    <th>Phone No</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($approvedVolunteers as $volunteer): ?>
                <tr>
                    <td><?php echo htmlspecialchars($volunteer['REGNO']); ?></td>
                    <td><?php echo htmlspecialchars($volunteer['CNAME']); ?></td>
                    <td><?php echo htmlspecialchars($volunteer['PHONENO']); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="regno" value="<?php echo htmlspecialchars($volunteer['REGNO']); ?>">
                            <button type="submit" name="remove" class="remove">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
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
                    "value": "#fff"
                },
                "shape": {
                    "type": "circle",
                    "stroke": {
                        "width": 0,
                        "color": "#fff"
                    },
                    "polygon": {
                        "nb_sides": 5
                    },
                    "image": {
                        "src": "img/github.svg",
                        "width": 100,
                        "height": 100
                    }
                },
                "opacity": {
                    "value": 0.5,
                    "random": false,
                    "anim": {
                        "enable": false,
                        "speed": 1,
                        "opacity_min": 0.1,
                        "sync": false
                    }
                },
                "size": {
                    "value": 3,
                    "random": true,
                    "anim": {
                        "enable": false,
                        "speed": 40,
                        "size_min": 0.1,
                        "sync": false
                    }
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
                    "bounce": false,
                    "attract": {
                        "enable": false,
                        "rotateX": 600,
                        "rotateY": 1200
                    }
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
